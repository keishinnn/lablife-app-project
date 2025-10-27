document.addEventListener("DOMContentLoaded", async () => {
    const connectBtn = document.getElementById("connectBtn");
    const joinCallBtn = document.getElementById("joinCallBtn");

    let client = null;
    let call = null;

    connectBtn.addEventListener("click", async () => {
        try {
            const res = await fetch("/u/video/get-video-token", { method: "POST" });
            const data = await res.json();

            console.log("Token received:", data);document.addEventListener("DOMContentLoaded", async () => {
    const connectBtn = document.getElementById("connectBtn");
    const joinCallBtn = document.getElementById("joinCallBtn");

    let client = null;
    let call = null;

    connectBtn.addEventListener("click", async () => {
        try {
            const res = await fetch("/u/video/get-video-token", { method: "POST" });
            const data = await res.json();

            console.log("Token received:", data);

            // ✅ Access the UMD bundle global
            const { StreamVideoClient } = window.StreamVideoClient;

            if (!StreamVideoClient) {
                console.error("StreamVideoClient not found in global object");
                return;
            }

            // ✅ Initialize client
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

        console.log("📞 Joined call:", call.id);

        const localVideo = document.getElementById("localVideo");
        const remoteVideo = document.getElementById("remoteVideo");

        // ✅ Enable camera and mic
        await call.camera.enable();
        await call.microphone.enable();

        // ✅ Attach local video
        call.camera.setElement(localVideo);

        // ✅ Handle remote participants
        call.on("participantJoined", (event) => {
            const participant = event.participant;
            console.log("👤 Remote participant joined:", participant.userId);

            participant.setElement(remoteVideo);
        });
    });
});


            const { StreamVideoClient } = window.StreamVideoClient; // ✅ Correct global name

            if (!StreamVideoClient) {
                console.error("StreamVideoClient not found in global object");
                return;
            }

            // ✅ Initialize the Stream Video client
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

        console.log("📞 Joined call:", call.id);

        // ✅ Use the new methods
        const localVideo = document.getElementById("localVideo");
        const remoteVideo = document.getElementById("remoteVideo");

        const localStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
        localVideo.srcObject = localStream;

        // ✅ Publish local tracks
        const [videoTrack] = localStream.getVideoTracks();
        const [audioTrack] = localStream.getAudioTracks();
        await call.publishTracks([videoTrack, audioTrack]);

        // ✅ Subscribe to remote participants
        call.on("participantJoined", async (event) => {
            console.log("👤 Remote participant joined:", event.participant.userId);

            event.participant.on("trackSubscribed", (trackEvent) => {
                if (trackEvent.track.kind === "video") {
                    remoteVideo.srcObject = new MediaStream([trackEvent.track]);
                }
            });
        });
    });
});
