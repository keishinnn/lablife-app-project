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

    const channelListeners = new Map();
    let isCurrentlyInCall = false;

    const messagesContainer = document.querySelector('.messages-container');
    const callView = document.querySelector('.video-call-container');
    const footer = document.querySelector('footer');
    const navbar = document.querySelector('.navbar-inner');

    const searchChatInput = document.getElementById('search-chat-input');
    const noResultsMessage = document.getElementById('no-results-message');

    const isDarkMode = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function escapeHtml(value) {
        return String(value ?? '')
            .replaceAll('&', '&amp;')
            .replaceAll('<', '&lt;')
            .replaceAll('>', '&gt;')
            .replaceAll('"', '&quot;')
            .replaceAll("'", '&#039;');
    }

    function safeImageUrl(value) {
        const url = String(value ?? '');

        if (url.startsWith('http://') || url.startsWith('https://') || url.startsWith('/')) {
            return url;
        }

        return '/assets/images/default-avatar.png';
    }

    await chatClient.connectUser({
        id: userId,
        name: userName,
        image: userImage,
    }, chatToken, {
        presence: true
    });

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
            lastMessage.user.id !== userId &&
            !isCurrentlyInCall
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
            .find(m => m.text && !["Video call invitation", "Call declined", "Call accepted", "Call declined - User is busy"].includes(m.text));

        const unreadCount = channel.countUnread();
        const isUser = lastMessage?.user?.id === userId;
        const prefix = isUser ? "You: " : "";
        let lastMessageText = prefix + truncateText(lastMessage?.text || "No messages yet", 30);

        const lastMessageTime = lastMessage?.created_at;
        const chatItem = `
            <a href="/u/messages?channelId=${channel.id}" class="chat-item">
                <div class="chat-list-section-five">
                    <div class="chat-list-section-six">
                        <div class="chat-list-section-seven">
                            <img src="${safeImageUrl(partner?.image || '/assets/images/default-avatar.png')}" alt="">
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
                            <h3>${escapeHtml(partner?.name || 'Unknown User')}</h3>
                            <span>${formatChatListTime(lastMessageTime)}</span>
                        </div>
                        <p style="font-size: 0.875rem; color: #9ca3af; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; ${unreadCount > 0 ? 'font-weight: bold; color: #fff;' : ''}">
                            ${escapeHtml(lastMessageText)}
                        </p>
                    </div>
                </div>
            </a>
        `;

        chatListContainer.insertAdjacentHTML('beforeend', chatItem);

        if (index === 0) {
            renderChatInterface(channel);
            const firstChatLink = chatListContainer.querySelector(`a[href="/u/messages?channelId=${channel.id}"]`);

            if (firstChatLink) {
                chatListContainer.querySelectorAll('a').forEach(a => a.classList.remove('active-chat'));
                firstChatLink.classList.add('active-chat');
            }
        }
    });

    searchChatInput.addEventListener('input', (e) => {
        const query = e.target.value.toLowerCase().trim();
        const allChatItems = chatListContainer.querySelectorAll('a.chat-item');
        let matchesFound = 0;

        allChatItems.forEach(item => {
            const nameElement = item.querySelector('.chat-list-section-nine h3');

            if (nameElement) {
                const name = nameElement.textContent.toLowerCase();

                if (name.includes(query)) {
                    item.style.display = 'block';
                    matchesFound++;
                } else {
                    item.style.display = 'none';
                }
            }
        });

        if (matchesFound === 0 && allChatItems.length > 0) {
            noResultsMessage.style.display = 'block';
        } else {
            noResultsMessage.style.display = 'none';
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

            if (event.message.text === "Call declined - User is busy" && event.message.user.id !== userId) {
                const popup = document.querySelector('.initiate-call-container');
                if (popup) {
                    const statusText = popup.querySelector('#call-status-text');
                    if (statusText) {
                        statusText.textContent = "In another call";
                    }

                    setTimeout(() => {
                        popup.remove();
                        if (videoCallBtn) videoCallBtn.disabled = false;
                        cleanupCall();
                    }, 2000);
                }
                return;
            }

            if (event.message.text === "Call declined" && event.message.user.id !== userId) {
                const popup = document.querySelector('.initiate-call-container');
                if (popup) popup.remove();

                chatClient.off('user.presence.changed');
                return;
            }

            if (event.message.text === "Call accepted" && event.message.user.id !== userId) {
                const popup = document.querySelector('.initiate-call-container');
                if (popup) popup.remove();

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

            if (!activeChannel || activeChannel.id !== channelId) return;
        }

        if (event.type === 'message.updated') {
            const updatedMsg = event.message;

            if (updatedMsg.text === "Video call invitation" && updatedMsg.call_is_active === false) {
                const popup = document.querySelector('.initiate-call-container, .receive-call-container');
                if (popup) popup.remove();

                chatClient.off('user.presence.changed');
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
            messagesContainer.classList.add('show-conversation');
        }

    });

    if (backBtn) {
        backBtn.addEventListener('click', () => {
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

    if (videoCallBtn) {
        videoCallBtn.addEventListener('click', async () => {
            if (!activeChannel) return;

            videoCallBtn.disabled = true;

            const members = Object.values(activeChannel.state.members).filter(m => m.user.id !== userId);
            const partner = members[0]?.user;

            if (!partner) {
                videoCallBtn.disabled = false;
                return;
            }

            const callId = `call_${Date.now()}`;

            try {
                const sentMessage = await activeChannel.sendMessage({
                    text: "Video call invitation",
                    call_id: callId,
                    caller_id: userId,
                    caller_name: userName,
                    caller_img: userImage,
                    call_is_active: true,
                });

                showInitiateCallPopup(sentMessage.message, activeChannel);
                videoCallBtn.disabled = false;

            } catch (error) {
                console.error("Failed to initiate call:", error);
                videoCallBtn.disabled = false;
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

        popup.querySelector('#initiate-end-call').addEventListener('click', async () => {
            chatClient.off('user.presence.changed', handlePresenceChange);
            await endInitiateVideoCall(msg.id);
            if (videoCallBtn) videoCallBtn.disabled = false;

            if (window.currentAutoCancelTimer) {
                clearTimeout(window.currentAutoCancelTimer);
                window.currentAutoCancelTimer = null;
            }
            cleanupCall();
            popup.remove();
        });

        const autoCancelTimer = setTimeout(async () => {
            const statusText = popup.querySelector('#call-status-text');
            if (statusText) statusText.textContent = "No answer. Call cancelled.";

            setTimeout(async () => {
                await endInitiateVideoCall(msg.id);
                if (videoCallBtn) videoCallBtn.disabled = false;
                chatClient.off('user.presence.changed', handlePresenceChange);
                popup.remove();
            }, 2000);
        }, 60000);

        window.currentAutoCancelTimer = autoCancelTimer;
        window.currentCallMessageId = msg.id;
    }

    async function showIncomingCallPopup(msg) {
        const callerName = msg.caller_name || "Someone";
        const callerId = msg.caller_id;
        const callId = msg.call_id;
        const callerImg = msg.caller_img || "/assets/images/default-avatar.png";

        const callChannel = channels.find(ch => {
            const members = Object.values(ch.state.members);
            return members.some(m => m.user.id === callerId);
        });

        if (!callChannel) {
            console.error("Could not find channel for incoming call");
            return;
        }

        if (isCurrentlyInCall) {
            try {
                if (!callChannel.initialized) {
                    await callChannel.watch();
                }

                await callChannel.sendMessage({
                    text: "Call declined - User is busy"
                });
            } catch (error) {
                console.error("Error auto-declining call:", error);
            }
            return;
        }

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
                    <svg width="2rem" height="2rem" viewBox="0 0 24 24" stroke="#fff" fill="#fff">
                        <path fill-rule="evenodd" clip-rule="evenodd"
                            d="M1.27396 8.94048C3.01237 7.88621 6.8401 6 12 6C17.1599 6 20.9876 7.88621 22.726 8.94048C23.7251 9.54634 24.1355 10.6912 23.9609 11.7514L23.5032 14.5308C23.2353 16.157 21.704 17.2467 20.101 16.9518L17.6354 16.4982C16.5887 16.3056 15.8888 15.2984 16.0637 14.2365L16.2935 12.8413C15.7061 12.4933 14.3714 11.9088 12 11.9088C9.62863 11.9088 8.29388 12.4933 7.70655 12.8413L7.93635 14.2365C8.11123 15.2984 7.41126 16.3056 6.36463 16.4982L3.89895 16.9518C2.29601 17.2467 0.764683 16.157 0.49684 14.5308L0.0390736 11.7514C-0.135542 10.6912 0.274923 9.54635 1.27396 8.94048ZM12 7.96961C7.30768 7.96961 3.82761 9.68804 2.2745 10.6299C2.04751 10.7676 1.8986 11.061 1.95897 11.4276L2.41674 14.207C2.50602 14.749 3.01646 15.1123 3.55077 15.014L6.01645 14.5603L5.76984 13.063C5.66906 12.4511 5.85834 11.6997 6.51793 11.2691C7.34118 10.7317 9.06148 9.93922 12 9.93922C14.9385 9.93922 16.6588 10.7317 17.4821 11.2691C18.1417 11.6997 18.3309 12.4511 18.2302 13.063L17.9836 14.5603L20.4492 15.014C20.9835 15.1123 21.494 14.749 21.5833 14.207L22.041 11.4276C22.1014 11.061 21.9525 10.7676 21.7255 10.6299C20.1724 9.68804 16.6923 7.96961 12 7.96961Z" />
                    </svg>
                </button>
                <p>Decline</p>
            </div>

            <div>
                <button class="receiver-accept-button">
                    <svg width="2rem" height="2rem" fill="#ffffff" stroke="#ffffff" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2}
                            d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                    </svg>
                </button>
                <p>Accept</p>
            </div>
        </div>
    </div>
`;
        document.body.appendChild(popup);

        popup.querySelector('.receiver-decline-button').addEventListener('click', async () => {
            try {
                await callChannel.sendMessage({
                    text: "Call declined"
                });

                popup.remove();
            } catch (error) {
                console.error("Error rejecting call:", error);
            }
        });

        popup.querySelector('.receiver-accept-button').addEventListener('click', async () => {
            popup.remove();

            await callChannel.sendMessage({
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

            isCurrentlyInCall = false;
        } catch (error) {
            console.error("Error ending call:", error);
        }
    }

    async function startVideoCall(callId, isCaller) {
        await cleanupCall()

        isCurrentlyInCall = true;

        try {
            messagesContainer.style.display = 'none';
            footer.style.display = 'none';
            navbar.style.display = 'none';

            const callView = document.createElement('div');
            callView.classList.add('video-call-container');
            callView.innerHTML = `
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
                    <button id="toggleMic" class="control-btn"><i class="fa-solid fa-microphone"></i></button>
                    <button id="toggleCam" class="control-btn"><i class="fa-solid fa-video"></i></button>
                    <button id="endCall" class="end-call control-btn"><i class="fa-solid fa-phone-slash"></i></button>
                </div>
            `;
            document.body.appendChild(callView);

            const dragElement = callView.querySelector('.local-video-container');

            let active = false;
            let xOffset = 0;
            let yOffset = 0;

            dragElement.addEventListener('mousedown', dragStart);
            dragElement.addEventListener('touchstart', dragStart, {
                passive: false
            });

            window.addEventListener('mouseup', dragEnd);
            window.addEventListener('touchend', dragEnd);

            window.addEventListener('mousemove', dragMove);
            window.addEventListener('touchmove', dragMove, {
                passive: false
            });

            function dragStart(e) {
                active = true;
                dragElement.style.transition = 'none';

                const clientX = e.type === 'touchstart' ? e.touches[0].clientX : e.clientX;
                const clientY = e.type === 'touchstart' ? e.touches[0].clientY : e.clientY;

                const rect = dragElement.getBoundingClientRect();

                xOffset = clientX - rect.left;
                yOffset = clientY - rect.top;

                dragElement.style.right = 'auto';
                dragElement.style.bottom = 'auto';
            }

            function dragMove(e) {
                if (!active) return;

                e.preventDefault();

                const clientX = e.type === 'touchmove' ? e.touches[0].clientX : e.clientX;
                const clientY = e.type === 'touchmove' ? e.touches[0].clientY : e.clientY;

                let newLeft = clientX - xOffset;
                let newTop = clientY - yOffset;

                dragElement.style.left = `${newLeft}px`;
                dragElement.style.top = `${newTop}px`;
            }

            function dragEnd() {
                if (!active) return;
                active = false;

                dragElement.style.transition = 'all 0.3s ease';

                const padding = 20;
                const rect = dragElement.getBoundingClientRect();
                const winWidth = window.innerWidth;
                const winHeight = window.innerHeight;

                if (rect.left + (rect.width / 2) < (winWidth / 2)) {
                    dragElement.style.left = `${padding}px`;
                    dragElement.style.right = 'auto';
                } else {
                    dragElement.style.left = 'auto';
                    dragElement.style.right = `${padding}px`;
                }

                let finalTop = rect.top;
                if (finalTop < padding) {
                    finalTop = padding;
                } else if (finalTop + rect.height > winHeight - padding) {
                    finalTop = winHeight - rect.height - padding;
                }

                dragElement.style.top = `${finalTop}px`;
                dragElement.style.bottom = 'auto';
            }

            const localVideoOverlay = callView.querySelector('#local-video-overlay');
            const remoteVideoOverlay = callView.querySelector('#remote-video-overlay');
            const remoteVideoModify = callView.querySelector('.remote-video-modify');
            const localVideoModify = callView.querySelector('.local-video-modify');

            const StreamVideoClientClass = window.StreamVideoClient?.StreamVideoClient || window.StreamVideoClient;
            if (!StreamVideoClientClass) throw new Error("StreamVideoClient SDK not loaded");

            const res = await fetch("/u/video/get-video-token", {
                method: "POST",
                headers: {
                    "X-CSRF-Token": csrfToken
                }
            });
            const tokenData = await res.json();
            const videoToken = tokenData.token;
            if (!videoToken) throw new Error("Video token missing from response");

            const client = StreamVideoClientClass.getOrCreateInstance({
                apiKey,
                user: {
                    id: userId,
                    name: userName,
                    image: userImage
                },
                token: videoToken
            });

            const call = client.call("default", callId);
            const remoteVideo = document.getElementById("remoteVideo");
            const remoteAudio = document.getElementById("remoteAudio");
            const localVideo = document.getElementById("localVideo");

            if (isCaller) {
                await call.join({
                    create: true
                });
            } else {
                let joined = false;
                for (let i = 0; i < 5; i++) {
                    try {
                        await call.get();
                        joined = true;
                        break;
                    } catch (e) {
                        await new Promise(r => setTimeout(r, 1000));
                    }
                }
                if (!joined) throw new Error("Call not found on server.");
                await call.join();
            }

            await call.camera.enable({
                publish: true
            });
            await call.microphone.enable({
                publish: true
            });

            const localStream = await getMediaStream();
            if (localStream) {
                localVideo.srcObject = localStream;
                await localVideo.play().catch(e => console.warn("Local preview error:", e));
            }

            const boundElements = new Set();

            function showCallEndedNotification(message) {
                const existingNotification = callView.querySelector('.call-ended-notification');
                if (existingNotification) return;

                const notification = document.createElement('div');
                notification.className = 'call-ended-notification';
                notification.style.cssText = `
                position: absolute;
                top: 50%;
                left: 50%;
                transform: translate(-50%, -50%);
                background: rgba(0, 0, 0, 0.9);
                color: white;
                padding: 2rem 3rem;
                border-radius: 1rem;
                font-size: 1.5rem;
                font-weight: bold;
                text-align: center;
                z-index: 1000;
                box-shadow: 0 10px 40px rgba(0, 0, 0, 0.5);
            `;
                notification.innerHTML = `
                <div style="margin-bottom: 1rem;">📞</div>
                <div>${message}</div>
                <div style="font-size: 1rem; font-weight: normal; margin-top: 0.5rem; opacity: 0.7;">
                    Ending call...
                </div>
            `;
                callView.appendChild(notification);
            }

            function bindParticipant(participant) {
                const elementKey = `${participant.sessionId}`;

                if (boundElements.has(elementKey)) {
                    return;
                }

                try {

                    const unbindVideo = call.bindVideoElement(
                        remoteVideo,
                        participant.sessionId,
                        "videoTrack"
                    );

                    const unbindAudio = call.bindAudioElement(
                        remoteAudio,
                        participant.sessionId
                    );

                    boundElements.add(elementKey);

                    if (!window.currentCallUnbinders) {
                        window.currentCallUnbinders = [];
                    }
                    window.currentCallUnbinders.push(unbindVideo, unbindAudio);

                } catch (e) {
                    console.error("❌ Failed to bind participant:", e);
                }
            }

            const initialParticipants = call.state.participants;
            initialParticipants.forEach((participant) => {
                if (participant.userId === userId) return;

                if (participant.publishedTracks.length > 0) {
                    bindParticipant(participant);
                }

                const isRemoteCameraOn = participant.publishedTracks.includes(2);
                remoteVideoOverlay.style.display = isRemoteCameraOn ? 'none' : 'flex';
            });


            call.on('participantJoined', (event) => {
                const participant = event.participant;
                if (participant.userId === userId) return;

                bindParticipant(participant);
            });

            call.on('trackPublished', (event) => {
                const participant = event.participant;
                if (participant && participant.userId !== userId && event.type === 2) {
                    remoteVideoOverlay.style.display = 'none';
                    remoteVideoModify.style.display = 'flex';

                    bindParticipant(participant);
                }
            });

            call.on('trackUnpublished', (event) => {
                const participant = event.participant;
                if (participant && participant.userId !== userId && event.type === 2) {
                    remoteVideoOverlay.style.display = 'flex';
                    remoteVideoModify.style.display = 'none';
                }
            });

            call.on("call.session_participant_left", (event) => {
                if (event.participant?.userId !== userId) {
                    showCallEndedNotification("Call ended");

                    setTimeout(async () => {
                        window.location.href = '/u/messages'
                    }, 2000);
                }
            });

            call.on("call.session_ended", () => {
                showCallEndedNotification("Call ended");

                setTimeout(async () => {
                    window.location.href = '/u/messages'
                }, 2000);
            });

            call.on("call.session_participant_updated", (event) => {
                const participant = event.participant;
                if (!participant || participant.userId === userId) return;

                const remoteVideoOverlay = document.getElementById("remote-video-overlay");

                if (participant.isCameraEnabled) {
                    remoteVideoOverlay.style.display = "none";
                } else {
                    remoteVideoOverlay.style.display = "flex";
                }
            });

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
                        localVideoOverlay.style.display = 'flex';
                        localVideoModify.style.display = 'none';
                    } else {
                        await call.camera.enable();
                        localVideoOverlay.style.display = 'none';
                        localVideoModify.style.display = 'flex';
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
                    call.off("call.session_ended");
                    call.off("call.session_participant_left");

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

                    if (localStream) {
                        localStream.getTracks().forEach(t => {
                            t.stop();
                        });
                    }

                    await call.leave();

                    if (window.currentCallMessageId) {
                        await endInitiateVideoCall(window.currentCallMessageId);
                        delete window.currentCallMessageId;
                    }

                    showCallEndedNotification("Call ended");

                    setTimeout(async () => {
                        await cleanupCall();
                    }, 2000);
                } catch (e) {
                    console.warn("Error leaving call:", e);
                }

                delete window.currentCall;
                delete window.currentStream;

            });

            window.currentCall = call;
            window.currentStream = localStream;

        } catch (err) {
            console.error("❌ Video call error:", err);
            alert("Failed to start video call: " + (err?.message || err));
            await cleanupCall();

            if (callView) callView.remove();
            messagesContainer.style.display = 'flex';
            footer.style.display = 'flex';
            navbar.style.display = 'flex';
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

    async function cleanupCall() {
        if (videoCallBtn) videoCallBtn.disabled = false;
        isCurrentlyInCall = false;

        try {
            if (window.currentCall) {
                window.currentCall.off("participantJoined");
                window.currentCall.off("trackPublished");
                window.currentCall.off("trackUnpublished");
                window.currentCall.off("call.session_ended");
                window.currentCall.off("call.session_participant_left");


                if (window.currentCallUnbinders) {
                    window.currentCallUnbinders.forEach(unbind => unbind());
                    delete window.currentCallUnbinders;
                }

                if (window.currentStream) {
                    window.currentStream.getTracks().forEach(t => t.stop());
                }

                await window.currentCall.leave().catch(e => console.warn("Leave error:", e));

                if (window.currentCallMessageId) {
                    await endInitiateVideoCall(window.currentCallMessageId).catch(e => console.warn(e));
                    delete window.currentCallMessageId;
                }
            }
        } catch (e) {
            console.warn("Cleanup error:", e);
        }

        if (window.currentAutoCancelTimer) {
            clearTimeout(window.currentAutoCancelTimer);
            window.currentAutoCancelTimer = null;
        }

        if (callView) callView.remove();
        if (messagesContainer) messagesContainer.style.display = 'flex';
        if (footer) footer.style.display = 'flex';
        if (navbar) navbar.style.display = 'flex';

        delete window.currentCall;
        delete window.currentStream;
        delete window.currentCallId;
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
        <img src="${safeImageUrl(partner.image)}" alt="${escapeHtml(partner.name)}">
        <div style="position: absolute; bottom: -0.25rem; right: -0.25rem;
            width: 0.75rem; height: 0.75rem; border: 2px solid white;
            border-radius: 9999px; background-color: ${partner?.online ? '#22c55e' : '#6b7280'}"></div>
    `;

        chatHeaderNameContainer.innerHTML = `
        <h2>${escapeHtml(partner.name)}</h2>
        <p>${partner?.online ? "Online" : "Last active " + calculateTime(partner?.last_active)}</p>
    `;

        const messages = channel.state.messages.filter(msg =>
            !["Video call invitation", "Call declined", "Call accepted", "Call declined - User is busy"].includes(msg.text)
        );

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
                    messagePreview.style.color = (isDarkMode ? "#9ca3af" : "#3d4149ff");
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

        if (["Video call invitation", "Call declined", "Call accepted", "Call declined - User is busy"].includes(msg.text)) return;

        const isUser = msg.user.id === userId;
        const time = formatMessageTime(msg.created_at);

        const bubbleBackground = isUser ?
            "linear-gradient(to right, #ec4899, #ef4444)" :
            (isDarkMode ? "#374151" : "#f1f0f0");

        const bubbleTextColor = isUser ?
            "#fff" :
            (isDarkMode ? "" : "grey");

        const timeTextColor = isUser ?
            "#f1f0f0" :
            (isDarkMode ? "#9ca3af" : "#6b7280");

        const messageBubble = `
        <div style="display:flex; justify-content:${isUser ? 'end' : 'start'};">
            <div style="max-width: 20rem; padding: 0.5rem 1rem; border-radius: 1rem;
                background: ${bubbleBackground}; 
                color: ${bubbleTextColor};  
                word-wrap: break-word;">
                
                <p style="margin: 0;">${escapeHtml(msg.text)}</p> 

                <p style="font-size:0.75rem; margin-top:0.25rem; color: ${timeTextColor};">
                    ${time}
                </p>
            </div>
        </div>
        `;
        chatContainer.insertAdjacentHTML('afterbegin', messageBubble);
    }

    function formatMessageTime(dateString) {
        const now = new Date();
        const past = new Date(dateString);

        const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());

        const startOfYesterday = new Date(startOfToday.getTime() - 86400000);

        if (past.getTime() >= startOfToday.getTime()) {
            return past.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }

        if (past.getTime() >= startOfYesterday.getTime()) {
            return `Yesterday at ${past.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        })}`;
        }

        const startOfYear = new Date(now.getFullYear(), 0, 1);

        if (past.getTime() >= startOfYear.getTime()) {
            return past.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }

        return past.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    function formatChatListTime(dateString) {
        if (!dateString) {
            return "";
        }

        const now = new Date();
        const past = new Date(dateString);
        const seconds = Math.floor((now.getTime() - past.getTime()) / 1000);
        const minutes = Math.floor(seconds / 60);
        const hours = Math.floor(minutes / 60);
        const days = Math.floor(hours / 24);
        const weeks = Math.floor(days / 7);

        if (seconds < 60) {
            return "Just now";
        }

        if (minutes < 60) {
            return `${minutes}m`;
        }

        if (hours < 24) {
            return `${hours}h`;
        }

        if (days < 7) {
            return `${days}d`;
        }

        if (days < 30) {
            return `${weeks}w`;
        }

        return past.toLocaleDateString('en-US', {
            month: 'numeric',
            day: 'numeric',
            year: '2-digit'
        });
    }


    function calculateTime(dateString) {
        const now = new Date();
        const past = new Date(dateString);

        const seconds = Math.floor((now.getTime() - past.getTime()) / 1000);

        if (seconds < 60) {
            return "Just now";
        }

        const minutes = Math.floor(seconds / 60);
        if (minutes < 60) {
            return `${minutes}m ago`;
        }

        const hours = Math.floor(minutes / 60);
        if (hours < 6) {
            return `${hours}h ago`;
        }

        const startOfToday = new Date(now.getFullYear(), now.getMonth(), now.getDate());

        if (past.getTime() >= startOfToday.getTime()) {
            return past.toLocaleTimeString('en-US', {
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }

        const startOfYesterday = new Date(startOfToday.getTime() - 86400000);

        if (past.getTime() >= startOfYesterday.getTime()) {
            return `Yesterday at ${past.toLocaleTimeString('en-US', {
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        })}`;
        }

        const startOfWeek = new Date(startOfToday.getTime() - (now.getDay() * 86400000));

        if (past.getTime() >= startOfWeek.getTime()) {
            return past.toLocaleDateString('en-US', {
                weekday: 'short',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }

        const startOfYear = new Date(now.getFullYear(), 0, 1);
        if (past.getTime() >= startOfYear.getTime()) {
            return past.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });
        }

        return past.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: 'numeric',
            minute: '2-digit',
            hour12: true
        });
    }

    function truncateText(text, maxLength = 30) {
        if (!text) return "";
        return text.length > maxLength ? text.slice(0, maxLength) + "..." : text;
    }

    const userControlBtn = document.getElementById('user-control-btn');
    const userControlModal = document.getElementById('user-control-modal');
    const closeUserControlBtn = document.querySelector('.close-user-control-modal-button');
    const userControlSelected = document.querySelectorAll('input[type="radio"][name="user-control-type"]');
    const nextButton = document.querySelector('.select-modal-submit-button');
    const otherUserInput = document.getElementById('other_user_id');

    const userBlockModal = document.getElementById('user-block-confirmation-modal');
    const userBlockCancelBtn = document.querySelector('.user-block-confirmation-modal-cancel');
    const userBlockCloseBtn = document.querySelector('.user-block-confirmation-modal-close');
    const confirmBlockBtn = document.getElementById('confirm-block-btn');
    const userBlockForm = document.getElementById('user-block-form-submit');

    const loadingBlockContainer = document.getElementById('pt-loading');
    const loadingBlockText = loadingBlockContainer.querySelector('.profile-loading-text');


    if (userControlBtn) {
        userControlBtn.addEventListener('click', () => {
            const members = Object.values(activeChannel.state.members).filter(m => m.user.id !== userId);
            const partner = members[0]?.user;

            if (partner && otherUserInput) {
                otherUserInput.value = partner.id;
            }

            showUserControlModal();
        })
    }

    if (closeUserControlBtn) {
        closeUserControlBtn.addEventListener('click', () => {
            hideUserControlModal();
        })
    }

    if (nextButton) {
        nextButton.addEventListener('click', () => {
            const selectedOption = document.querySelector('input[name="user-control-type"]:checked');

            if (!selectedOption) {
                alert("Please select an option first.");
                return;
            }

            if (selectedOption.value === "block-user") {
                hideUserControlModal();
                showUserBlockModal();
            } else if (selectedOption.value === "report-user") {
                alert("Report feature coming soon!");
            }
        });
    }

    if (userBlockForm) {
        userBlockForm.addEventListener('submit', async () => {
            event.preventDefault();

            showBlockLoading();

            const blockedUserId = otherUserInput.value;
            if (!blockedUserId) {
                console.warn("No target user found.");
                return;
            }

            await handleBlockOtherUser(blockedUserId);
        });
    }

    async function handleBlockOtherUser(blockedUserId) {
        try {
            // 1️⃣ Call your backend to store block in DB
            const res = await fetch("/u/block-other-user", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-Token": csrfToken
                },
                body: JSON.stringify({
                    other_user_id: blockedUserId
                })
            });

            if (!res.ok) throw new Error("Failed to block user in database");
            const result = await res.json();

            await chatClient.blockUser(blockedUserId);
        } catch (err) {
            console.error("Error blocking user:", err);
            alert("Failed to block user. Please try again.");
        } finally {
            window.location.href = '/u/messages';
        }
    }

    function showUserControlModal() {
        userControlModal.style.display = 'flex';
    }

    function hideUserControlModal() {
        userControlModal.style.display = 'none';
        userControlSelected.forEach(radio => radio.checked = false);
    }

    function showUserBlockModal() {
        userBlockModal.style.display = 'flex';
    }

    function hideUserBlockModal() {
        userBlockModal.style.display = 'none';
    }

    function showBlockLoading() {
        if (!loadingBlockContainer || !loadingBlockText) return;

        loadingBlockContainer.style.display = 'flex';
        loadingBlockText.textContent = 'Blocking user...';
    }

    if (userBlockCancelBtn) {
        userBlockCancelBtn.addEventListener('click', hideUserBlockModal);
    }

    if (userBlockCloseBtn) {
        userBlockCloseBtn.addEventListener('click', hideUserBlockModal);
    }

    chatContainer.scrollTop = chatContainer.scrollHeight;

    window.addEventListener('resize', () => {
        if (window.matchMedia("(min-width: 48.001rem)").matches) {
            messagesContainer.classList.remove('show-conversation');
        }
    });

    window.addEventListener("beforeunload", async (event) => {
        try {
            channelListeners.forEach((handlers, channelId) => {
                const channel = chatClient.channel(channelId);
                channel.off('message.new', handlers.messageHandler);
                channel.off('typing.start', handlers.typingStartHandler);
                channel.off('typing.stop', handlers.typingStopHandler);
            });
            chatClient.disconnectUser();

            if (window.currentCall) {
                cleanupCall();

                await window.currentCall.leave();

                if (window.currentStream) {
                    window.currentStream.getTracks().forEach(track => track.stop());
                }

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

<?php require base_path('Views\user\messages\modals\user.control.view.php') ?>
<?php require base_path('Views\user\messages\modals\user.block.confirmation.view.php') ?>

<?php require base_path('views/shared/footer.php') ?>
