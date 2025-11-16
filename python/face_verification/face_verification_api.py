# face_verification_api.py
import os
import io
import time
import math
import statistics
import logging
from concurrent.futures import ThreadPoolExecutor, as_completed
from functools import lru_cache

import numpy as np
import cv2
import requests
from flask import Flask, request, jsonify
from flask_cors import CORS

import face_recognition

try:
    import redis
except Exception:
    redis = None

from dotenv import load_dotenv
load_dotenv()

APP_PORT = int(os.environ.get("FV_API_PORT", 5002))
MAX_FRAMES = int(os.environ.get("FV_MAX_FRAMES", 2))  
FRAME_MAX_PIXELS = int(os.environ.get("FV_FRAME_MAX_PIXELS", 800*800))
PROFILE_CACHE_TTL = int(os.environ.get("FV_PROFILE_TTL", 60 * 60 * 6)) 
REQUEST_TIMEOUT = int(os.environ.get("FV_REQUEST_TIMEOUT", 10)) 

MAX_ATTEMPTS = int(os.environ.get("FV_MAX_ATTEMPTS", 5))
ATTEMPT_WINDOW = int(os.environ.get("FV_ATTEMPT_WINDOW", 10 * 60))

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("face-verif")

app = Flask(__name__)
CORS(app)

VERIFY_ATTEMPTS = {}
_PROFILE_CACHE = {}  

REDIS_URL = os.environ.get("REDIS_URL")
redis_client = None
if REDIS_URL and redis:
    try:
        redis_client = redis.from_url(REDIS_URL, decode_responses=False)
        logger.info("Using Redis for caching and rate-limiting")
    except Exception as e:
        logger.warning("Failed to connect to Redis: %s", e)
        redis_client = None

def resize_if_needed(img_rgb):
    h, w = img_rgb.shape[:2]
    if h * w <= FRAME_MAX_PIXELS:
        return img_rgb
    scale = math.sqrt(FRAME_MAX_PIXELS / float(h * w))
    new_w, new_h = int(w * scale), int(h * scale)
    img_small = cv2.resize(img_rgb, (new_w, new_h), interpolation=cv2.INTER_AREA)
    return img_small

def load_image_from_file_storage(file_storage):
    data = file_storage.read()
    if not data:
        return None
    arr = np.frombuffer(data, np.uint8)
    img = cv2.imdecode(arr, cv2.IMREAD_COLOR)
    if img is None:
        return None
    img_rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    return resize_if_needed(img_rgb)

def load_image_from_url(url):
    try:
        with requests.get(url, timeout=REQUEST_TIMEOUT, stream=True) as resp:
            resp.raise_for_status()
            data = resp.content
    except Exception as e:
        logger.exception("Failed to fetch profile image: %s", e)
        return None
    arr = np.frombuffer(data, np.uint8)
    img = cv2.imdecode(arr, cv2.IMREAD_COLOR)
    if img is None:
        logger.warning("cv2 failed to decode profile image.")
        return None
    img_rgb = cv2.cvtColor(img, cv2.COLOR_BGR2RGB)
    return resize_if_needed(img_rgb)

def _cache_set_profile_encoding(user_id, encoding_vec):
    now = time.time()
    if redis_client:
        buf = io.BytesIO()
        np.save(buf, encoding_vec, allow_pickle=False)
        buf.seek(0)
        redis_client.setex(f"fv_profile_enc:{user_id}", PROFILE_CACHE_TTL, buf.read())
    else:
        _PROFILE_CACHE[user_id] = {"encoding": encoding_vec, "ts": now}

def _cache_get_profile_encoding(user_id):
    if redis_client:
        data = redis_client.get(f"fv_profile_enc:{user_id}")
        if not data:
            return None
        buf = io.BytesIO(data)
        buf.seek(0)
        try:
            arr = np.load(buf, allow_pickle=False)
            return arr
        except Exception:
            return None
    else:
        entry = _PROFILE_CACHE.get(user_id)
        if not entry:
            return None
        if time.time() - entry["ts"] > PROFILE_CACHE_TTL:
            _PROFILE_CACHE.pop(user_id, None)
            return None
        return entry["encoding"]

def get_or_build_profile_encoding(user_id, profile_url):
    """Return cached encoding or compute and cache it. This is the single slow step."""
    enc = _cache_get_profile_encoding(user_id)
    if enc is not None:
        return enc

    img = load_image_from_url(profile_url)
    if img is None:
        return None

    encs = face_recognition.face_encodings(img, num_jitters=0)
    if not encs:
        return None
    enc = encs[0]
    _cache_set_profile_encoding(user_id, enc)
    return enc

def incr_attempt(user_id):
    now = time.time()
    if redis_client:
        key = f"fv_attempts:{user_id}"
        new = redis_client.incr(key)
        if new == 1:
            redis_client.expire(key, ATTEMPT_WINDOW)
        return int(new)
    else:
        entry = VERIFY_ATTEMPTS.get(user_id)
        if not entry:
            VERIFY_ATTEMPTS[user_id] = {"count": 1, "ts": now}
            return 1
        if now - entry["ts"] > ATTEMPT_WINDOW:
            VERIFY_ATTEMPTS[user_id] = {"count": 1, "ts": now}
            return 1
        entry["count"] += 1
        entry["ts"] = now
        return entry["count"]

def get_attempt_count(user_id):
    if redis_client:
        key = f"fv_attempts:{user_id}"
        val = redis_client.get(key)
        if not val:
            return 0
        return int(val)
    else:
        entry = VERIFY_ATTEMPTS.get(user_id)
        if not entry:
            return 0
        if time.time() - entry["ts"] > ATTEMPT_WINDOW:
            return 0
        return entry["count"]

def reset_attempts(user_id):
    if redis_client:
        redis_client.delete(f"fv_attempts:{user_id}")
    else:
        VERIFY_ATTEMPTS.pop(user_id, None)

def process_frame_for_distance(frame_rgb, profile_enc):
    """
    Return distance or None if no face/encoding found.
    We keep this function CPU bound but run in ThreadPool to utilize multiple cores.
    """
    try:
        locations = face_recognition.face_locations(frame_rgb, model='hog')
        if not locations:
            return None
        encs = face_recognition.face_encodings(frame_rgb, known_face_locations=[locations[0]], num_jitters=0)
        if not encs:
            return None
        d = float(np.linalg.norm(profile_enc - encs[0]))
        return d
    except Exception as e:
        logger.exception("Error processing frame: %s", e)
        return None

@app.route("/health", methods=["GET"])
def health():
    return jsonify({"status": "ok"}), 200

@app.route("/verify-user", methods=["POST"])
def verify_user():
    start_ts = time.time()

    user_id = request.form.get("user_id")
    profile_url = request.form.get("profile_url")
    frames_files = request.files.getlist("frames")

    if not user_id or not profile_url or not frames_files:
        return jsonify({"is_verified": False, "message": "missing data"}), 400

    attempts = get_attempt_count(user_id)
    if attempts >= MAX_ATTEMPTS:
        return jsonify({"is_verified": False, "message": f"Too many attempts. Try again later."}), 429

    if len(frames_files) > MAX_FRAMES:
        frames_files = frames_files[:MAX_FRAMES]

    profile_enc = get_or_build_profile_encoding(user_id, profile_url)
    if profile_enc is None:
        return jsonify({"is_verified": False, "message": "Cannot load or encode profile image"}), 400

    frames_rgb = []
    for fs in frames_files:
        img = load_image_from_file_storage(fs)
        if img is not None:
            frames_rgb.append(img)
    if not frames_rgb:
        return jsonify({"is_verified": False, "message": "no valid frames uploaded"}), 200

    distances = []
    with ThreadPoolExecutor(max_workers=min(len(frames_rgb), 4)) as ex:
        futures = [ex.submit(process_frame_for_distance, fr, profile_enc) for fr in frames_rgb]
        for fut in as_completed(futures):
            try:
                res = fut.result()
                if res is not None:
                    distances.append(res)
            except Exception as e:
                logger.exception("Frame worker error: %s", e)

    if not distances:
        incr_attempt(user_id)
        elapsed = time.time() - start_ts
        logger.info("verify_user finished: no distances computed (%.3fs)", elapsed)
        return jsonify({"is_verified": False, "message": "no face detected in capture"}), 200

    median_distance = float(statistics.median(distances))
    MATCH_THRESHOLD = float(os.environ.get("FV_MATCH_THRESHOLD", 0.45))

    is_match = median_distance <= MATCH_THRESHOLD

    if is_match:
        reset_attempts(user_id)
    else:
        incr_attempt(user_id)

    elapsed = time.time() - start_ts
    logger.info("verify_user finished (user=%s) match=%s median=%.4f frames=%d elapsed=%.3fs",
                user_id, is_match, median_distance, len(distances), elapsed)

    return jsonify({
        "is_verified": bool(is_match),
        "distance": median_distance,
        "frames_checked": len(distances),
        "elapsed_seconds": elapsed,
        "message": "ok" if is_match else "Face and Profile picture did not matched!"
    })

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=APP_PORT, debug=False)
