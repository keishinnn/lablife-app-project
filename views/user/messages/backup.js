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
            <video id="localVideo" class="local-video" autoplay muted playsinline></video>
            <div class="controls-bar">
                <button id="toggleMic" class="control-btn"><i class="fa-solid fa-microphone"></i></button>
                <button id="toggleCam" class="control-btn"><i class="fa-solid fa-video"></i></button>
                <button id="endCall" class="end-call control-btn"><i class="fa-solid fa-phone-slash"></i></button>
            </div>
        `;
        document.body.appendChild(callView);

        // Check what's available in the SDK
        console.log("SDK exports:", Object.keys(window.StreamVideoClient || {}));

        // The ES module bundle exports StreamVideoClient directly
        const StreamVideoClientClass = window.StreamVideoClient?.StreamVideoClient || window.StreamVideoClient;

        if (!StreamVideoClientClass) {
            throw new Error("StreamVideoClient SDK not loaded");
        }

        // Get video token
        const res = await fetch("/u/video/get-video-token", {
            method: "POST"
        });
        const tokenData = await res.json();
        const videoToken = tokenData.token;

        if (!videoToken) {
            throw new Error("Video token missing from response");
        }

        console.log("🔑 Video token obtained");

        // Create client
        const client = new StreamVideoClientClass({
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
        const localVideo = document.getElementById("localVideo");

        // ✅ Correct remote video binding
        // 🧩 Listen when any participant updates
        call.on("participant_updated", (event) => {
            const p = event.participant;
            if (!p || p.userId === userId) return;
            console.log("🔁 participant_updated:", {
                id: p.userId,
                session: p.sessionId,
                publishedTracks: p.publishedTracks,
                tracks: p.tracks
            });

            try {
                const remoteVideo = document.getElementById("remoteVideo");
                console.log("🎥 Trying bind via participant_updated...");
                call.bindVideoElement(p.sessionId, remoteVideo);
                call.bindAudioElement(p.sessionId, remoteVideo);
                console.log("✅ Bound via participant_updated");
            } catch (err) {
                console.warn("participant_updated bind failed:", err);
            }
        });

        // 🧩 Listen specifically for new tracks (very reliable)
        call.on("track_published", (event) => {
            const {
                participant,
                trackType
            } = event;
            if (!participant || participant.userId === userId) return;
            console.log("📡 track_published:", {
                userId: participant.userId,
                session: participant.sessionId,
                trackType
            });
            try {
                const remoteVideo = document.getElementById("remoteVideo");
                call.bindVideoElement(participant.sessionId, remoteVideo);
                call.bindAudioElement(participant.sessionId, remoteVideo);
                console.log("✅ Bound remote track after publish:", trackType);
            } catch (err) {
                console.warn("track_published bind failed:", err);
            }
        });

        // Join call
        console.log(`📞 Joining call...`);
        await call.get();

        if (isCaller) {
            await call.join({
                create: true
            });
        } else {
            await call.join();
        }



        // Bind any existing participants after joining
        for (const participant of call.state.remoteParticipants.values()) {
            if (participant.userId !== userId) {
                call.bindVideoElement(participant.sessionId, document.getElementById("remoteVideo"));
                call.bindAudioElement(participant.sessionId, document.getElementById("remoteVideo"));
            }
        }

        // 🔧 Force attach remote video stream manually if SDK bind doesn't show anything
        for (const participant of call.state.remoteParticipants.values()) {
            if (participant.userId === userId) continue;

            // StreamVideo SDK usually stores tracks inside participant.tracks.video
            const videoTrack = participant.tracks?.video?.track;
            if (videoTrack) {
                console.log("🎬 Manually attaching remote video track for:", participant.userId);
                const remoteStream = new MediaStream([videoTrack]);
                const remoteVideo = document.getElementById("remoteVideo");
                remoteVideo.srcObject = remoteStream;
                await remoteVideo.play().catch(e => console.warn("Remote play failed:", e));
            } else {
                console.warn("⚠️ No remote video track found for:", participant.userId);
            }
        }

        console.log("✅ Joined call");

        // 🧩 Now participants list is available
        if (call.state?.remoteParticipants) {
            console.log("🧭 Remote participants after join:", call.state.remoteParticipants.size);
            for (const p of call.state.remoteParticipants.values()) {
                console.log("🧭 participant:", {
                    userId: p.userId,
                    session: p.sessionId,
                    publishedTracks: p.publishedTracks
                });
                if (p.userId !== userId) {
                    try {
                        call.bindVideoElement(p.sessionId, document.getElementById("remoteVideo"));
                        call.bindAudioElement(p.sessionId, document.getElementById("remoteVideo"));
                        console.log("✅ Bound pre-existing participant:", p.userId);
                    } catch (err) {
                        console.warn("Pre-bind failed:", err);
                    }
                }
            }
        } else {
            console.warn("⚠️ call.state.remoteParticipants not ready yet");
        }

        // 🧠 Delay + re-check remote participants for late track publishing
        setTimeout(async () => {
            for (const participant of call.state.remoteParticipants.values()) {
                if (participant.userId === userId) continue;
                console.log("🔁 Re-checking remote participant after join:", participant.userId);

                const videoTrack = participant.tracks?.video?.track;
                if (videoTrack) {
                    const remoteVideo = document.getElementById("remoteVideo");
                    const remoteStream = new MediaStream([videoTrack]);
                    remoteVideo.srcObject = remoteStream;
                    await remoteVideo.play().catch(e => console.warn("Remote video play failed:", e));
                    console.log("✅ Late-bound remote video:", participant.userId);
                } else {
                    console.warn("⚠️ Still no remote track yet for:", participant.userId);
                }
            }
        }, 2000);


        // Enable our camera and mic
        console.log("📤 Enabling local media...");

        await call.camera.enable();
        console.log("✅ Camera enabled");

        await call.microphone.enable();
        console.log("✅ Microphone enabled");

        // Wait a bit for tracks to initialize
        await new Promise(r => setTimeout(r, 1000));

        // Attach local preview
        const localStream = call.camera.state.mediaStream;
        if (localStream) {
            localVideo.srcObject = localStream;
            await localVideo.play().catch(e => console.warn("Local preview:", e));
            console.log("✅ Local preview attached");
        }

        for (const participant of call.state.remoteParticipants.values()) {
            if (participant.userId === userId) continue;
            console.log("🎥 Binding remote participant:", participant.userId);
            call.bindVideoElement(participant.sessionId, document.getElementById("remoteVideo"));
            call.bindAudioElement(participant.sessionId, document.getElementById("remoteVideo"));
        }

        // Setup controls
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
            } catch (e) {
                console.error("Camera toggle error:", e);
            }
        });

        endBtn.addEventListener("click", async () => {
            try {
                if (localStream) {
                    localStream.getTracks().forEach(t => t.stop());
                }
                await call.leave();

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