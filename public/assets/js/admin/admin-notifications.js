let lastCheck = null;

async function checkNewReports() {
  try {
    const res = await fetch(`/admin/api/check-new-reports?since=${encodeURIComponent(lastCheck || '')}`);
    if (!res.ok) return;
    const data = await res.json();

    if (data.new_reports && data.new_reports.length > 0) {
      const report = data.new_reports[0];
      showAdminNotification(report.type, report.title);
    }

    lastCheck = new Date().toISOString();
  } catch (err) {
    console.error('Error checking reports:', err);
  }
}

function showAdminNotification(type, title) {
  const popup = document.getElementById('admin-notification');
  const titleElem = document.getElementById('notif-title');
  const msgElem = document.getElementById('notif-message');

  titleElem.textContent = `New ${type === 'bug' ? 'Bug' : 'User'} Report!`;
  msgElem.textContent = title ? title : 'A new report was just submitted.';

  popup.style.display = 'flex';

  setTimeout(() => {
    popup.classList.add('hide');
    setTimeout(() => {
      popup.style.display = 'none';
      popup.classList.remove('hide');
    }, 300);
  }, 4000);
}

// Check every 10 seconds
setInterval(checkNewReports, 10000);
checkNewReports();
