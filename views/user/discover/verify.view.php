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
    const startBtn = document.getElementById('capture-start');
    const verifyBtn = document.getElementById('capture-verify');
    const status = document.getElementById('verify-status');
    const oval = document.querySelector('.oval-border');

    const API_VERIFY = "http://127.0.0.1:5002/verify-user";
    const CURRENT_USER_ID = "<?= $user->id ?>";
    const PROFILE_PHOTO_URL = "<?= $user->avatarUrl ?>";

    let isCapturing = false;
    let detectionInterval = null;
    let detectionsBuffer = [];
    const MAX_BUFFER = 8;

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

    function computeEAR(eye) {
        const p = (i) => eye[i];
        const A = Math.hypot(p(1)[0] - p(5)[0], p(1)[1] - p(5)[1]);
        const B = Math.hypot(p(2)[0] - p(4)[0], p(2)[1] - p(4)[1]);
        const C = Math.hypot(p(0)[0] - p(3)[0], p(0)[1] - p(3)[1]);
        if (C === 0) return 0;
        return (A + B) / (2.0 * C);
    }

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

    function captureCropped(rect) {
        const ctx = canvas.getContext('2d');
        canvas.width = rect.sw;
        canvas.height = rect.sh;
        ctx.drawImage(video, rect.sx, rect.sy, rect.sw, rect.sh, 0, 0, rect.sw, rect.sh);
        return canvas.toDataURL('image/jpeg', 0.9);
    }

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
            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);
            return await resp.json();
        } catch (err) {
            clearTimeout(timeout);
            throw new Error("Network or API timeout: " + err.message);
        }
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
            if (isCapturing) {
                setTimeout(runDetection, 200);
                return;
            }
            if (video.paused || video.readyState < 2) {
                setTimeout(runDetection, 200);
                return;
            }

            const result = await faceapi.detectSingleFace(video, options).withFaceLandmarks(true);

            if (!result) {
                oval.style.borderColor = '#f80000ff';
                status.textContent = 'No face detected';
                detectionsBuffer.push(null);
                if (detectionsBuffer.length > MAX_BUFFER) detectionsBuffer.shift();
                console.log('[DEBUG] No face detected');

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
            if (inOval) {
                oval.style.borderColor = '#22c55e';
                status.textContent = 'Face aligned — please blink or move slightly';
            } else {
                oval.style.borderColor = '#f97316';
                status.textContent = 'Move to center';
            }
            const lm = result.landmarks;
            const leftEye = lm.getLeftEye().map(p => [p.x, p.y]);
            const rightEye = lm.getRightEye().map(p => [p.x, p.y]);
            const ear = (computeEAR(leftEye) + computeEAR(rightEye)) / 2.0;
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
            const lastTwo = detectionsBuffer.slice(-3);
            const centeredCount = lastTwo.filter(d => d && isBoxCenterInOval(d.pageBox)).length;
            const earValues = detectionsBuffer.filter(Boolean).map(d => d.ear);
            const earMin = Math.min(...earValues);
            const earMax = Math.max(...earValues);
            const earDiff = earMax - earMin;
            const centers = detectionsBuffer.filter(Boolean).map(d => d.center.y);
            const motion = centers.length >= 2 ? Math.abs(centers[centers.length - 1] - centers[0]) : 0;
            const BLINK_EAR_DIFF_THRESHOLD = 0.015;
            const MOTION_THRESHOLD_PX = 4;
            const blinkDetected = earDiff > BLINK_EAR_DIFF_THRESHOLD;
            const centeredEnough = centeredCount >= 2;
            const smallMotion = motion > MOTION_THRESHOLD_PX;

            console.log('[DEBUG] Detection:', {
                ear: ear.toFixed(3),
                earDiff: earDiff.toFixed(3),
                motion: motion.toFixed(2),
                centeredCount,
                blinkDetected,
                smallMotion,
                inOval
            });

            if (centeredEnough && (blinkDetected || smallMotion)) {
                isCapturing = true;

                oval.style.borderColor = '#0ea5e9';
                status.textContent = 'Capturing frames...';
                console.log('[DEBUG] Triggering capture (centeredEnough && liveness OK)');

                const rect = getOverlayRect();
                const frames = [];
                const ctx = canvas.getContext('2d');
                for (let i = 0; i < 3; i++) {
                    canvas.width = rect.sw;
                    canvas.height = rect.sh;
                    ctx.drawImage(video, rect.sx, rect.sy, rect.sw, rect.sh, 0, 0, rect.sw, rect.sh);
                    frames.push(canvas.toDataURL('image/jpeg', 0.9));
                    console.log(`[DEBUG] Captured frame ${i + 1}/3`);
                    await new Promise(r => setTimeout(r, 650));
                }

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
                    } else if (!result.is_verified) {
                        status.textContent = '❌ Face and Profile picture did not matched!';

                        fetch('/u/account/increment-fail', {
                                method: 'POST'
                            })
                            .then(res => res.json())
                            .then(data => {
                                if (data.status === 'locked') {
                                    const mins = Math.ceil(data.remaining_seconds / 60);
                                    status.textContent = `⛔ Too many failed attempts. Try again in ${mins} minute(s).`;
                                    isCapturing = false;
                                    showTooManyAttempts(mins);
                                    return;
                                }

                                console.log('Failed attempts:', data.fail_count);
                            })
                            .catch(err => console.error('Failed to record verification attempt:', err));

                        detectionsBuffer = [];
                        isCapturing = false;
                        setTimeout(runDetection, 5000);
                    }
                } catch (err) {
                    console.error('[DEBUG] Error posting frames:', err);
                    status.textContent = 'Server error. Try again.';
                    detectionsBuffer = [];
                    isCapturing = false;
                    setTimeout(runDetection, 200);
                }
            } else {
                setTimeout(runDetection, 200);
            }
        }

        runDetection();
    }

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