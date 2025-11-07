<?php require base_path('Views/admin/shared/header.php') ?>

<?php require base_path('Views/admin/shared/sidebar.php') ?>

<main class="admin-content">
  <header class="admin-top">
    <h1>User Reports</h1>
    <div class="admin-user">Signed in as <?= htmlspecialchars($_SESSION['admin_email']) ?></div>
  </header>

  <div id="user-report-list" class="bug-report-list"></div>
  <div id="loader" class="loader">Loading...</div>
</main>
</div>

<div id="report-modal" class="report-modal" aria-hidden="true">
  <div class="modal-backdrop" onclick="closeUserModal()"></div>
  <div class="modal-panel">
    <button class="modal-close" onclick="closeUserModal()">×</button>
    <div id="report-modal-content">Loading...</div>
  </div>
</div>

<?php require base_path('Views/admin/shared/footer.php') ?>