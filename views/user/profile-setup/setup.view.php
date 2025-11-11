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
    <link rel="stylesheet" href="/assets/css/matches-page/matches-page.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/css/matches-page/loading/matches-loading.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/css/footer.css?v=<?php echo time(); ?>">


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
    <link rel="stylesheet" href="/assets/css/discover-page/verify-face.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/css/discover-page/verify-next.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/css/discover-page/popup-modal.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="/assets/css/messages-page/user-report.css?v=<?php echo time(); ?>">



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
                    <h1>Let's set you up first . . .</h1>
                </div>

                <form action="/u/submit-setup" method="POST" id="setup-form" enctype="multipart/form-data">
                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    <div class="setup-row">
                        <div class="setup-field">
                            <label for="full-name">Full Name *</label>
                            <input
                                type="text"
                                name="full-name"
                                required
                                placeholder="Enter your full name"
                                minlength="8"
                                value="<?= htmlspecialchars($_SESSION['full-name'] ?? '') ?>">
                        </div>

                        <div class="setup-field">
                            <label>Gender *</label>
                            <select name="gender" required>
                                <option value="">-- Select --</option>
                                <option value="male" <?= ($_SESSION['gender'] ?? '') === 'male' ? 'selected' : '' ?>>Male</option>
                                <option value="female" <?= ($_SESSION['gender'] ?? '') === 'female' ? 'selected' : '' ?>>Female</option>
                                <option value="other" <?= ($_SESSION['gender'] ?? '') === 'other' ? 'selected' : '' ?>>Other</option>
                            </select>
                        </div>
                    </div>

                    <div class="setup-row">
                        <div class="setup-field">
                            <label>Birthdate *</label>
                            <input
                                type="date"
                                name="birthdate"
                                required
                                max="<?= date('Y-m-d', strtotime('-18 years')) ?>"
                                value="<?= htmlspecialchars($_SESSION['birthdate'] ?? '') ?>">
                        </div>

                        <div class="setup-field">
                            <label for="user_avatar">Profile Picture *</label>
                            <input class="setup-upload" id="user_avatar" type="file" accept="image/*" name="avatar_input">

                            <?php if (!empty($_SESSION['avatar_temp'])): ?>
                                <p class="setup-help" id="user_avatar_help">Last uploaded: <?= htmlspecialchars($_SESSION['avatar_temp']) ?></p>
                                <img src="/uploads/tmp/<?= htmlspecialchars($_SESSION['avatar_temp']) ?>"
                                    alt="Preview" style="max-width:100px;">
                            <?php endif; ?>

                            <div class="setup-help" id="user_avatar_help">
                                A profile picture is useful to engage
                            </div>
                        </div>
                    </div>

                    <div class="setup-field">
                        <label>Bio (optional) *</label>
                        <textarea
                            name="bio"
                            maxlength="200"
                            placeholder="Write a short bio..."><?= htmlspecialchars($_SESSION['bio'] ?? '') ?></textarea>
                    </div>

                    <?php if (!empty($error)): ?>
                        <div class="setup-error" id="form-error">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <button class="setup-btn" type="submit" id="setup-button">
                        Next
                    </button>

                    <div style="    margin-top: 1.5rem;
    text-align: center;
    font-size: 0.9rem;
    font-weight: 500;
    color: #6b7280;">
                        <p>Step 1 of 2</p>
                    </div>

                </form>
            </div>
        </div>
    </div>

</body>

</html>