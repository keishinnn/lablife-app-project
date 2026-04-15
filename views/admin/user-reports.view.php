<?php require base_path('Views/admin/shared/header.php') ?>

<?php require base_path('Views/admin/shared/sidebar.php') ?>

<?php
$awaitingUserReports = $awaitingUserReports ?? [];
$inProgressUserReports = $inProgressUserReports ?? [];
$resolvedUserReports = $resolvedUserReports ?? [];

usort($awaitingUserReports, fn($a, $b) => strtotime($a->createdAt) <=> strtotime($b->createdAt));
usort($inProgressUserReports, fn($a, $b) => strtotime($a->createdAt) <=> strtotime($b->createdAt));
usort($resolvedUserReports, fn($a, $b) => strtotime($a->createdAt) <=> strtotime($b->createdAt));
?>

<main class="admin-content">
  <header class="admin-top">
    <h1>User Reports</h1>
    <div class="admin-user">Signed in as <?= htmlspecialchars($_SESSION['admin_email']) ?></div>
  </header>

  <div class="reports-nav">
    <button id="user-reports-btn-awaiting">Awaiting</button>
    <button id="user-reports-btn-in-progress">In Progress</button>
    <button id="user-reports-btn-resolved">Resolved</button>
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

  <!-- Awaiting User Reports Table -->
  <div class="table-container">
    <table class="reports-table" id="awaiting-user-reports-table">
      <thead>
        <tr>
          <th>Created At</th>
          <th>Reporter ID</th>
          <th>Reported User ID</th>
          <th>Category Name</th>
          <th>Context</th>
          <th>Reason</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($awaitingUserReports)): ?>
          <?php foreach ($awaitingUserReports as $report): ?>
            <tr data-context='<?= htmlspecialchars(json_encode($report->context ?? []), ENT_QUOTES, 'UTF-8') ?>'>
              <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($report->createdAt))) ?></td>
              <td><?= htmlspecialchars($report->reporterId) ?></td>
              <td><?= htmlspecialchars($report->reportedUserId) ?></td>
              <td><?= htmlspecialchars($report->categoryName) ?></td>
              <td><button class="user-report-context-btn">View</button></td>
              <td><?= htmlspecialchars($report->reasonText) ?></td>
              <td><?= htmlspecialchars($report->statusName) ?></td>
              <td style="display: flex; gap: 1rem;">
                <form action="/admin/set-in-progress-user-report" method="post" class="delete-awaiting-bug-report-form" style="display:inline;">
                  <input type="hidden" name="user_report_id" value="<?= $report->id ?>">
                  <button type="submit" class="set-in-progress-bug-report-btn">Set In Progress</button>
                </form>

                <form action="/admin/delete-user-report" method="post" style="display:inline;">
                  <input type="hidden" name="user_report_id" value="<?= $report->id ?>">
                  <button type="submit" class="delete-awaiting-bug-report-btn">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" style="text-align:center;">No user reports found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- In Progress User Reports Table -->
    <table class="reports-table" id="in-progress-user-reports-table">
      <thead>
        <tr>
          <th>Created At</th>
          <th>Reporter ID</th>
          <th>Reported User ID</th>
          <th>Category Name</th>
          <th>Context</th>
          <th>Reason</th>
          <th>Status</th>
          <th>Action</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($inProgressUserReports)): ?>
          <?php foreach ($inProgressUserReports as $report): ?>
            <tr data-context='<?= htmlspecialchars(json_encode($report->context ?? []), ENT_QUOTES, 'UTF-8') ?>'>
              <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($report->createdAt))) ?></td>
              <td><?= htmlspecialchars($report->reporterId) ?></td>
              <td><?= htmlspecialchars($report->reportedUserId) ?></td>
              <td><?= htmlspecialchars($report->categoryName) ?></td>
              <td><button class="user-report-context-btn">View</button></td>
              <td><?= htmlspecialchars($report->reasonText) ?></td>
              <td><?= htmlspecialchars($report->statusName) ?></td>
              <td style="display: flex; gap: 1rem;">
                <form action="/admin/set-resolved-user-report" method="post" class="delete-awaiting-user-report-form" style="display:inline;">
                  <input type="hidden" name="user_report_id" value="<?= $report->id ?>">
                  <button type="button" class="set-resolved-user-report-btn" id="">Take Action</button>
                </form>

                <form action="/admin/delete-user-report" method="post" style="display:inline;">
                  <input type="hidden" name="user_report_id" value="<?= $report->id ?>">
                  <button type="submit" class="delete-awaiting-bug-report-btn">Delete</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="8" style="text-align:center;">No user reports found.</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>

    <!-- Resolved User Reports Table -->
    <table class="reports-table" id="resolved-user-reports-table">
      <thead>
        <tr>
          <th>Created At</th>
          <th>Reporter ID</th>
          <th>Reported User ID</th>
          <th>Category Name</th>
          <th>Context</th>
          <th>Reason</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($resolvedUserReports)): ?>
          <?php foreach ($resolvedUserReports as $report): ?>
            <tr data-context='<?= htmlspecialchars(json_encode($report->context ?? []), ENT_QUOTES, 'UTF-8') ?>'>
              <td><?= htmlspecialchars(date('Y-m-d H:i', strtotime($report->createdAt))) ?></td>
              <td><?= htmlspecialchars($report->reporterId) ?></td>
              <td><?= htmlspecialchars($report->reportedUserId) ?></td>
              <td><?= htmlspecialchars($report->categoryName) ?></td>
              <td><button class="user-report-context-btn">View</button></td>
              <td><?= htmlspecialchars($report->reasonText) ?></td>
              <td style="font-style: italic; color: green;"><?= htmlspecialchars($report->statusName) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="7" style="text-align:center;">No user reports found.</td>
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
<?php require base_path('Views/admin/modals/context.modal.view.php') ?>
<?php require base_path('Views/admin/modals/take.action.modal.view.php') ?>

<script>
  const awaitingBtn = document.getElementById('user-reports-btn-awaiting');
  const inProgressBtn = document.getElementById('user-reports-btn-in-progress');
  const resolvedBtn = document.getElementById('user-reports-btn-resolved');

  const awaitingTable = document.getElementById('awaiting-user-reports-table');
  const inProgressTable = document.getElementById('in-progress-user-reports-table');
  const resolvedTable = document.getElementById('resolved-user-reports-table');

  const contextModalContainer = document.getElementById("context-modal");
  const contextCloseBtn = document.querySelector(".close-context-modal");
  const contextMessagesList = document.getElementById("context-messages-list");

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

  document.querySelectorAll(".user-report-context-btn").forEach(button => {
    button.addEventListener("click", (event) => {
      const row = event.target.closest("tr");
      const contextData = row.getAttribute("data-context");

      contextMessagesList.innerHTML = "";

      if (contextData) {
        try {
          const parsedContext = JSON.parse(contextData);

          if (Array.isArray(parsedContext) && parsedContext.length > 0) {
            parsedContext.forEach(msg => {
              const listItem = document.createElement("li");
              listItem.innerHTML = `
                <div class="context-message-user">${msg.userId}</div>
                <div style="color: black; margin-top: 0.5rem; margin-bottom: 0.5rem;">${msg.text}</div>
                <div class="context-message-time">
                  ${new Date(msg.createdAt).toLocaleString()}
                </div>
              `;
              contextMessagesList.appendChild(listItem);
            });
          } else {
            contextMessagesList.innerHTML = "<li>No conversation context available.</li>";
          }
        } catch (error) {
          console.error("Error parsing context JSON:", error);
          contextMessagesList.innerHTML = "<li>Invalid context data.</li>";
        }
      } else {
        contextMessagesList.innerHTML = "<li>No context available.</li>";
      }

      contextModalContainer.style.display = "flex";
    });
  });

  contextCloseBtn.addEventListener("click", () => {
    contextModalContainer.style.display = "none";
  });

  contextModalContainer.addEventListener("click", (e) => {
    if (e.target === contextModalContainer) {
      contextModalContainer.style.display = "none";
    }
  });

  const takeActionModal = document.getElementById("take-action-modal");
  const closeTakeActionModalBtn = document.getElementById("close-take-action-modal-btn");
  const banReportedUserBtn = document.getElementById("ban-reported-user-btn");
  const setTakeActionResolvedBtn = document.getElementById("set-take-action-resolved-btn");

  const banUserForm = document.getElementById("ban-user-form");
  const setResolvedForm = document.getElementById("set-resolved-form");
  const banReportedUserIdInput = document.getElementById("ban-reported-user-id");
  const banReportIdInput = document.getElementById("ban-report-id");
  const resolvedReportIdInput = document.getElementById("resolved-report-id");

  document.querySelectorAll(".set-resolved-user-report-btn").forEach(btn => {
    btn.addEventListener("click", e => {
      const row = e.target.closest("tr");
      const reportedUserId = row.cells[2].innerText;

      const reportId = row.querySelector('input[name="user_report_id"]').value;

      takeActionModal.dataset.reportedUserId = reportedUserId;
      takeActionModal.dataset.reportId = reportId;

      takeActionModal.style.display = "flex";
    });
  });

  closeTakeActionModalBtn.addEventListener("click", () => {
    takeActionModal.style.display = "none";
  });

  takeActionModal.addEventListener("click", e => {
    if (e.target === takeActionModal) takeActionModal.style.display = "none";
  });

  banReportedUserBtn.addEventListener("click", () => {
    const userId = takeActionModal.dataset.reportedUserId;
    const reportId = takeActionModal.dataset.reportId;

    if (!userId || !reportId) {
      alert("Error: Missing user ID or report ID");
      return;
    }

    banReportedUserIdInput.value = userId;
    banReportIdInput.value = reportId;

    banUserForm.submit();
  });

  setTakeActionResolvedBtn.addEventListener("click", () => {
    const reportId = takeActionModal.dataset.reportId;
    resolvedReportIdInput.value = reportId;
    setResolvedForm.submit();
  });

  filterStartDate.addEventListener('input', applyDateFilter);
  filterEndDate.addEventListener('input', applyDateFilter);
</script>

<?php require base_path('Views/admin/shared/footer.php') ?>