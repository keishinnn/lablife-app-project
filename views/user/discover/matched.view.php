<?php

// file path = root/Views/user/discover/index.view.php
require base_path('views/shared/header.php');
?>

<div class="discover-section">
    <div class="discover-container">
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

            <div class="discover-match-text">
                <h1>Match Found!</h1>
            </div>
        </header>

        <div class="discover-match-container">

            <div class="discover-match-card">
                <div class="discover-match-card-swipe">
                    <div class="discover-match-card-section-two">
                        <img src="<?php echo $user->avatarUrl ?>" alt="">

                        <div class="discover-match-card-section-three">
                            <div class="discover-match-card-section-four">
                                <div class="discover-match-card-section-five">
                                    <h2><?php echo htmlspecialchars($user->fullName)  ?>, 32</h2>
                                    <p class="discover-match-card-user-text"><?php echo htmlspecialchars($user->username) ?></p>
                                    <p class="discover-match-card-bio-text"><?php echo htmlspecialchars($user->bio) ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Buttons -->
            <div style="margin-top: 2rem;">
                <div class="discover-match-buttons">
                    <button class="discover-match-button-dislike" aria-label="Pass">
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

                    <button class="discover-match-button-like" aria-label="Like">
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
                </div>
            </div>
        </div>
    </div>
</div>


<?php require base_path('views/shared/footer.php') ?>