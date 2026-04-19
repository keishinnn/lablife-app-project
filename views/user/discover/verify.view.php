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

    const API_VERIFY = '/u/account/verify-face';
    const API_HEALTH = '/u/account/verify-service-health';
    const CURRENT_USER_ID = <?= json_encode($user->id) ?>;
    const PROFILE_PHOTO_URL = <?= json_encode($user->avatarUrl) ?>;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let isCapturing = false;
    let livenessState = 'CENTER';
    let earBuffer = [];

    const EAR_BUFFER_MAX = 10;
    const YAW_THRESHOLD_LEFT = 0.38;
    const YAW_THRESHOLD_RIGHT = 0.62;
    const EAR_BLINK_DIFF = 0.025;
    const ACTION_TIMEOUT_MS = 4000;

    let lastActionTime = Date.now();

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

    async function getMediaStream() {
        try {
            const devices = await navigator.mediaDevices.enumerateDevices();
            const webcams = devices.filter(d => d.kind === 'videoinput');

            if (webcams.length > 0) {
                return await navigator.mediaDevices.getUserMedia({
                    video: {
                        deviceId: webcams[0].deviceId
                    },
                    audio: false,
                });
            }

            const obsCamera = devices.find(d => d.label.includes('OBS Virtual Camera'));
            if (obsCamera) {
                return await navigator.mediaDevices.getUserMedia({
                    video: {
                        deviceId: obsCamera.deviceId
                    },
                    audio: false,
                });
            }

            return null;
        } catch (err) {
            console.error('Camera error:', err);
            return null;
        }
    }

    function computeEAR(eye) {
        const p = i => eye[i];
        const a = Math.hypot(p(1)[0] - p(5)[0], p(1)[1] - p(5)[1]);
        const b = Math.hypot(p(2)[0] - p(4)[0], p(2)[1] - p(4)[1]);
        const c = Math.hypot(p(0)[0] - p(3)[0], p(0)[1] - p(3)[1]);

        if (c === 0) return 0;
        return (a + b) / (2 * c);
    }

    function calculateYaw(landmarks) {
        try {
            const jaw = landmarks.getJawOutline();
            const leftJaw = jaw[0];
            const rightJaw = jaw[16];
            const noseTip = landmarks.getNose()[6];

            if (!leftJaw || !rightJaw || !noseTip) return 0.5;

            const jawWidth = rightJaw.x - leftJaw.x;
            const noseToLeft = noseTip.x - leftJaw.x;

            if (jawWidth === 0) return 0.5;
            return Math.max(0, Math.min(1, noseToLeft / jawWidth));
        } catch (e) {
            console.error('Error calculating yaw:', e);
            return 0.5;
        }
    }

    function resetLiveness(showMsg = true) {
        if (showMsg && status.textContent !== 'Capturing frames...') {
            status.textContent = 'Move to center';
        }

        livenessState = 'CENTER';
        earBuffer = [];
        lastActionTime = Date.now();

        if (oval) {
            oval.style.borderColor = '#f97316';
        }
    }

    function isBoxCenterInOval(box) {
        const oRect = oval.getBoundingClientRect();
        const cx = box.x + box.width / 2;
        const cy = box.y + box.height / 2;

        if (cx < oRect.left || cx > oRect.right || cy < oRect.top || cy > oRect.bottom) {
            return false;
        }

        const rx = oRect.width / 2;
        const ry = oRect.height / 2;
        const dx = cx - (oRect.left + rx);
        const dy = cy - (oRect.top + ry);

        return ((dx * dx) / (rx * rx)) + ((dy * dy) / (ry * ry)) <= 1;
    }

    function getOverlayRect() {
        const ov = oval.getBoundingClientRect();
        const v = video.getBoundingClientRect();
        const offsetX = ov.left - v.left;
        const offsetY = ov.top - v.top;
        const scaleX = video.videoWidth / v.width;
        const scaleY = video.videoHeight / v.height;

        return {
            sx: Math.round(offsetX * scaleX),
            sy: Math.round(offsetY * scaleY),
            sw: Math.round(ov.width * scaleX),
            sh: Math.round(ov.height * scaleY),
        };
    }

    async function postFrames(framesDataUrls) {
        const controller = new AbortController();
        const timeout = setTimeout(() => controller.abort(), 20000);

        const form = new FormData();
        form.append('user_id', CURRENT_USER_ID);
        form.append('profile_url', PROFILE_PHOTO_URL);

        framesDataUrls.forEach((dataUrl, index) => {
            const blob = dataURLtoBlob(dataUrl);
            form.append('frames', blob, `frame${index}.jpg`);
        });

        try {
            const response = await fetch(API_VERIFY, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken,
                },
                body: form,
                signal: controller.signal,
            });

            clearTimeout(timeout);

            if (!response.ok) {
                let errorJson = null;
                try {
                    errorJson = await response.json();
                } catch (e) {
                    errorJson = null;
                }

                const err = new Error(`HTTP ${response.status}`);
                err.response = response;
                err.json = errorJson;
                throw err;
            }

            return await response.json();
        } catch (err) {
            clearTimeout(timeout);
            if (err.response) throw err;
            throw new Error(`Network or API timeout: ${err.message}`);
        }
    }

    function dataURLtoBlob(dataUrl) {
        const parts = dataUrl.split(',');
        const mime = parts[0].match(/:(.*?);/)[1];
        const bytes = atob(parts[1]);
        const arr = new Uint8Array(bytes.length);

        for (let i = 0; i < bytes.length; i += 1) {
            arr[i] = bytes.charCodeAt(i);
        }

        return new Blob([arr], {
            type: mime
        });
    }

    async function loadModels() {
        const modelUrl = '/assets/models';
        await faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl);
        await faceapi.nets.faceLandmark68TinyNet.loadFromUri(modelUrl);
    }

    async function markVerified() {
        const response = await fetch('/u/account/set-verified', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
            },
            body: JSON.stringify({
                user_id: CURRENT_USER_ID
            }),
        });

        if (!response.ok) {
            throw new Error('Failed to update verification state.');
        }
    }

    async function incrementFailCount() {
        const response = await fetch('/u/account/increment-fail', {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken,
            },
        });

        if (!response.ok) {
            throw new Error('Failed to update verification attempts.');
        }

        return response.json();
    }

    async function startDetectionLoop() {
        const options = new faceapi.TinyFaceDetectorOptions({
            inputSize: 224,
            scoreThreshold: 0.5,
        });

        async function runDetection() {
            if (video.paused || video.readyState < 2) {
                setTimeout(runDetection, 200);
                return;
            }

            const result = await faceapi.detectSingleFace(video, options).withFaceLandmarks(true);
            const now = Date.now();

            if (!result) {
                oval.style.borderColor = '#f80000';
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
                height: box.height * scaleY,
            };

            const inOval = isBoxCenterInOval(pageBox);
            if (!inOval) {
                oval.style.borderColor = '#f97316';
                resetLiveness();
                setTimeout(runDetection, 200);
                return;
            }

            oval.style.borderColor = '#22c55e';
            const lm = result.landmarks;

            if (now - lastActionTime > ACTION_TIMEOUT_MS && livenessState !== 'CAPTURE') {
                resetLiveness(true);
                setTimeout(runDetection, 200);
                return;
            }

            switch (livenessState) {
                case 'CENTER':
                    status.textContent = 'Great! Now please blink';
                    livenessState = 'BLINK';
                    lastActionTime = now;
                    break;

                case 'BLINK': {
                    status.textContent = 'Please blink';
                    const leftEye = lm.getLeftEye().map(p => [p.x, p.y]);
                    const rightEye = lm.getRightEye().map(p => [p.x, p.y]);
                    const ear = (computeEAR(leftEye) + computeEAR(rightEye)) / 2;

                    earBuffer.push(ear);
                    if (earBuffer.length > EAR_BUFFER_MAX) earBuffer.shift();

                    if (earBuffer.length === EAR_BUFFER_MAX) {
                        const earMin = Math.min(...earBuffer);
                        const earMax = Math.max(...earBuffer);

                        if ((earMax - earMin) > EAR_BLINK_DIFF) {
                            livenessState = 'TURN_LEFT';
                            lastActionTime = now;
                            earBuffer = [];
                        }
                    }
                    break;
                }

                case 'TURN_LEFT': {
                    status.textContent = 'Now turn your head right';
                    const yawLeft = calculateYaw(lm);
                    if (yawLeft < YAW_THRESHOLD_LEFT) {
                        livenessState = 'TURN_RIGHT';
                        lastActionTime = now;
                    }
                    break;
                }

                case 'TURN_RIGHT': {
                    status.textContent = 'Now turn your head left';
                    const yawRight = calculateYaw(lm);
                    if (yawRight > YAW_THRESHOLD_RIGHT) {
                        livenessState = 'CAPTURE';
                        lastActionTime = now;
                    }
                    break;
                }

                case 'CAPTURE': {
                    status.textContent = 'Perfect! Hold still, look at the camera';
                    const yawCenter = calculateYaw(lm);

                    if (yawCenter > YAW_THRESHOLD_LEFT + 0.05 && yawCenter < YAW_THRESHOLD_RIGHT - 0.05) {
                        isCapturing = true;
                        oval.style.borderColor = '#0ea5e9';

                        const rect = getOverlayRect();
                        const frames = [];
                        const ctx = canvas.getContext('2d');

                        for (let i = 0; i < 2; i += 1) {
                            canvas.width = rect.sw;
                            canvas.height = rect.sh;
                            ctx.drawImage(video, rect.sx, rect.sy, rect.sw, rect.sh, 0, 0, rect.sw, rect.sh);
                            frames.push(canvas.toDataURL('image/jpeg', 0.9));
                            await new Promise(resolve => setTimeout(resolve, 300));
                        }

                        status.textContent = 'Do not move';

                        try {
                            const resultPayload = await postFrames(frames);

                            if (resultPayload.is_verified) {
                                status.textContent = 'Verified';
                                await markVerified();
                                setTimeout(() => window.location.reload(), 900);
                            } else {
                                status.textContent = resultPayload.message || 'Face and profile picture did not match.';
                                oval.style.borderColor = '#f80000';

                                try {
                                    const failData = await incrementFailCount();
                                    if (failData.status === 'locked') {
                                        const mins = Math.ceil(failData.remaining_seconds / 60);
                                        status.textContent = `Too many failed attempts. Try again in ${mins} minute(s).`;
                                        isCapturing = true;
                                        showTooManyAttempts(mins);
                                        return;
                                    }
                                } catch (err) {
                                    console.error('Failed to record verification attempt:', err);
                                }

                                setTimeout(() => {
                                    isCapturing = false;
                                    resetLiveness(true);
                                    runDetection();
                                }, 2000);
                            }
                        } catch (err) {
                            console.error('Verification error:', err);
                            oval.style.borderColor = '#f80000';

                            if (err.response && err.response.status === 429) {
                                const msg = err.json ? err.json.message : 'Too many attempts. Please try again later.';
                                const minsMatch = msg.match(/(\d+)\s+minute/);
                                const waitMins = minsMatch ? parseInt(minsMatch[1], 10) : 10;
                                status.textContent = msg;
                                isCapturing = true;
                                showTooManyAttempts(waitMins);
                                return;
                            }

                            status.textContent = 'Server error. Try again.';
                            setTimeout(() => {
                                isCapturing = false;
                                resetLiveness(true);
                                runDetection();
                            }, 2000);
                        }

                        return;
                    }
                    break;
                }
            }

            if (!isCapturing) {
                setTimeout(runDetection, 200);
            }
        }

        runDetection();
    }

    function showTooManyAttempts(time) {
        reusablePopup(`Too many attempts. Please try again in ${time} minute${time > 1 ? 's' : ''}.`, 'Try again', 'Exit');
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
        });
    }

    if (popupModalBtnTwo) {
        popupModalBtnTwo.addEventListener('click', () => {
            window.location.href = '/u/verify-next';
        });
    }

    async function checkAPIHealth() {
        try {
            const res = await fetch(API_HEALTH);
            return res.ok;
        } catch {
            return false;
        }
    }

    (async function init() {
        try {
            const failRes = await fetch('/u/account/fail-status');
            const data = await failRes.json();
            const mins = Math.ceil(data.remaining_seconds / 60);

            if (data.remaining_seconds > 0) {
                showTooManyAttempts(mins);
                return;
            }

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

            status.textContent = 'Camera ready - position face inside oval';
            await new Promise(resolve => setTimeout(resolve, 300));
            startDetectionLoop();
        } catch (err) {
            console.error('Init error:', err);
            showServiceUnavailable();
        }
    })();
</script>

<?php require base_path('views/shared/footer.php') ?>