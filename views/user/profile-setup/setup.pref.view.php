<?php

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lablife</title>
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/css/footer.css">
    <link rel="stylesheet" href="/assets/css/auth-page.css">
    <link rel="stylesheet" href="/assets/css/index-page.css">
    <link rel="stylesheet" href="/assets/css/profile-page/edit/profile-edit-page.css">
    <link rel="stylesheet" href="/assets/css/profile-page/profile-page.css">
    <link rel="stylesheet" href="/assets/css/profile-page/setup-user.css">
    <link rel="stylesheet" href="/assets/css/profile-page/setup-user-pref.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/css/profile-page/profile-null.css">
    <link rel="stylesheet" href="/assets/css/profile-page/profile-loading.css">
    <link rel="stylesheet" href="/assets/css/profile-page/profile-get-loading.css">
    <link rel="stylesheet" href="/assets/css/profile-page/modals-style/hobbies-modal.css">
    <link rel="stylesheet" href="/assets/css/profile-page/modals-style/ptypes-modal.css">
    <link rel="stylesheet" href="/assets/css/profile-page/modals-style/interests-modal.css">
    <link rel="stylesheet" href="/assets/css/discover-page/discover-page.css">
    <link rel="stylesheet" href="/assets/css/profile-page/edit/preference-edit-page.css">
    <link rel="stylesheet" href="/assets/css/discover-page/discover-loading.css">
    <link rel="stylesheet" href="/assets/css/discover-page/notifications/notification.css">
    <link rel="stylesheet" href="/assets/css/discover-page/notifications/notification-rejected.css">
    <link rel="stylesheet" href="/assets/css/discover-page/notifications/notif-recon-loading.css">
    <link rel="stylesheet" href="/assets/css/discover-page/notifications/notification-expired.css">
    <link rel="stylesheet" href="/assets/css/matches-page/matches-page.css">
    <link rel="stylesheet" href="/assets/css/matches-page/loading/matches-loading.css">
    <link rel="stylesheet" href="/assets/css/footer.css">

    <link rel="stylesheet" href="/assets/css/matches-page/matches-page.css">
    <link rel="stylesheet" href="/assets/css/matches-page/loading/matches-loading.css">
    <link rel="stylesheet" href="/assets/css/messages-page/chat-list-page.css">
    <link rel="stylesheet" href="/assets/css/messages-page/messages-page.css">
    <link rel="stylesheet" href="/assets/css/messages-page/chat-header.css">
    <link rel="stylesheet" href="/assets/css/messages-page/chat-interface.css">
    <link rel="stylesheet" href="/assets/css/video-call/initiate-call.css">
    <link rel="stylesheet" href="/assets/css/video-call/receive-call.css">
    <link rel="stylesheet" href="/assets/css/video-call/video-call.css">
    <link rel="stylesheet" href="/assets/css/messages-page/user-control.css">
    <link rel="stylesheet" href="/assets/css/messages-page/user-block-confimation.css">
    <link rel="stylesheet" href="/assets/css/discover-page/verify-face.css">
    <link rel="stylesheet" href="/assets/css/discover-page/verify-next.css">
    <link rel="stylesheet" href="/assets/css/discover-page/popup-modal.css">
    <link rel="stylesheet" href="/assets/css/messages-page/user-report.css">



    <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js"></script>
    <script src="https://cdn.jsdelivr.net/npm/stream-chat@8/dist/browser.full-bundle.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <script defer src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"></script>


    <script src="/assets/js/stream-video-client.js"></script>
</head>

<body>
    <div id="page-content">
        <div class="setup-page">
            <div class="setup-container">
                <div class="setup-text">
                    <h1>Set your preferences</h1>
                </div>

                <form action="/u/submit-finish-setup" method="POST" id="preferences-form">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <!-- Age Preference -->
                    <div class="age-preference-row">
                        <div class="setup-field">
                            <label for="age_min">Minimum Age</label>
                            <input
                                type="number"
                                id="age_min"
                                name="age_min"
                                min="18"
                                max="99"
                                required
                                value="<?= htmlspecialchars($ageMin ?? 18) ?>">
                        </div>

                        <div class="setup-field">
                            <label for="age_max">Maximum Age</label>
                            <input
                                type="number"
                                id="age_max"
                                name="age_max"
                                min="18"
                                max="99"
                                required
                                value="<?= htmlspecialchars($ageMax ?? 35) ?>">
                        </div>
                    </div>

                    <div class="setup-two-distance-gender-row">

                        <div class="setup-field">
                            <label for="distance">Preferred Distance (km)</label>
                            <input
                                type="number"
                                id="distance"
                                name="distance"
                                min="1"
                                max="500"
                                required
                                value="<?= htmlspecialchars($distance ?? 50) ?>">
                        </div>

                        <!-- Gender Preference -->
                        <div class="setup-field">
                            <label for="gender_preference">Preferred Gender</label>
                            <select name="gender_preference" id="gender_preference" required>
                                <option value="">-- Select --</option>
                                <option value="male" <?= ($genderPreference ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($genderPreference ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= ($genderPreference ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <!-- Error message -->
                    <?php if (!empty($error)): ?>
                        <div class="setup-error" id="form-error">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>



                    <!-- Submit button -->
                    <div class="setup-two-buttons-container">
                        <a href="/u/setup-profile" class="setup-back-btn">Go Back</a>
                        <button class="setup-btn" type="submit" id="finish-preferences-button">
                            Finish Setup
                        </button>
                    </div>

                    <!-- Step indicator -->
                    <div style="    margin-top: 1.5rem;
    text-align: center;
    font-size: 0.9rem;
    font-weight: 500;
    color: #6b7280;">
                        <p>Step 2 of 2</p>
                    </div>

                </form>
            </div>
        </div>
    </div>

    <div id="pt-loading" class="profile-loading-container">
        <div class="profile-loading-section">
            <div class="profile-loading-icon"></div>
            <p class="profile-loading-text">Saving...</p>
        </div>
    </div>

    <script type="module" src="/assets/js/loading-state/setup-profile-loading.js"></script>

</body>

</html>