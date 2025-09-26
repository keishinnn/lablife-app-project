<?php
// root/views/.shared/header.php
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/global.css">
    <link rel="stylesheet" href="/assets/css/auth-page.css">
    <link rel="stylesheet" href="/assets/css/index-page.css">
    <link rel="stylesheet" href="/assets/css/profile-page/setup-user.css">
    <link rel="stylesheet" href="/assets/css/profile-page/profile-null.css">
    <link rel="stylesheet" href="/assets/css/profile-page/profile-loading.css">
    <link rel="stylesheet" href="/assets/css/profile-page/profile-page.css">
</head>

<body>

    <header>
    </header>

    <div class="setup-page">
        <div class="setup-container">
            <div class="setup-text">
                <h1>Let's set you up first</h1>
            </div>

            <form action="/u/submit-setup" method="POST" id="setup-form" enctype="multipart/form-data">

                <div class="setup-row">
                    <div class="setup-field">
                        <label for="full-name">Full Name</label>
                        <input
                            type="text"
                            name="full-name"
                            required
                            placeholder="Enter your full name"
                            minlength="8"
                            value="<?= htmlspecialchars($fullName ?? '') ?>">
                    </div>

                    <div class="setup-field">
                        <label>Gender *</label>
                        <select name="gender" required>
                            <option value="">-- Select --</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                <div class="setup-row">
                    <div class="setup-field">
                        <label>Birthdate *</label>
                        <input type="date" name="birthdate" required max="<?= date('Y-m-d', strtotime('-18 years')) ?>">
                    </div>

                    <div class="setup-field">
                        <label for="user_avatar">Upload file</label>
                        <input class="setup-upload" aria-describedby="user_avatar_help" id="user_avatar" type="file" accept="image/*" name="avatar_input">
                        <div class="setup-help" id="user_avatar_help">
                            A profile picture is useful to engage
                        </div>
                    </div>
                </div>

                <div class="setup-field">
                    <label>Bio (optional)</label>
                    <textarea name="bio" maxlength="200" placeholder="Write a short bio..."></textarea>
                </div>

                <?php if (!empty($error)): ?>
                    <div class="setup-error" id="form-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>

                <button class="setup-btn" type="submit" <?= $isLoading ? 'disabled' : '' ?> id="setup-button">
                    <?= $isLoading ? 'Loading...' : 'Next' ?>
                </button>

            </form>
        </div>
    </div>

    <?php require(base_path("views/shared/footer.php")) ?>