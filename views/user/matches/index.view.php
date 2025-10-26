<?php
require base_path('views/shared/header.php');

?>

<div class="matches-container">
    <div class="matches-section">
        <header>
            <h1>Your Matches</h1>
            <p> <?php echo count($matchedUsers) ?> matches</p>
        </header>

        <?php if (empty($matchedUsers)): ?>

            <div class="matches-section-one">
                <div class="matches-section-two">
                    <span>💕</span>
                </div>
                <h2>No matches yet</h2>
                <p>Start swiping to find your perfect match</p>
                <a href="/u/discover" id="nav-start-swiping-btn">Start Swiping</a>
            </div>
        <?php else: ?>
            <div class="matches-section-three">
                <div class="matches-section-four">
                    <!-- loop for every matches -->
                    <?php foreach ($matchedUsers as $matchedUser): ?>
                        <a class="matched-message-link" data-id="<?= $matchedUser->id ?>" href="/u/messages?channelId=${channel.id}">
                            <div class="matches-section-five">
                                <div class="matches-section-img">
                                    <img src="<?php echo $matchedUser->avatarUrl ?>" alt="<?php echo htmlspecialchars($matchedUser->fullName) ?>">
                                </div>

                                <div class="matches-section-six">
                                    <h3>
                                        <?php echo htmlspecialchars($matchedUser->fullName) ?>, <?php echo (calculateAge($matchedUser->birthdate)) ?>
                                    </h3>
                                    <p class="matches-section-six-username">@<?php echo $matchedUser->username ?></p>
                                    <p class="matches-section-six-bio"><?php echo htmlspecialchars($matchedUser->bio ?? '') ?></p>
                                </div>
                                <div class="matches-section-seven">
                                    <div
                                        id="online-dot-<?= $matchedUser->id ?>"
                                        style="width: 0.75rem; height: 0.75rem;
               border-radius: 9999px;
               background-color: <?= $matchedUser->isOnline ? '#22c55e' : '#6b7280'; ?>;">
                                    </div>
                                </div>

                            </div>
                        </a>
                    <?php endforeach; ?>

                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<script type="module">
    import {
        subscribeToSupabase
    } from "/assets/js/supabase/client.js";

    const supabaseClient = subscribeToSupabase(
        '<?= $_ENV['SUPABASE_URL'] ?>',
        '<?= $_ENV['SUPABASE_ANON_KEY'] ?>'
    );

    const matchedUserIds = <?= json_encode(array_map(fn($u) => $u->id, $matchedUsers)); ?>;

    let isOnlineSub = null;

    isOnlineSub = supabaseClient
        .channel('public:users')
        .on(
            'postgres_changes', {
                event: 'UPDATE',
                schema: 'public',
                table: 'users',
                filter: `id=in.(${matchedUserIds.join(",")})`
            },
            (payload) => {
                const updatedUser = payload.new;
                console.log("Online status changed:", updatedUser);

                const userDot = document.querySelector(
                    `#online-dot-${updatedUser.id}`
                );
                if (userDot) {
                    userDot.style.backgroundColor = updatedUser.is_online ?
                        '#22c55e' :
                        '#6b7280';
                }
            }
        )
        .subscribe();

    document.querySelectorAll('.matched-message-link').forEach(link => {
        link.addEventListener('click', async () => {
            const userId = link.dataset.id;
            console.log("Clicked matched user:", userId);

            try {
                const res = await fetch('/u/matches/create-channel', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': '<?= $_SESSION["csrf_token"] ?? "" ?>'
                    },
                    body: JSON.stringify({
                        targetUserId: userId
                    })
                });

                const data = await res.json();

                if (data.success) {
                    const channelId = data.channel_id;
                    window.location.href = `/u/messages?channelId=${channelId}`;
                } else {
                    console.error('Error:', data.error);
                }
            } catch (err) {
                console.error('Failed to create channel:', err);
            }
        });
    });

    window.addEventListener('beforeunload', () => {
        if (isOnlineSub) isOnlineSub.unsubscribe();
    });
</script>

<?php require base_path('views/shared/footer.php') ?>