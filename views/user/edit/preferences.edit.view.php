<?php
// file path = root/views/user/edit/profile.preferences.edit.view.php
require(base_path("views/shared/header.php"));

?>

<div class="profile-edit-page-container">
    <div class="profile-edit-page-section">
        <header class="profile-edit-header">
            <h1>Edit Preferences</h1>
            <p>Update your dating preferences</p>
        </header>

        <div class="profile-edit-page-section-two">
            <div class="profile-edit-page-form">

                <!-- optional small user preview -->
                <div class="profile-edit-page-section-three">
                    <label class="profile-edit-pp-text">Profile</label>
                    <div class="profile-edit-page-section-four">
                        <div class="profile-edit-page-section-five">
                            <div class="profile-edit-page-section-six">
                                <img src="<?php echo $user->avatarUrl ?? "/assets/images/default-avatar.png" ?>" alt="Profile" id="pp-img">
                            </div>
                        </div>
                        <div>
                            <p class="profile-edit-page-upload-text"><?php echo htmlspecialchars($user->fullName) ?></p>
                            <p class="profile-edit-page-types-text">@<?php echo htmlspecialchars($user->username) ?></p>
                        </div>
                    </div>
                </div>

                <?php
                // display flash messages (if any)
                if (!empty($_SESSION['flash_message'])): ?>
                    <div class="profile-flash success"><?= htmlspecialchars($_SESSION['flash_message']) ?></div>
                    <?php unset($_SESSION['flash_message']);
                endif;

                if (!empty($_SESSION['flash_error'])): ?>
                    <div class="profile-flash error"><?= htmlspecialchars($_SESSION['flash_error']) ?></div>
                    <?php unset($_SESSION['flash_error']);
                endif;
                ?>

                <form action="/u/submit-edit-preferences" method="POST" class="preferences-form">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <label>Age Range 18-99</label>
                    <div class="profile-edit-page-section-seven">
                        <div class="age-range">
                            <input 
                                type="number" 
                                id="age_min" 
                                name="age_min" 
                                min="18" 
                                max="99" 
                                value="<?php echo htmlspecialchars($preferences['age_range']['min'] ?? 18) ?>" 
                                required
                            >
                            <span class="age-range-sep">to</span>
                            <input 
                                type="number" 
                                id="age_max" 
                                name="age_max" 
                                min="18" 
                                max="99" 
                                value="<?php echo htmlspecialchars($preferences['age_range']['max'] ?? 35) ?>" 
                                required
                            >
                        </div>
                    </div>

                    <div class="profile-edit-page-section-ten">
                        <div class="profile-edit-page-section-eleven">
                            <label for="gender_preference">Gender Preference</label>
                            <?php $gp = $preferences['gender_preference'] ?? 'other'; ?>
                            <select name="gender_preference" id="gender_preference" required>
                                <option value="male" <?= $gp === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= $gp === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= $gp === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>

                        <div class="profile-edit-page-section-twelve">
                            <label for="distance">Maximum Distance (km)</label>
                            <input type="number" name="distance" id="distance" min="1" max="500"
                                value="<?php echo htmlspecialchars($preferences['distance'] ?? 50) ?>" required>
                        </div>
                    </div>

                    <div class="profile-edit-page-section-fourteen">
                        <a href="/u/profile">Cancel</a>
                        <button type="submit" class="profile-edit-page-section-fourteen-btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require(base_path("views/shared/footer.php")) ?>
