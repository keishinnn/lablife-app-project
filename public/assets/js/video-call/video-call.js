document.addEventListener("DOMContentLoaded", async () => {
    const connectBtn = document.getElementById("connectBtn");
    const joinCallBtn = document.getElementById("joinCallBtn");
    const toggleMic = document.getElementById("toggleMic");
    const toggleCam = document.getElementById("toggleCam");
    const screenShare = document.getElementById("screenShare");
    const endCall = document.getElementById("endCall");

    const localVideo = document.getElementById("localVideo");
    const remoteVideo = document.getElementById("remoteVideo");

    let client = null;
    let call = null;
    let micEnabled = true;
    let camEnabled = true;

    connectBtn.addEventListener("click", async () => {
        const res = await fetch("/u/video/get-video-token", { method: "POST" });
        const data = await res.json();

        const { StreamVideoClient } = window.StreamVideoClient;

        client = new StreamVideoClient({
            apiKey: "3uwkv6gcdj8y",
            user: {
                id: data.userId,
                name: data.userName,
                image: data.userImage,
            },
            token: data.token.token,
        });

        console.log("✅ Connected as:", data.userName);
    });

    joinCallBtn.addEventListener("click", async () => {
        if (!client) return alert("Connect first!");

        call = client.call("default", "dev_test_call");
        await call.join({ create: true });

        console.log("📞 Joined call:", call.id);

        await call.camera.enable();
        await call.microphone.enable();

        call.camera.setElement(localVideo);

        call.on("participantJoined", (event) => {
            const participant = event.participant;
            participant.setElement(remoteVideo);
            console.log("👤 Joined:", participant.userId);
        });
    });

    // 🎤 Toggle Mic
    toggleMic.addEventListener("click", async () => {
        if (!call) return;
        micEnabled = !micEnabled;
        if (micEnabled) {
            await call.microphone.enable();
        } else {
            await call.microphone.disable();
        }
        toggleMic.innerHTML = `<i class="fas fa-microphone${micEnabled ? "" : "-slash"}"></i>`;
    });

    // 🎥 Toggle Camera
    toggleCam.addEventListener("click", async () => {
        if (!call) return;
        camEnabled = !camEnabled;
        if (camEnabled) {
            await call.camera.enable();
        } else {
            await call.camera.disable();
        }
        toggleCam.innerHTML = `<i class="fas fa-video${camEnabled ? "" : "-slash"}"></i>`;
    });

    // 🖥️ Screen Share
    screenShare.addEventListener("click", async () => {
        if (!call) return;
        try {
            const screenStream = await navigator.mediaDevices.getDisplayMedia({ video: true });
            const [screenTrack] = screenStream.getVideoTracks();
            await call.publishTracks([screenTrack]);
            console.log("🖥️ Screen sharing started");
        } catch (err) {
            console.error("❌ Screen share failed:", err);
        }
    });

    // 🚪 End Call
    endCall.addEventListener("click", async () => {
        if (call) {
            await call.leave();
            console.log("👋 Left call");
        }
        window.location.href = "/u/dashboard"; // or any redirect
    });
});
