<?php

// file path - root/views/auth/register.view.php

require(base_path("views/shared/header.php"));
?>

<div class="login-page">
    <div class="login-page-container">
        <div class="login-page-text">
            <h1>Create Your Account</h1>
        </div>

        <form action="/register" method="POST" id="register-form">
            <div class="login-email-field">
                <label for="email">Email</label>
                <input
                    type="email"
                    name="email"
                    id="email"
                    required
                    placeholder="Enter your email"
                    value="<?= htmlspecialchars($email ?? '') ?>">
            </div>

            <div class="login-password-field">
                <label for="password">Password</label>
                <input
                    type="password" name="password" id="password"
                    required
                    placeholder="Enter your password">
            </div>

            <!-- Cloudflare Turnstile -->
            <div class="cf-turnstile" data-sitekey="<?= htmlspecialchars($siteKey) ?>"></div>

            <div class="login-error" id="form-error" style="<?= !empty($error) ? '' : 'display:none;' ?>">
                <?= htmlspecialchars($error ?? '') ?>
            </div>
            <div class="policy-consent">
                <label class="checkbox-container">
                    <input type="checkbox" name="agree_privacy" required>
                    I have read and agree to the 
                    <a href="/privacy-policy">Privacy Policy</a>
                    and 
                    <a href="/terms">Terms of Service</a>.
                </label>
            </div>

            <button type="submit" id="sign-up-btn">
                Sign Up
            </button>

            <div class="login-page-redirect">
                <a href="/login">Already have an account? Sign in</a>
            </div>
        </form>
    </div>
</div>

<?php require(base_path("views/shared/footer.php")) ?>