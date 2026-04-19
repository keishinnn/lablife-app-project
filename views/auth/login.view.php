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

            <div class="login-divider" aria-hidden="true">
                <span>or</span>
            </div>

            <button
                type="button"
                class="login-social-button"
                data-google-oauth-button
                data-supabase-url="<?= htmlspecialchars($_ENV['SUPABASE_URL'] ?? '', ENT_QUOTES, 'UTF-8') ?>"
                data-supabase-anon-key="<?= htmlspecialchars($_ENV['SUPABASE_ANON_KEY'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                <svg viewBox="0 0 24 24" aria-hidden="true">
                    <path fill="#EA4335" d="M12 10.2v3.9h5.4c-.22 1.26-.95 2.32-2.05 3.03l3.32 2.58c1.94-1.78 3.05-4.4 3.05-7.5 0-.71-.06-1.39-.18-2.05H12Z" />
                    <path fill="#34A853" d="M12 22c2.76 0 5.08-.91 6.77-2.47l-3.32-2.58c-.92.62-2.1.99-3.45.99-2.65 0-4.9-1.79-5.7-4.19l-3.43 2.65C4.55 19.76 8.01 22 12 22Z" />
                    <path fill="#4A90E2" d="M6.3 13.75A5.98 5.98 0 0 1 6 12c0-.61.1-1.2.3-1.75L2.87 7.6A9.97 9.97 0 0 0 2 12c0 1.61.38 3.13 1.07 4.4l3.23-2.65Z" />
                    <path fill="#FBBC05" d="M12 6.07c1.5 0 2.84.52 3.9 1.54l2.92-2.92C17.08 3.07 14.76 2 12 2 8.01 2 4.55 4.24 2.87 7.6l3.43 2.65C7.1 7.86 9.35 6.07 12 6.07Z" />
                </svg>
                <span>Continue with Google</span>
            </button>


            <div class="login-page-redirect">
                <a href="/register">Don't have an account? Sign Up</a>
            </div>
        </form>
    </div>

</div>

<?php require(base_path("views/shared/footer.php")) ?>
