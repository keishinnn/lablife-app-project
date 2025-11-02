    async function startVideoCall(callId, isCaller) {
        const messagesContainer = document.querySelector('.messages-container');

        if (window.currentAutoCancelTimer) {
            clearTimeout(window.currentAutoCancelTimer);
            window.currentAutoCancelTimer = null;
        }

        try {
            console.log("🎥 Starting video call:", callId, "| isCaller:", isCaller);
            messagesContainer.style.display = 'none';

            // Build UI
            const callView = document.createElement('div');
            callView.classList.add('video-call-container');
            callView.innerHTML = `
            <video id="remoteVideo" class="remote-video" autoplay playsinline></video>
            <audio id="remoteAudio" autoplay playsinline></audio>
            <video id="localVideo" class="local-video" autoplay muted playsinline></video>
            <div class="controls-bar">
                <button id="toggleMic" class="control-btn"><i class="fa-solid fa-microphone"></i></button>
                <button id="toggleCam" class="control-btn"><i class="fa-solid fa-video"></i></button>
                <button id="endCall" class="end-call control-btn"><i class="fa-solid fa-phone-slash"></i></button>
            </div>
        `;
            document.body.appendChild(callView);

            const StreamVideoClientClass = window.StreamVideoClient?.StreamVideoClient || window.StreamVideoClient;
            if (!StreamVideoClientClass) throw new Error("StreamVideoClient SDK not loaded");

            // Get video token
            const res = await fetch("/u/video/get-video-token", {
                method: "POST"
            });
            const tokenData = await res.json();
            const videoToken = tokenData.token;
            if (!videoToken) throw new Error("Video token missing from response");
            console.log("🔑 Video token obtained");

            // Initialize client
            const client = StreamVideoClientClass.getOrCreateInstance({
                apiKey,
                user: {
                    id: userId,
                    name: userName,
                    image: userImage
                },
                token: videoToken
            });
            console.log("⚙️ Client initialized");

            // Create call
            const call = client.call("default", callId);
            const remoteVideo = document.getElementById("remoteVideo");
            const remoteAudio = document.getElementById("remoteAudio");
            const localVideo = document.getElementById("localVideo");

            // Join call
            if (isCaller) {
                console.log("📞 Creating and joining call as caller...");
                await call.join({
                    create: true
                });
            } else {
                console.log("📞 Joining existing call as receiver...");
                let joined = false;
                for (let i = 0; i < 5; i++) {
                    try {
                        await call.get();
                        joined = true;
                        break;
                    } catch (e) {
                        console.log("⏳ Waiting for call to exist...");
                        await new Promise(r => setTimeout(r, 1000));
                    }
                }
                if (!joined) throw new Error("Call not found on server.");
                await call.join();
            }
            console.log("✅ Joined call");

            // Enable local media AFTER join
            console.log("📤 Enabling local media...");
            await call.camera.enable({
                publish: true
            });
            await call.microphone.enable({
                publish: true
            });
            console.log("✅ Camera and Microphone enabled");

            // Attach local preview using getUserMedia
            const localStream = await getMediaStream();
            if (localStream) {
                localVideo.srcObject = localStream;
                await localVideo.play().catch(e => console.warn("Local preview error:", e));
                console.log("✅ Local preview attached");
            }

            // Track bound elements to prevent duplicate bindings
            const boundElements = new Set();
            let processCount = 0;
            const MAX_PROCESS_COUNT = 10;

            // Helper function to bind participant video and audio
            function bindParticipant(participant) {
                const elementKey = `${participant.sessionId}`;

                // Skip if already bound
                if (boundElements.has(elementKey)) {
                    console.log("⏭️ Already bound:", elementKey);
                    return;
                }

                try {
                    console.log("🔗 Binding participant:", {
                        sessionId: participant.sessionId,
                        userId: participant.userId,
                        publishedTracks: participant.publishedTracks
                    });

                    // Bind video element using Stream SDK method
                    const unbindVideo = call.bindVideoElement(
                        remoteVideo,
                        participant.sessionId,
                        "videoTrack"
                    );
                    console.log("✅ Video bound for:", participant.sessionId);

                    // Bind audio element using Stream SDK method
                    const unbindAudio = call.bindAudioElement(
                        remoteAudio,
                        participant.sessionId
                    );
                    console.log("✅ Audio bound for:", participant.sessionId);

                    // Mark as bound
                    boundElements.add(elementKey);

                    // Store unbind functions for cleanup
                    if (!window.currentCallUnbinders) {
                        window.currentCallUnbinders = [];
                    }
                    window.currentCallUnbinders.push(unbindVideo, unbindAudio);

                } catch (e) {
                    console.error("❌ Failed to bind participant:", e);
                }
            }

            // Subscribe to participant changes
            console.log("🔄 Setting up participant subscription...");
            const subscription = call.state.participants$.subscribe((participants) => {
                if (processCount >= MAX_PROCESS_COUNT) {
                    console.warn("⚠️ Max process count reached");
                    return;
                }

                processCount++;
                console.log(`🔄 Participants updated (${processCount}/${MAX_PROCESS_COUNT}):`, participants.length);

                participants.forEach((participant) => {
                    // Skip local participant
                    if (participant.userId === userId) return;

                    // Only bind if they have published tracks
                    if (participant.publishedTracks && participant.publishedTracks.length > 0) {
                        bindParticipant(participant);
                    }
                });
            });

            // Controls
            const micBtn = callView.querySelector("#toggleMic");
            const camBtn = callView.querySelector("#toggleCam");
            const endBtn = callView.querySelector("#endCall");
            let micEnabled = true;
            let camEnabled = true;

            micBtn.addEventListener("click", async () => {
                try {
                    if (micEnabled) {
                        await call.microphone.disable();
                    } else {
                        await call.microphone.enable();
                    }
                    micEnabled = !micEnabled;
                    micBtn.innerHTML = micEnabled ?
                        '<i class="fa-solid fa-microphone"></i>' :
                        '<i class="fa-solid fa-microphone-slash"></i>';
                    console.log("🎤 Microphone toggled:", micEnabled);
                } catch (e) {
                    console.error("Mic toggle error:", e);
                }
            });

            camBtn.addEventListener("click", async () => {
                try {
                    if (camEnabled) {
                        await call.camera.disable();
                    } else {
                        await call.camera.enable();
                    }
                    camEnabled = !camEnabled;
                    camBtn.innerHTML = camEnabled ?
                        '<i class="fa-solid fa-video"></i>' :
                        '<i class="fa-solid fa-video-slash"></i>';
                    console.log("📹 Camera toggled:", camEnabled);
                } catch (e) {
                    console.error("Camera toggle error:", e);
                }
            });

            endBtn.addEventListener("click", async () => {
                try {
                    // Unsubscribe from participants
                    if (subscription) {
                        subscription.unsubscribe();
                        console.log("🔌 Unsubscribed from participants");
                    }

                    // Call unbind functions
                    if (window.currentCallUnbinders) {
                        window.currentCallUnbinders.forEach(unbind => {
                            try {
                                unbind();
                            } catch (e) {
                                console.warn("Unbind error:", e);
                            }
                        });
                        delete window.currentCallUnbinders;
                    }

                    // Stop local tracks
                    if (localStream) {
                        localStream.getTracks().forEach(t => {
                            t.stop();
                            console.log("🛑 Stopped track:", t.kind);
                        });
                    }

                    // Leave the call
                    await call.leave();
                    console.log("👋 Left the call");

                    // Mark call as inactive in chat
                    if (window.currentCallMessageId) {
                        await endInitiateVideoCall(window.currentCallMessageId);
                        delete window.currentCallMessageId;
                    }
                } catch (e) {
                    console.warn("Error leaving call:", e);
                }

                callView.remove();
                messagesContainer.style.display = 'block';
                delete window.currentCall;
                delete window.currentStream;
            });

            // Save references
            window.currentCall = call;
            window.currentStream = localStream;
            window.currentCallSubscription = subscription;

        } catch (err) {
            console.error("❌ Video call error:", err);
            alert("Failed to start video call: " + (err?.message || err));
            messagesContainer.style.display = 'block';
            const callView = document.querySelector('.video-call-container');
            if (callView) callView.remove();
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
                audio: true
            });

        const obsCamera = devices.find(d => d.label.includes("OBS Virtual Camera"));
        if (obsCamera)
            return await navigator.mediaDevices.getUserMedia({
                video: {
                    deviceId: obsCamera.deviceId
                },
                audio: true
            });

        alert("No camera found. Please connect one or enable OBS Virtual Camera.");
        return null;
    } catch (err) {
        console.error("Camera error:", err);
        return null;
    }
}