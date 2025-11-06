<?php

require base_path('views/shared/header.php');

?>

<div class="verify-container">
    <video id="verify-video" autoplay playsinline muted></video>

    <!-- Blue border overlay -->
    <div class="oval-border"></div>

    <div class="verify-controls">
        <button id="capture-start" class="btn">Start</button>
        <button id="capture-verify" class="btn" style="display:none">Verify</button>
        <p id="verify-status"></p>
    </div>

    <canvas id="capture-canvas" width="640" height="480" style="display:none"></canvas>
</div>

<script type="module">
    /*
  Realtime client script:
  - loads face-api models
  - detects face & landmarks in video
  - checks if face bounding-box center is inside oval
  - computes EAR (eye aspect ratio) to detect blink
  - when criteria met -> capture 3 frames -> POST to Flask /verify-user
*/

    const video = document.getElementById('verify-video');
    const canvas = document.getElementById('capture-canvas');
    const startBtn = document.getElementById('capture-start');
    const verifyBtn = document.getElementById('capture-verify');
    const status = document.getElementById('verify-status');
    const oval = document.querySelector('.oval-border');

    const API_VERIFY = "http://127.0.0.1:5001/verify-user"; // your Flask URL
    const CURRENT_USER_ID = "<?= $user->id ?>";
    const PROFILE_PHOTO_URL = "<?= $user->avatarUrl ?>";

    let detectionInterval = null;
    let detectionsBuffer = []; // keep last N detections for blink/motion
    const MAX_BUFFER = 8;

    // ----- helper: start camera -----
    async function startCameraAuto() {
        const stream = await getMediaStream();
        if (!stream) {
            status.textContent = "No camera detected.";
            return;
        }
        video.srcObject = stream;
        await video.play();
    }

    startBtn.addEventListener('click', async () => {
        startBtn.disabled = true;
        status.textContent = "Initializing camera...";
        await startCameraAuto();
        status.textContent = "Align your face inside the oval.";
        await new Promise(r => setTimeout(r, 800));
        verifyBtn.style.display = 'inline-block';
    });

    async function getMediaStream() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const webcams = devices.filter(d => d.kind === "videoinput");

            if (webcams.length > 0)
                return await navigator.mediaDevices.getUserMedia({
                    video: {
                        deviceId: webcams[0].deviceId
                    },
                    audio: false
                });

            const obsCamera = devices.find(d => d.label.includes("OBS Virtual Camera"));
            if (obsCamera)
                return await navigator.mediaDevices.getUserMedia({
                    video: {
                        deviceId: obsCamera.deviceId
                    },
                    audio: false
                });

            alert("No camera found. Please connect one or enable OBS Virtual Camera.");
            return null;
        } catch (err) {
            console.error("Camera error:", err);
            return null;
        }
    }

    // ----- compute EAR for one eye (landmarks are arrays of points [x,y]) -----
    function computeEAR(eye) {
        // eye: array of 6 points (x,y)
        const p = (i) => eye[i];
        const A = Math.hypot(p(1)[0] - p(5)[0], p(1)[1] - p(5)[1]);
        const B = Math.hypot(p(2)[0] - p(4)[0], p(2)[1] - p(4)[1]);
        const C = Math.hypot(p(0)[0] - p(3)[0], p(0)[1] - p(3)[1]);
        if (C === 0) return 0;
        return (A + B) / (2.0 * C);
    }

    // ----- is center of detected box inside oval area? -----
    function isBoxCenterInOval(box) {
        // box: {x, y, width, height} in page (CSS) coordinates
        const vRect = video.getBoundingClientRect();
        const oRect = oval.getBoundingClientRect();

        // scale detector box (which is relative to video internal resolution)
        // We'll use face-api's detection box in video display coordinates if we draw to overlay canvas.
        const cx = box.x + box.width / 2;
        const cy = box.y + box.height / 2;

        // check inside oval's bounding rect first
        if (cx < oRect.left || cx > oRect.right || cy < oRect.top || cy > oRect.bottom) return false;

        // treat oval as ellipse; compute normalized coords
        const rx = oRect.width / 2;
        const ry = oRect.height / 2;
        const dx = cx - (oRect.left + rx);
        const dy = cy - (oRect.top + ry);

        // ellipse equation x^2/rx^2 + y^2/ry^2 <= 1
        const val = (dx * dx) / (rx * rx) + (dy * dy) / (ry * ry);
        return val <= 1.0;
    }

    // ----- capture cropped image from video given overlay rect in video pixel coordinates -----
    function captureCropped(rect) {
        // rect.sx, sy, sw, sh in video pixels (as your getOverlayRect computed)
        const ctx = canvas.getContext('2d');
        canvas.width = rect.sw;
        canvas.height = rect.sh;
        ctx.drawImage(video, rect.sx, rect.sy, rect.sw, rect.sh, 0, 0, rect.sw, rect.sh);
        return canvas.toDataURL('image/jpeg', 0.9);
    }

    // ----- helper: compute overlay rect in video pixel coordinates (like your getOverlayRect) -----
    function getOverlayRect() {
        const ov = oval.getBoundingClientRect();
        const v = video.getBoundingClientRect();
        const offsetX = ov.left - v.left;
        const offsetY = ov.top - v.top;
        const scaleX = video.videoWidth / v.width;
        const scaleY = video.videoHeight / v.height;
        const sx = Math.round(offsetX * scaleX);
        const sy = Math.round(offsetY * scaleY);
        const sw = Math.round(ov.width * scaleX);
        const sh = Math.round(ov.height * scaleY);
        return {
            sx,
            sy,
            sw,
            sh,
            left: ov.left,
            top: ov.top,
            width: ov.width,
            height: ov.height
        };
    }

    // ----- send frames to Flask -----
    async function postFrames(framesDataUrls) {
        const form = new FormData();
        form.append('user_id', CURRENT_USER_ID);
        form.append('profile_url', PROFILE_PHOTO_URL);
        framesDataUrls.forEach((durl, i) => {
            // convert dataURL to Blob
            const blob = dataURLtoBlob(durl);
            form.append('frames', blob, `frame${i}.jpg`);
        });

        // put an API secret if you want e.g. headers: {'X-API-KEY': 'supersecret'}
        const resp = await fetch(API_VERIFY, {
            method: 'POST',
            body: form
        });
        return resp.json();
    }

    function dataURLtoBlob(dataurl) {
        const arr = dataurl.split(','),
            mime = arr[0].match(/:(.*?);/)[1];
        const bstr = atob(arr[1]);
        let n = bstr.length;
        const u8arr = new Uint8Array(n);
        while (n--) {
            u8arr[n] = bstr.charCodeAt(n);
        }
        return new Blob([u8arr], {
            type: mime
        });
    }

    // ----- load face-api models (use local /models or hosted) -----
    async function loadModels() {
        // Example: host models at /assets/models (download them there)
        const MODEL_URL = '/assets/models';
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL);
        // optional: face recognition on client if desired
        console.log('face-api models loaded');
    }

    // ----- main detection loop -----
    async function startDetectionLoop() {
        const options = new faceapi.TinyFaceDetectorOptions({
            inputSize: 224,
            scoreThreshold: 0.5
        });

        // run detection at about 5 fps to balance CPU
        detectionInterval = setInterval(async () => {
            if (video.paused || video.readyState < 2) return;

            // detect single face with landmarks
            const result = await faceapi.detectSingleFace(video, options).withFaceLandmarks(true);
            if (!result) {
                oval.style.borderColor = '#38bdf8'; // blue
                status.textContent = 'No face detected';
                detectionsBuffer.push(null);
                if (detectionsBuffer.length > MAX_BUFFER) detectionsBuffer.shift();
                return;
            }

            // convert result box to page coords
            const box = result.detection.box;
            // face-api's box is in video internal pixels but offset relative to video element => map to page coords
            // compute box in page coordinates (left, top, width, height)
            const vRect = video.getBoundingClientRect();
            const scaleX = vRect.width / video.videoWidth;
            const scaleY = vRect.height / video.videoHeight;
            const pageBox = {
                x: vRect.left + box.x * scaleX,
                y: vRect.top + box.y * scaleY,
                width: box.width * scaleX,
                height: box.height * scaleY
            };

            // check if face center inside oval
            if (isBoxCenterInOval(pageBox)) {
                oval.style.borderColor = '#22c55e'; // green
                status.textContent = 'Face aligned — please blink or move slightly';
            } else {
                oval.style.borderColor = '#f97316'; // orange if not centered
                status.textContent = 'Move to center';
            }

            // compute EAR from landmarks (we have 68point landmarks)
            const lm = result.landmarks;
            const leftEye = lm.getLeftEye().map(p => [p.x, p.y]);
            const rightEye = lm.getRightEye().map(p => [p.x, p.y]);
            const ear = (computeEAR(leftEye) + computeEAR(rightEye)) / 2.0;

            // store a compact detection for buffer: {ear, centerY, box}
            const center = {
                x: pageBox.x + pageBox.width / 2,
                y: pageBox.y + pageBox.height / 2
            };
            detectionsBuffer.push({
                ear,
                center,
                pageBox,
                timestamp: Date.now(),
                landmarks: lm
            });
            if (detectionsBuffer.length > MAX_BUFFER) detectionsBuffer.shift();

            // Evaluate liveness heuristics:
            // 1) require face to be centered for at least 2 consecutive detections
            // 2) require EAR variation across buffer to indicate blink
            const lastTwo = detectionsBuffer.slice(-3); // last few
            const centeredCount = lastTwo.filter(d => d && isBoxCenterInOval(d.pageBox)).length;

            // compute EAR min/max over buffer where detection exists
            const earValues = detectionsBuffer.filter(Boolean).map(d => d.ear);
            const earMin = Math.min(...earValues);
            const earMax = Math.max(...earValues);
            const earDiff = earMax - earMin;

            // motion check: compute vertical movement of center y across buffer
            const centers = detectionsBuffer.filter(Boolean).map(d => d.center.y);
            const motion = centers.length >= 2 ? Math.abs(centers[centers.length - 1] - centers[0]) : 0;

            const BLINK_EAR_DIFF_THRESHOLD = 0.015; // small threshold to detect change (tweak)
            const MOTION_THRESHOLD_PX = 4; // require at least small motion

            const blinkDetected = earDiff > BLINK_EAR_DIFF_THRESHOLD;
            const centeredEnough = centeredCount >= 2;
            const smallMotion = motion > MOTION_THRESHOLD_PX;

            // if face centered + (blink OR motion) -> capture frames and submit
            if (centeredEnough && (blinkDetected || smallMotion)) {
                // stop detection to avoid double submits
                clearInterval(detectionInterval);

                oval.style.borderColor = '#0ea5e9';
                status.textContent = 'Capturing frames...';

                // compute overlay rect in video pixels then capture frames (3)
                const rect = getOverlayRect();
                const frames = [];
                const ctx = canvas.getContext('2d');

                for (let i = 0; i < 3; i++) {
                    // draw and capture
                    canvas.width = rect.sw;
                    canvas.height = rect.sh;
                    ctx.drawImage(video, rect.sx, rect.sy, rect.sw, rect.sh, 0, 0, rect.sw, rect.sh);
                    frames.push(canvas.toDataURL('image/jpeg', 0.9));
                    await new Promise(r => setTimeout(r, 650));
                }

                status.textContent = 'Sending to server...';
                try {
                    const result = await postFrames(frames);
                    console.log('verify result:', result);
                    if (result.is_verified) {
                        status.textContent = '✅ Verified';
                        // tell PHP backend to update is_verified
                        await fetch('/u/account/set-verified', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                user_id: CURRENT_USER_ID
                            })
                        });
                        setTimeout(() => window.location.reload(), 900);
                    } else {
                        status.textContent = '❌ Verification failed: ' + (result.message || 'no match');
                        // restart detection
                        detectionsBuffer = [];
                        startDetectionLoop();
                    }
                } catch (err) {
                    console.error('Error posting frames', err);
                    status.textContent = 'Server error. Try again.';
                    detectionsBuffer = [];
                    startDetectionLoop();
                }
            }

        }, 200); // 200 ms => ~5 fps
    }

    // ----- bind UI and start --------
    (async function init() {
        status.textContent = 'Loading face models...';
        await loadModels();

        const ok = await startCameraAuto();
        if (!ok) {
            status.textContent = 'Camera not available.';
            return;
        }
        status.textContent = 'Camera ready — position face inside oval';
        // small delay to ensure video videoWidth/Height available
        await new Promise(r => setTimeout(r, 300));
        startDetectionLoop();
    })();
</script>


<?php require base_path('views/shared/footer.php') ?>