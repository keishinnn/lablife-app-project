<?php
// root/views/shared/header.php
use Core\Auth;

$isAuthenticatedArea = path_starts_with('/u');
$isHomePage = path_is('/') || path_is('/u');
$isAuthPage = path_is('/login') || path_is('/register');
$isProfilePage = path_is('/u/profile');
$isProfileEditPage = path_is('/u/profile-edit');
$isProfilePreferencesPage = path_is('/u/profile-preferences-edit');
$isBlockedUsersPage = path_is('/u/profile/blocked-users');
$isProfileSetupPage = path_is('/u/setup-profile') || path_is('/u/setup-profile-preferences');
$isProfileLoadingPage = path_is('/u/profile-loading');
$isDiscoverPage = path_is('/u/discover') || path_is('/u/discover/matched-user');
$isVerifyPage = path_is('/u/verify') || path_is('/u/verify-next');
$isMatchesPage = path_is('/u/matches');
$isMessagesPage = path_is('/u/messages');
$needsSupabase = $isDiscoverPage || $isMatchesPage;
$needsStreamChat = $isMessagesPage || $isBlockedUsersPage;
$needsStreamVideo = $isMessagesPage || path_is('/test-video');
$needsFaceApi = $isVerifyPage;
$needsFontAwesome = $isMessagesPage || path_is('/test-video');
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
    <title>Lablife</title>
    <link rel="icon" type="image/png" href="/assets/images/logo.png">
    <link rel="stylesheet" href="/assets/css/global.css">
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/css/footer.css">
    <link rel="stylesheet" href="/assets/css/profile-page/profile-loading.css">
    <?php if ($isAuthPage): ?>
        <link rel="stylesheet" href="/assets/css/auth-page.css">
    <?php endif; ?>
    <?php if ($isHomePage): ?>
        <link rel="stylesheet" href="/assets/css/index-page.css">
    <?php endif; ?>
    <?php if ($isProfilePage || $isProfileEditPage || $isProfilePreferencesPage || $isProfileSetupPage || $isProfileLoadingPage || path_is('/u/profile-not-found') || path_is('/u/profile/blocked-users')): ?>
        <link rel="stylesheet" href="/assets/css/profile-page/profile-page.css">
        <link rel="stylesheet" href="/assets/css/profile-page/profile-null.css">
        <link rel="stylesheet" href="/assets/css/profile-page/profile-loading.css">
        <link rel="stylesheet" href="/assets/css/profile-page/profile-get-loading.css">
    <?php endif; ?>
    <?php if ($isProfilePage): ?>
        <link rel="stylesheet" href="/assets/css/profile-page/modals-style/hobbies-modal.css">
        <link rel="stylesheet" href="/assets/css/profile-page/modals-style/ptypes-modal.css">
        <link rel="stylesheet" href="/assets/css/profile-page/modals-style/interests-modal.css">
    <?php endif; ?>
    <?php if ($isProfileEditPage): ?>
        <link rel="stylesheet" href="/assets/css/profile-page/edit/profile-edit-page.css">
    <?php endif; ?>
    <?php if ($isProfilePreferencesPage): ?>
        <link rel="stylesheet" href="/assets/css/profile-page/edit/profile-edit-page.css">
        <link rel="stylesheet" href="/assets/css/profile-page/edit/preference-edit-page.css">
    <?php endif; ?>
    <?php if ($isBlockedUsersPage): ?>
        <link rel="stylesheet" href="/assets/css/matches-page/matches-page.css">
        <link rel="stylesheet" href="/assets/css/messages-page/user-block-confimation.css">
    <?php endif; ?>
    <?php if ($isProfileSetupPage): ?>
        <link rel="stylesheet" href="/assets/css/profile-page/setup-user.css">
        <link rel="stylesheet" href="/assets/css/profile-page/setup-user-pref.css">
    <?php endif; ?>
    <?php if ($isDiscoverPage): ?>
        <link rel="stylesheet" href="/assets/css/discover-page/discover-page.css?v=<?php echo time(); ?>">
        <link rel="stylesheet" href="/assets/css/discover-page/discover-loading.css">
        <link rel="stylesheet" href="/assets/css/discover-page/notifications/notification.css">
        <link rel="stylesheet" href="/assets/css/discover-page/notifications/notification-rejected.css">
        <link rel="stylesheet" href="/assets/css/discover-page/notifications/notif-recon-loading.css">
        <link rel="stylesheet" href="/assets/css/discover-page/notifications/notification-expired.css">
        <link rel="stylesheet" href="/assets/css/profile-page/profile-get-loading.css">
        <link rel="stylesheet" href="/assets/css/profile-page/profile-loading.css">
        <link rel="stylesheet" href="/assets/css/matches-page/loading/matches-loading.css">
    <?php endif; ?>
    <?php if ($isVerifyPage): ?>
        <link rel="stylesheet" href="/assets/css/discover-page/verify-face.css">
        <link rel="stylesheet" href="/assets/css/discover-page/verify-next.css">
        <link rel="stylesheet" href="/assets/css/discover-page/popup-modal.css">
    <?php endif; ?>
    <?php if ($isMatchesPage): ?>
        <link rel="stylesheet" href="/assets/css/matches-page/matches-page.css">
        <link rel="stylesheet" href="/assets/css/matches-page/loading/matches-loading.css">
    <?php endif; ?>
    <?php if ($isMessagesPage): ?>
        <link rel="stylesheet" href="/assets/css/messages-page/chat-list-page.css">
        <link rel="stylesheet" href="/assets/css/messages-page/messages-page.css">
        <link rel="stylesheet" href="/assets/css/messages-page/chat-header.css">
        <link rel="stylesheet" href="/assets/css/messages-page/chat-interface.css">
        <link rel="stylesheet" href="/assets/css/messages-page/user-control.css">
        <link rel="stylesheet" href="/assets/css/messages-page/user-block-confimation.css">
        <link rel="stylesheet" href="/assets/css/messages-page/user-report.css">
        <link rel="stylesheet" href="/assets/css/video-call/initiate-call.css">
        <link rel="stylesheet" href="/assets/css/video-call/receive-call.css">
        <link rel="stylesheet" href="/assets/css/video-call/video-call.css">
    <?php endif; ?>

    <?php if ($needsSupabase): ?>
        <script src="https://cdn.jsdelivr.net/npm/@supabase/supabase-js"></script>
    <?php endif; ?>
    <?php if ($needsStreamChat): ?>
        <script src="https://cdn.jsdelivr.net/npm/stream-chat@8/dist/browser.full-bundle.min.js"></script>
    <?php endif; ?>
    <?php if ($needsFontAwesome): ?>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <?php endif; ?>
    <?php if ($needsFaceApi): ?>
        <script defer src="https://unpkg.com/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <?php endif; ?>
    <?php if ($needsStreamVideo): ?>
        <script src="/assets/js/stream-video-client.js"></script>
    <?php endif; ?>
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="navbar-inner">
                    <!-- Logo and LabLife text-->
                    <a href="<?php echo !Auth::check() ? '/' : '/u'; ?>" class="logo" id="nav-logo-btn">
                        <img src="/assets/images/logo.png" alt="LabLife Logo" class="logo-img">
                        <span class="logo-text">LabLife</span>
                    </a>

                    <!-- Show the a tags if user is authenticated -->
                    <?php if (Auth::check()) : ?>
                        <div class="nav-links">
                            <a href="/u/discover" class="nav-link">Discover</a>
                            <a href="/u/matches" class="nav-link">Matches</a>
                            <a href="/u/messages" class="nav-link">
                                Messages
                                <span class="badge"></span>
                            </a>
                            <a href="/u/profile" class="nav-link">Profile</a>
                        </div>
                    <?php endif; ?>

                    <!-- Show the Sign Out button if a user is authenticated, otherwise show Sign In button -->
                    <?php if (Auth::check()): ?>
                        <form action="/logout" method="post">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8') ?>">
                            <button class="btn btn-signout" type="submit">
                                <!--                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 
                            0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 
                            0 013 3v1" />
                                </svg> -->
                                Sign Out
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="/login" class="btn btn-signin">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <div id="page-content">
