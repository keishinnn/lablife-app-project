<?php
// file path = root/views/user/edit/profile.preferences.edit.view.php
require(base_path("views/shared/header.php"));

?>

<div class="matches-container">
    <div class="matches-section">
        <header>
            <h1>Your Blocked Users</h1>
            <p> <?php echo count($blockedUsers) ?> block<?= count($blockedUsers) > 1 ? "s" : "" ?></p>
        </header>

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

    const loadingUnblockContainer = document.getElementById('pt-loading');
    const loadingUnblockText = loadingUnblockContainer.querySelector('.profile-loading-text');

    await chatClient.connectUser({
        id: userId,
        name: userName,
        image: userImage,
    }, chatToken, {
        presence: true
    });

    if (unblockUserForms.length > 0) {
        unblockUserForms.forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();

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

            if (!res.ok) throw new Error("Failed to unblock user in database");
            const result = await res.json();

            await chatClient.unBlockUser(userBlockedId);
            location.reload();
        } catch (err) {
            console.error("Error blocking user:", err);
            alert("Failed to block user. Please try again.");
        }
    }

    function showUnblockLoading() {
        if (!loadingUnblockContainer || !loadingUnblockText) return;

        loadingUnblockContainer.style.display = 'flex';
        loadingUnblockText.textContent = 'Unblocking user...';
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