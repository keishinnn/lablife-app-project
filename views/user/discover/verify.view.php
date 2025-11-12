<?php

require base_path('views/shared/header.php');

?>

<div class="verify-container">
    <video id="verify-video" autoplay playsinline muted></video>

    <div class="oval-border"></div>

    <div class="verify-controls">
        <h2 id="verify-status"></h2>
    </div>
    <canvas id="capture-canvas" width="640" height="480" style="display:none"></canvas>
</div>

<?php require base_path('Views/user/discover/modals/popup.view.php') ?>

<script type="module">
    const popupModal = document.getElementById('popup-modal-id');
    const popupModalTitle = document.getElementById('popup-modal-title');
    const popupModalBtnOne = document.getElementById('popup-modal-btn-one');
    const popupModalBtnTwo = document.getElementById('popup-modal-btn-two');

    const video = document.getElementById('verify-video');
    const canvas = document.getElementById('capture-canvas');
    const status = document.getElementById('verify-status');
    const oval = document.querySelector('.oval-border');

    const API_VERIFY = "http://127.0.0.1:5002/verify-user";
    const CURRENT_USER_ID = "<?= $user->id ?>";
    const PROFILE_PHOTO_URL = "<?= $user->avatarUrl ?>";

    console.log(PROFILE_PHOTO_URL);

    let isCapturing = false;

    // --- Liveness State Machine Variables ---
    let livenessState = 'CENTER'; // 'CENTER', 'BLINK', 'TURN_LEFT', 'TURN_RIGHT', 'CAPTURE'
    let earBuffer = [];
    const EAR_BUFFER_MAX = 10;
    const YAW_THRESHOLD_LEFT = 0.38;
    const YAW_THRESHOLD_RIGHT = 0.62;
    const EAR_BLINK_DIFF = 0.025;
    let lastActionTime = Date.now();
    const ACTION_TIMEOUT_MS = 4000;

    async function startCameraAuto() {
        try {
            const stream = await getMediaStream();
            if (!stream) {
                noCameraDetectedPopup();
                return false;
            }
            video.srcObject = stream;
            await video.play();
            return true;
        } catch (err) {
            noCameraDetectedPopup();
            return false;
        }
    }

    // (This function is unchanged)
    async function startFaceRecognitionAPI() {
        try {
            const response = await fetch('/u/start-face-recognition-api', {
                method: 'POST'
            });
            if (!response.ok) {
                console.error("Failed to start Face Recognition API");
                return false;
            }
            console.log("Face Recognition API startup triggered");
            return true;
        } catch (err) {
            console.error("Error calling start-face-recognition-api:", err);
            return false;
        }
    }

    // (This function is unchanged)
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

    // (This function is unchanged)
    function computeEAR(eye) {
        const p = (i) => eye[i];
        const A = Math.hypot(p(1)[0] - p(5)[0], p(1)[1] - p(5)[1]);
        const B = Math.hypot(p(2)[0] - p(4)[0], p(2)[1] - p(4)[1]);
        const C = Math.hypot(p(0)[0] - p(3)[0], p(0)[1] - p(3)[1]);
        if (C === 0) return 0;
        return (A + B) / (2.0 * C);
    }

    // (This function is unchanged)
    function calculateYaw(landmarks) {
        try {
            const jaw = landmarks.getJawOutline();
            const leftJaw = jaw[0]; // Point 0
            const rightJaw = jaw[16]; // Point 16
            const noseTip = landmarks.getNose()[6]; // Point 33 (tip of nose)

            if (!leftJaw || !rightJaw || !noseTip) return 0.5;
            const jawWidth = rightJaw.x - leftJaw.x;
            const noseToLeft = noseTip.x - leftJaw.x;
            if (jawWidth === 0) return 0.5;
            const ratio = Math.max(0, Math.min(1, noseToLeft / jawWidth));
            return ratio;
        } catch (e) {
            console.error("Error calculating yaw:", e);
            return 0.5;
        }
    }

    // (This function is unchanged)
    function resetLiveness(showMsg = true) {
        if (showMsg && status.textContent !== 'Capturing frames...') {
            status.textContent = 'Move to center';
        }
        livenessState = 'CENTER';
        earBuffer = [];
        lastActionTime = Date.now();
        if (oval) {
            oval.style.borderColor = '#f97316'; // Orange
        }
    }

    // (This function is unchanged)
    function isBoxCenterInOval(box) {
        const vRect = video.getBoundingClientRect();
        const oRect = oval.getBoundingClientRect();
        const cx = box.x + box.width / 2;
        const cy = box.y + box.height / 2;

        if (cx < oRect.left || cx > oRect.right || cy < oRect.top || cy > oRect.bottom) return false;

        const rx = oRect.width / 2;
        const ry = oRect.height / 2;
        const dx = cx - (oRect.left + rx);
        const dy = cy - (oRect.top + ry);

        const val = (dx * dx) / (rx * rx) + (dy * dy) / (ry * ry);
        return val <= 1.0;
    }

    // (This function is unchanged)
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

    // --- UPDATED postFrames function ---
    async function postFrames(framesDataUrls) {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 20000);

        const form = new FormData();
        form.append('user_id', CURRENT_USER_ID);
        form.append('profile_url', PROFILE_PHOTO_URL);
        framesDataUrls.forEach((durl, i) => {
            const blob = dataURLtoBlob(durl);
            form.append('frames', blob, `frame${i}.jpg`);
        });

        try {
            const resp = await fetch(API_VERIFY, {
                method: 'POST',
                body: form,
                signal: controller.signal
            });
            clearTimeout(timeout);

            // --- NEW: Handle HTTP errors by reading the JSON body ---
            if (!resp.ok) {
                let errorJson = null;
                try {
                    // Try to get the specific error message from the API
                    errorJson = await resp.json();
                } catch (e) {
                    // Ignore if response is not JSON
                }

                // Throw an error that includes the status and the JSON payload
                const err = new Error(`HTTP ${resp.status}`);
                err.response = resp; // Attach the full response
                err.json = errorJson; // Attach the parsed JSON
                throw err;
            }
            // --- END NEW LOGIC ---

            return await resp.json();

        } catch (err) {
            clearTimeout(timeout);
            // Re-throw if it's our custom error, or create a new one
            if (err.response) {
                throw err; // It's our custom error, pass it on
            }
            // This catches network errors or aborts
            throw new Error("Network or API timeout: " + err.message);
        }
    }
    // --- END UPDATED postFrames ---

    // (This function is unchanged)
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

    // (This function is unchanged)
    async function loadModels() {
        const MODEL_URL = '/assets/models';
        await faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL);
        await faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL);
    }

    async function startDetectionLoop() {
        console.log('[DEBUG] Starting detection loop...');
        const options = new faceapi.TinyFaceDetectorOptions({
            inputSize: 224,
            scoreThreshold: 0.5
        });

        async function runDetection() {

            if (video.paused || video.readyState < 2) {
                setTimeout(runDetection, 200);
                return;
            }

            const result = await faceapi.detectSingleFace(video, options).withFaceLandmarks(true);
            const now = Date.now();

            if (!result) {
                oval.style.borderColor = '#f80000ff'; // Red
                resetLiveness();
                setTimeout(runDetection, 200);
                return;
            }

            const box = result.detection.box;
            const vRect = video.getBoundingClientRect();
            const scaleX = vRect.width / video.videoWidth;
            const scaleY = vRect.height / video.videoHeight;
            const pageBox = {
                x: vRect.left + box.x * scaleX,
                y: vRect.top + box.y * scaleY,
                width: box.width * scaleX,
                height: box.height * scaleY
            };
            const inOval = isBoxCenterInOval(pageBox);

            if (!inOval) {
                oval.style.borderColor = '#f97316'; // Orange
                resetLiveness();
                setTimeout(runDetection, 200);
                return;
            }

            oval.style.borderColor = '#22c55e'; // Green
            const lm = result.landmarks;

            if (now - lastActionTime > ACTION_TIMEOUT_MS && livenessState !== 'CAPTURE') {
                console.log(`[DEBUG] Liveness step '${livenessState}' timed out.`);
                resetLiveness(true); // Reset with message
                setTimeout(runDetection, 200);
                return;
            }

            // --- LIVENESS STATE MACHINE ---
            switch (livenessState) {
                case 'CENTER':
                    status.textContent = 'Great! Now please blink';
                    livenessState = 'BLINK';
                    lastActionTime = now;
                    break;

                case 'BLINK':
                    status.textContent = 'Please blink';
                    const leftEye = lm.getLeftEye().map(p => [p.x, p.y]);
                    const rightEye = lm.getRightEye().map(p => [p.x, p.y]);
                    const ear = (computeEAR(leftEye) + computeEAR(rightEye)) / 2.0;

                    earBuffer.push(ear);
                    if (earBuffer.length > EAR_BUFFER_MAX) earBuffer.shift();

                    if (earBuffer.length === EAR_BUFFER_MAX) {
                        const earMin = Math.min(...earBuffer);
                        const earMax = Math.max(...earBuffer);
                        const earDiff = earMax - earMin;

                        console.log(`[DEBUG_BLINK] earDiff: ${earDiff.toFixed(4)}, Target: ${EAR_BLINK_DIFF}`);

                        if (earDiff > EAR_BLINK_DIFF) {
                            console.log("[DEBUG] Blink detected!");
                            livenessState = 'TURN_LEFT';
                            lastActionTime = now;
                            earBuffer = [];
                        }
                    }
                    break;

                case 'TURN_LEFT':
                    status.textContent = 'Now turn your head left';
                    const yawLeft = calculateYaw(lm);
                    if (yawLeft < YAW_THRESHOLD_LEFT) {
                        console.log("[DEBUG] Left turn detected!");
                        livenessState = 'TURN_RIGHT';
                        lastActionTime = now;
                    }
                    break;

                case 'TURN_RIGHT':
                    status.textContent = 'Now turn your head right';
                    const yawRight = calculateYaw(lm);
                    if (yawRight > YAW_THRESHOLD_RIGHT) {
                        console.log("[DEBUG] Right turn detected!");
                        livenessState = 'CAPTURE';
                        lastActionTime = now;
                    }
                    break;

                case 'CAPTURE':
                    status.textContent = 'Perfect! Hold still, look at the camera';
                    const yawCenter = calculateYaw(lm);

                    // Wait for user to re-center their face
                    if (yawCenter > YAW_THRESHOLD_LEFT + 0.05 && yawCenter < YAW_THRESHOLD_RIGHT - 0.05) {
                        isCapturing = true; // <-- Set to true
                        oval.style.borderColor = '#0ea5e9'; // Blue for capture
                        console.log('[DEBUG] Re-centered. Triggering capture...');

                        // --- CAPTURE LOGIC ---
                        const rect = getOverlayRect();
                        const frames = [];
                        const ctx = canvas.getContext('2d');

                        // --- *** THE FIX (Part 2) *** ---
                        // Capture 5 frames for a more robust median
                        for (let i = 0; i < 5; i++) {
                            canvas.width = rect.sw;
                            canvas.height = rect.sh;
                            ctx.drawImage(video, rect.sx, rect.sy, rect.sw, rect.sh, 0, 0, rect.sw, rect.sh);
                            frames.push(canvas.toDataURL('image/jpeg', 0.9));
                            // Update debug log
                            console.log(`[DEBUG] Captured frame ${i + 1}/5`);
                            await new Promise(r => setTimeout(r, 650)); // Wait between frames
                        }
                        // --- *** END FIX (Part 2) *** ---


                        status.textContent = 'Do not move';
                        console.log('[DEBUG] Sending frames to backend API...');

                        try {
                            const result = await postFrames(frames);
                            console.log('[DEBUG] Verification API result:', result);

                            if (result.is_verified) {
                                status.textContent = '✅ Verified';
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
                                // --- Handle verification failure (e.g., distance > 0.5) ---
                                status.textContent = result.message || '❌ Face and Profile picture did not matched!';
                                oval.style.borderColor = '#f80000ff';

                                fetch('/u/account/increment-fail', {
                                        method: 'POST'
                                    })
                                    .then(res => res.json())
                                    .then(data => {
                                        if (data.status === 'locked') {
                                            const mins = Math.ceil(data.remaining_seconds / 60);
                                            status.textContent = `⛔ Too many failed attempts. Try again in ${mins} minute(s).`;
                                            isCapturing = true; // Lock the screen
                                            showTooManyAttempts(mins);
                                            return;
                                        }
                                        console.log('Failed attempts:', data.fail_count);

                                        // Wait 5 seconds BEFORE resetting
                                        setTimeout(() => {
                                            isCapturing = false;
                                            resetLiveness(true);
                                            runDetection();
                                        }, 5000);
                                    })
                                    .catch(err => {
                                        console.error('Failed to record verification attempt:', err);
                                        status.textContent = 'Server error. Try again.';
                                        oval.style.borderColor = '#f80000ff';

                                        setTimeout(() => {
                                            isCapturing = false;
                                            resetLiveness(true);

                                            runDetection();
                                        }, 2000);
                                    });
                            }
                        } catch (err) {
                            // --- Handle 429 and other network errors ---
                            console.error('[DEBUG] Error posting frames:', err);
                            oval.style.borderColor = '#f80000ff';

                            // Check if this is a 429 (Rate Limit) error
                            if (err.response && err.response.status === 429) {
                                // Get the message from the server's JSON response
                                const msg = err.json ? err.json.message : "Too many attempts. Please try again later.";
                                status.textContent = `⛔ ${msg}`;
                                isCapturing = true; // Lock the screen

                                // Try to parse the minutes from the message
                                const minsMatch = msg.match(/(\d+)\s+minute/);
                                const waitMins = minsMatch ? parseInt(minsMatch[1], 10) : 10;

                                showTooManyAttempts(waitMins);

                                // IMPORTANT: DO NOT restart the loop. Just return.
                                return;
                            }

                            // Handle other generic errors (500, network down, etc.)
                            status.textContent = 'Server error. Try again.';
                            setTimeout(() => {
                                isCapturing = false;
                                resetLiveness(true);
                                runDetection();
                            }, 2000); // Wait 2s
                        }
                        // --- END CAPTURE LOGIC ---

                        // We MUST return here to let the timers in try/catch take control
                        return;
                    }
                    break;
            }

            // This is the main loop. It only runs if isCapturing is false
            // (because the CAPTURE case returns before reaching this)
            if (!isCapturing) {
                setTimeout(runDetection, 200);
            }
        }

        runDetection();
    }

    // (All popup/init functions below are unchanged)

    function showTooManyAttempts(time) {
        reusablePopup(`Too many attempts. Please try again in ${time} minute${ time > 1 ? "s" : ""}.`, 'Try again', 'Exit');
    }

    function noCameraDetectedPopup() {
        reusablePopup('No camera detected or permission denied.', 'Try again', 'Exit');
    }

    function showServiceUnavailable() {
        reusablePopup('Verification service unavailable. Please try again.', 'Try again', 'Exit');
    }

    function reusablePopup(title, btnOneText, btnTwoText) {
        popupModal.style.display = 'flex';
        popupModalTitle.textContent = title;
        popupModalBtnOne.textContent = btnOneText;
        popupModalBtnTwo.textContent = btnTwoText;
    }

    if (popupModalBtnOne) {
        popupModalBtnOne.addEventListener('click', () => {
            window.location.href = '/u/verify';
        })
    }

    if (popupModalBtnTwo) {
        popupModalBtnTwo.addEventListener('click', () => {
            window.location.href = '/u/verify-next';
        })
    }

    async function checkAPIHealth() {
        try {
            const res = await fetch("http://127.0.0.1:5002/health");
            return res.ok;
        } catch {
            return false;
        }
    }

    (async function init() {
        try {
            const failRes = await fetch('/u/account/fail-status');
            const data = await failRes.json();
            console.log('Fail count:', data.fail_count);
            const mins = Math.ceil(data.remaining_seconds / 60);

            if (data.remaining_seconds > 0) {
                showTooManyAttempts(mins);
                return;
            }

            await new Promise(r => setTimeout(r, 500));
            const isAlive = await checkAPIHealth();
            if (!isAlive) {
                showServiceUnavailable();
                return;
            }

            status.textContent = 'Loading face models...';
            await loadModels();

            const ok = await startCameraAuto();
            if (!ok) {
                status.textContent = 'Camera not available.';
                return;
            }

            status.textContent = 'Camera ready — position face inside oval';
            await new Promise(r => setTimeout(r, 300));

            startDetectionLoop();

        } catch (err) {
            console.error('Init error:', err);
            showServiceUnavailable();
        }
    })();
</script>


<?php require base_path('views/shared/footer.php') ?>