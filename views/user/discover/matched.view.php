<?php

require base_path('views/shared/header.php');

use Core\Auth;

?>

<div class="discover-section" id="discover-section-modify">
    <div class="discover-container" id="discover-container-id">
        <header>
            <div class="discover-section-backBtn-section">
                <button title="Go back" id="discover-back-btn">
                    <svg class="discover-backBtn-svg"
                        fill="none"
                        stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path
                            strokeLinecap="round"
                            strokeLinejoin="round"
                            strokeWidth="2"
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
                        <img src="<?php echo htmlspecialchars($partner->avatarUrl ?? '/assets/images/default-avatar.png'); ?>" alt="">

                        <div class="discover-match-card-section-three">
                            <div class="discover-match-card-section-four">
                                <div class="discover-match-card-section-five">
                                    <h2><?php echo htmlspecialchars($partner->fullName) ?>, <?php echo calculateAge($partner->birthdate) ?></h2>
                                    <p class="discover-match-card-user-text">@<?php echo htmlspecialchars($partner->username) ?></p>
                                    <p class="discover-match-card-bio-text"><?php echo htmlspecialchars($partner->bio ?? '') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div style="margin-top: 2rem;">
                <div class="discover-match-buttons">
                    <form id="submit-dislike-form" method="POST">
                        <button class="discover-match-button-dislike" aria-label="Pass" type="submit">
                            <svg
                                style="width: 2rem; height: 2rem; color: #ef4444;"
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

    <?php require base_path('views/notifications/loading/recon-loading.php') ?>
</div>

<?php require base_path('views/notifications/matched-notif.php') ?>
<?php require base_path('views/notifications/rejected-notif.php') ?>
<?php require base_path('views/notifications/expired-notif.php') ?>

<script type="module">
    import { subscribeToSupabase } from "/assets/js/supabase/client.js";

    const supabaseClient = subscribeToSupabase(
        '<?= $_ENV['SUPABASE_URL'] ?>',
        '<?= $_ENV['SUPABASE_ANON_KEY'] ?>',
        <?= json_encode(Auth::getValidAccessToken() ?? '') ?>
    );

    const duration = 60;
    let remaining = duration;
    const sqSize = 180;
    const strokeWidth = 8;
    const radius = (sqSize - strokeWidth) / 2;
    const dashArray = radius * Math.PI * 2;

    const currentUser = '<?= Auth::user(); ?>';
    const partnerId = '<?= $partner->id ?>';
    const pageCsrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    const progressBar = document.querySelector('.match-timer-bar');
    const progressText = document.getElementById('match-timer-text');
    const discoverBackBtn = document.getElementById('discover-back-btn');
    const submitLikeForm = document.getElementById('submit-like-form');
    const submitDislikeForm = document.getElementById('submit-dislike-form');
    const startChatBtn = document.getElementById('start-chat-btn');
    const keepSearchingBtn = document.getElementById('keep-searching-btn');
    const matchedModal = document.getElementById('matchModal');
    const matchedNotification = document.querySelector('.notification-container');
    const rejectedModal = document.getElementById('rejected-modal');
    const rejectedModalNotif = document.querySelector('.notification-rejected-container');
    const rejectProgressBar = document.querySelector('.notification-border-path');
    const expiredModal = document.getElementById('expired-modal');
    const expiredModalNotif = document.querySelector('.notification-expired-container');
    const expiredProgressBar = document.querySelector('.notification-expired-border-path');
    const reconLoading = document.getElementById('recon-loading');
    const discoverSectionModify = document.getElementById('discover-section-modify');
    const discoverContainer = document.getElementById('discover-container-id');
    const messageLoading = document.getElementById('messages-loading');
    const pageContent = document.getElementById('page-content');
    const csrfToken = submitLikeForm?.querySelector('input[name="csrf_token"]')?.value ?? pageCsrfToken;

    let timerPaused = false;
    let timerInterval = null;
    let cleanupInProgress = false;
    let isIntentionalNavigation = false;
    let terminalState = null;
    let rejectAnimating = false;
    let rejectStart = null;
    let expiredStart = null;

    let matchSessionSub = null;
    let matchesSub = null;
    let dislikeSub = null;

    function markTerminalState(nextState) {
        if (terminalState) {
            return false;
        }

        terminalState = nextState;
        return true;
    }

    function showMatchedModal() {
        if (!matchedModal || !matchedNotification) return;

        matchedModal.style.display = 'flex';
        matchedNotification.classList.add('visible');
    }

    function hideMatchedModal() {
        if (!matchedModal || !matchedNotification) return;

        matchedModal.style.display = 'none';
        matchedNotification.classList.remove('visible');
    }

    function animateRejectBorder(timestamp) {
        if (!rejectStart) rejectStart = timestamp;
        const elapsed = (timestamp - rejectStart) / 1000;

        if (elapsed <= 3) {
            const progress = elapsed / 3;
            const offset = rejectProgressBar.getTotalLength() * progress;
            rejectProgressBar.style.strokeDashoffset = -offset;
            requestAnimationFrame(animateRejectBorder);
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

        requestAnimationFrame(animateRejectBorder);
    }

    function animateExpiredBorder(timestamp) {
        if (!expiredStart) expiredStart = timestamp;
        const elapsed = (timestamp - expiredStart) / 1000;

        if (elapsed <= 3) {
            const progress = elapsed / 3;
            const offset = expiredProgressBar.getTotalLength() * progress;
            expiredProgressBar.style.strokeDashoffset = -offset;
            requestAnimationFrame(animateExpiredBorder);
        } else {
            rejectAnimating = false;
        }
    }

    function startExpiredBorderAnimation() {
        if (!expiredProgressBar || rejectAnimating) return;

        rejectAnimating = true;
        expiredStart = null;
        const length = expiredProgressBar.getTotalLength();
        expiredProgressBar.style.strokeDasharray = length;
        expiredProgressBar.style.strokeDashoffset = 0;

        requestAnimationFrame(animateExpiredBorder);
    }

    function showReconLoading() {
        if (discoverContainer) {
            discoverContainer.style.display = 'none';
        }
        if (reconLoading) {
            reconLoading.style.display = 'flex';
        }
        if (discoverSectionModify) {
            discoverSectionModify.style.display = 'flex';
            discoverSectionModify.style.justifyContent = 'center';
            discoverSectionModify.style.alignItems = 'center';
            discoverSectionModify.style.flexDirection = 'column';
        }
    }

    function showNoMatchThisTime() {
        if (!rejectedModal || !rejectedModalNotif) return;

        rejectedModal.style.display = 'flex';
        rejectedModalNotif.classList.add('visible');
        startRejectBorderAnimation();

        setTimeout(() => {
            rejectedModal.style.display = 'none';
            rejectedModalNotif.classList.remove('visible');
            restartSearch();
        }, 3000);
    }

    function showMatchExpired() {
        if (!expiredModal || !expiredModalNotif) return;

        expiredModal.style.display = 'flex';
        expiredModalNotif.classList.add('visible');
        startExpiredBorderAnimation();

        setTimeout(() => {
            expiredModal.style.display = 'none';
            expiredModalNotif.classList.remove('visible');
            restartSearch();
        }, 3000);
    }

    function createHeartBurst(button) {
        const rect = button.getBoundingClientRect();

        for (let i = 0; i < 5; i += 1) {
            const heart = document.createElement('span');
            heart.innerHTML = `
                <svg width="20" height="20" viewBox="0 0 24 24" fill="#22c55e" xmlns="http://www.w3.org/2000/svg">
                    <path d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 6.42 3.42 5 5.5 5c1.74 0 3.41 1.01 4.13 2.44h1.75C13.09 6.01 14.76 5 16.5 5 18.58 5 20 6.42 20 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"/>
                </svg>`;
            heart.classList.add('heart-feedback');

            const xOffset = Math.random() * 40 - 20;
            const yOffset = Math.random() * 10 - 5;
            heart.style.left = `${rect.left + rect.width / 2 + xOffset}px`;
            heart.style.top = `${rect.top + window.scrollY + yOffset}px`;
            heart.style.fontSize = `${Math.random() * 12 + 16}px`;

            document.body.appendChild(heart);
            setTimeout(() => heart.remove(), 1000);
        }
    }

    function createHeartBreakBurst(button) {
        const rect = button.getBoundingClientRect();

        for (let i = 0; i < 3; i += 1) {
            const heartBreak = document.createElement('span');
            heartBreak.innerHTML = `
                <svg width="20" height="20" fill="#ef4444" viewBox="0 0 20 20">
                    <path fillRule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clipRule="evenodd" />
                </svg>`;
            heartBreak.classList.add('heart-feedback');

            const xOffset = Math.random() * 40 - 20;
            const yOffset = Math.random() * 10 - 5;
            heartBreak.style.left = `${rect.left + rect.width / 2 + xOffset}px`;
            heartBreak.style.top = `${rect.top + window.scrollY + yOffset}px`;
            heartBreak.style.fontSize = `${Math.random() * 12 + 16}px`;

            document.body.appendChild(heartBreak);
            setTimeout(() => heartBreak.remove(), 1000);
        }
    }

    async function setSessionStatusMatched() {
        try {
            await fetch('/u/discover/set-matched-session', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': pageCsrfToken
                },
                keepalive: true
            });
        } catch (err) {
            console.warn("Match session update failed:", err);
        }
    }

    async function setSessionStatusRejected() {
        try {
            await fetch('/u/discover/set-rejected-session', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': pageCsrfToken
                },
                keepalive: true
            });
        } catch (err) {
            console.warn("Reject session failed:", err);
        }
    }

    async function setSessionStatusExpired() {
        try {
            await fetch('/u/discover/set-expired-session', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': pageCsrfToken
                },
                keepalive: true
            });
        } catch (err) {
            console.warn("Expire session failed:", err);
        }
    }

    async function setSearchExpired() {
        if (cleanupInProgress) {
            console.warn("Cleanup already in progress - skipping.");
            return;
        }

        cleanupInProgress = true;

        try {
            unsubscribeAll();

            await fetch('/u/discover/set-search-expired', {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': pageCsrfToken
                },
                keepalive: true
            });
        } catch (err) {
            console.warn("Search cleanup failed:", err);
        } finally {
            cleanupInProgress = false;
        }
    }

    function unsubscribeAll() {
        [matchesSub, matchSessionSub, dislikeSub].forEach((sub) => {
            sub?.unsubscribe();
        });

        matchesSub = null;
        matchSessionSub = null;
        dislikeSub = null;
    }

    async function restartSearch() {
        try {
            isIntentionalNavigation = true;
            unsubscribeAll();
            showReconLoading();
            sessionStorage.setItem('searching', 'true');
            window.location.href = '/u/discover';
        } catch (err) {
            console.error("Failed to restart search:", err);
            window.location.href = '/u/discover';
        }
    }

    function startTimer() {
        if (timerInterval) {
            clearInterval(timerInterval);
        }

        timerInterval = setInterval(async () => {
            if (timerPaused) return;

            remaining -= 1;

            if (remaining < 0) {
                clearInterval(timerInterval);
                timerInterval = null;
                timerPaused = true;

                if (markTerminalState('expired')) {
                    setSessionStatusExpired();
                    showMatchExpired();
                }
                return;
            }

            const percentage = (remaining / duration) * 100;
            const dashOffset = dashArray - (dashArray * percentage) / 100;

            progressBar.style.strokeDasharray = dashArray;
            progressBar.style.strokeDashoffset = dashOffset;
            progressText.textContent = `${remaining}s`;
        }, 1000);
    }

    if (progressBar && progressText) {
        progressBar.style.strokeDasharray = dashArray;
        progressBar.style.strokeDashoffset = 0;
        startTimer();
    }

    matchesSub = supabaseClient
        .channel('public:matches')
        .on(
            'postgres_changes',
            {
                event: '*',
                schema: 'public',
                table: 'matches',
            },
            (payload) => {
                console.log("Matches payload:", payload);
                const match = payload.new;
                if (!match) return;

                if (
                    (match.user1_id === currentUser || match.user2_id === currentUser) &&
                    (match.user1_id === partnerId || match.user2_id === partnerId)
                ) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                    timerPaused = true;

                    if (markTerminalState('matched')) {
                        setSessionStatusMatched();
                        showMatchedModal();
                    }
                }
            }
        )
        .subscribe((status) => {
            console.log("Matches subscription status:", status);
        });

    matchSessionSub = supabaseClient
        .channel('public:match_sessions')
        .on(
            'postgres_changes',
            {
                event: '*',
                schema: 'public',
                table: 'match_sessions',
            },
            async (payload) => {
                console.log("Matched page session payload:", payload);
                const session = payload.new;
                if (!session) return;

                const isUserInSession = session.user_a === currentUser || session.user_b === currentUser;
                const isPartnerInSession = session.user_a === partnerId || session.user_b === partnerId;

                if (!isUserInSession || !isPartnerInSession) {
                    return;
                }

                if (session.status === 'expired') {
                    clearInterval(timerInterval);
                    timerInterval = null;
                    timerPaused = true;

                    if (markTerminalState('expired')) {
                        showMatchExpired();
                    }
                }

                if (session.status === 'rejected') {
                    const selfRejected = sessionStorage.getItem('selfRejected') === 'true';
                    if (selfRejected) {
                        sessionStorage.removeItem('selfRejected');
                        return;
                    }

                    clearInterval(timerInterval);
                    timerInterval = null;
                    timerPaused = true;

                    if (markTerminalState('rejected')) {
                        showNoMatchThisTime();
                        await new Promise((resolve) => setTimeout(resolve, 3000));
                    }
                }
            }
        )
        .subscribe((status) => {
            console.log("Match session subscription status:", status);
        });

    dislikeSub = supabaseClient
        .channel('public:dislikes_from_current_to_partner')
        .on(
            'postgres_changes',
            {
                event: '*',
                schema: 'public',
                table: 'dislikes',
                filter: `from_user_id=in.(${currentUser},${partnerId})`
            },
            (payload) => {
                console.log("Dislike payload:", payload);
                const dislike = payload.new;
                if (!dislike) return;

                if (
                    (dislike.from_user_id === partnerId && dislike.to_user_id === currentUser) ||
                    (dislike.from_user_id === currentUser && dislike.to_user_id === partnerId)
                ) {
                    clearInterval(timerInterval);
                    timerInterval = null;
                    timerPaused = true;

                    if (markTerminalState('rejected')) {
                        setSessionStatusRejected();
                        showNoMatchThisTime();
                    }

                    dislikeSub?.unsubscribe();
                    dislikeSub = null;
                }
            }
        )
        .subscribe((status) => {
            console.log("Dislike subscription status:", status);
        });

    if (submitLikeForm) {
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

                await res.json();
                if (!res.ok) {
                    throw new Error('Failed to like other user');
                }
            } catch (err) {
                console.error("Error liking other user:", err);
            }
        });
    }

    if (submitDislikeForm) {
        submitDislikeForm.addEventListener('submit', async (e) => {
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

                await res.json();
                if (!res.ok) {
                    throw new Error('Failed to dislike other user');
                }
            } catch (err) {
                console.error("Error disliking other user:", err);
            }
        });
    }

    if (keepSearchingBtn) {
        keepSearchingBtn.addEventListener('click', () => {
            isIntentionalNavigation = true;
            hideMatchedModal();
            showReconLoading();
            sessionStorage.setItem('searching', 'true');
            window.location.href = '/u/discover';
        });
    }

    if (discoverBackBtn) {
        discoverBackBtn.addEventListener('click', async () => {
            try {
                isIntentionalNavigation = true;
                sessionStorage.setItem('selfRejected', 'true');
                await setSessionStatusRejected();
                await setSearchExpired();
                window.location.href = '/u/discover';
            } catch (err) {
                console.error("Failed to navigate back:", err);
                window.location.href = '/u/discover';
            }
        });
    }

    if (startChatBtn) {
        startChatBtn.addEventListener('click', async () => {
            isIntentionalNavigation = true;

            if (pageContent && messageLoading) {
                pageContent.style.pointerEvents = 'none';
                pageContent.style.display = 'none';
                messageLoading.style.display = 'flex';
            }

            try {
                await fetch('/u/discover/set-search-expired', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': pageCsrfToken
                    },
                    keepalive: true
                });

                const res = await fetch('/u/matches/create-channel', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': pageCsrfToken
                    },
                    body: JSON.stringify({
                        targetUserId: partnerId
                    })
                });

                const data = await res.json();

                if (data.success) {
                    window.location.href = `/u/messages?channelId=${data.channel_id}`;
                    return;
                }

                console.error('Error:', data.error);
            } catch (err) {
                console.error("Failed to create channel:", err);
            }

            if (messageLoading && pageContent) {
                messageLoading.style.display = 'none';
                pageContent.style.display = 'block';
                pageContent.style.pointerEvents = 'auto';
            }
        });
    }

    window.addEventListener('beforeunload', () => {
        unsubscribeAll();

        if (!isIntentionalNavigation && !terminalState) {
            setSearchExpired();
            setSessionStatusRejected();
        }
    });

    window.addEventListener('pagehide', () => {
        unsubscribeAll();

        if (!isIntentionalNavigation && !terminalState) {
            setSearchExpired();
            setSessionStatusRejected();
        }
    });
</script>

<?php require base_path('views/shared/footer.php') ?>
