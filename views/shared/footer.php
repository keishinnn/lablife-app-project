<?php
use Core\Auth;

$isRegisterPage = path_is('/register');
$isLoginPage = path_is('/login');
$isGoogleAuthCallbackPage = path_is('/auth/google/callback');
$isProfilePage = path_is('/u/profile');
$isProfileEditPage = path_is('/u/profile-edit');
$isDiscoverPage = path_is('/u/discover');
$isProfilePreferencesPage = path_is('/u/profile-preferences-edit');
$isBlockedUsersPage = path_is('/u/profile/blocked-users');
$isProfileSetupPage = path_is('/u/setup-profile') || path_is('/u/setup-profile-preferences');
$isMatchesPage = path_is('/u/matches');
$isMessagesPage = path_is('/u/messages');
$isAuthenticatedArea = path_starts_with('/u');

$quickLinks = Auth::check()
  ? [
    ['label' => 'Home', 'href' => '/u'],
    ['label' => 'Discover', 'href' => '/u/discover'],
    ['label' => 'Matches', 'href' => '/u/matches'],
    ['label' => 'Messages', 'href' => '/u/messages'],
    ['label' => 'Profile', 'href' => '/u/profile'],
  ]
  : [
    ['label' => 'Home', 'href' => '/'],
    ['label' => 'Login', 'href' => '/login'],
    ['label' => 'Register', 'href' => '/register'],
    ['label' => 'Privacy Policy', 'href' => '/privacy-policy'],
  ];

$supportLinks = [
  ['label' => 'Privacy Policy', 'href' => '/privacy-policy'],
  ['label' => 'Terms of Service', 'href' => '/terms'],
  ['label' => 'Report a Bug', 'href' => '/bug-report'],
];

if (Auth::check()) {
  $supportLinks[] = ['label' => 'Blocked Users', 'href' => '/u/profile/blocked-users'];
}
?>

</div>
<?php if ($isAuthenticatedArea): ?>
  <?php require base_path('views/user/loading/global.loading.view.php') ?>
  <?php require base_path('views/user/profile/loading/profile.loading.view.php') ?>
  <?php require base_path('views/user/matches/loading/matches.loading.view.php') ?>
  <?php require base_path('views/user/loading/messages.loading.view.php') ?>
<?php endif; ?>

<footer class="site-footer">
  <div class="site-footer-top">
    <section class="site-footer-brand">
      <a href="<?= Auth::check() ? '/u' : '/' ?>" class="site-footer-logo-link">
        <img src="/assets/images/logo.png" alt="LabLife Logo" class="site-footer-logo">
        <div class="site-footer-brand-copy">
          <p class="site-footer-brand-name">LabLife</p>
          <p class="site-footer-brand-tag">MEANINGFUL MATCHES</p>
        </div>
      </a>

      <p class="site-footer-brand-text">
        Verified matching and messaging designed to help people connect with more intention, safety, and clarity.
      </p>
    </section>

    <section class="site-footer-column">
      <p class="site-footer-heading">Quick Links</p>
      <ul class="site-footer-links">
        <?php foreach ($quickLinks as $link): ?>
          <li>
            <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>

    <section class="site-footer-column">
      <p class="site-footer-heading">Support</p>
      <ul class="site-footer-links">
        <?php foreach ($supportLinks as $link): ?>
          <li>
            <a href="<?= htmlspecialchars($link['href'], ENT_QUOTES, 'UTF-8') ?>">
              <?= htmlspecialchars($link['label'], ENT_QUOTES, 'UTF-8') ?>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </section>

    <section class="site-footer-column">
      <p class="site-footer-heading">Safety & Feedback</p>
      <div class="site-footer-actions">
        <a href="<?= Auth::check() ? '/u/discover' : '/login' ?>" class="site-footer-icon-link" aria-label="Open Discover">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 21s-6.716-4.266-8.77-8.056C1.302 9.39 3.25 5 7.51 5c1.932 0 3.313 1.005 4.49 2.49C13.177 6.005 14.558 5 16.49 5c4.26 0 6.208 4.39 4.28 7.944C18.716 16.734 12 21 12 21Z" />
          </svg>
        </a>
        <a href="/bug-report" class="site-footer-icon-link" aria-label="Report a bug">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M9 3h6l1 2h2a1 1 0 1 1 0 2h-1v2.082A5.002 5.002 0 0 1 19 13v2h1a1 1 0 1 1 0 2h-1v2a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2v-2H4a1 1 0 1 1 0-2h1v-2a5.002 5.002 0 0 1 2-3.918V7H6a1 1 0 1 1 0-2h2l1-2Zm0 6a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Zm6 0a1.5 1.5 0 1 0 0 3 1.5 1.5 0 0 0 0-3Z" />
          </svg>
        </a>
        <a href="/privacy-policy" class="site-footer-icon-link" aria-label="View privacy policy">
          <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M12 3 5 6v5c0 5.04 2.948 9.772 7 11 4.052-1.228 7-5.96 7-11V6l-7-3Zm0 5a3 3 0 0 1 3 3c0 1.12-.624 2.095-1.545 2.598V16h-2.91v-2.402A2.996 2.996 0 0 1 9 11a3 3 0 0 1 3-3Z" />
          </svg>
        </a>
      </div>

      <div class="site-footer-meta">
        <p><?= Auth::check() ? 'Signed in and ready to match.' : 'Create an account to start matching.' ?></p>
        <p>Need help? Use the bug report form and we’ll review it.</p>
      </div>
    </section>
  </div>

  <div class="site-footer-bottom">
    &copy; <?= date('Y') ?> LabLife. All rights reserved.
  </div>
</footer>

<?php if ($isRegisterPage): ?>
  <script type="module" src="/assets/js/register-validation.js"></script>
<?php endif; ?>
<?php if ($isLoginPage): ?>
  <script type="module" src="/assets/js/login-loading.js"></script>
<?php endif; ?>
<?php if ($isLoginPage || $isRegisterPage || $isGoogleAuthCallbackPage): ?>
  <script type="module" src="/assets/js/auth/google-oauth.js"></script>
<?php endif; ?>
<?php if ($isProfilePage): ?>
  <script type="module" src="/assets/js/modals/ptype-modal.js"></script>
  <script type="module" src="/assets/js/modals/hobbies-modal.js"></script>
  <script type="module" src="/assets/js/modals/interests-modal.js"></script>
<?php endif; ?>
<?php if ($isProfileEditPage): ?>
  <script type="module" src="/assets/js/loading-state/edit-profile-loading.js"></script>
  <script type="module" src="/assets/js/upload-photo/profile-edit-upload-photo.js"></script>
<?php endif; ?>
<?php if ($isProfilePreferencesPage): ?>
  <script type="module" src="/assets/js/loading-state/edit-preferences-loading.js"></script>
<?php endif; ?>
<?php if ($isProfileSetupPage): ?>
  <script type="module" src="/assets/js/loading-state/setup-profile-loading.js"></script>
<?php endif; ?>
<?php if ($isAuthenticatedArea): ?>
  <script type="module" src="/assets/js/user/set-online-status.js"></script>
  <script type="module" src="/assets/js/loading-state/global-loading.js"></script>
<?php endif; ?>
<?php if ($isMessagesPage): ?>
  <script src="/assets/js/messages/user-report.js"></script>
<?php endif; ?>

<?php if ($isRegisterPage || $isLoginPage || $isGoogleAuthCallbackPage): ?>
  <!-- Cloudflare Turnstile JS -->
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
<?php endif; ?>

<!-- Loader inside modal -->
<?php if ($isProfilePage || $isProfileEditPage || $isProfilePreferencesPage || $isBlockedUsersPage): ?>
  <div id="pt-loading" class="profile-loading-container">
    <div class="profile-loading-section">
      <div class="profile-loading-icon"></div>
      <p class="profile-loading-text">Saving...</p>
    </div>
  </div>
<?php endif; ?>

<?php if ($isProfileSetupPage): ?>
  <div id="setup-preferences-loading" class="profile-loading-container">
    <div class="profile-loading-section">
      <div class="profile-loading-icon"></div>
      <p class="profile-loading-text">Saving...</p>
    </div>
  </div>
<?php endif; ?>

<?php if ($isDiscoverPage): ?>
  <!-- Finding Match Loading -->
  <div id="search-loading" class="page-loading-container">
    <div class="page-loading-section">
      <div class="profile-loading-icon"></div>
      <p class="page-loading-text">Finding your match...</p>
    </div>
  </div>
<?php endif; ?>

</body>

</html>
