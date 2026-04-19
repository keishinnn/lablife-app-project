<?php

require(base_path("views/shared/header.php"));
// /views/auth/login.view.php
?>

<div class="login-page">
    <div class="login-page-container">
        <div class="login-page-text">
            <h1>Sign in to your account</h1>
        </div>

        <form action="/login" method="POST" id="login-form">
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
            <div class="login-email-field">
                <label for="email">Email</label>
                <input
                    type="email"
                    name="email"
                    required
                    placeholder="Enter your email"
                    minlength="8"
                    value="<?= htmlspecialchars($email ?? '') ?>">
            </div>

            <div class="login-password-field">
                <label for="password">Password</label>
                <input
                    type="password" name="password" id="password"
                    required
                    placeholder="Enter your password">
            </div>

            <?php if (!empty($requireCaptcha) && !empty($siteKey)): ?>
                <div class="login-turnstile-wrap">
                    <p class="login-turnstile-text">Please complete the security check to continue.</p>
                    <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars($siteKey, ENT_QUOTES, 'UTF-8') ?>"></div>
                </div>
            <?php endif; ?>

            <div class="login-error" id="form-error" style="<?= !empty($error) ? '' : 'display:none;' ?>">
                <?= htmlspecialchars($error ?? '') ?>
            </div>

            <button id="login-btn" type="submit" <?= $isLoading ? 'disabled' : '' ?>>
                <?= $isLoading ? 'Loading...' : 'Sign In' ?>
            </button>


            <div class="login-page-redirect">
                <a href="/register">Don't have an account? Sign Up</a>
            </div>
        </form>
    </div>

</div>

<?php require(base_path("views/shared/footer.php")) ?>
