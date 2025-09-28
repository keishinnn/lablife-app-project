<?php
// root/views/.shared/header.php
$isLoading = false;
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
                <!-- Distance Preference -->


                <!-- Error message -->
                <?php if (!empty($error)): ?>
                    <div class="setup-error" id="form-error">
                        <?= htmlspecialchars($error) ?>
                    </div>
                <?php endif; ?>



                <!-- Submit button -->
                <div class="setup-two-buttons-container">
                    <a href="/u/setup-profile" class="setup-back-btn">Go Back</a>
                    <button class="setup-btn" type="submit" <?= $isLoading ? 'disabled' : '' ?> id="preferences-button">
                        <?= $isLoading ? 'Saving...' : 'Finish Setup' ?>
                    </button>
                </div>

                <!-- Step indicator -->
                <div class="setup-step-two-container">
                    <p>Step 2 of 2</p>
                </div>

            </form>
        </div>
    </div>

    <?php require(base_path("views/shared/footer.php")) ?>