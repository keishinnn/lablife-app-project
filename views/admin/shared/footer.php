</div>

<div id="report-modal" class="report-modal" aria-hidden="true">
  <div class="modal-backdrop" onclick="closeBugModal()"></div>
  <div class="modal-panel">
    <button class="modal-close" onclick="closeBugModal()">×</button>
    <div id="report-modal-content">Loading...</div>
  </div>
</div>


<?php require base_path('views/admin/partials/notification-popup.view.php'); ?>

<script src="/assets/js/admin/admin-notifications.js?v=1" defer></script>
<script src="/assets/js/admin/dashboard-charts.js?v=1" defer></script>
<script src="/assets/js/admin/nav-link-active.js" defer></script>
<script src="/assets/js/admin/user-reports.js?v=2" defer></script>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="/assets/js/admin/modals/awaiting-delete-modal.js"></script>
<script src="/assets/js/admin/modals/set-resolved-modal.js"></script>

</body>

</html>