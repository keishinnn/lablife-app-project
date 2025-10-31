<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Video Call</title>

    <script src="/assets/js/stream-video-client.js"></script>

    <!-- Font Awesome for icons -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

    <style>
        /* ====== VIDEO CALL LAYOUT ====== */
        body {
            margin: 0;
            background: #0a0a0a;
            color: white;
            font-family: "Segoe UI", sans-serif;
            height: 100vh;
            overflow: hidden;
        }

        /* Container for the call */
        .video-call-container {
            position: relative;
            width: 100%;
            height: 100%;
            background-color: #000;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* Remote video takes full space */
        .remote-video {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Local video (small one) */
        .local-video {
            position: absolute;
            bottom: 20px;
            right: 20px;
            width: 220px;
            height: 140px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid white;
            background: #000;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.2);
        }

        /* Toolbar for controls */
        .controls-bar {
            position: absolute;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 20px;
            background: rgba(0, 0, 0, 0.5);
            padding: 10px 20px;
            border-radius: 40px;
            backdrop-filter: blur(5px);
        }

        .controls-bar button {
            background: #1e1e1e;
            color: white;
            border: none;
            padding: 14px;
            border-radius: 50%;
            cursor: pointer;
            transition: 0.3s;
            font-size: 18px;
        }

        .controls-bar button:hover {
            background: #444;
        }

        /* End call button */
        .controls-bar .end-call {
            background: #d93025;
        }

        .controls-bar .end-call:hover {
            background: #b22218;
        }

        /* Responsive adjustment */
        @media (max-width: 600px) {
            .local-video {
                width: 120px;
                height: 80px;
            }

            .controls-bar {
                gap: 10px;
                padding: 8px 12px;
            }

            .controls-bar button {
                padding: 10px;
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="video-call-container">
        <!-- Remote (other user) -->
        <video id="remoteVideo" class="remote-video" autoplay playsinline></video>

        <!-- Local (you) -->
        <video id="localVideo" class="local-video" autoplay muted playsinline></video>

        <!-- Control buttons -->
        <div class="controls-bar">
            <button id="toggleMic"><i class="fa-solid fa-microphone"></i></button>
            <button id="toggleCam"><i class="fa-solid fa-video"></i></button>
            <button id="endCall" class="end-call"><i class="fa-solid fa-phone-slash"></i></button>
        </div>
    </div>

    <script>
        async function startVideoCall(callId, isCaller) {
            const messagesContainer = document.querySelector('.messages-container');

            try {
                console.log("🔹 Starting video call", {
                    callId,
                    isCaller
                });

                messagesContainer.style.display = 'none';
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

                // ✅ Initialize Stream Video Client
                console.log("🔹 Fetching video token...");
                // ✅ Initialize Stream client
                const StreamVideoClient = window.StreamVideoClient;
                const res = await fetch("/u/video/get-video-token", {
                    method: "POST"
                });
                const {
                    token
                } = await res.json();
                console.log("✅ Video token received", token);

                if (!window.StreamVideoClient) {
                    console.error("❌ StreamVideoClient not found. Ensure the Stream Video SDK script is loaded.");
                    return;
                }

                const client = StreamVideoClient({
                    apiKey: apiKey,
                    user: {
                        id: userId,
                        name: userName,
                        image: userImage
                    },
                    token: token.token
                });

                console.log("🔹 Joining Stream video call...");
                const call = client.call("default", callId);
                await call.join({
                    create: true
                });
                console.log("✅ Joined call successfully");

                // ✅ Get local stream
                console.log("🔹 Getting local media...");
                const stream = await getMediaStream();
                if (!stream) {
                    console.error("❌ Could not get local media stream");
                    return;
                }
                console.log("✅ Local media stream ready", stream);

                const localVideo = document.getElementById("localVideo");
                const remoteVideo = document.getElementById("remoteVideo");
                localVideo.srcObject = stream;
                await localVideo.play().catch(console.error);
                console.log("🎥 Local video playing");

                // ✅ Enable camera + mic in Stream
                await call.camera.enable();
                await call.microphone.enable();
                console.log("🎤 Camera and mic enabled on call");

                // ✅ Debug remote track subscription
                call.on("trackSubscribed", (event) => {
                    console.log("📡 Track subscribed event:", event);
                    if (event.track.kind === "video") {
                        console.log("✅ Remote video track received");
                        const remoteStream = new MediaStream([event.track]);
                        remoteVideo.srcObject = remoteStream;
                        remoteVideo.play().catch(console.error);
                    } else if (event.track.kind === "audio") {
                        console.log("🎧 Remote audio track subscribed");
                    } else {
                        console.warn("⚠️ Unknown track kind:", event.track.kind);
                    }
                });

                call.on("participantJoined", (e) => {
                    console.log("👥 Participant joined:", e.participant.userId);
                });

                call.on("participantLeft", (e) => {
                    console.log("🚪 Participant left:", e.participant.userId);
                });

                call.on("connectionStateChanged", (e) => {
                    console.log("🌐 Connection state changed:", e.newState);
                });

                call.on("error", (err) => {
                    console.error("❌ Stream call error:", err);
                });

                // ✅ Store for cleanup
                window.currentCall = call;
                window.currentStream = stream;

                // ✅ Call controls
                const micBtn = callView.querySelector("#toggleMic");
                const camBtn = callView.querySelector("#toggleCam");
                const endBtn = callView.querySelector("#endCall");
                let micEnabled = true;
                let camEnabled = true;

                micBtn.addEventListener("click", () => {
                    micEnabled = !micEnabled;
                    stream.getAudioTracks().forEach(t => t.enabled = micEnabled);
                    micBtn.innerHTML = micEnabled ?
                        '<i class="fa-solid fa-microphone"></i>' :
                        '<i class="fa-solid fa-microphone-slash"></i>';
                    console.log(`🎤 Mic ${micEnabled ? "enabled" : "muted"}`);
                });

                camBtn.addEventListener("click", () => {
                    camEnabled = !camEnabled;
                    stream.getVideoTracks().forEach(t => t.enabled = camEnabled);
                    camBtn.innerHTML = camEnabled ?
                        '<i class="fa-solid fa-video"></i>' :
                        '<i class="fa-solid fa-video-slash"></i>';
                    console.log(`📷 Camera ${camEnabled ? "on" : "off"}`);
                });

                endBtn.addEventListener("click", async () => {
                    console.log("☎️ Call ended manually");
                    await call.leave();
                    stream.getTracks().forEach(t => t.stop());
                    callView.remove();
                    delete window.currentCall;
                    delete window.currentStream;
                });

            } catch (err) {
                console.error("❌ Error starting video call:", err);
            }
        }


        document.addEventListener("DOMContentLoaded", async () => {
            const localVideo = document.getElementById("localVideo");
            const remoteVideo = document.getElementById("remoteVideo");

            const toggleMicBtn = document.getElementById("toggleMic");
            const toggleCamBtn = document.getElementById("toggleCam");
            const endCallBtn = document.getElementById("endCall");

            let client = null;
            let call = null;
            let micEnabled = true;
            let camEnabled = true;

            try {
                const res = await fetch("/u/video/get-video-token", {
                    method: "POST"
                });
                const data = await res.json();

                const {
                    StreamVideoClient
                } = window.StreamVideoClient;

                client = new StreamVideoClient({
                    apiKey: "3uwkv6gcdj8y",
                    user: {
                        id: data.userId,
                        name: data.userName,
                        image: data.userImage,
                    },
                    token: data.token.token,
                });

                console.log("✅ Connected to Stream as:", data.userName);

                // Join or create call
                const callId = "dev_test_call";
                call = client.call("default", callId);
                await call.join({
                    create: true
                });

                // Use OBS Virtual Camera if available
                const devices = await navigator.mediaDevices.enumerateDevices();
                const obsCamera = devices.find(d => d.label.includes("OBS Virtual Camera"));

                if (obsCamera) {
                    const stream = await navigator.mediaDevices.getUserMedia({
                        video: {
                            deviceId: obsCamera.deviceId
                        },
                        audio: true,
                    });

                    // Attach local stream manually to <video>
                    localVideo.srcObject = stream;
                    localVideo.play();

                    // Replace default camera track in call with OBS
                    const videoTrack = stream.getVideoTracks()[0];
                    const audioTrack = stream.getAudioTracks()[0];

                    await call.localParticipant.setCamera(videoTrack);
                    await call.localParticipant.setMicrophone(audioTrack);

                } else {
                    // fallback to default
                    await call.camera.enable();
                    await call.microphone.enable();
                    call.camera.attach(localVideo);
                }

                // Attach remote participant video
                call.on("participantJoined", e => {
                    console.log("👤 Remote participant joined:", e.participant.userId);
                    e.participant.attach(remoteVideo);
                });

                console.log("📞 Joined call:", callId);

                // Toggle mic
                toggleMicBtn.onclick = () => {
                    micEnabled = !micEnabled;
                    call.localParticipant.microphone.enabled = micEnabled;
                    toggleMicBtn.innerHTML = micEnabled ?
                        '<i class="fa-solid fa-microphone"></i>' :
                        '<i class="fa-solid fa-microphone-slash"></i>';
                };

                // Toggle camera
                toggleCamBtn.onclick = () => {
                    camEnabled = !camEnabled;
                    call.localParticipant.camera.enabled = camEnabled;
                    toggleCamBtn.innerHTML = camEnabled ?
                        '<i class="fa-solid fa-video"></i>' :
                        '<i class="fa-solid fa-video-slash"></i>';
                };

                // End call
                endCallBtn.onclick = async () => {
                    await call.leave();
                    window.location.href = "/u/messages";
                };

            } catch (err) {
                console.error("❌ Error initializing call:", err);
                alert("Failed to connect video call.");
            }
        });
    </script>
</body>

</html>