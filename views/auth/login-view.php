<?php

if (!defined('ROOT')) {
    die('Direct access not allowed.');
}

include(ROOT . "/views/shared/header.php")

?>

<div class="login-page">
    <div class="login-page-container">
        <div class="login-page-text">
            <h1>Sign in to your account</h1>
        </div>


        <form action="" method="post">
            <div class="login-email-field">
                <label for="email">Email</label>
                <input
                    type="email"
                    name="email"
                    required
                    placeholder="Enter your email">
            </div>

            <div class="login-password-field">
                <label for="password">Password</label>
                <input
                    type="password" name="password" id="password"
                    required
                    placeholder="Enter your password">
            </div>

            <?php if ($error): ?>
                <div class="login-error">
                    <?php echo $error ?>
                </div>
            <?php endif; ?>

            <?php if ($loading): ?>
                <button disabled>
                    Loading...
                </button>
            <?php else: ?>
                <button>
                    Sign In
                </button>
            <?php endif; ?>

            <div class="login-page-redirect">
                <a href="/lablife-app-project/register">Don't have an account? Sign Up</a>
            </div>
        </form>
    </div>

</div>

<?php include(ROOT . "/views/shared/footer.php") ?>