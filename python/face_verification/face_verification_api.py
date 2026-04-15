from flask import Flask, request, jsonify
from flask_cors import CORS
import face_recognition, cv2, numpy as np, urllib.request
from io import BytesIO

app = Flask(__name__)
CORS(app)

def load_image_from_file_storage(file_storage):
    # file_storage is Werkzeug FileStorage
    data = file_storage.read()
    arr = np.frombuffer(data, np.uint8)
    img = cv2.imdecode(arr, cv2.IMREAD_COLOR)
    # convert BGR to RGB for face_recognition
    return cv2.cvtColor(img, cv2.COLOR_BGR2RGB)

def load_image_from_url(url):
    resp = urllib.request.urlopen(url)
    data = resp.read()
    arr = np.frombuffer(data, np.uint8)
    img = cv2.imdecode(arr, cv2.IMREAD_COLOR)
    return cv2.cvtColor(img, cv2.COLOR_BGR2RGB)

def compute_ear(eye):
    # eye is list of (x,y) coords
    # EAR formula using vertical/horizontal distances
    import math
    A = np.linalg.norm(np.array(eye[1]) - np.array(eye[5]))
    B = np.linalg.norm(np.array(eye[2]) - np.array(eye[4]))
    C = np.linalg.norm(np.array(eye[0]) - np.array(eye[3]))
    if C == 0:
        return 0.0
    ear = (A + B) / (2.0 * C)
    return ear

@app.route('/verify-user', methods=['POST'])
def verify():
    user_id = request.form.get('user_id')
    profile_url = request.form.get('profile_url')
    frames_files = request.files.getlist('frames')

    if not profile_url or not frames_files:
        return jsonify({"is_verified": False, "message":"missing data"}), 400

    # Load profile image from URL
    try:
        profile_img = load_image_from_url(profile_url)
    except Exception as e:
        return jsonify({"is_verified": False, "message":"cannot load profile image"}), 400

    # get encoding for profile image
    profile_encs = face_recognition.face_encodings(profile_img)
    if not profile_encs:
        return jsonify({"is_verified": False, "message":"no face in profile image"}), 200
    profile_enc = profile_encs[0]

    # For liveness: compute EAR across frames to detect blink and ensure face present
    ears = []
    face_present_count = 0
    frame_encodings = []

    for fs in frames_files:
        try:
            img = load_image_from_file_storage(fs)
        except Exception as e:
            continue

        # detect face encodings
        encs = face_recognition.face_encodings(img)
        if encs:
            frame_encodings.append(encs[0])
            face_present_count += 1

        # landmarks for blink detection
        landmarks = face_recognition.face_landmarks(img)
        if landmarks:
            # face_landmarks returns list of facial feature dicts; use first
            lm = landmarks[0]
            left_eye = lm.get('left_eye')
            right_eye = lm.get('right_eye')
            if left_eye and right_eye:
                ears.append((compute_ear(left_eye) + compute_ear(right_eye)) / 2.0)

    # Basic liveness check: require face present in at least 2 frames and EAR variation (blink)
    if face_present_count < 2:
        return jsonify({"is_verified": False, "message":"no face detected in live capture"}), 200

    # detect blink: look for EAR drop then rise
    blink_detected = False
    if len(ears) >= 2:
        # simple heuristic: min EAR significantly lower than max
        if (max(ears) - min(ears)) > 0.08:   # tweak threshold
            blink_detected = True

    # If no blink, still allow but require stronger face-match (lower distance)
    # Now compare profile_enc with each frame encoding and choose best match
    best_distance = 1.0
    for fe in frame_encodings:
        d = face_recognition.face_distance([profile_enc], fe)[0]
        if d < best_distance: best_distance = d

    # set thresholds: if blink detected, allow looser threshold; else stricter
    if blink_detected:
        match_threshold = 0.55   # larger number => looser
    else:
        match_threshold = 0.45   # stricter when no liveness proven

    is_match = best_distance <= match_threshold

    return jsonify({
        "is_verified": bool(is_match),
        "distance": float(best_distance),
        "blink_detected": bool(blink_detected),
        "face_present_count": face_present_count,
        "message": "ok"
    })

if __name__ == "__main__":
    from flask_cors import CORS
    CORS(app)
    app.run(host="0.0.0.0", port=5001, debug=True)
