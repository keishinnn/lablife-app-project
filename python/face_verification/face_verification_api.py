from flask import Flask, request, jsonify
from flask_cors import CORS
import face_recognition, cv2, numpy as np
import requests
from io import BytesIO
import math
import time
import statistics 

app = Flask(__name__)
CORS(app)

VERIFY_ATTEMPTS = {}
MAX_ATTEMPTS = 5
ATTEMPT_WINDOW = 600 

def load_image_from_file_storage(file_storage):
    data = file_storage.read()
    arr = np.frombuffer(data, np.uint8)

    img = cv2.imdecode(arr, cv2.IMREAD_COLOR) 
    
    if img is None:
        print("Error: Failed to decode image from file storage.")
        return None

    max_size = 800
    h, w = img.shape[:2]
    if h > max_size or w > max_size:
        scale = max_size / max(h, w)
        new_w, new_h = int(w * scale), int(h * scale)
        img = cv2.resize(img, (new_w, new_h), interpolation=cv2.INTER_AREA)
    
    return cv2.cvtColor(img, cv2.COLOR_BGR2RGB)

def load_image_from_url(url):

    resp = requests.get(url, timeout=10) 
    resp.raise_for_status()
    
    data = resp.content
    arr = np.frombuffer(data, np.uint8)

    img = cv2.imdecode(arr, cv2.IMREAD_COLOR) 
    
    if img is None:
        print("Error: Failed to decode image from URL.")
        return None

    max_size = 800
    h, w = img.shape[:2]
    if h > max_size or w > max_size:
        scale = max_size / max(h, w)
        new_w, new_h = int(w * scale), int(h * scale)
        img = cv2.resize(img, (new_w, new_h), interpolation=cv2.INTER_AREA)

    return cv2.cvtColor(img, cv2.COLOR_BGR2RGB)


def compute_ear(eye):
    A = np.linalg.norm(np.array(eye[1]) - np.array(eye[5]))
    B = np.linalg.norm(np.array(eye[2]) - np.array(eye[4]))
    C = np.linalg.norm(np.array(eye[0]) - np.array(eye[3]))
    if C == 0:
        return 0.0
    ear = (A + B) / (2.0 * C)
    return ear

@app.route('/health', methods=['GET'])
def health():
    return jsonify({"status": "ok"}), 200

@app.route('/verify-user', methods=['POST'])
def verify():
    user_id = request.form.get('user_id')
    profile_url = request.form.get('profile_url')
    frames_files = request.files.getlist('frames')

    current_time = time.time()
    if user_id in VERIFY_ATTEMPTS:
        user_data = VERIFY_ATTEMPTS[user_id]
        time_since = current_time - user_data["timestamp"]
        
        if time_since > ATTEMPT_WINDOW:
            user_data["count"] = 0 
        
        if user_data["count"] >= MAX_ATTEMPTS:
            return jsonify({
                "is_verified": False, 
                "message": "Too many attempts. Please try again in 10 minutes."
            }), 429
    else:
        VERIFY_ATTEMPTS[user_id] = {"count": 0, "timestamp": current_time}

    if not profile_url or not frames_files:
        return jsonify({"is_verified": False, "message":"missing data"}), 400

    try:
        profile_img = load_image_from_url(profile_url)
        if profile_img is None:
             # This means cv2.imdecode failed
             print("Error loading profile image: cv2.imdecode returned None. Image might be corrupt.")
             return jsonify({"is_verified": False, "message":"Profile image is corrupt or invalid."}), 400
             
    except requests.exceptions.Timeout:
        print(f"Error loading profile image: Timeout connecting to {profile_url}")
        return jsonify({"is_verified": False, "message":"Cannot load profile image: Timeout"}), 400
        
    except requests.exceptions.HTTPError as err:
        print(f"Error loading profile image: HTTP Error {err.response.status_code} for {profile_url}")
        return jsonify({"is_verified": False, "message":f"Cannot load profile image: HTTP {err.response.status_code}"}), 400
        
    except requests.exceptions.RequestException as e:
        print(f"Error loading profile image: Network error {e}")
        return jsonify({"is_verified": False, "message":"Cannot load profile image: Network error"}), 400
        
    except Exception as e:
        print(f"An unexpected error occurred while loading profile image: {e}")
        return jsonify({"is_verified": False, "message":"Cannot load profile image: Unknown server error"}), 400

    profile_encs = face_recognition.face_encodings(profile_img, num_jitters=10)
    
    if not profile_encs:
        return jsonify({"is_verified": False, "message":"no face in profile image"}), 200
    profile_enc = profile_encs[0]

    ears = []
    face_present_count = 0
    
    distances = [] 

    for fs in frames_files:
        try:
            img = load_image_from_file_storage(fs)
            if img is None:
                print("Skipping a corrupt frame.")
                continue 
        except Exception as e:
            print(f"Error loading frame: {e}")
            continue

        face_locations = face_recognition.face_locations(img)
        if not face_locations:
            continue

        first_face_location = [face_locations[0]]
        face_present_count += 1

        encs = face_recognition.face_encodings(img, known_face_locations=first_face_location, num_jitters=2)
        if encs:
            d = face_recognition.face_distance([profile_enc], encs[0])[0]
            distances.append(d)

        landmarks_list = face_recognition.face_landmarks(img, face_locations=first_face_location)
        if landmarks_list:
            lm = landmarks_list[0]
            left_eye = lm.get('left_eye')
            right_eye = lm.get('right_eye')
            if left_eye and right_eye:
                ears.append((compute_ear(left_eye) + compute_ear(right_eye)) / 2.0)

    if face_present_count < 3: 
        return jsonify({"is_verified": False, "message":"no face detected in live capture"}), 200

    blink_detected = False
    if len(ears) >= 2:
        if (max(ears) - min(ears)) > 0.08: 
            blink_detected = True

    if not distances:
        return jsonify({"is_verified": False, "message":"could not get face encoding from capture"}), 200

    median_distance = statistics.median(distances)

    match_threshold = 0.45

    is_match = median_distance <= match_threshold

    if is_match:
        if user_id in VERIFY_ATTEMPTS:
            VERIFY_ATTEMPTS.pop(user_id, None) 
    else:
        VERIFY_ATTEMPTS[user_id]["count"] += 1
        VERIFY_ATTEMPTS[user_id]["timestamp"] = current_time

    return jsonify({
        "is_verified": bool(is_match),
        "distance": float(median_distance), 
        "blink_detected": bool(blink_detected), 
        "face_present_count": face_present_count,
        "message": "ok" if is_match else "Face and Profile picture did not matched!"
    })

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5002, debug=True)