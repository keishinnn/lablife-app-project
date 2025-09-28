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
    <link rel="stylesheet" href="/assets/css/profile-page/setup-user-pref.css">
</head>

<body>

    <header>
    </header>

    <div class="setup-page">
        <div class="setup-container">
            <div class="setup-text">
                <h1>Let's set you up first . . .</h1>
            </div>

            <form action="/u/submit-setup" method="POST" id="setup-form" enctype="multipart/form-data">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                <div class="setup-row">
                    <div class="setup-field">
                        <label for="full-name">Full Name</label>
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
                        <label for="user_avatar">Profile Picture</label>
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
                    <label>Bio (optional)</label>
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

                <button class="setup-btn" type="submit" <?= $isLoading ? 'disabled' : '' ?> id="setup-button">
                    <?= $isLoading ? 'Loading...' : 'Next' ?>
                </button>

                <div class="setup-step-two-container">
                    <p>Step 1 of 2</p>
                </div>

            </form>
        </div>
    </div>

    <?php require(base_path("views/shared/footer.php")) ?>