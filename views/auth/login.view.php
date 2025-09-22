<?php

include(base_path("views/shared/header.php"));
// /views/auth/login.view.php
?>

<div class="login-page">
    <div class="login-page-container">
        <div class="login-page-text">
            <h1>Sign in to your account</h1>
        </div>

        <form action="/login" method="POST">
            <div class="login-email-field">
                <label for="email">Email</label>
                <input
                    type="email"
                    name="email"
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

            <?php if (!empty($error)): ?>
                <div class=" login-error"><?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <button type="submit">
                Sign In
            </button>

            <div class="login-page-redirect">
                <a href="/register">Don't have an account? Sign Up</a>
            </div>
        </form>
    </div>

</div>

<?php include(base_path("views/shared/footer.php")) ?>