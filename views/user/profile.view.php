<?php
require(base_path("views/shared/header.php"));
?>

<?php if ($isLoading): ?>
    <div class="profile-loading-container">
        <div class="profile-loading-section">
            <div class="profile-loading-icon"></div>
            <p class="profile-loading-text">Loading your profile</p>
        </div>
    </div>
<?php elseif (!isset($user)): ?>
    <div class="profile-null-container">
        <div class="profile-null-section">
            <div class="profile-null-symbol">
                <span class="profile-null-text">❌</span>
            </div>
            <h2>Profile not found</h2>
            <p class="profile-null-error-text"><?php echo $error || "Unable to load your profile. Please try again." ?></p>
            <button class="profile-null-button">Retry</button>
        </div>
    </div>
<?php else: ?>
    <!-- render normal -->
    <div class="profile-container">
        <div class="profile-section">
            <header class="profile-header">
                <h1>My Profile</h1>
                <p>Manage your profile and preferences</p>
            </header>

            <div class="profile-section-one">
                <div class="profile-section-two">
                    <div class="profile-section-three">
                        <div class="profile-section-four">
                            <div class="profile-section-five">
                                <div class="profile-section-six">
                                    <div class="profile-section-seven">
                                        <img src="<?php echo $user->avatarUrl ?>" alt="<?php echo $user->fullName ?>">
                                    </div>
                                </div>

                                <div class="profile-inner-one">
                                    <h2><?php echo $user->fullName /* and age */ ?></h2>
                                    <p class="profile-inner-one-text-one">@<?php echo $user->username ?></p>
                                    <p class="profile-inner-one-text-two">Member since <?php echo $user->createdAt ?></p>
                                </div>
                            </div>

                            <div class="profile-inner-two">
                                <div>
                                    <h3>About Me</h3>
                                    <p><?php echo $user->bio ?></p>
                                </div>

                                <div class="profile-fk-namings">
                                    <h3>Basic Information</h3>

                                    <div class="profile-fk-namings-one">
                                        <div>
                                            <label class="profile-fk-namings-gender">Gender</label>
                                            <p class="profile-fk-namings-gender-text"><?php echo $user->gender ?></p>
                                        </div>
                                        <div>
                                            <label class="profile-birthday-text">Birthday</label>
                                            <p class="profile-birthday-text-p">
                                                <?php echo $user->birthdate ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 class="profile-dating-preferences-text">Dating Preferences</h3>
                                    <div class="profile-dating-preferences-grid">
                                        <div>
                                            <label class="profile-dating-age-label">Age Range</label>
                                            <p class="profile-dating-age-text"><?php echo $userPreferences['age_range']['min'] ?> - <?php echo $userPreferences['age_range']['max'] ?></p>
                                        </div>
                                        <div>
                                            <label class="profile-dating-distance-label">Distance</label>
                                            <p class="profile-dating-distance-text"><?php echo $userPreferences['distance'] ?> km</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="profile-section-eight">
                        <div class="profile-section-nine">
                            <h3 class="profile-quick-action-text">Quick Actions</h3>

                            <div class="profile-section-ten">
                                <a href="/u/profile-edit">
                                    <div class="profile-section-eleven">
                                        <div class="profile-section-twelve">
                                            <svg
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth={2}
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </div>
                                        <span class="profile-edit-profile-text">Edit Profile</span>
                                    </div>
                                    <svg
                                        class="profile-arrow-symbol"
                                        fill="none"
                                        stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path
                                            strokeLinecap="round"
                                            strokeLinejoin="round"
                                            strokeWidth={2}
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                                <a href="/u/profile-preferences-edit" class="profile-edit-action">
                                    <div class="profile-section-eleven">
                                        <div class="profile-section-twelve">
                                            <!-- Icon (same style as Edit Profile or change later if needed) -->
                                            <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8c-1.657 0-3 1.343-3 3v4h6v-4c0-1.657-1.343-3-3-3zM5 12h1v8H5zM18 12h1v8h-1z" />
                                            </svg>
                                        </div>
                                        <span class="profile-edit-profile-text">Edit Preferences</span>
                                    </div>
                                    <svg class="profile-arrow-symbol" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 5l7 7-7 7" />
                                    </svg>
                                </a>
                            </div>
                        </div>
                        <div class="profile-section-thirteen">
                            <h3>Account</h3>
                            <div class="profile-section-fourteen">
                                <div class="profile-section-fifteen">
                                    <span class="profile-username-label">Username</span>
                                    <span class="profile-username-text">@<?php echo $user->username ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>



<?php require(base_path("views/shared/footer.php")) ?>