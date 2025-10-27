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
    let activeChannel = null;
    let renderedMessageIds = new Set();
    let openIntentionalChannel = <?= json_encode($channelId) ?>

    const apiKey = <?= json_encode($_ENV['STREAM_API_KEY']) ?>;
    const userId = <?= json_encode($streamToken['userId']) ?>;
    const token = <?= json_encode($streamToken['token']) ?>;
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

    const backBtn = document.querySelector('.back-to-list-btn');

    const chatClient = StreamChat.getInstance(apiKey);

    await chatClient.connectUser({
        id: userId,
        name: userName,
        image: userImage,
    }, token, {
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
        const lastMessage = channel.state.messages.slice(-1)[0];
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

    chatClient.on((event) => {
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

        const isUser = msg.user.id === userId;
        const time = new Date(msg.created_at).toLocaleTimeString([], {
            hour: '2-digit',
            minute: '2-digit'
        });

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


    function calculateTime(dateString) {
        const now = new Date();
        const past = new Date(dateString);
        const seconds = Math.floor((now.getTime() - past.getTime()) / 1000);

        if (seconds < 60) return "Just now";
        if (seconds < 120) return `${Math.floor(seconds / 60)} minute ago`;
        if (seconds < 3600 && seconds > 120) return `${Math.floor(seconds / 60)} minutes ago`;
        if (seconds < 86400) return `${Math.floor(seconds / 3600)} hours ago`;
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
</script>

<?php require base_path('views/shared/footer.php') ?>