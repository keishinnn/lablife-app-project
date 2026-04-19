<?php
$isRegisterPage = path_is('/register');
$isLoginPage = path_is('/login');
$isProfilePage = path_is('/u/profile');
$isProfileEditPage = path_is('/u/profile-edit');
$isDiscoverPage = path_is('/u/discover');
$isProfilePreferencesPage = path_is('/u/profile-preferences-edit');
$isBlockedUsersPage = path_is('/u/profile/blocked-users');
$isProfileSetupPage = path_is('/u/setup-profile') || path_is('/u/setup-profile-preferences');
$isMatchesPage = path_is('/u/matches');
$isMessagesPage = path_is('/u/messages');
$isAuthenticatedArea = path_starts_with('/u');
?>

</div>
<?php if ($isAuthenticatedArea): ?>
  <?php require base_path('views/user/loading/global.loading.view.php') ?>
  <?php require base_path('views/user/profile/loading/profile.loading.view.php') ?>
  <?php require base_path('views/user/matches/loading/matches.loading.view.php') ?>
  <?php require base_path('views/user/loading/messages.loading.view.php') ?>
<?php endif; ?>

<footer class="site-footer">
  <p>&copy; <?= date('Y') ?> LabLife. All rights reserved.</p>

  <button id="bugReportBtn" class="report-bug-btn">Report a Bug</button>

  <script>
    // Redirect to Bug Report page
    const bugBtn = document.getElementById('bugReportBtn');
    if (bugBtn) {
      bugBtn.addEventListener('click', () => {
        window.location.href = '/bug-report';
      });
    }
  </script>
</footer>

<?php if ($isRegisterPage): ?>
  <script type="module" src="/assets/js/register-validation.js"></script>
<?php endif; ?>
<?php if ($isLoginPage): ?>
  <script type="module" src="/assets/js/login-loading.js"></script>
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

<?php if ($isRegisterPage): ?>
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
