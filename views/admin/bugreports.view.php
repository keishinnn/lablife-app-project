<?php require base_path('Views/admin/shared/header.php') ?>

<?php require base_path('Views/admin/shared/sidebar.php') ?>

<main class="admin-content">
  <header class="admin-top">
    <h1>Bug Reports</h1>
    <div class="admin-user">Signed in as <?= htmlspecialchars($_SESSION['admin_email']) ?></div>
  </header>

  <div id="bug-report-list" class="bug-report-list"></div>
  <div id="loader" class="loader">Loading...</div>
</main>

<?php require base_path('Views/admin/shared/footer.php') ?>