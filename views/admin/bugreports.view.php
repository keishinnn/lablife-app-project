<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Bug Reports | LabLife</title>
  <link rel="stylesheet" href="/assets/css/global.css">
  <link rel="stylesheet" href="/assets/css/admin/admin-dashboard.css?v=<?= filemtime(base_path('public/assets/css/admin/admin-dashboard.css')) ?>">
</head>
<body>
<div class="admin-app">
  <aside class="admin-sidebar">
    <div class="brand">LabLife</div>
    <nav>
      <a href="/admin/dashboard" class="nav-link">Dashboard</a>
      <a href="/admin/user-reports" class="nav-link">User Reports</a>
      <a href="/admin/bug-reports" class="nav-link active">Bug Reports</a>
    </nav>
    <div class="sidebar-footer">
      <form method="post" action="/admin/login"><button class="signout">Sign Out</button></form>
    </div>
  </aside>

  <main class="admin-content">
    <header class="admin-top">
      <h1>Bug Reports</h1>
      <div class="admin-user">Signed in as <?= htmlspecialchars($_SESSION['admin_email']) ?></div>
    </header>

    <div id="bug-report-list" class="bug-report-list"></div>
    <div id="loader" class="loader">Loading...</div>
  </main>
</div>

<div id="report-modal" class="report-modal" aria-hidden="true">
  <div class="modal-backdrop" onclick="closeBugModal()"></div>
  <div class="modal-panel">
    <button class="modal-close" onclick="closeBugModal()">×</button>
    <div id="report-modal-content">Loading...</div>
  </div>
</div>

<script src="/assets/js/admin/bug-report.js?v=2" defer></script>

<?php require base_path('views/admin/partials/notification-popup.view.php'); ?>
<script src="/assets/js/admin/admin-notifications.js?v=1" defer></script>

</body>
</html>
