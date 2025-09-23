<?php

// file path - root/views/auth/register.view.php

include(base_path("views/shared/header.php"));
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

            <?php if (!empty($message)): ?>
                <div class="login-error" id="form-error">
                    <?= htmlspecialchars($message) ?>
                </div>
            <?php endif; ?>

            <div class="login-error" id="form-error" style="<?= !empty($error) ? '' : 'display:none;' ?>">
                <?= htmlspecialchars($error ?? '') ?>
            </div>

            <button type="submit" id="sign-up-btn">
                Sign Up
            </button>

            <div class="login-page-redirect">
                <a href="/login">Already have an account? Sign in</a>
            </div>
        </form>

        <!-- Cloudflare Turnstile JS -->
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    </div>
</div>

<?php include(base_path("views/shared/footer.php")) ?>