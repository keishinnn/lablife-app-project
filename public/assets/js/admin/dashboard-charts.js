let matchesChart;

// Fetch matches trend
async function loadMatchesChart() {
  try {
    const res = await fetch('/admin/api/dashboard/matches');
    if (!res.ok) return;
    const json = await res.json();

    const labels = json.data.map(r => new Date(r.date).toLocaleDateString());
    const counts = json.data.map(r => r.count);

    const ctx = document.getElementById('matchesChart').getContext('2d');
    if (matchesChart) matchesChart.destroy();

    matchesChart = new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Matches',
          data: counts,
          borderColor: '#ec4899',
          backgroundColor: 'rgba(236,72,153,0.3)',
          fill: true,
          tension: 0.3
        }]
      },
      options: {
        responsive: true,
        plugins: { legend: { display: false } },
        scales: {
          x: { grid: { color: '#374151' }, ticks: { color: '#9ca3af' } },
          y: { grid: { color: '#374151' }, ticks: { color: '#9ca3af' } }
        }
      }
    });
  } catch (err) {
    console.error('Error loading matches chart:', err);
  }
}

// Fetch active users count
async function loadActiveUsers() {
  try {
    const res = await fetch('/admin/api/dashboard/active-users');
    if (!res.ok) return;
    const json = await res.json();

    document.getElementById('activeUsersCount').textContent = json.active_users ?? 0;
  } catch (err) {
    console.error('Error loading active users:', err);
  }
}

// Initial load
loadMatchesChart();
loadActiveUsers();

// Auto-refresh every 10 seconds
setInterval(() => {
  loadMatchesChart();
  loadActiveUsers();
}, 10000);
