document.addEventListener("DOMContentLoaded", async () => {
    const connectBtn = document.getElementById("connectBtn");
    const joinCallBtn = document.getElementById("joinCallBtn");

    let client = null;
    let call = null;

    connectBtn.addEventListener("click", async () => {
        try {
const res = await fetch("/u/video/get-video-token", {
    method: "POST",
    headers: {
        "X-CSRF-Token": document.querySelector('meta[name="csrf-token"]')?.content ?? ""
    }
});
            const data = await res.json();

            console.log("Token received:", data);

            const { StreamVideoClient } = window.StreamVideoClient;

            if (!StreamVideoClient) {
                console.error("StreamVideoClient not found in global object");
                return;
            }

            // ✅ This already connects automatically
            client = new StreamVideoClient({
                apiKey: "3uwkv6gcdj8y",
                user: {
                    id: data.userId,
                    name: data.userName,
                    image: data.userImage,
                },
                token: data.token.token,
            });

            console.log("✅ Connected to Stream Video as:", data.userName);
        } catch (err) {
            console.error("❌ Failed to connect:", err);
        }
    });


    joinCallBtn.addEventListener("click", async () => {
        if (!client) {
            alert("Please connect first.");
            return;
        }

        const callId = "dev_test_call";
        call = client.call("default", callId);

        await call.join({ create: true });

        // Get list of all cameras
        const devices = await navigator.mediaDevices.enumerateDevices();
        const obsCamera = devices.find(d => d.label.includes("OBS Virtual Camera"));

        if (obsCamera) {
            const stream = await navigator.mediaDevices.getUserMedia({
                video: { deviceId: obsCamera.deviceId },
                audio: true,
            });

            const localEl = document.getElementById("localVideo");
            localEl.srcObject = stream;
            localEl.play();

            // Attach manually to the Stream Video call
            await call.publishStream(stream, { video: true, audio: true });
        } else {
            // fallback to default webcam if OBS is not running
            await call.camera.enable();
            await call.microphone.enable();
        }

        const remoteEl = document.getElementById("remoteVideo");

        call.on("participantJoined", e => {
            console.log("👤 Remote participant joined:", e.participant.userId);
            e.participant.attach(remoteEl);
        });

        console.log("📞 Joined call:", call.id);
    });

});
