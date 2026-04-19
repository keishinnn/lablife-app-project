<?php

require(base_path("views/shared/header.php"));
?>

<div
    class="login-page"
    id="google-auth-callback"
    data-supabase-url="<?= htmlspecialchars($supabaseUrl ?? '', ENT_QUOTES, 'UTF-8') ?>"
    data-supabase-anon-key="<?= htmlspecialchars($supabaseAnonKey ?? '', ENT_QUOTES, 'UTF-8') ?>">
    <div class="login-page-container">
        <div class="login-page-text">
            <h1>Signing you in</h1>
            <p class="login-oauth-helper">Please wait while we finish your Google sign-in.</p>
        </div>

        <div class="login-oauth-status-card">
            <div class="profile-loading-icon"></div>
            <p id="google-auth-status-text">Connecting your LabLife account...</p>
        </div>

        <div class="login-error" id="google-auth-error" style="display:none;"></div>

        <div class="login-page-redirect">
            <a href="/login">Back to Sign In</a>
        </div>
    </div>
</div>

<?php require(base_path("views/shared/footer.php")) ?>
