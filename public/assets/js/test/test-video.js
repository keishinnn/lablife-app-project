document.addEventListener("DOMContentLoaded", async () => {
    const connectBtn = document.getElementById("connectBtn");
    const joinCallBtn = document.getElementById("joinCallBtn");

    let client = null;
    let call = null;

    connectBtn.addEventListener("click", async () => {
        try {
            const res = await fetch("/u/video/get-video-token", { method: "POST" });
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

        // Enable camera & mic
        await call.camera.enable();
        await call.microphone.enable();

        const localEl = document.getElementById("localVideo");
        const remoteEl = document.getElementById("remoteVideo");

        call.camera.attach(localEl);

        call.on("participantJoined", e => {
            console.log("👤 Remote participant joined:", e.participant.userId);
            e.participant.attach(remoteEl);
        });

        console.log("📞 Joined call:", call.id);
    });
});
