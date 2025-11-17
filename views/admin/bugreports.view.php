<?php require base_path('Views/admin/shared/header.php') ?>

<?php require base_path('Views/admin/shared/sidebar.php') ?>

<?php
$awaitingBugReports = $awaitingBugReports ?? [];
$inProgressBugReports = $inProgressBugReports ?? [];
$resolvedBugReports = $resolvedBugReports ?? [];

usort($awaitingBugReports, fn($a, $b) => strtotime($a->createdAt) <=> strtotime($b->createdAt));
usort($inProgressBugReports, fn($a, $b) => strtotime($a->createdAt) <=> strtotime($b->createdAt));
usort($resolvedBugReports, fn($a, $b) => strtotime($a->createdAt) <=> strtotime($b->createdAt));
?>


<main class="admin-content">
  <header class="admin-top">
    <h1>Bug Reports</h1>
    <div class="admin-user">Signed in as <?= htmlspecialchars($_SESSION['admin_email']) ?></div>
  </header>

  <div class="reports-nav">
    <button id="bug-reports-btn-awaiting">Awaiting</button>
    <button id="bug-reports-btn-in-progress">In Progress</button>
    <button id="bug-reports-btn-resolved">Resolved</button>
  </div>

  <div class="reports-filters" style="margin: 1rem 0; display: flex; flex-wrap: wrap; gap: 1rem; justify-content: end; align-items: center;">
    <label for="filter-start-date" style="display: flex; flex-direction: column; font-size: 0.85rem; color: #ffffffff;">
      Start Date
      <input
        type="date"
        id="filter-start-date"
        style="
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid #ccc;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        font-size: 0.9rem;
        transition: all 0.2s ease-in-out;
        margin-top: 0.25rem;
      "
        onfocus="this.style.borderColor='#6366f1'; this.style.boxShadow='0 0 0 2px rgba(99,102,241,0.2)';"
        onblur="this.style.borderColor='#ccc'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">
    </label>

    <label for="filter-end-date" style="display: flex; flex-direction: column; font-size: 0.85rem; color: #ffffffff;">
      End Date
      <input
        type="date"
        id="filter-end-date"
        style="
        padding: 0.5rem 1rem;
        border-radius: 8px;
        border: 1px solid #ccc;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        font-size: 0.9rem;
        transition: all 0.2s ease-in-out;
        margin-top: 0.25rem;
      "
        onfocus="this.style.borderColor='#6366f1'; this.style.boxShadow='0 0 0 2px rgba(99,102,241,0.2)';"
        onblur="this.style.borderColor='#ccc'; this.style.boxShadow='0 2px 4px rgba(0,0,0,0.05)';">
    </label>
  </div>

  <!-- Awaiting Bug Reports Table -->
  <div class="table-container">
    <table class="reports-table" id="awaiting-bug-reports-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>User ID</th>
          <th>Title</th>
          <th>Description</th>
          <th>Created At</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($awaitingBugReports)): ?>
          <?php foreach ($awaitingBugReports as $report): ?>
            <tr>
              <td><?= htmlspecialchars($report->id) ?></td>
              <td><?= htmlspecialchars($report->userId) ?></td>
              <td><?= htmlspecialchars($report->title) ?></td>
              <td><?= htmlspecialchars($report->description) ?></td>
              <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($report->createdAt))) ?></td>
              <td><?= htmlspecialchars($report->statusName) ?></td>
              <td style="display: flex; gap: 1rem;">
                <form action="/admin/set-in-progress-bug-report" method="post" class="delete-awaiting-bug-report-form" style="display:inline;">
                  <input type="hidden" name="bug_report_id" value="<?= $report->id ?>">
                  <button type="submit" class="set-in-progress-bug-report-btn">Set In Progress</button>
                </form>

                <form action="/admin/delete-bug-report" method="post" class="delete-awaiting-bug-report-form" style="display:inline;">
                  <input type="hidden" name="bug_report_id" value="<?= $report->id ?>">
                  <button type="submit" class="delete-awaiting-bug-report-btn">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" style="text-align:center;">No bug reports found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- In Progress Bug Reports Table -->
    <table class="reports-table" id="in-progress-bug-reports-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>User ID</th>
          <th>Title</th>
          <th>Description</th>
          <th>Created At</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($inProgressBugReports)): ?>
          <?php foreach ($inProgressBugReports as $report): ?>
            <tr>
              <td><?= htmlspecialchars($report->id) ?></td>
              <td><?= htmlspecialchars($report->userId) ?></td>
              <td><?= htmlspecialchars($report->title) ?></td>
              <td><?= htmlspecialchars($report->description) ?></td>
              <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($report->createdAt))) ?></td>
              <td><?= htmlspecialchars($report->statusName) ?></td>
              <td style="display: flex; gap: 1rem;">
                <form action="/admin/set-resolved-bug-report" method="post" class="delete-awaiting-bug-report-form" style="display:inline;">
                  <input type="hidden" name="bug_report_id" value="<?= $report->id ?>">
                  <button type="submit" class="set-resolved-bug-report-btn">Set Resolve</button>
                </form>

                <form action="/admin/delete-bug-report" method="post" class="delete-awaiting-bug-report-form" style="display:inline;">
                  <input type="hidden" name="bug_report_id" value="<?= $report->id ?>">
                  <button type="submit" class="delete-awaiting-bug-report-btn">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" style="text-align:center;">No bug reports found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Resolved Bug Reports Table -->
    <table class="reports-table" id="resolved-bug-reports-table">
      <thead>
        <tr>
          <th>ID</th>
          <th>User ID</th>
          <th>Title</th>
          <th>Description</th>
          <th>Created At</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($resolvedBugReports)): ?>
          <?php foreach ($resolvedBugReports as $report): ?>
            <tr>
              <td><?= htmlspecialchars($report->id) ?></td>
              <td><?= htmlspecialchars($report->userId) ?></td>
              <td><?= htmlspecialchars($report->title) ?></td>
              <td><?= htmlspecialchars($report->description) ?></td>
              <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($report->createdAt))) ?></td>
              <td style="font-style: italic; color: green;"><?= htmlspecialchars($report->statusName) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" style="text-align:center;">No bug reports found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div id="bug-report-list" class="bug-report-list"></div>
  <div id="loader" class="loader">Loading...</div>
</main>

<?php require base_path('Views/admin/modals/delete.confirmation.view.php') ?>
<?php require base_path('Views/admin/modals/set.resolved.confirmation.view.php') ?>

<script>
  const awaitingBtn = document.getElementById('bug-reports-btn-awaiting');
  const inProgressBtn = document.getElementById('bug-reports-btn-in-progress');
  const resolvedBtn = document.getElementById('bug-reports-btn-resolved');

  const awaitingTable = document.getElementById('awaiting-bug-reports-table');
  const inProgressTable = document.getElementById('in-progress-bug-reports-table');
  const resolvedTable = document.getElementById('resolved-bug-reports-table');

  const filterStartDate = document.getElementById('filter-start-date');
  const filterEndDate = document.getElementById('filter-end-date');

  function hideAllTables() {
    if (awaitingTable) awaitingTable.style.display = 'none';
    if (inProgressTable) inProgressTable.style.display = 'none';
    if (resolvedTable) resolvedTable.style.display = 'none';
  }

  function resetActiveNav() {
    awaitingBtn.classList.remove('active');
    inProgressBtn.classList.remove('active');
    resolvedBtn.classList.remove('active');
  }

  function applyDateFilter() {
    const start = filterStartDate.value ? new Date(filterStartDate.value) : null;
    const end = filterEndDate.value ? new Date(filterEndDate.value) : null;

    [awaitingTable, inProgressTable, resolvedTable].forEach(table => {
      if (!table) return;
      table.querySelectorAll('tbody tr').forEach(row => {
        const createdAtCell = row.cells[0];
        if (!createdAtCell) return;

        const rowDate = new Date(createdAtCell.innerText);
        const afterStart = !start || rowDate >= start;
        const beforeEnd = !end || rowDate <= end;

        row.style.display = (afterStart && beforeEnd) ? '' : 'none';
      });
    });
  }

  hideAllTables();
  if (awaitingTable) awaitingTable.style.display = 'table';
  awaitingBtn.classList.add('active');

  awaitingBtn.addEventListener('click', () => {
    hideAllTables();
    resetActiveNav();
    awaitingTable.style.display = 'table';
    awaitingBtn.classList.add('active');
  });

  inProgressBtn.addEventListener('click', () => {
    hideAllTables();
    resetActiveNav();
    inProgressTable.style.display = 'table';
    inProgressBtn.classList.add('active');
  });

  resolvedBtn.addEventListener('click', () => {
    hideAllTables();
    resetActiveNav();
    resolvedTable.style.display = 'table';
    resolvedBtn.classList.add('active');
  });

  filterStartDate.addEventListener('input', applyDateFilter);
  filterEndDate.addEventListener('input', applyDateFilter);
</script>

<?php require base_path('Views/admin/shared/footer.php') ?>