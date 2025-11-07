</div>
<?php require(base_path('Views/user/matches/loading/matches.loading.view.php')) ?>
<?php require(base_path('Views/user/profile/loading/profile.loading.view.php')) ?>
<?php require(base_path('Views/user/loading/global.loading.view.php')) ?>
<?php require(base_path('Views/user/loading/messages.loading.view.php')) ?>

<footer class="site-footer">
  <p>&copy; <?= date('Y') ?> LabLife. All rights reserved.</p>

  <button id="bugReportBtn" class="report-bug-btn">Report a Bug</button>
  <button id="userReportBtn" class="report-bug-btn">Report a User</button>

  <script>
    // Redirect to Bug Report page
    const bugBtn = document.getElementById('bugReportBtn');
    if (bugBtn) {
      bugBtn.addEventListener('click', () => {
        window.location.href = '/bug-report';
      });
    }

    // Redirect to User Report form
    const userBtn = document.getElementById('userReportBtn');
    if (userBtn) {
      userBtn.addEventListener('click', () => {
        // Temporary generic access link for now (replace UUID later)
        window.location.href = '/u/report-user?user_id=replace-this-with-valid-uuid';
      });
    }
  </script>
</footer>

<script type="module" src="/assets/js/register-validation.js"></script>
<script type="module" src="/assets/js/login-loading.js"></script>
<script type="module" src="/assets/js/modals/ptype-modal.js"></script>
<script type="module" src="/assets/js/loading-state/edit-profile-loading.js"></script>
<script type="module" src="/assets/js/modals/hobbies-modal.js"></script>
<script type="module" src="/assets/js/modals/interests-modal.js"></script>
<script type="module" src="/assets/js/loading-state/setup-profile-loading.js"></script>
<script type="module" src="/assets/js/upload-photo/profile-edit-upload-photo.js"></script>
<script type="module" src="/assets/js/loading-state/edit-preferences-loading.js"></script>
<script type="module" src="/assets/js/user/set-online-status.js"></script>
<script type="module" src="/assets/js/loading-state/matches/matches-loading.js"></script>
<script type="module" src="/assets/js/loading-state/profile/profile-loading.js"></script>
<script type="module" src="/assets/js/loading-state/global-loading.js?v=<?php echo time(); ?>"></script>
<script type="module" src="/assets/js/loading-state/messages/messages-loading.js?v=<?php echo time(); ?>"></script>

<!-- Cloudflare Turnstile JS -->
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

<!-- Loader inside modal -->
<div id="pt-loading" class="profile-loading-container">
  <div class="profile-loading-section">
    <div class="profile-loading-icon"></div>
    <p class="profile-loading-text">Saving...</p>
  </div>
</div>

<!-- Finding Match Loading -->
<div id="search-loading" class="profile-loading-container">
  <div class="profile-loading-section">
    <div class="profile-loading-icon"></div>
    <p class="profile-loading-text">Finding your match...</p>
  </div>
</div>

</body>

</html>