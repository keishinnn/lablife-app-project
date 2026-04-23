<?php require base_path('views/shared/header.php'); ?>

<section class="video-call-container">
    <div class="remote-video-container">
        <div class="remote-video-modify">
            <video id="remoteVideo" class="remote-video" autoplay playsinline></video>
        </div>

        <div id="remote-video-overlay" class="video-off-overlay" style="display: none;">
            <i class="fa-solid fa-video-slash"></i>
        </div>
    </div>

    <audio id="remoteAudio" autoplay playsinline></audio>

    <div class="local-video-container">
        <div class="local-video-modify">
            <video id="localVideo" class="local-video" autoplay muted playsinline></video>
        </div>

        <div id="local-video-overlay" class="video-off-overlay" style="display: none;">
            <i class="fa-solid fa-video-slash"></i>
        </div>
    </div>

    <div class="controls-bar">
        <button id="toggleMic" type="button" aria-label="Toggle microphone">
            <i class="fa-solid fa-microphone"></i>
        </button>
        <button id="toggleCam" type="button" aria-label="Toggle camera">
            <i class="fa-solid fa-video"></i>
        </button>
        <button id="endCall" type="button" class="end-call" aria-label="End call">
            <i class="fa-solid fa-phone-slash"></i>
        </button>
    </div>
</section>

<script>
    document.addEventListener("DOMContentLoaded", async () => {
        const callId = <?= json_encode($callId ?? '') ?>;
        const isCaller = <?= json_encode((bool) ($isCaller ?? false)) ?>;
        const localVideo = document.getElementById("localVideo");
        const remoteVideo = document.getElementById("remoteVideo");
        const remoteAudio = document.getElementById("remoteAudio");
        const localVideoOverlay = document.getElementById("local-video-overlay");
        const remoteVideoOverlay = document.getElementById("remote-video-overlay");
        const localVideoModify = document.querySelector(".local-video-modify");
        const remoteVideoModify = document.querySelector(".remote-video-modify");
        const dragElement = document.querySelector(".local-video-container");

        const toggleMicBtn = document.getElementById("toggleMic");
        const toggleCamBtn = document.getElementById("toggleCam");
        const endCallBtn = document.getElementById("endCall");
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? "";
        const apiKey = <?= json_encode($_ENV['STREAM_API_KEY']) ?>;

        let client = null;
        let call = null;
        let micEnabled = true;
        let camEnabled = true;
        let dragActive = false;
        let xOffset = 0;
        let yOffset = 0;

        if (!callId) {
            window.location.href = "/u/messages";
            return;
        }

        dragElement?.addEventListener("mousedown", dragStart);
        dragElement?.addEventListener("touchstart", dragStart, {
            passive: false
        });

        window.addEventListener("mouseup", dragEnd);
        window.addEventListener("touchend", dragEnd);

        window.addEventListener("mousemove", dragMove);
        window.addEventListener("touchmove", dragMove, {
            passive: false
        });

        function dragStart(event) {
            if (!dragElement) {
                return;
            }

            dragActive = true;
            dragElement.style.transition = "none";

            const clientX = event.type === "touchstart" ? event.touches[0].clientX : event.clientX;
            const clientY = event.type === "touchstart" ? event.touches[0].clientY : event.clientY;
            const rect = dragElement.getBoundingClientRect();

            xOffset = clientX - rect.left;
            yOffset = clientY - rect.top;

            dragElement.style.right = "auto";
            dragElement.style.bottom = "auto";
        }

        function dragMove(event) {
            if (!dragActive || !dragElement) {
                return;
            }

            event.preventDefault();

            const clientX = event.type === "touchmove" ? event.touches[0].clientX : event.clientX;
            const clientY = event.type === "touchmove" ? event.touches[0].clientY : event.clientY;

            dragElement.style.left = `${clientX - xOffset}px`;
            dragElement.style.top = `${clientY - yOffset}px`;
        }

        function dragEnd() {
            if (!dragActive || !dragElement) {
                return;
            }

            dragActive = false;
            dragElement.style.transition = "all 0.3s ease";

            const padding = 20;
            const rect = dragElement.getBoundingClientRect();
            const winWidth = window.innerWidth;
            const winHeight = window.innerHeight;

            if (rect.left + (rect.width / 2) < (winWidth / 2)) {
                dragElement.style.left = `${padding}px`;
                dragElement.style.right = "auto";
            } else {
                dragElement.style.left = "auto";
                dragElement.style.right = `${padding}px`;
            }

            let finalTop = rect.top;
            if (finalTop < padding) {
                finalTop = padding;
            } else if (finalTop + rect.height > winHeight - padding) {
                finalTop = winHeight - rect.height - padding;
            }

            dragElement.style.top = `${finalTop}px`;
            dragElement.style.bottom = "auto";
        }

        try {
            const res = await fetch("/u/video/get-video-token", {
                method: "POST",
                headers: {
                    "X-CSRF-Token": csrfToken
                }
            });

            if (!res.ok) {
                throw new Error("Failed to get video token.");
            }

            const data = await res.json();
            const StreamVideoClientClass = window.StreamVideoClient?.StreamVideoClient || window.StreamVideoClient;

            if (!StreamVideoClientClass) {
                throw new Error("StreamVideoClient not found.");
            }

            client = StreamVideoClientClass.getOrCreateInstance({
                apiKey,
                user: {
                    id: data.userId,
                    name: data.userName,
                    image: data.userImage,
                },
                token: data.token,
            });

            call = client.call("default", callId);

            if (isCaller) {
                await call.join({
                    create: true
                });
            } else {
                let joined = false;

                for (let i = 0; i < 5; i += 1) {
                    try {
                        await call.get();
                        joined = true;
                        break;
                    } catch (error) {
                        await new Promise((resolve) => setTimeout(resolve, 1000));
                    }
                }

                if (!joined) {
                    throw new Error("Call not found on server.");
                }

                await call.join();
            }

            await call.camera.enable({
                cameraFacingMode: "user"
            });
            await call.microphone.enable();

            const localStream = await navigator.mediaDevices.getUserMedia({
                video: {
                    facingMode: "user"
                },
                audio: true,
            });

            localVideo.srcObject = localStream;
            await localVideo.play().catch(console.warn);
            localVideoOverlay.style.display = "none";
            localVideoModify.style.display = "flex";

            const boundParticipants = new Set();

            function bindParticipant(participant) {
                if (!participant || participant.userId === data.userId || boundParticipants.has(participant.sessionId)) {
                    return;
                }

                boundParticipants.add(participant.sessionId);

                try {
                    call.bindVideoElement(remoteVideo, participant.sessionId, "videoTrack");
                    call.bindAudioElement(remoteAudio, participant.sessionId, "audioTrack");
                    remoteVideoOverlay.style.display = "none";
                    remoteVideoModify.style.display = "flex";
                } catch (error) {
                    console.warn("Failed to bind remote participant:", error);
                }
            }

            call.state.participants.forEach((participant) => {
                bindParticipant(participant);
            });

            call.on("participantJoined", (event) => {
                bindParticipant(event.participant);
            });

            call.on("trackPublished", (event) => {
                if (event.participant?.userId !== data.userId && event.type === 2) {
                    remoteVideoOverlay.style.display = "none";
                    remoteVideoModify.style.display = "flex";
                    bindParticipant(event.participant);
                }
            });

            call.on("trackUnpublished", (event) => {
                if (event.participant?.userId !== data.userId && event.type === 2) {
                    remoteVideoOverlay.style.display = "flex";
                    remoteVideoModify.style.display = "none";
                }
            });

            toggleMicBtn.addEventListener("click", async () => {
                try {
                    if (micEnabled) {
                        await call.microphone.disable();
                    } else {
                        await call.microphone.enable();
                    }

                    micEnabled = !micEnabled;
                    toggleMicBtn.innerHTML = micEnabled
                        ? '<i class="fa-solid fa-microphone"></i>'
                        : '<i class="fa-solid fa-microphone-slash"></i>';
                } catch (error) {
                    console.warn("Failed to toggle microphone:", error);
                }
            });

            toggleCamBtn.addEventListener("click", async () => {
                try {
                    if (camEnabled) {
                        await call.camera.disable();
                        localVideoOverlay.style.display = "flex";
                        localVideoModify.style.display = "none";
                    } else {
                        await call.camera.enable({
                            cameraFacingMode: "user"
                        });
                        localVideoOverlay.style.display = "none";
                        localVideoModify.style.display = "flex";
                    }

                    camEnabled = !camEnabled;
                    toggleCamBtn.innerHTML = camEnabled
                        ? '<i class="fa-solid fa-video"></i>'
                        : '<i class="fa-solid fa-video-slash"></i>';
                } catch (error) {
                    console.warn("Failed to toggle camera:", error);
                }
            });

            endCallBtn.addEventListener("click", async () => {
                try {
                    await fetch("/u/video/end-video-call", {
                        method: "POST",
                        headers: {
                            "X-CSRF-Token": csrfToken
                        },
                        body: new URLSearchParams({
                            callId
                        })
                    });

                    await call.leave();
                } catch (error) {
                    console.warn("Failed to leave call:", error);
                }

                if (localStream) {
                    localStream.getTracks().forEach((track) => track.stop());
                }

                window.location.href = "/u/messages";
            });
        } catch (err) {
            console.error("Error initializing call:", err);
            alert("Failed to connect video call.");
        }
    });
</script>

<?php require base_path('views/shared/footer.php'); ?>
