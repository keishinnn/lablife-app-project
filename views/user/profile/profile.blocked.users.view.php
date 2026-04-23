<?php
// file path = root/views/user/edit/profile.preferences.edit.view.php
require(base_path("views/shared/header.php"));

?>

<div class="matches-container">
    <div class="matches-section">
        <header class="matches-section-header-left">
            <a href="/u/profile" class="matches-back-link">← Back to profile</a>
            <h1>Your Blocked Users</h1>
            <p> <?php echo count($blockedUsers) ?> block<?= count($blockedUsers) > 1 ? "s" : "" ?></p>
        </header>
        <div id="blocked-users-feedback" class="profile-flash error" style="display:none; margin-bottom: 1rem;"></div>

        <?php if (empty($blockedUsers)): ?>

            <div class="matches-section-one">
                <h2>No blocked users</h2>
            </div>
        <?php else: ?>
            <div class="matches-section-three">
                <div class="matches-section-four">
                    <!-- loop for every blocked users -->
                    <?php foreach ($blockedUsers as $blockedUser): ?>
                        <a class="matched-message-link">
                            <div class="matches-section-five">
                                <div class="matches-section-img">
                                    <img src="<?php echo $blockedUser->avatarUrl ?>" alt="<?php echo htmlspecialchars($blockedUser->fullName) ?>">
                                </div>

                                <div class="matches-section-six">
                                    <h3>
                                        <?php echo htmlspecialchars($blockedUser->fullName) ?>, <?php echo (calculateAge($blockedUser->birthdate)) ?>
                                    </h3>
                                    <p class="matches-section-six-username">@<?php echo $blockedUser->username ?></p>
                                    <p class="matches-section-six-bio"><?php echo htmlspecialchars($blockedUser->bio ?? '') ?></p>
                                </div>
                                <form method="post" class="unblock-user-form">
                                    <div class="matches-section-seven">
                                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                        <input type="hidden" value="<?= $blockedUser->id ?>" class="blocked_user_id" name="blocked_user_id">
                                        <button type="submit" class="user-block-confirmation-modal-confirm">
                                            Unblock
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </a>
                    <?php endforeach; ?>

                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script type="module">
    const apiKey = <?= json_encode($_ENV['STREAM_API_KEY']) ?>;
    const userId = <?= json_encode($streamToken['userId']) ?>;
    const chatToken = <?= json_encode($streamToken['token']) ?>;
    const userName = <?= json_encode($streamToken['userName']) ?>;
    const userImage = <?= json_encode($streamToken['userImage']) ?>;

    const chatClient = StreamChat.getInstance(apiKey);

    const unblockUserForms = document.querySelectorAll('.unblock-user-form');
    const userBlockedIdInput = document.getElementById('blocked_user_id');
    const blockedUsersFeedback = document.getElementById('blocked-users-feedback');

    const loadingUnblockContainer = document.getElementById('pt-loading');
    const loadingUnblockText = loadingUnblockContainer.querySelector('.profile-loading-text');

    await chatClient.connectUser({
        id: userId,
        name: userName,
        image: userImage,
    }, chatToken, {
        presence: true
    });

    function showBlockedUsersFeedback(message) {
        if (!blockedUsersFeedback) return;
        blockedUsersFeedback.textContent = message;
        blockedUsersFeedback.style.display = 'block';
    }

    function clearBlockedUsersFeedback() {
        if (!blockedUsersFeedback) return;
        blockedUsersFeedback.textContent = '';
        blockedUsersFeedback.style.display = 'none';
    }

    if (unblockUserForms.length > 0) {
        unblockUserForms.forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                clearBlockedUsersFeedback();

                showUnblockLoading();

                const userBlockedId = form.querySelector('.blocked_user_id').value;

                if (!userBlockedId) {
                    console.warn("No target user found.");
                    hideUnblockLoading();
                    return;
                }

                await handleUnblockUser(userBlockedId);

            });
        });
    }

    async function handleUnblockUser(userBlockedId) {
        try {
            const res = await fetch("/u/unblock-other-user", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-Token": document.querySelector('input[name="csrf_token"]').value
                },
                body: JSON.stringify({
                    blocked_user_id: userBlockedId
                })
            });

            const result = await res.json().catch(() => ({}));
            if (!res.ok) {
                showBlockedUsersFeedback(result.message || result.error || "Failed to unblock user. Please try again.");
                hideUnblockLoading();
                return;
            }

            await chatClient.unBlockUser(userBlockedId);
            location.reload();
        } catch (err) {
            console.error("Error blocking user:", err);
            showBlockedUsersFeedback("Failed to unblock user. Please try again.");
            hideUnblockLoading();
        }
    }

    function showUnblockLoading() {
        if (!loadingUnblockContainer || !loadingUnblockText) return;

        loadingUnblockContainer.style.display = 'flex';
        loadingUnblockText.textContent = 'Unblocking user...';
    }

    function hideUnblockLoading() {
        if (!loadingUnblockContainer) return;

        loadingUnblockContainer.style.display = 'none';
    }

    window.addEventListener("beforeunload", async (event) => {
        try {
            chatClient.disconnectUser();
        } catch (error) {
            console.warn("Error during cleanup:", error);
        }
    });
</script>

<?php require(base_path("views/shared/footer.php")) ?>
