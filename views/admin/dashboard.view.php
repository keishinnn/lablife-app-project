<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin Dashboard | LabLife</title>
  <link rel="stylesheet" href="/assets/css/global.css">
  <link rel="stylesheet" href="/assets/css/admin/admin-dashboard.css?v=<?= filemtime(base_path('public/assets/css/admin/admin-dashboard.css')) ?>">


</head>
<body>
<div class="admin-app">
  <aside class="admin-sidebar">
    <div class="brand">LabLife</div>
    <nav>
      <a href="/admin/dashboard" class="nav-link active">Dashboard</a>
      <a href="/admin/user-reports" class="nav-link">User Reports</a>
      <a href="/admin/bug-reports" class="nav-link">Bug Reports</a>
    </nav>
    <div class="sidebar-footer">
      <form method="post" action="/admin/login"><button class="signout">Sign Out</button></form>
    </div>
  </aside>

  <main class="admin-content">
    <header class="admin-top">
      <h1>Dashboard Overview</h1>
      <div class="admin-user">Signed in as <?= htmlspecialchars($_SESSION['admin_email']) ?></div>
    </header>

    <section class="cards">
      <div class="card pink"><h3>Total Reports</h3><p><?= (int)$data['total_reports'] ?></p></div>
      <div class="card red"><h3>User Reports</h3><p><?= (int)$data['user_reports'] ?></p></div>
      <div class="card orange"><h3>Bug Reports</h3><p><?= (int)$data['bug_reports'] ?></p></div>
      <div class="card green"><h3>Resolved</h3><p><?= (int)$data['finished_reports'] ?></p></div>
    </section>

    <section class="inboxes">
    <!-- USER REPORTS -->
    <div class="inbox">
      <h2>User Reports</h2>
      <div class="inbox-list">
        <?php foreach ($data['recent_user_reports'] as $r): ?>
          <div class="inbox-item">
            <div>
              <strong>Reason:</strong>
              <?= htmlspecialchars($r['reason'] ?? 'No reason provided') ?><br>
              <span>Reported ID: <?= htmlspecialchars($r['reported_id'] ?? 'N/A') ?></span>
            </div>
            <small><?= date('M j, Y', strtotime($r['created_at'] ?? 'now')) ?></small>
          </div>
        <?php endforeach; ?>
      </div>
      
    </div>

    <!-- BUG REPORTS -->
    <div class="inbox">
      <h2>Bug Reports</h2>
      <div class="inbox-list">
        <?php foreach ($data['recent_bug_reports'] as $r): ?>
          <div class="inbox-item" onclick="openBugModal('<?= htmlspecialchars($r['id']) ?>')">
            <div>
              <strong><?= htmlspecialchars($r['title'] ?? 'Untitled Bug') ?></strong><br>
              <span><?= mb_strimwidth(htmlspecialchars($r['description'] ?? ''), 0, 40, '…') ?></span>
            </div>
            <small><?= date('M j, Y', strtotime($r['created_at'] ?? 'now')) ?></small>
          </div>
        <?php endforeach; ?>
      </div> 
  </section>

<div id="report-modal" class="report-modal" aria-hidden="true">
  <div class="modal-backdrop" onclick="closeBugModal()"></div>
  <div class="modal-panel">
    <button class="modal-close" onclick="closeBugModal()">×</button>
    <div id="report-modal-content">Loading...</div>
  </div>
</div>

<section class="charts">
  <div class="chart-box">
    <h2>New Matches (Last 7 Days)</h2>
    <canvas id="matchesChart"></canvas>
  </div>

  <div class="chart-box">
    <h2>Active Users</h2>
    <div class="active-count" id="activeUsersCount">0</div>
    <p class="active-sub">Currently online users</p>
  </div>
</section>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="/assets/js/admin/dashboard-charts.js?v=1" defer></script>


<script src="/assets/js/admin/bug-reports.js" defer></script>

<?php require base_path('views/admin/partials/notification-popup.view.php'); ?>
<script src="/assets/js/admin/admin-notifications.js?v=1" defer></script>

</body>
</html>
