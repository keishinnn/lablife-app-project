<?php
require(base_path("views/shared/header.php"));

$avatarUrl = !empty($user->avatarUrl)
    ? $user->avatarUrl
    : '/assets/images/default-avatar.png';

?>

<div class="profile-container" id="profile-modify-style">
    <div class="profile-section" id="profile-container-section">
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
                                    <img src="<?php echo htmlspecialchars($avatarUrl); ?>"
                                        alt="<?php echo htmlspecialchars($user->fullName); ?>">
                                </div>
                            </div>

                            <div class="profile-inner-one">
                                <h2><?php echo $user->fullName /* and age */ ?></h2>
                                <p class="profile-inner-one-text-one">@<?php echo $user->username ?></p>
                                <p class="profile-inner-one-text-two">Member since <?php echo date("Y-m-d", strtotime($user->createdAt)) ?></p>
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

                            <div class="profile-dating-preferences-grid" style="margin-top: 2rem;">
                                <div>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <h3 class="profile-dating-preferences-text">Personality Type</h3>
                                        <?php if (isset($personalityType)): ?>
                                            <button style="background: transparent; border: none; padding-bottom: 10px" class="p-page-edit-btn-ha" id="p-ptypes-edit-btn">
                                                <svg
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24" height="1.5rem" width="1.5rem">
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth={2}
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                    </div>

                                    <div style="display: flex;">
                                        <?php if (isset($personalityType)): ?>
                                            <p class="p-page-pt-type-text"><?php echo $personalityType->name ?></p>
                                        <?php else: ?>
                                            <button class="profile-pt-btn" style="display: flex; align-items: center; gap: 0.5rem" id="p-ptypes-add-btn">
                                                <span style="font-size: 1.5rem;">+</span> Add
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <h3 class="profile-dating-preferences-text">Hobbies</h3>
                                        <?php if (!empty($userHobbies)): ?>
                                            <button style="background: transparent; border: none; padding-bottom: 10px" class="p-page-edit-btn-ha" id="p-hb-edit-btn">
                                                <svg
                                                    fill="none"
                                                    stroke="currentColor"
                                                    viewBox="0 0 24 24" height="1.5rem" width="1.5rem">
                                                    <path
                                                        strokeLinecap="round"
                                                        strokeLinejoin="round"
                                                        strokeWidth={2}
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                    <div style="display: flex; flex-direction: row; gap: 1rem; flex-wrap: wrap;">
                                        <?php if (!empty($userHobbies)): ?>
                                            <?php foreach ($userHobbies as $hobby): ?>
                                                <p class="p-pf-hb-text">
                                                    <?php echo $hobby->name ?>
                                                </p>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <button class="profile-pt-btn" style="display: flex; align-items: center; gap: 0.5rem" id="p-hb-add-btn">
                                                <span style="font-size: 1.5rem;">+</span> Add
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>

                            </div>


                            <div style="margin-top: 2rem;">
                                <div style="display: flex; gap: 0.5rem;">
                                    <h3 class="profile-dating-preferences-text">Interests</h3>
                                    <?php if (!empty($userInterests)): ?>
                                        <button style="background: transparent; border: none; padding-bottom: 10px" class="p-page-edit-btn-ha" id="p-interests-edit-btn">
                                            <svg
                                                fill="none"
                                                stroke="currentColor"
                                                viewBox="0 0 24 24" height="1.5rem" width="1.5rem">
                                                <path
                                                    strokeLinecap="round"
                                                    strokeLinejoin="round"
                                                    strokeWidth={2}
                                                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                        </button>
                                    <?php endif; ?>
                                </div>

                                <div style="display: flex; flex-direction: row; gap: 1rem; flex-wrap: wrap;">
                                    <?php if (!empty($userInterests)): ?>
                                        <?php foreach ($userInterests as $interest): ?>
                                            <p class="p-pf-hb-text">
                                                <?php echo $interest->name ?>
                                            </p>
                                        <?php endforeach; ?>
                                    <?php else: ?>
                                        <button class="profile-pt-btn" style="display: flex; align-items: center; gap: 0.5rem" id="p-interests-add-btn">
                                            <span style="font-size: 1.5rem;">+</span> Add
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger" style="margin-top: 2rem;"><?php echo $error ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="profile-section-eight">
                    <div class="profile-section-nine">
                        <h3 class="profile-quick-action-text">Quick Actions</h3>

                        <div class="profile-section-ten">
                            <a href="/u/profile-edit" class="profile-edit-action">
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
                            <a href="/u/profile/blocked-users" class="profile-edit-action" id="blocked-users-nav-btn">
                                <div class="profile-section-eleven">
                                    <div class="profile-section-twelve">
                                        <!-- Icon (same style as Edit Profile or change later if needed) -->
                                        <svg fill="#ffffff" viewBox="0 0 85.16 85.16">
                                            <path d="M72.697,12.458c-16.611-16.611-43.63-16.611-60.24,0c-16.604,16.611-16.604,43.636,0,60.243 c16.61,16.611,43.637,16.611,60.24,0C89.309,56.094,89.309,29.066,72.697,12.458z M19.129,19.128 c10.917-10.92,27.617-12.618,40.335-5.096L14.037,59.468C6.506,46.749,8.205,30.048,19.129,19.128z M66.024,66.029 c-10.842,10.842-27.381,12.587-40.065,5.25l45.314-45.316C78.621,38.648,76.873,55.187,66.024,66.029z"></path>
                                        </svg>
                                    </div>
                                    <span class="profile-edit-profile-text">Blocked Users</span>
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



<!-- Modals -->
<?php require(base_path('Views/modals/ptypes-modal.php')) ?>
<?php require(base_path('Views/modals/hobbies-modal.php')) ?>
<?php require(base_path('Views/modals/interests-modal.php')) ?>

<!-- Footer -->
<?php require(base_path("views/shared/footer.php")) ?>
