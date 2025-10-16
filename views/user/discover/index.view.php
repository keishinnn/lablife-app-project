<?php

// file path = root/Views/user/discover/index.view.php
require base_path('views/shared/header.php');

use Core\Auth;

?>

<main class="hero">
    <section id="hero-section">
        <h1 style="margin-bottom: 3rem" id="status-msg">
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

    findMatchForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        findBtn.disabled = true;
        statusMsg.textContent = "Searching for matches...";
        discoverLoading.style.display = 'flex';

        hideStartSearchContainer();
        showStopSearchContainer();

        try {
            // Subscribe to Supabase realtime for new active users

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

                        // Check if the current user is part of this session
                        if (session.user_a === currentUser || session.user_b === currentUser) {

                            if (matchSessionSub) matchSessionSub.unsubscribe();

                            const partnerId = session.user_a === currentUser ? session.user_b : session.user_a;

                            window.location.href = `/u/discover/matched-user?partner=${partnerId}&match=${session.id}`;

                            cleanupSearch();
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
                        event: 'UPDATE',
                        schema: 'public',
                        table: 'active_match_searches',
                    },
                    (payload) => {
                        const session = payload.new;

                        // Check if the current user is part of this session
                        if (session.user_id === currentUser && session.status == 'expired') {
                            hideStopSearchContainer();
                            showStartSearchContainer();
                            cleanupSearch();
                            if (activeSearchSub) activeSearchSub.unsubscribe();

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

    stopSearchForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        cleanupSearch();

        showStartSearchContainer();
        hideStopSearchContainer();

        statusMsg.textContent = "Find Your Match";
        discoverLoading.style.display = 'none';
        findBtn.disabled = false;
    });

    // Cleanup listener — runs when the user leaves the page
    async function cleanupSearch() {
        try {
            console.log("Cleaning up active match search for user:", currentUser);
            await fetch('/u/discover/stop-search', {
                method: 'POST',
                keepalive: true
            });
        } catch (err) {
            console.warn("Cleanup failed or not needed:", err);
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
    window.addEventListener('beforeunload', cleanupSearch);
    window.addEventListener('pagehide', cleanupSearch);
</script>
<?php require base_path('views/shared/footer.php') ?>