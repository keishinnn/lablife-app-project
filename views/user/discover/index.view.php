<?php

// file path = root/Views/user/discover/index.view.php
require base_path('views/shared/header.php');

use Core\Auth;

?>


<main class="hero">
    <section id="hero-section">
        <?php if (!$isVerified): ?>
            <div style="display: flex; align-items: center; justify-content: center; flex-direction: column;">
                <h1>Your account is not verified yet</h1>
                <p style="max-width: 45rem;">
                    You need to verify your account before you can start finding matches.
                    Please click the Get Verified button to start verifying your account.
                </p>

                <a href="/u/verify" id="start-match-btn" class="btn btn-primary">
                    Get Verified →
                </a>
            </div>


        <?php else: ?>
            <h1 id="status-msg">
                Find Your Match
            </h1>

            <div class="discover-loading" id="discover-loading-indicator">
                <svg width="64px" height="48px">
                    <polyline points="0.157 23.954, 14 23.954, 21.843 48, 43 0, 50 24, 64 24" id="back"></polyline>
                    <polyline points="0.157 23.954, 14 23.954, 21.843 48, 43 0, 50 24, 64 24" id="front"></polyline>
                </svg>
            </div>

            <div style="align-items: center; justify-content: center; display: flex;" id="find-match-container">
                <form id="find-match-form" method="POST" style="text-align: center;">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <button type="submit" id="start-match-btn" class="btn btn-primary">
                        Start Finding →
                    </button>


                    <p style="margin-top: 1rem;" id="match-search-text">Click start to find a match.</p>
                </form>
            </div>
            <div id="stop-match-container">
                <form id="stop-match-form" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                    <button type="submit" class="btn btn-primary">
                        Stop
                    </button>
                </form>
            </div>
        <?php endif; ?>
    </section>
</main>




<?php require(base_path('Views/notifications/rejected-notif.php')) ?>

<script type="module">
    import {
        subscribeToSupabase
    } from "/assets/js/supabase/client.js";

    const supabaseClient = subscribeToSupabase(
        '<?= $_ENV['SUPABASE_URL'] ?>',
        '<?= $_ENV['SUPABASE_ANON_KEY'] ?>'
    );

    const startSearchContainer = document.getElementById('find-match-container');
    const stopSearchContainer = document.getElementById('stop-match-container');
    const currentUser = '<?= Auth::user(); ?>';

    const findMatchForm = document.getElementById('find-match-form');
    const stopSearchForm = document.getElementById('stop-match-form');
    const findBtn = document.getElementById('start-match-btn');
    const statusMsg = document.getElementById('status-msg');
    const discoverLoading = document.getElementById('discover-loading-indicator');
    const matchSearchText = document.getElementById("match-search-text");

    let matchSessionSub = null;
    let activeSearchSub = null;
    let searchStarted = false;

    let cleanupInProgress = false;
    let searchLocked = false;
    let isIntentionalNavigation = false;

    if (findMatchForm) {
        findMatchForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (searchLocked) {
                console.warn("Search is already active — ignoring duplicate click.");
                return;
            }

            searchLocked = true;

            findBtn.disabled = true;
            statusMsg.textContent = "Searching for matches...";
            discoverLoading.style.display = 'flex';

            hideStartSearchContainer();
            showStopSearchContainer();

            try {
                matchSessionSub = supabaseClient
                    .channel('public:match_sessions')
                    .on(
                        'postgres_changes', {
                            event: '*',
                            schema: 'public',
                            table: 'match_sessions',
                        },
                        (payload) => {
                            const session = payload.new;
                            console.log(session);

                            if ((session.user_a === currentUser || session.user_b === currentUser) && session.status === 'pending') {
                                isIntentionalNavigation = true;

                                if (matchSessionSub) matchSessionSub.unsubscribe();
                                if (activeSearchSub) activeSearchSub.unsubscribe();

                                const partnerId = session.user_a === currentUser ? session.user_b : session.user_a;

                                window.location.href = `/u/discover/matched-user?partner=${partnerId}&match=${session.id}`;
                                sessionStorage.setItem('searching', 'false');
                                setSearchInMatch();
                            }
                        }
                    )
                    .subscribe((status) => {
                        console.log("🛰️ Subscription status:", status);
                    });

                activeSearchSub = supabaseClient
                    .channel('public:active_match_searches')
                    .on(
                        'postgres_changes', {
                            event: '*',
                            schema: 'public',
                            table: 'active_match_searches',
                            filter: `user_id=eq.${currentUser}`,
                        },
                        (payload) => {
                            const session = payload.new;
                            console.log(session);

                            // Check if the current user is part of this session
                            if (session.user_id === currentUser && session.status == 'expired') {
                                hideStopSearchContainer();
                                showStartSearchContainer();
                                if (activeSearchSub) activeSearchSub.unsubscribe();
                                if (matchSessionSub) matchSessionSub.unsubscribe();

                                statusMsg.textContent = "Failed to find match";
                                findBtn.disabled = false;
                                findBtn.textContent = "Retry";
                                matchSearchText.textContent = "Click retry to find a match."
                                discoverLoading.style.display = 'none';
                            }
                        }
                    )
                    .subscribe((status) => {
                        console.log("Subscription status:", status);
                    });

                const res = await fetch('/u/discover/start-search', {
                    method: 'POST'
                });
                if (!res.ok) throw new Error('Failed to start search');
                searchStarted = true;

            } catch (err) {
                console.error("❌ Error starting match search:", err);
                statusMsg.textContent = "Failed to start search.";
                findBtn.disabled = false;
                discoverLoading.style.display = 'none';
            }
        });
    }


    if (stopSearchForm) {
        stopSearchForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            await setSearchExpired(true);

            showStartSearchContainer();
            hideStopSearchContainer();

            statusMsg.textContent = "Find Your Match";
            discoverLoading.style.display = 'none';
            findBtn.disabled = false;
            searchLocked = false;
        });
    }

    // Cleanup listener — runs when the user leaves the page
    async function setSearchExpired(fromStopButton = false) {
        if (cleanupInProgress) {
            console.warn("Cleanup already in progress — skipping.");
            return;
        }
        cleanupInProgress = true;

        try {
            if (matchSessionSub) {
                await matchSessionSub.unsubscribe();
                matchSessionSub = null;
            }
            if (activeSearchSub) {
                await activeSearchSub.unsubscribe();
                activeSearchSub = null;
            }

            await fetch('/u/discover/set-search-expired', {
                method: 'POST',
                keepalive: true
            });
        } catch (err) {
            console.warn("Cleanup failed:", err);
        } finally {
            cleanupInProgress = false;
            if (!fromStopButton) searchLocked = false;
        }
    }

    async function setSearchInMatch() {
        try {
            if (matchSessionSub) {
                await matchSessionSub.unsubscribe();
                matchSessionSub = null;
            }
            if (activeSearchSub) {
                await activeSearchSub.unsubscribe();
                activeSearchSub = null;
            }

            await fetch('/u/discover/set-search-in-match', {
                method: 'POST',
                keepalive: true
            });
        } catch (err) {
            console.warn("Cleanup failed:", err);
        }
    }

    function showStopSearchContainer() {
        stopSearchContainer.style.display = 'flex';
        stopSearchContainer.style.pointerEvents = 'auto';
    }

    function hideStopSearchContainer() {
        stopSearchContainer.style.display = 'none';
        stopSearchContainer.style.pointerEvents = 'none';
    }

    function showStartSearchContainer() {
        startSearchContainer.style.display = 'flex';
        startSearchContainer.style.pointerEvents = "auto";
    }

    function hideStartSearchContainer() {
        startSearchContainer.style.display = 'none';
        startSearchContainer.style.pointerEvents = "none";
    }

    if (sessionStorage.getItem('searching') === 'true') {
        sessionStorage.removeItem('searching');

        findBtn.disabled = true;
        statusMsg.textContent = "Searching for matches...";
        discoverLoading.style.display = 'flex';
        hideStartSearchContainer();
        showStopSearchContainer();

        findMatchForm.dispatchEvent(new Event('submit'));
    }

    // Listen for page unloads, tab close, navigation, etc.
    window.addEventListener('beforeunload', (event) => {
        if (!isIntentionalNavigation) setSearchExpired(false);
    });

    window.addEventListener('pagehide', (event) => {
        if (!isIntentionalNavigation) setSearchExpired(false);
    });

    window.addEventListener('load', () => {
        const reconLoading = document.getElementById('recon-loading');
        if (reconLoading) reconLoading.style.display = 'none';
    });
</script>
<?php require base_path('views/shared/footer.php') ?>