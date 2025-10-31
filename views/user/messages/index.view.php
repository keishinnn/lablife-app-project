<?php
require base_path('views/shared/header.php');
?>

<div class="messages-container" id="message-container-wrapper">
    <div class="messages-chat-list">
        <!-- left side -->
        <?php require(base_path('Views/user/messages/chat/chat.list.view.php')) ?>
    </div>

    <!-- right side -->
    <div class="messages-chat-conversation">

        <?php require(base_path('Views/user/messages/chat/chat.header.view.php')) ?>
        <div style="flex: 1 1 0%; min-height: 0;">
            <?php require(base_path('Views/user/messages/chat/chat.view.php')) ?>
        </div>
    </div>

</div>

<div class="chat-list-section-one" id="empty-state">
    <div class="chat-list-section-two">
        <span>💬</span>
    </div>
    <h2>No conversations yet</h2>
    <p>Start swiping to find matches and begin conversations!</p>
    <a href="/u/discover">Start Swiping</a>
</div>

<script type="module">
    console.log(window.StreamVideoClient);

    // reset states
    if (window.currentAutoCancelTimer) {
        clearTimeout(window.currentAutoCancelTimer);
        window.currentAutoCancelTimer = null;
    }
    delete window.currentCall;
    delete window.currentStream;

    let activeChannel = null;
    let renderedMessageIds = new Set();
    let openIntentionalChannel = <?= json_encode($channelId) ?>

    const apiKey = <?= json_encode($_ENV['STREAM_API_KEY']) ?>;
    const userId = <?= json_encode($streamToken['userId']) ?>;
    const chatToken = <?= json_encode($streamToken['token']) ?>;
    const userName = <?= json_encode($streamToken['userName']) ?>;
    const userImage = <?= json_encode($streamToken['userImage']) ?>;

    const chatHeaderImgContainer = document.querySelector('.chat-header-section-four');
    const chatHeaderNameContainer = document.querySelector('.chat-header-section-five');
    const typingIndicator = document.querySelector('.chat-interface-section-three');
    const chatContainer = document.querySelector('.chat-interface-section-messages');
    const chatListContainer = document.querySelector('.chat-list-section-three');
    const sendMssgBtn = document.getElementById('send-message-btn');
    const sendMssgInput = document.getElementById('send-message-input');
    const sendMssgForm = document.querySelector('.chat-interface-form');

    // variables for displaying no message channels
    const emptyChannels = document.getElementById('empty-state');
    const messageContainerWrapper = document.getElementById('message-container-wrapper');

    // video call variables
    const videoCallBtn = document.getElementById('start-video-call-btn');

    const backBtn = document.querySelector('.back-to-list-btn');

    const chatClient = StreamChat.getInstance(apiKey);

    await chatClient.connectUser({
        id: userId,
        name: userName,
        image: userImage,
    }, chatToken, {
        presence: true
    });

    const channelListeners = new Map();

    chatClient.on('user.presence.changed', event => {
        const updatedUser = event.user;
        if (!updatedUser) return;

        const chatItemImg = chatListContainer.querySelector(`img[src="${updatedUser.image}"]`);
        if (chatItemImg) {
            const statusDot = chatItemImg.parentElement.querySelector('div');
            if (statusDot) {
                statusDot.style.backgroundColor = updatedUser.online ? '#22c55e' : '#6b7280';
            }
        }

        const currentHeaderName = chatHeaderNameContainer.querySelector('h2');
        if (currentHeaderName && currentHeaderName.textContent === updatedUser.name) {
            chatHeaderNameContainer.querySelector('p').textContent = updatedUser.online ?
                'Online' :
                `Last active ${calculateTime(updatedUser.last_active)}`;

            const headerDot = chatHeaderImgContainer.querySelector('div');
            if (headerDot) {
                headerDot.style.backgroundColor = updatedUser.online ? '#22c55e' : '#6b7280';
            }
        }
    });


    const filters = {
        members: {
            $in: [userId]
        }
    };
    const sort = {
        last_message_at: -1
    };

    const channels = await chatClient.queryChannels(filters, sort, {
        watch: true,
        state: true,
        presence: true
    });

    for (const channel of channels) {
        const lastMessage = channel.state.messages[channel.state.messages.length - 1];

        if (!lastMessage) continue;

        if (lastMessage.call_is_active === false) continue;

        if (
            lastMessage.text === "Video call invitation" &&
            lastMessage.call_is_active === true &&
            lastMessage.user.id !== userId
        ) {
            showIncomingCallPopup(lastMessage);
        }
    }


    if (channels.length === 0) {
        emptyChannels.style.display = 'flex';
        messageContainerWrapper.style.display = 'none';
        messageContainerWrapper.pointerEvents = 'none';
    } else {
        emptyChannels.style.display = 'none';
        messageContainerWrapper.style.display = 'flex';
        messageContainerWrapper.pointerEvents = 'none';
    }

    chatListContainer.innerHTML = "";

    channels.forEach((channel, index) => {
        const members = Object.values(channel.state.members).filter(m => m.user.id !== userId);
        const partner = members[0]?.user;
        const lastMessage = [...channel.state.messages]
            .reverse()
            .find(m => m.text && !["Video call invitation", "Call declined"].includes(m.text));

        const unreadCount = channel.countUnread();
        const isUser = lastMessage?.user?.id === userId;
        const prefix = isUser ? "You: " : "";
        let lastMessageText = prefix + truncateText(lastMessage?.text || "No messages yet", 30);


        const lastMessageTime = lastMessage?.created_at ?
            new Date(lastMessage.created_at).toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            }) :
            "";

        const chatItem = `
            <a href="/u/messages?channelId=${channel.id}" class="chat-item">
                <div class="chat-list-section-five">
                    <div class="chat-list-section-six">
                        <div class="chat-list-section-seven">
                            <img src="${partner?.image || '/assets/default-avatar.png'}" alt="">
                            <div style="
                                position: absolute;
                                bottom: -0.25rem;
                                right: -0.25rem;
                                width: 0.75rem;
                                height: 0.75rem;
                                border: 2px solid #fff;
                                border-radius: 9999px;
                                background-color: ${partner?.online ? '#22c55e' : '#6b7280'};
                            "></div>
                        </div>
                        ${unreadCount > 0
                        ? `<div style="
                                position: absolute;
                                top: -0.25rem;
                                right: -0.25rem;
                                z-index: 20;
                                display: flex;
                                align-items: center;
                                justify-content: center;
                                font-weight: 700;
                                color: white;
                                background-color: #ef4444;
                                ${unreadCount > 99
                                ? 'padding: 0 0.5rem; height: 1.5rem; border-radius: 9999px; font-size: 10px;'
                                : 'width: 1.5rem; height: 1.5rem; border-radius: 9999px; font-size: 12px;'}
                            ">
                                ${unreadCount > 99 ? '99+' : unreadCount}
                            </div>`
                        : ''}

                    </div>
                    <div class="chat-list-section-eight">
                        <div class="chat-list-section-nine">
                            <h3>${partner?.name || 'Unknown User'}</h3>
                            <span>${lastMessageTime}</span>
                        </div>
                        <p style="font-size: 0.875rem; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; ${unreadCount > 0 ? 'font-weight: bold; color: #fff;' : ''}">
                            ${lastMessageText}
                        </p>
                    </div>
                </div>
            </a>
        `;

        chatListContainer.insertAdjacentHTML('afterbegin', chatItem);

        if (index === 0) {
            renderChatInterface(channel);
            const firstChatLink = chatListContainer.querySelector(`a[href="/u/messages?channelId=${channel.id}"]`);

            if (firstChatLink) {
                chatListContainer.querySelectorAll('a').forEach(a => a.classList.remove('active-chat'));
                firstChatLink.classList.add('active-chat');
            }
        }
    });

    chatClient.on(async (event) => {
        if (!event.channel_id) return;

        const channelId = event.channel_id;
        const chatLink = chatListContainer.querySelector(`a[href="/u/messages?channelId=${channelId}"]`);

        if (!chatLink) return;

        const messagePreview = chatLink.querySelector('.chat-list-section-eight p');
        const chatItemImgContainer = chatLink.querySelector('.chat-list-section-six');

        const removeUnreadBadge = () => {
            const existingBadge = chatItemImgContainer.querySelector('.unread-badge');
            if (existingBadge) existingBadge.remove();
        };

        const renderUnreadBadge = (count) => {
            removeUnreadBadge();
            if (count <= 0) return;

            const badge = document.createElement('div');
            badge.classList.add('unread-badge');
            badge.style.cssText = `
            position: absolute;
            top: -0.25rem;
            right: -0.25rem;
            z-index: 20;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: white;
            background-color: #ef4444;
            ${count > 99
                ? 'padding: 0 0.5rem; height: 1.5rem; border-radius: 9999px; font-size: 10px;'
                : 'width: 1.5rem; height: 1.5rem; border-radius: 9999px; font-size: 12px;'}
        `;
            badge.textContent = count > 99 ? '99+' : count;
            chatItemImgContainer.appendChild(badge);
        };

        if (event.type === 'message.new') {
            const channel = channels.find(ch => ch.id === channelId);
            const unreadCount = channel?.countUnread() ?? 0;

            const isUser = event.user?.id === userId;
            const prefix = isUser ? "You: " : "";

            if (!activeChannel || activeChannel.id !== channelId) return;

            if (event.message.text === "Call declined" && event.message.user.id !== userId) {
                const popup = document.querySelector('.initiate-call-container');
                if (popup) popup.remove();

                chatClient.off('user.presence.changed');
                return;
            }

            if (event.message.text === "Call accepted" && event.message.user.id !== userId) {
                console.log("Call accepted by receiver, joining call now...");
                const popup = document.querySelector('.initiate-call-container');
                if (popup) popup.remove();

                // FIX: Start as caller (you already created the call)
                await startVideoCall(event.message.call_id, true);
                return;
            }

            if (event.message.text?.includes("Video call invitation") && event.message.user.id !== userId) {
                showIncomingCallPopup(event.message);
                return;
            }

            if (["Video call invitation", "Call declined", "Call accepted"].includes(event.message.text)) {
                return;
            }

            if (messagePreview) {
                messagePreview.style.fontWeight = isUser ? 'normal' : 'bold';
                messagePreview.style.color = isUser ? '#9ca3af' : '#fff';
                messagePreview.textContent = prefix + truncateText(event.message.text, 30);
            }

            const lastMessageTime = new Date(event.message.created_at).toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
            const timeLabel = chatLink.querySelector('.chat-list-section-nine span');
            if (timeLabel) timeLabel.textContent = lastMessageTime;

            if (!isUser) renderUnreadBadge(unreadCount);
            else removeUnreadBadge();
        }

        if (event.type === 'message.updated') {
            const updatedMsg = event.message;

            console.log(event.message);

            // Check if this was a call message and if the call is now inactive
            if (updatedMsg.text === "Video call invitation" && updatedMsg.call_is_active === false) {
                const popup = document.querySelector('.initiate-call-container, .receive-call-container');
                if (popup) popup.remove();

                chatClient.off('user.presence.changed');

                console.log("Call ended or cancelled by either user, popup removed.");
            }
        }

        if (event.type === 'message.read' && event.user?.id === userId) {
            const channel = channels.find(ch => ch.id === channelId);
            const unreadCount = channel?.countUnread() ?? 0;
            if (unreadCount <= 0) {
                removeUnreadBadge();
                if (messagePreview) {
                    messagePreview.style.fontWeight = 'normal';
                    messagePreview.style.color = '#9ca3af';
                }
            } else {
                renderUnreadBadge(unreadCount);
            }
        }

        if (event.type === 'channel.updated' || event.type === 'notification.message_new') {
            const channel = channels.find(ch => ch.id === channelId);
            const unreadCount = channel?.countUnread() ?? 0;
            renderUnreadBadge(unreadCount);
        }
    });

    chatListContainer.addEventListener('click', async (e) => {
        const link = e.target.closest('a.chat-item');
        if (!link) return;

        e.preventDefault();
        const channelId = new URL(link.href).searchParams.get('channelId');
        const selectedChannel = channels.find(ch => ch.id === channelId);
        if (!selectedChannel) return;

        chatListContainer.querySelectorAll('a').forEach(a => a.classList.remove('active-chat'));
        link.classList.add('active-chat');

        renderChatInterface(selectedChannel);

        if (window.matchMedia("(max-width: 48rem)").matches) {
            const messagesContainer = document.querySelector('.messages-container');
            messagesContainer.classList.add('show-conversation');
        }

    });

    if (backBtn) {
        backBtn.addEventListener('click', () => {
            const messagesContainer = document.querySelector('.messages-container');
            messagesContainer.classList.remove('show-conversation');
        });
    }


    if (openIntentionalChannel) {
        const selectedChannel = channels.find(ch => ch.id === openIntentionalChannel);
        if (selectedChannel) {
            chatListContainer.querySelectorAll('a').forEach(a => a.classList.remove('active-chat'));
            const link = chatListContainer.querySelector(`a[href="/u/messages?channelId=${selectedChannel.id}"]`);
            if (link) link.classList.add('active-chat');

            renderChatInterface(selectedChannel);
        }
    } else if (channels.length > 0) {
        renderChatInterface(channels[0]);
    }

    /* 
        VIDEO CALL FUNCTIONALITY
    */
    // In your videoCallBtn click handler
    if (videoCallBtn) {
        videoCallBtn.addEventListener('click', async () => {
            if (!activeChannel) return;

            const callId = `call_${Date.now()}`;

            try {
                // NOW send the invitation message
                const sentMessage = await activeChannel.sendMessage({
                    text: "Video call invitation",
                    call_id: callId,
                    caller_id: userId,
                    caller_name: userName,
                    caller_img: userImage,
                    call_is_active: true,
                });

                // Open your own video call UI (as caller)
                showInitiateCallPopup(sentMessage.message, activeChannel);

            } catch (error) {
                console.error("Failed to initiate call:", error);
                alert("Failed to start call. Please try again.");
            }
        });
    }

    async function showInitiateCallPopup(msg, channel) {
        const members = Object.values(channel.state.members).filter(m => m.user.id !== userId);
        const partner = members[0]?.user || {
            name: "Unknown",
            image: "/assets/default-avatar.png"
        };

        const popup = document.createElement('div');
        popup.classList.add('initiate-call-container');
        popup.innerHTML = `
        <div class="initiate-call-section">
            <div class="initiate-other-user">
                <div class="initiate-other-user-avatar">
                    <img src="${partner.image}" alt="${partner.name}">
                </div>
                <h1>${partner.name}</h1>
                <p id="call-status-text">${partner.online ? 'Ringing...' : 'Calling...'}</p>
            </div>

            <div class="initiate-other-end-initiate">
                <button class="receiver-decline-button" id="initiate-end-call">
                    <svg width="2rem" height="2rem" viewBox="0 0 24 24" stroke="#fff" fill="#fff">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M1.27396 8.94048C3.01237 7.88621 6.8401 6 12 6C17.1599 6 20.9876 7.88621 22.726 8.94048C23.7251 9.54634 24.1355 10.6912 23.9609 11.7514L23.5032 14.5308C23.2353 16.157 21.704 17.2467 20.101 16.9518L17.6354 16.4982C16.5887 16.3056 15.8888 15.2984 16.0637 14.2365L16.2935 12.8413C15.7061 12.4933 14.3714 11.9088 12 11.9088C9.62863 11.9088 8.29388 12.4933 7.70655 12.8413L7.93635 14.2365C8.11123 15.2984 7.41126 16.3056 6.36463 16.4982L3.89895 16.9518C2.29601 17.2467 0.764683 16.157 0.49684 14.5308L0.0390736 11.7514C-0.135542 10.6912 0.274923 9.54635 1.27396 8.94048Z" />
                    </svg>
                </button>
                <p>End Call</p>
            </div>
        </div>
    `;

        document.body.appendChild(popup);

        const handlePresenceChange = (event) => {
            if (event.user.id === partner.id) {
                const statusText = popup.querySelector('#call-status-text');
                if (statusText) {
                    statusText.textContent = event.user.online ? 'Ringing...' : 'Calling...';
                }
            }
        };

        chatClient.on('user.presence.changed', handlePresenceChange);

        popup.querySelector('#initiate-end-call').addEventListener('click', () => {
            chatClient.off('user.presence.changed', handlePresenceChange);
            endInitiateVideoCall(msg.id);
            popup.remove();
        });

        const autoCancelTimer = setTimeout(() => {
            const statusText = popup.querySelector('#call-status-text');
            if (statusText) statusText.textContent = "No answer. Call cancelled.";

            setTimeout(async () => {
                await endInitiateVideoCall(msg.id);
                popup.remove();
            }, 2000);
        }, 60000);

        window.currentAutoCancelTimer = autoCancelTimer;

        // Store the message ID for later use
        window.currentCallMessageId = msg.id;
    }

    async function showIncomingCallPopup(msg) {
        const callerName = msg.caller_name || "Someone";
        const callId = msg.call_id;
        const callerImg = msg.caller_img || "/assets/images/default-avatar.png";

        const popup = document.createElement('div');
        popup.classList.add('receive-call-container');
        popup.innerHTML = `
        <div class="receive-call-section">
            <div class="receiver-other-user">
                <div class="initiate-other-user-avatar">
                    <img src="${callerImg}" alt="${callerName}">
                </div>

                <h1>${callerName}</h1>
                <p>Incoming Video Call...</p>
            </div>

            <div class="receiver-other-buttons">
                <div>
                    <button class="receiver-decline-button">
                        <svg
                            width="2rem"
                            height="2rem"
                            viewBox="0 0 24 24"
                            stroke="#fff"
                            fill="#fff">
                            <path
                                fill-rule="evenodd"
                                clip-rule="evenodd"
                                d="M1.27396 8.94048C3.01237 7.88621 6.8401 6 12 6C17.1599 6 20.9876 7.88621 22.726 8.94048C23.7251 9.54634 24.1355 10.6912 23.9609 11.7514L23.5032 14.5308C23.2353 16.157 21.704 17.2467 20.101 16.9518L17.6354 16.4982C16.5887 16.3056 15.8888 15.2984 16.0637 14.2365L16.2935 12.8413C15.7061 12.4933 14.3714 11.9088 12 11.9088C9.62863 11.9088 8.29388 12.4933 7.70655 12.8413L7.93635 14.2365C8.11123 15.2984 7.41126 16.3056 6.36463 16.4982L3.89895 16.9518C2.29601 17.2467 0.764683 16.157 0.49684 14.5308L0.0390736 11.7514C-0.135542 10.6912 0.274923 9.54635 1.27396 8.94048ZM12 7.96961C7.30768 7.96961 3.82761 9.68804 2.2745 10.6299C2.04751 10.7676 1.8986 11.061 1.95897 11.4276L2.41674 14.207C2.50602 14.749 3.01646 15.1123 3.55077 15.014L6.01645 14.5603L5.76984 13.063C5.66906 12.4511 5.85834 11.6997 6.51793 11.2691C7.34118 10.7317 9.06148 9.93922 12 9.93922C14.9385 9.93922 16.6588 10.7317 17.4821 11.2691C18.1417 11.6997 18.3309 12.4511 18.2302 13.063L17.9836 14.5603L20.4492 15.014C20.9835 15.1123 21.494 14.749 21.5833 14.207L22.041 11.4276C22.1014 11.061 21.9525 10.7676 21.7255 10.6299C20.1724 9.68804 16.6923 7.96961 12 7.96961Z" />
                        </svg>
                    </button>
                    <p>Decline</p>
                </div>

                <div>
                    <button class="receiver-accept-button">
                        <svg
                            width="2rem"
                            height="2rem"
                            fill="#ffffff"
                            stroke="#ffffff"
                            viewBox="0 0 24 24">
                            <path
                                strokeLinecap="round"
                                strokeLinejoin="round"
                                strokeWidth={2}
                                d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                        </svg></button>
                    <p>Accept</p>
                </div>
            </div>
        </div>
    `;
        document.body.appendChild(popup);

        popup.querySelector('.receiver-decline-button').addEventListener('click', async () => {
            try {
                await activeChannel.sendMessage({
                    text: "Call declined"
                });
                popup.remove();
            } catch (error) {
                console.error("Error rejecting call:", error);
            }
        });

        popup.querySelector('.receiver-accept-button').addEventListener('click', async () => {
            popup.remove();

            await activeChannel.sendMessage({
                text: "Call accepted",
                call_id: msg.call_id
            });

            await startVideoCall(callId, false);
        });
    }

    async function endInitiateVideoCall(messageId) {
        try {
            await chatClient.partialUpdateMessage(messageId, {
                set: {
                    call_is_active: false
                }
            });
        } catch (error) {
            console.error("Error ending call:", error);
        }
    }

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

            const devices = await navigator.mediaDevices.enumerateDevices();
            const videoDevice = devices.find(d => d.kind === "videoinput" && d.label.includes("OBS"));
            console.log("Using virtual camera:", videoDevice);

            // Enable local media AFTER join
            console.log("📤 Enabling local media...");
            await call.camera.enable({
                publish: true
            });
            await call.camera.state.ready;

            await call.microphone.enable({
                publish: true
            });
            console.log("✅ Camera and Microphone enabled");

            // Verify publishing state
            console.log("📹 Camera status:", call.camera.state.status);
            console.log("🎤 Microphone status:", call.microphone.state.status);
            console.log("📡 Published tracks:", call.state.localParticipant?.publishedTracks);

            // Attach local preview using getUserMedia
            const localStream = await getMediaStream();
            if (localStream) {
                localVideo.srcObject = localStream;
                await localVideo.play().catch(e => console.warn("Local preview error:", e));
                console.log("✅ Local preview attached");
            }

            // CRITICAL: Add debouncing to prevent infinite loops
            let isProcessing = false;
            let lastProcessedState = null;
            let processCount = 0;
            const MAX_PROCESS_COUNT = 50; // Limit to 50 updates total

            // Handle remote participants using reactive subscription
            console.log("🔄 Setting up remote participant subscription...");

            const videoSubscription = call.state.remoteParticipants$.subscribe((participants) => {
                // PREVENT INFINITE LOOPS
                if (processCount >= MAX_PROCESS_COUNT) {
                    console.warn("⚠️ Max process count reached, stopping updates");
                    return;
                }

                processCount++;
                console.log(`🔄 Remote participants changed (${processCount}/${MAX_PROCESS_COUNT}):`, participants.size);

                if (participants.size === 0) {
                    console.log("⚠️ No remote participants yet");
                    return;
                }

                try {
                    participants.forEach((participant) => {
                        if (participant.userId === userId) return;

                        // Create a state fingerprint to detect actual changes
                        const stateFingerprint = JSON.stringify({
                            userId: participant.userId,
                            publishedTracks: participant.publishedTracks,
                            hasVideo: !!participant.videoStream,
                            hasAudio: !!participant.audioStream
                        });

                        // Skip if nothing changed
                        if (lastProcessedState === stateFingerprint) {
                            console.log("⏭️ State unchanged, skipping...");
                            return;
                        }

                        lastProcessedState = stateFingerprint;

                        console.log("📌 Processing participant:", {
                            userId: participant.userId,
                            sessionId: participant.sessionId,
                            publishedTracks: participant.publishedTracks,
                            hasVideoStream: !!participant.videoStream,
                            hasAudioStream: !!participant.audioStream
                        });

                        // Log ALL properties of the participant
                        console.log("🔍 All participant properties:", Object.keys(participant));

                        // Try to find video-related properties
                        for (const key in participant) {
                            if (key.toLowerCase().includes('video') || key.toLowerCase().includes('track')) {
                                console.log(`🎬 Found property "${key}":`, participant[key]);
                            }
                        }

                        // Method 1: Use SDK's bindVideoElement with explicit video element
                        if (participant.publishedTracks?.length > 0) {
                            try {
                                // Try binding video element
                                call.bindVideoElement(remoteVideo, participant.sessionId);
                                console.log("✅ bindVideoElement succeeded for sessionId:", participant.sessionId);
                            } catch (e) {
                                console.warn("⚠️ bindVideoElement failed:", e);
                            }
                        }

                        // Method 2: Direct stream access if available
                        if (participant.videoStream || participant.audioStream) {
                            try {
                                let stream = remoteVideo.srcObject;
                                if (!(stream instanceof MediaStream)) {
                                    stream = new MediaStream();
                                }

                                if (participant.videoStream) {
                                    const videoTracks = participant.videoStream.getVideoTracks();
                                    if (videoTracks.length > 0) {
                                        console.log("📹 Adding video track from videoStream");
                                        stream.getVideoTracks().forEach(t => stream.removeTrack(t));
                                        stream.addTrack(videoTracks[0]);
                                    }
                                }

                                if (participant.audioStream) {
                                    const audioTracks = participant.audioStream.getAudioTracks();
                                    if (audioTracks.length > 0) {
                                        console.log("🔊 Adding audio track from audioStream");
                                        stream.getAudioTracks().forEach(t => stream.removeTrack(t));
                                        stream.addTrack(audioTracks[0]);
                                    }
                                }

                                if (stream.getTracks().length > 0) {
                                    remoteVideo.srcObject = stream;
                                    remoteVideo.play().catch(e => console.warn("Remote play error:", e));
                                    console.log("✅ Remote stream applied");
                                }
                            } catch (e) {
                                console.warn("⚠️ Direct stream method failed:", e);
                            }
                        }

                        // Method 3: Check call.state for video tracks
                        try {
                            console.log("🎯 Checking call.state.callParticipants...");
                            const callParticipants = call.state.callParticipants;
                            if (callParticipants && callParticipants.length > 0) {
                                const remoteParticipant = callParticipants.find(p => p.userId === participant.userId);
                                if (remoteParticipant) {
                                    console.log("🎯 Found in callParticipants:", {
                                        keys: Object.keys(remoteParticipant),
                                        tracks: remoteParticipant.publishedTracks
                                    });

                                    // Log all properties that might contain video
                                    for (const key in remoteParticipant) {
                                        console.log(`  - ${key}:`, typeof remoteParticipant[key], remoteParticipant[key]);
                                    }
                                }
                            }
                        } catch (e) {
                            console.warn("⚠️ callParticipants check failed:", e);
                        }
                    });
                } catch (e) {
                    console.warn("Error occured:", e);
                }
            });

            // Listen to specific track events (limited logging)
            let trackEventCount = 0;
            const MAX_TRACK_EVENTS = 20;

            call.on("call.session_participant_joined", (event) => {
                if (trackEventCount++ < MAX_TRACK_EVENTS) {
                    console.log("👤 Participant joined:", event.participant);
                }
            });

            call.on("call.session_participant_left", (event) => {
                if (trackEventCount++ < MAX_TRACK_EVENTS) {
                    console.log("👋 Participant left:", event.participant);
                }
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
                    // Unsubscribe from remote participants
                    if (videoSubscription) {
                        videoSubscription.unsubscribe();
                        console.log("🔌 Unsubscribed from remote participants");
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
            window.currentCallSubscription = videoSubscription;

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

            // Try to find OBS Virtual Camera first
            let camera = devices.find(d => d.kind === "videoinput" && d.label.includes("OBS Virtual Camera"));

            // If OBS Virtual Camera is not found, use default webcam
            if (!camera) {
                camera = devices.find(d => d.kind === "videoinput");
            }

            if (!camera) {
                alert("No camera found. Please connect one or enable OBS Virtual Camera.");
                return null;
            }

            console.log("🎥 Using camera:", camera.label);

            return await navigator.mediaDevices.getUserMedia({
                video: {
                    deviceId: {
                        exact: camera.deviceId
                    }
                },
                audio: true
            });
        } catch (err) {
            console.error("Camera error:", err);
            alert("Failed to access camera: " + err.message);
            return null;
        }
    }

    async function renderChatInterface(channel) {
        renderedMessageIds = new Set();

        if (activeChannel && channelListeners.has(activeChannel.id)) {
            const oldHandlers = channelListeners.get(activeChannel.id);
            activeChannel.off('message.new', oldHandlers.messageHandler);
            activeChannel.off('typing.start', oldHandlers.typingStartHandler);
            activeChannel.off('typing.stop', oldHandlers.typingStopHandler);
            channelListeners.delete(activeChannel.id);
        }

        activeChannel = channel;

        chatContainer.innerHTML = "";

        const members = Object.values(channel.state.members).filter(m => m.user.id !== userId);
        const partner = members[0]?.user || {
            name: "Unknown User",
            image: "/assets/default-avatar.png"
        };

        chatHeaderImgContainer.innerHTML = `
        <img src="${partner.image}" alt="${partner.name}">
        <div style="position: absolute; bottom: -0.25rem; right: -0.25rem;
            width: 0.75rem; height: 0.75rem; border: 2px solid white;
            border-radius: 9999px; background-color: ${partner?.online ? '#22c55e' : '#6b7280'}"></div>
    `;

        chatHeaderNameContainer.innerHTML = `
        <h2>${partner.name}</h2>
        <p>${partner?.online ? "Online" : "Last active " + calculateTime(partner?.last_active)}</p>
    `;

        const messages = channel.state.messages;
        chatContainer.innerHTML = "";
        messages.forEach(msg => renderMessage(msg));
        await channel.markRead();

        chatContainer.scrollTop = chatContainer.scrollHeight;

        const messageHandler = async (event) => {
            if (!activeChannel || activeChannel.id !== event.channel_id) return;
            if (event.user?.id === userId) return;

            await activeChannel.markRead();

            const chatLink = chatListContainer.querySelector(`a[href="/u/messages?channelId=${channel.id}"]`);

            if (chatLink) {
                const chatItemImgContainer = chatLink.querySelector('.chat-list-section-six');
                const messagePreview = chatLink.querySelector('.chat-list-section-eight p');
                const unreadBadge = chatItemImgContainer.querySelector('.unread-badge');
                if (unreadBadge) unreadBadge.remove();

                if (messagePreview) {
                    messagePreview.style.fontWeight = 'normal';
                    messagePreview.style.color = '#9ca3af';
                }
            }

            renderMessage(event.message);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        };

        const typingStartHandler = (event) => {
            if (activeChannel.id === event.channel_id && event.user.id !== userId)
                typingIndicator.style.display = "flex";
        };

        const typingStopHandler = (event) => {
            if (activeChannel.id === event.channel_id && event.user.id !== userId)
                typingIndicator.style.display = "none";
        };

        if (!channelListeners.has(channel.id)) {
            channel.on('message.new', messageHandler);
            channel.on('typing.start', typingStartHandler);
            channel.on('typing.stop', typingStopHandler);
            channelListeners.set(channel.id, {
                messageHandler,
                typingStartHandler,
                typingStopHandler
            });
        }

        let typingTimeout;
        sendMssgInput.oninput = async () => {
            const text = sendMssgInput.value.trim();
            sendMssgBtn.disabled = !text;

            await channel.keystroke();
            clearTimeout(typingTimeout);
            typingTimeout = setTimeout(async () => {
                await channel.stopTyping();
            }, 1000);
        };

        sendMssgForm.onsubmit = async (e) => {
            e.preventDefault();
            const text = sendMssgInput.value.trim();
            if (!text) return;

            const tempId = 'temp-' + Date.now();

            renderMessage({
                id: tempId,
                text,
                user: {
                    id: userId
                },
                created_at: new Date()
            });
            chatContainer.scrollTop = chatContainer.scrollHeight;

            await channel.sendMessage({
                text
            });
            sendMssgInput.value = "";
            sendMssgBtn.disabled = true;
            await channel.stopTyping();
        };
    }

    function renderMessage(msg) {

        if (renderedMessageIds.has(msg.id)) return;
        renderedMessageIds.add(msg.id);

        if (["Video call invitation", "Call declined", "Call accepted"].includes(msg.text)) return;

        const isUser = msg.user.id === userId;
        const time = formatMessageTime(msg.created_at);

        const messageBubble = `
        <div style="display:flex; justify-content:${isUser ? 'end' : 'start'};">
            <div style="max-width: 20rem; padding: 0.5rem 1rem; border-radius: 1rem;
                background: ${isUser ? "linear-gradient(to right, #ec4899, #ef4444)" : "#374151"};
                color: #fff; word-wrap: break-word;">
                <p>${msg.text}</p>
                <p style="font-size:0.75rem;margin-top:0.25rem;">${time}</p>
            </div>
        </div>
    `;
        chatContainer.insertAdjacentHTML('afterbegin', messageBubble);
    }

    function formatMessageTime(dateString) {
        const now = new Date();
        const past = new Date(dateString);
        const seconds = Math.floor((now.getTime() - past.getTime()) / 1000);

        if (seconds < 86400) {
            return past.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        if (seconds < 604800) return `${Math.floor(seconds / 86400)}d`;
        if (seconds < 2592000) return `${Math.floor(seconds / 604800)} w`;
        if (seconds < 31536000) return ``;

        return ``;
    }

    function calculateTime(dateString) {
        const now = new Date();
        const past = new Date(dateString);
        const seconds = Math.floor((now.getTime() - past.getTime()) / 1000);

        if (seconds < 60) return "Just now";
        if (seconds < 86400) {
            return past.toLocaleTimeString([], {
                hour: '2-digit',
                minute: '2-digit'
            });
        }
        if (seconds < 604800) return `${Math.floor(seconds / 86400)} days ago`;
        if (seconds < 2592000) return `${Math.floor(seconds / 604800)} weeks ago`;
        if (seconds < 31536000) return `${Math.floor(seconds / 2592000)} months ago`;

        return `${Math.floor(seconds / 31536000)} year(s) ago`;
    }

    function truncateText(text, maxLength = 30) {
        if (!text) return "";
        return text.length > maxLength ? text.slice(0, maxLength) + "..." : text;
    }

    chatContainer.scrollTop = chatContainer.scrollHeight;

    window.addEventListener('beforeunload', () => {
        channelListeners.forEach((handlers, channelId) => {
            const channel = chatClient.channel(channelId);
            channel.off('message.new', handlers.messageHandler);
            channel.off('typing.start', handlers.typingStartHandler);
            channel.off('typing.stop', handlers.typingStopHandler);
        });
        chatClient.disconnectUser();
    });

    window.addEventListener('resize', () => {
        const messagesContainer = document.querySelector('.messages-container');
        if (window.matchMedia("(min-width: 48.001rem)").matches) {
            messagesContainer.classList.remove('show-conversation');
        }
    });

    // 🧹 Cleanup when user reloads or closes tab
    window.addEventListener("beforeunload", async (event) => {
        try {
            if (window.currentCall) {
                console.log("Cleaning up video call before unload...");

                // End the Stream call session if exists
                await window.currentCall.leave();

                // Stop all local media tracks (camera/mic)
                if (window.currentStream) {
                    window.currentStream.getTracks().forEach(track => track.stop());
                }

                // Optionally, mark call as inactive in the channel
                if (window.currentMessageId && chatClient) {
                    await chatClient.partialUpdateMessage(window.currentMessageId, {
                        set: {
                            call_is_active: false
                        }
                    });
                }
            }
        } catch (error) {
            console.warn("Error during cleanup:", error);
        }
    });
</script>

<?php require base_path('views/shared/footer.php') ?>