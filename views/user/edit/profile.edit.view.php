<?php

require(base_path("views/shared/header.php"));

?>

<div class="profile-edit-page-container">
    <div class="profile-edit-page-section">
        <header class="profile-edit-header">
            <h1>
                Edit Profile
            </h1>
            <p>
                Update your profile information
            </p>
        </header>

        <div class="profile-edit-page-section-two">
            <!-- am too lazy to re-edit the design -->
            <div class="profile-edit-page-form">
                <div class="profile-edit-page-section-three">
                    <label for="" class="profile-edit-pp-text">
                        Profie Picture
                    </label>
                    <div class="profile-edit-page-section-four">
                        <div class="profile-edit-page-section-five">
                            <div class="profile-edit-page-section-six">
                                <img src="<?php echo $user->avatarUrl ?? "/assets/images/default-avatar.png" ?>" alt="Profile" id="pp-img">
                            </div>

                            <!-- Camera button -->
                            <label for="avatar-upload" class="profile-edit-avatar-button">
                                <svg width="15px" height="15px" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2 8.37722C2 8.0269 2 7.85174 2.01462 7.70421C2.1556 6.28127 3.28127 5.1556 4.70421 5.01462C4.85174 5 5.03636 5 5.40558 5C5.54785 5 5.61899 5 5.67939 4.99634C6.45061 4.94963 7.12595 4.46288 7.41414 3.746C7.43671 3.68986 7.45781 3.62657 7.5 3.5C7.54219 3.37343 7.56329 3.31014 7.58586 3.254C7.87405 2.53712 8.54939 2.05037 9.32061 2.00366C9.38101 2 9.44772 2 9.58114 2H14.4189C14.5523 2 14.619 2 14.6794 2.00366C15.4506 2.05037 16.126 2.53712 16.4141 3.254C16.4367 3.31014 16.4578 3.37343 16.5 3.5C16.5422 3.62657 16.5633 3.68986 16.5859 3.746C16.874 4.46288 17.5494 4.94963 18.3206 4.99634C18.381 5 18.4521 5 18.5944 5C18.9636 5 19.1483 5 19.2958 5.01462C20.7187 5.1556 21.8444 6.28127 21.9854 7.70421C22 7.85174 22 8.0269 22 8.37722V16.2C22 17.8802 22 18.7202 21.673 19.362C21.3854 19.9265 20.9265 20.3854 20.362 20.673C19.7202 21 18.8802 21 17.2 21H6.8C5.11984 21 4.27976 21 3.63803 20.673C3.07354 20.3854 2.6146 19.9265 2.32698 19.362C2 18.7202 2 17.8802 2 16.2V8.37722Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    <path d="M12 16.5C14.2091 16.5 16 14.7091 16 12.5C16 10.2909 14.2091 8.5 12 8.5C9.79086 8.5 8 10.2909 8 12.5C8 14.7091 9.79086 16.5 12 16.5Z" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                                <input type="file" id="avatar-upload" name="avatar_input" accept="image/*" hidden>
                            </label>
                        </div>

                        <div>
                            <p class="profile-edit-page-upload-text">
                                Upload a new profile picture
                            </p>
                            <p class="profile-edit-page-types-text">
                                JPG, PNG or GIF . Max 5MB.
                            </p>
                        </div>
                    </div>
                </div>
                
                <form action="/u/submit-edit-profile" enctype="multipart/form-data" method="POST">
                    <div class="profile-edit-page-section-seven">
                        <div class="profile-edit-page-section-eight">
                            <label for="full-name">Full Name *</label>
                            <input
                                type="text"
                                name="full-name"
                                placeholder="Enter your full name"
                                required
                                value="<?php echo $user->fullName ?>"
                                minlength="5"
                                maxlength="50" />
                        </div>

                        <div class="profile-edit-page-section-nine">
                            <label for="username">Username *</label>
                            <input
                                type="text"
                                name="username"
                                required
                                placeholder="Choose a username"
                                value="<?php echo $user->username ?>"
                                minlength="3"
                                maxlength="20" />
                        </div>
                    </div>

                    <div class="profile-edit-page-section-ten">
                        <div class="profile-edit-page-section-eleven">
                            <label for="gender">Gender *</label>
                            <select name="gender" id="" required>
                                <option value="male" <?= $user->gender === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= $user->gender === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= $user->gender === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>

                        <div class="profile-edit-page-section-twelve">
                            <label for="birthdate">Birthday *</label>
                            <input
                                type="date"
                                name="birthdate"
                                id=""
                                required
                                class=""
                                value="<?= htmlspecialchars($user->birthdate) ?>">
                        </div>
                    </div>

                    <div class="profile-edit-page-section-thirteen">
                        <label for="bio">About Me *</label>
                        <textarea
                            name="bio"
                            id="bio"
                            required
                            rows="4"
                            maxlength="500"
                            placeholder="Tell others about yourself..."><?= htmlspecialchars($user->bio ?? '') ?></textarea>
                        <p>/500 characters</p>
                    </div>

                    <?php if (!isset($error)): ?>
                        <div class="profile-edit-page-error">
                            <?php echo $error ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!isset($message)): ?>
                        <div class="profile-edit-page-error">
                            <?php echo $error ?>
                        </div>
                    <?php endif; ?>

                    <div class="profile-edit-page-section-fourteen">
                        <a href="/u/profile">Cancel</a>
                        <button type="submit" class="profile-edit-page-section-fourteen-btn"><?php echo $isLoading ? "Saving..." : "Save Changes" ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- script for updating profile photo without creating a form -->
    <script>
        const fileInput = document.getElementById('avatar-upload');
        const previewImg = document.getElementById('pp-img');

        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (!file) return;

            // Add loading effect to current avatar
            previewImg.classList.add('avatar-loading');

            // Prepare upload
            const formData = new FormData();
            formData.append('avatar_input', file);

            // Upload to backend
            fetch('/u/submit-edit-avatar', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    console.log("Upload response:", data); // 👀 DEBUG
                    if (data.success && data.avatarUrl) {
                        previewImg.src = data.avatarUrl + "?t=" + Date.now();
                    } else {
                        alert(data.message || 'Failed to upload avatar.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Something went wrong while uploading.');
                })
                .finally(() => {
                    previewImg.classList.remove('avatar-loading');
                });
        });
    </script>

</div>

<?php require(base_path("views/shared/footer.php")) ?>