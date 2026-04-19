<?php

require base_path('views/shared/header.php');

use Core\Auth;

?>

<section class="discover-section discover-search-page">
    <div class="discover-container discover-search-container">
        <div class="discover-search-copy">
            <h1 id="status-msg">Find Your Match</h1>
            <p id="match-search-text">Click start to find a match.</p>
        </div>

        <div class="discover-loading" id="discover-loading-indicator">
            <svg width="64px" height="48px">
                <polyline points="0.157 23.954, 14 23.954, 21.843 48, 43 0, 50 24, 64 24" id="back"></polyline>
                <polyline points="0.157 23.954, 14 23.954, 21.843 48, 43 0, 50 24, 64 24" id="front"></polyline>
            </svg>
        </div>

        <div class="discover-search-actions" id="find-match-container">
            <form id="find-match-form" method="POST" class="discover-search-form">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <button type="submit" id="start-match-btn" class="btn btn-primary">
                    Start Finding
                </button>
            </form>
        </div>

        <div class="discover-search-actions" id="stop-match-container">
            <form id="stop-match-form" method="POST" class="discover-search-form">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <button type="submit" class="btn btn-primary">
                    Stop Searching
                </button>
            </form>
        </div>
    </div>
</section>

<?php require base_path('views/notifications/rejected-notif.php') ?>

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
    const matchSearchText = document.getElementById('match-search-text');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    let matchSessionSub = null;
    let activeSearchSub = null;
    let cleanupInProgress = false;
    let searchLocked = false;
    let isIntentionalNavigation = false;

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
        startSearchContainer.style.pointerEvents = 'auto';
    }

    function hideStartSearchContainer() {
        startSearchContainer.style.display = 'none';
        startSearchContainer.style.pointerEvents = 'none';
    }

    async function setSearchExpired(fromStopButton = false) {
        if (cleanupInProgress) {
            console.warn("Cleanup already in progress - skipping.");
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
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                keepalive: true
            });
        } catch (err) {
            console.warn("Cleanup failed:", err);
        } finally {
            cleanupInProgress = false;
            if (!fromStopButton) {
                searchLocked = false;
            }
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
                headers: {
                    'X-CSRF-Token': csrfToken
                },
                keepalive: true
            });
        } catch (err) {
            console.warn("Cleanup failed:", err);
        }
    }

    if (findMatchForm) {
        findMatchForm.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (searchLocked) {
                console.warn("Search is already active - ignoring duplicate click.");
                return;
            }

            searchLocked = true;
            findBtn.disabled = true;
            statusMsg.textContent = "Searching for matches...";
            matchSearchText.textContent = "Waiting for another user to respond.";
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
                            if (!session) return;

                            if (
                                (session.user_a === currentUser || session.user_b === currentUser) &&
                                session.status === 'pending'
                            ) {
                                isIntentionalNavigation = true;
                                const partnerId = session.user_a === currentUser ? session.user_b : session.user_a;

                                sessionStorage.setItem('searching', 'false');
                                setSearchInMatch().finally(() => {
                                    window.location.href = `/u/discover/matched-user?partner=${partnerId}&match=${session.id}`;
                                });
                            }
                        }
                    )
                    .subscribe((status) => {
                        console.log("Subscription status:", status);
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
                            if (!session) return;

                            if (session.user_id === currentUser && session.status === 'expired') {
                                hideStopSearchContainer();
                                showStartSearchContainer();

                                if (activeSearchSub) {
                                    activeSearchSub.unsubscribe();
                                    activeSearchSub = null;
                                }
                                if (matchSessionSub) {
                                    matchSessionSub.unsubscribe();
                                    matchSessionSub = null;
                                }

                                statusMsg.textContent = "Failed to find match";
                                matchSearchText.textContent = "Click retry to find a match.";
                                findBtn.disabled = false;
                                findBtn.textContent = "Retry";
                                discoverLoading.style.display = 'none';
                                searchLocked = false;
                            }
                        }
                    )
                    .subscribe((status) => {
                        console.log("Search status subscription:", status);
                    });

                const res = await fetch('/u/discover/start-search', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': csrfToken
                    }
                });

                if (!res.ok) {
                    throw new Error('Failed to start search');
                }
            } catch (err) {
                console.error("Error starting match search:", err);
                statusMsg.textContent = "Failed to start search.";
                matchSearchText.textContent = "Please try again.";
                findBtn.disabled = false;
                discoverLoading.style.display = 'none';
                showStartSearchContainer();
                hideStopSearchContainer();
                searchLocked = false;
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
            matchSearchText.textContent = "Click start to find a match.";
            discoverLoading.style.display = 'none';
            findBtn.disabled = false;
            findBtn.textContent = "Start Finding";
            searchLocked = false;
        });
    }

    if (sessionStorage.getItem('searching') === 'true') {
        sessionStorage.removeItem('searching');

        findBtn.disabled = true;
        statusMsg.textContent = "Searching for matches...";
        matchSearchText.textContent = "Waiting for another user to respond.";
        discoverLoading.style.display = 'flex';
        hideStartSearchContainer();
        showStopSearchContainer();

        findMatchForm.dispatchEvent(new Event('submit'));
    }

    window.addEventListener('beforeunload', () => {
        if (!isIntentionalNavigation) {
            setSearchExpired(false);
        }
    });

    window.addEventListener('pagehide', () => {
        if (!isIntentionalNavigation) {
            setSearchExpired(false);
        }
    });
</script>

<?php require base_path('views/shared/footer.php') ?>