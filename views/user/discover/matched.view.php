<?php

// file path = root/Views/user/discover/index.view.php
require base_path('views/shared/header.php');

use Core\Auth;

?>

<div class="discover-section" id="discover-section-modify">
    <div class="discover-container" id="discover-container-id">
        <header>
            <div class="discover-section-backBtn-section">
                <button title="Go back">
                    <svg class="discover-backBtn-svg"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth={2}
                            d="M15 19l-7-7 7-7" />
                    </svg>
                </button>
                <div style="flex: 1 1 0%;"></div>
            </div>

            <div class="match-timer-container">
                <svg id="progress-svg" width="4rem" height="4rem" viewBox="0 0 180 180">
                    <circle class="match-timer-bg" cx="90" cy="90" r="86" stroke-width="8"></circle>
                    <circle
                        class="match-timer-bar"
                        cx="90"
                        cy="90"
                        r="86"
                        stroke-width="8"
                        transform="rotate(-90 90 90)"></circle>
                    <text
                        id="match-timer-text"
                        x="90"
                        y="95"
                        text-anchor="middle"
                        alignment-baseline="middle">
                        60s
                    </text>
                </svg>
            </div>

            <div class="discover-match-text">
                <h1>Match Found!</h1>
            </div>
        </header>

        <div class="discover-match-container">

            <div class="discover-match-card">
                <div class="discover-match-card-swipe">
                    <div class="discover-match-card-section-two">
                        <img src="<?php echo $partner->avatarUrl ?>" alt="">

                        <div class="discover-match-card-section-three">
                            <div class="discover-match-card-section-four">
                                <div class="discover-match-card-section-five">
                                    <h2><?php echo htmlspecialchars($partner->fullName)  ?>, <?php echo (calculateAge($partner->birthdate)) ?></h2>
                                    <p class="discover-match-card-user-text">@<?php echo htmlspecialchars($partner->username) ?></p>
                                    <p class="discover-match-card-bio-text"><?php echo htmlspecialchars($partner->bio) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div style="margin-top: 2rem;">
                <div class="discover-match-buttons">
                    <form id="submit-dislike-form" method="POST">
                        <button class="discover-match-button-dislike" aria-label="Pass" type="submit">
                            <svg
                                style="  width: 2rem; height: 2rem; color: #ef4444;"
                                fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    fillRule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clipRule="evenodd" />
                            </svg>
                        </button>
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    </form>

                    <form id="submit-like-form" method="POST">

                        <button class="discover-match-button-like" aria-label="Like" type="submit">
                            <svg
                                style="width: 2rem; height: 2rem; color: #22c55e;"
                                fill="currentColor"
                                viewBox="0 0 20 20">
                                <path
                                    fillRule="evenodd"
                                    d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z"
                                    clipRule="evenodd" />
                            </svg>
                        </button>
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    </form>
                </div>
            </div>
        </div>
    </div>

    <?php require(base_path('Views/notifications/loading/recon-loading.php')) ?>
</div>

<!-- Loading state in Edit Profile -->
<?php require(base_path('Views/notifications/matched-notif.php')) ?>
<?php require(base_path('Views/notifications/rejected-notif.php')) ?>
<?php require(base_path('Views/notifications/expired-notif.php')) ?>

<script type="module">
    import {
        subscribeToSupabase
    } from "/assets/js/supabase/client.js";

    const supabaseClient = subscribeToSupabase(
        '<?= $_ENV['SUPABASE_URL'] ?>',
        '<?= $_ENV['SUPABASE_ANON_KEY'] ?>'
    );

    // match timer variables
    const duration = 60;
    let remaining = duration;
    const sqSize = 180;
    const strokeWidth = 8;
    const radius = (sqSize - strokeWidth) / 2;
    const dashArray = radius * Math.PI * 2;
    const progressBar = document.querySelector('.match-timer-bar');
    const progressText = document.getElementById('match-timer-text');

    // form variables
    const submitLikeForm = document.getElementById('submit-like-form');
    const submitdisLikeForm = document.getElementById('submit-dislike-form');
    const csrfToken = submitLikeForm.querySelector('input[name="csrf_token"]').value;
    const partnerId = "<?= $partner->id ?>";

    const currentUser = '<?= Auth::user(); ?>';

    // matched modal variables
    const matchedModal = document.getElementById('matchModal');
    const matchedNotification = document.querySelector('.notification-container');
    const keepSearchingBtn = document.getElementById('keep-searching-btn');
    let isIntentionalNavigation = false;

    const reconLoading = document.getElementById('recon-loading');
    const discoverSectionModify = document.getElementById('discover-section-modify');
    const discoverContainer = document.getElementById('discover-container-id');

    // Rejected Notification
    const rejectedModal = document.getElementById('rejected-modal');
    const rejectedModalNotif = document.querySelector('.notification-rejected-container');
    const rejectProgressBar = document.querySelector(".notification-border-path");
    const rejectDuration = 3;
    let rejectStart = null;
    let rejectAnimating = false;

    // Expired Session Notification
    const expiredModal = document.getElementById('expired-modal');
    const expiredModalNotif = document.querySelector('.notification-expired-container');
    const expiredProgressBar = document.querySelector(".notification-expired-border-path");

    // for subscription
    let matchSessionSub = null;
    let matchesSub = null;
    let dislikesSub = null;

    if (progressBar && progressText) {
        progressBar.style.strokeDasharray = dashArray;
        progressBar.style.strokeDashoffset = 0;

        const interval = setInterval(() => {
            remaining--;
            if (remaining < 0) {
                clearInterval(interval);
                return;
            }

            const percentage = (remaining / duration) * 100;
            const dashOffset = dashArray - (dashArray * percentage) / 100;

            progressBar.style.strokeDashoffset = dashOffset;
            progressText.textContent = `${remaining}s`;
        }, 1000);
    } else {
        console.error("match-timer-bar or match-timer-text element not found.");
    }

    matchesSub = supabaseClient
        .channel('public:matches')
        .on(
            'postgres_changes', {
                event: '*',
                schema: 'public',
                table: 'matches',
            },
            (payload) => {
                const match = payload.new;
                console.log(match);

                if (match.user1_id === currentUser || match.user2_id === currentUser && match.user1_id === partnerId || match.user2_id === partnerId) {
                    showMatchedModal();

                    if (matchesSub) matchesSub.unsubscribe();
                }
            }
        )
        .subscribe((status) => {
            console.log("🛰️ Subscription status on matches:", status);
        });

    matchSessionSub = supabaseClient
        .channel('match_sessions')
        .on(
            'postgres_changes', {
                event: '*',
                schema: 'public',
                table: 'match_sessions',
            },
            (payload) => {
                const session = payload.new;
                console.log(session);

                if ((session.user_a === currentUser || session.user_b === currentUser) && session.status === 'expired') {
                    if (matchSessionSub) matchSessionSub.unsubscribe();
                    showMatchExpired();

                    (async () => {
                        try {
                            await cleanupMatchSession();

                            // Trigger new search before redirect
                            const res = await fetch('/u/discover/start-search', {
                                method: 'POST'
                            });

                            if (!res.ok) throw new Error('Failed to start search');

                            sessionStorage.setItem('searching', 'true');

                            window.location.href = '/u/discover';
                        } catch (err) {
                            console.error("Failed to auto-restart search:", err);
                            window.location.href = '/u/discover';
                        }
                    })();
                }
            }
        )
        .subscribe((status) => {
            console.log("🛰️ Subscription status on match_sessions:", status);
        });

    dislikesSub = supabaseClient
        .channel('public:dislikes')
        .on(
            'postgres_changes', {
                event: '*',
                schema: 'public',
                table: 'dislikes',
            },
            (payload) => {
                const dislike = payload.new;

                if (dislike.from_user_id === partnerId && dislike.to_user_id === currentUser) {

                    showNoMatchThisTime();
                    if (dislikesSub) dislikesSub.unsubscribe();

                } else if (dislike.from_user_id === currentUser && dislike.to_user_id === partnerId) {

                    showNoMatchThisTime();

                    if (dislikesSub) dislikesSub.unsubscribe();
                } else {

                    showNoMatchThisTime();

                    if (dislikesSub) dislikesSub.unsubscribe();
                }
            }
        )
        .subscribe((status) => {
            console.log("🛰️ Subscription status on matches:", status);
        });

    submitLikeForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        createHeartBurst(e.currentTarget);
        try {
            const res = await fetch('/u/discover/like', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    partner: partnerId
                })
            });

            const data = await res.json();
            if (!res.ok) throw new Error('Failed to like other user');

        } catch (err) {
            console.error("Error liking other user:", err);
        }
    });

    submitdisLikeForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        createHeartBreakBurst(e.currentTarget);

        try {
            const res = await fetch('/u/discover/dislike', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken
                },
                body: JSON.stringify({
                    partner: partnerId
                })
            });

            const data = await res.json();
            if (!res.ok) throw new Error('Failed to dislike other user');

        } catch (err) {
            console.error("Error liking other user:", err);
        }
    });

    function showMatchedModal() {
        if (!matchedNotification) return;

        matchedModal.style.display = 'flex';
        matchedNotification.classList.add('visible');
    }

    keepSearchingBtn.addEventListener('click', async (e) => {
        try {
            isIntentionalNavigation = true;

            const res = await fetch('/u/discover/start-search', {
                method: 'POST'
            });
            if (!res.ok) throw new Error('Failed to start search');

            sessionStorage.setItem('searching', 'true');
            window.location.href = '/u/discover';
        } catch (err) {
            console.error('Failed to auto-restart search:', err);
            window.location.href = '/u/discover';
        }
    });

    function animateBorder(timestamp) {
        if (!rejectStart) rejectStart = timestamp;
        const elapsed = (timestamp - rejectStart) / 1000;

        if (elapsed <= rejectDuration) {
            const progress = elapsed / rejectDuration;
            const offset = rejectProgressBar.getTotalLength() * progress;
            rejectProgressBar.style.strokeDashoffset = -offset;
            requestAnimationFrame(animateBorder);
        } else {
            rejectAnimating = false;
        }
    }

    function startRejectBorderAnimation() {
        if (!rejectProgressBar || rejectAnimating) return;

        rejectAnimating = true;
        rejectStart = null;
        const length = rejectProgressBar.getTotalLength();
        rejectProgressBar.style.strokeDasharray = length;
        rejectProgressBar.style.strokeDashoffset = 0;

        requestAnimationFrame(animateBorder);
    }

    function animateExpiredBorder(timestamp) {
        if (!rejectStart) rejectStart = timestamp;
        const elapsed = (timestamp - rejectStart) / 1000;

        if (elapsed <= rejectDuration) {
            const progress = elapsed / rejectDuration;
            const offset = expiredProgressBar.getTotalLength() * progress;
            expiredProgressBar.style.strokeDashoffset = -offset;
            requestAnimationFrame(animateBorder);
        } else {
            rejectAnimating = false;
        }
    }

    function startExpiredBorderAnimation() {
        if (!animateExpiredBorder || rejectAnimating) return;

        rejectAnimating = true;
        rejectStart = null;
        const length = expiredProgressBar.getTotalLength();
        expiredProgressBar.style.strokeDasharray = length;
        expiredProgressBar.style.strokeDashoffset = 0;

        requestAnimationFrame(animateExpiredBorder);
    }

    // Cleanup listener — runs when the user leaves the page
    async function cleanupMatchSession() {
        try {
            console.log("Cleaning up match session for user:", currentUser);
            await fetch('/u/discover/set-rejected-session', {
                method: 'POST',
                keepalive: true
            });
        } catch (err) {
            console.warn("Cleanup failed or not needed:", err);
        }
    }

    async function restartSearch() {
        try {
            isIntentionalNavigation = true;
            showReconLoading();
            await cleanupMatchSession();

            // Trigger new search before redirect
            const res = await fetch('/u/discover/start-search', {
                method: 'POST'
            });

            if (!res.ok) throw new Error('Failed to start search');

            sessionStorage.setItem('searching', 'true');
            hideReconLoading();
            window.location.href = '/u/discover';
        } catch (err) {
            console.error("Failed to auto-restart search:", err);
            window.location.href = '/u/discover';
        }
    }

    function showNoMatchThisTime() {
        if (!rejectedModalNotif) return;

        rejectedModal.style.display = 'flex';
        rejectedModalNotif.classList.add('visible');

        startRejectBorderAnimation();

        setTimeout(() => {
            rejectedModal.style.display = 'none';
            rejectedModalNotif.classList.remove('visible');
            restartSearch()
        }, 3000);
    }

    function showMatchExpired() {
        if (!expiredModalNotif) return;

        expiredModal.style.display = 'flex';
        expiredModalNotif.classList.add('visible');

        startExpiredBorderAnimation();

        setTimeout(() => {
            expiredModal.style.display = 'none';
            expiredModalNotif.classList.remove('visible');
            restartSearch()
        }, 3000);
    }

    function showReconLoading() {
        discoverContainer.style.display = "none";
        reconLoading.style.display = "flex";

        discoverSectionModify.style.display = "flex";
        discoverSectionModify.style.justifyContent = "center";
        discoverSectionModify.style.alignItems = "center";
        discoverSectionModify.style.flexDirection = "column";
    }

    function hideReconLoading() {
        reconLoading.style.display = "none";
    }

    function createHeartBurst(button) {
        const rect = button.getBoundingClientRect();
        const container = document.body;

        for (let i = 0; i < 5; i++) {
            const heart = document.createElement('span');
            heart.innerHTML = `
  <svg width="20" height="20" viewBox="0 0 24 24" fill="#22c55e" xmlns="http://www.w3.org/2000/svg">
    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 
             2 6.42 3.42 5 5.5 5c1.74 0 3.41 1.01 4.13 2.44h1.75C13.09 6.01 
             14.76 5 16.5 5 18.58 5 20 6.42 20 8.5c0 3.78-3.4 6.86-8.55 
             11.54L12 21.35z"/>
  </svg>`;
            heart.classList.add('heart-feedback');

            // Randomize small offsets so hearts don't overlap
            const xOffset = Math.random() * 40 - 20;
            const yOffset = Math.random() * 10 - 5;
            heart.style.left = `${rect.left + rect.width / 2 + xOffset}px`;
            heart.style.top = `${rect.top + window.scrollY + yOffset}px`;
            heart.style.fontSize = `${Math.random() * 12 + 16}px`;

            container.appendChild(heart);

            // Remove after animation ends
            setTimeout(() => heart.remove(), 1000);
        }
    }

    function createHeartBreakBurst(button) {
        const rect = button.getBoundingClientRect();
        const container = document.body;

        for (let i = 0; i < 3; i++) {
            const heartBreak = document.createElement('span');
            heartBreak.innerHTML = `
                                        <svg
                                width="20" height="20" fill="#ef4444"
                                viewBox="0 0 20 20">
                                <path
                                    fillRule="evenodd"
                                    d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                    clipRule="evenodd" />
                            </svg>
            `;
            heartBreak.classList.add('heart-feedback');

            // Randomize small offsets so hearts don't overlap
            const xOffset = Math.random() * 40 - 20;
            const yOffset = Math.random() * 10 - 5;
            heartBreak.style.left = `${rect.left + rect.width / 2 + xOffset}px`;
            heartBreak.style.top = `${rect.top + window.scrollY + yOffset}px`;
            heartBreak.style.fontSize = `${Math.random() * 12 + 16}px`;

            container.appendChild(heartBreak);

            // Remove after animation ends
            setTimeout(() => heartBreak.remove(), 1000);
        }
    }

    // Listen for page unloads, tab close, navigation, etc.
    window.addEventListener('beforeunload', (event) => {
        if (!isIntentionalNavigation) cleanupMatchSession();
    });

    window.addEventListener('pagehide', (event) => {
        if (!isIntentionalNavigation) cleanupMatchSession();
    });
</script>

<?php require base_path('views/shared/footer.php') ?>