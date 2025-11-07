from flask import Flask, request, jsonify
from flask_cors import CORS
import face_recognition, cv2, numpy as np, urllib.request
import requests
from io import BytesIO
import math 

app = Flask(__name__)
CORS(app)

def load_image_from_file_storage(file_storage):
    data = file_storage.read()
    arr = np.frombuffer(data, np.uint8)
    img = cv2.imdecode(arr, cv2.IMREAD_COLOR)
    return cv2.cvtColor(img, cv2.COLOR_BGR2RGB)

def load_image_from_url(url):
    resp = requests.get(url, timeout=5) 
    resp.raise_for_status() 
    
    data = resp.content 
    arr = np.frombuffer(data, np.uint8)
    img = cv2.imdecode(arr, cv2.IMREAD_COLOR)
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

    if not profile_url or not frames_files:
        return jsonify({"is_verified": False, "message":"missing data"}), 400

    try:
        profile_img = load_image_from_url(profile_url)
    except Exception as e:
        print(f"Error loading profile image: {e}")
        return jsonify({"is_verified": False, "message":"cannot load profile image"}), 400

    profile_encs = face_recognition.face_encodings(profile_img)
    if not profile_encs:
        return jsonify({"is_verified": False, "message":"no face in profile image"}), 200
    profile_enc = profile_encs[0]

    ears = []
    face_present_count = 0
    frame_encodings = []

    for fs in frames_files:
        try:
            img = load_image_from_file_storage(fs)
        except Exception as e:
            print(f"Error loading frame: {e}")
            continue

        face_locations = face_recognition.face_locations(img)

        if not face_locations:
            continue

        first_face_location = [face_locations[0]]
        face_present_count += 1

        encs = face_recognition.face_encodings(img, known_face_locations=first_face_location)
        if encs:
            frame_encodings.append(encs[0])

        landmarks_list = face_recognition.face_landmarks(img, face_locations=first_face_location)
        if landmarks_list:
            lm = landmarks_list[0]
            left_eye = lm.get('left_eye')
            right_eye = lm.get('right_eye')
            if left_eye and right_eye:
                ears.append((compute_ear(left_eye) + compute_ear(right_eye)) / 2.0)

    if face_present_count < 2:
        return jsonify({"is_verified": False, "message":"no face detected in live capture"}), 200

    blink_detected = False
    if len(ears) >= 2:

        if (max(ears) - min(ears)) > 0.08: 
            blink_detected = True

    best_distance = 1.0
    if not frame_encodings:
        return jsonify({"is_verified": False, "message":"could not get face encoding from capture"}), 200

    for fe in frame_encodings:
        d = face_recognition.face_distance([profile_enc], fe)[0]
        if d < best_distance: best_distance = d

    if blink_detected:
        match_threshold = 0.55  
    else:
        match_threshold = 0.45

    is_match = best_distance <= match_threshold

    return jsonify({
        "is_verified": bool(is_match),
        "distance": float(best_distance),
        "blink_detected": bool(blink_detected),
        "face_present_count": face_present_count,
        "message": "ok"
    })

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=5002, debug=True)