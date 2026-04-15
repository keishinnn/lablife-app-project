let page = 1;
let loading = false;
const list = document.getElementById("user-report-list");
const loader = document.getElementById("loader");

async function fetchReports() {
  if (loading) return;
  loading = true;
  loader.style.display = "block";
  loader.textContent = "Loading...";

  try {
    const res = await fetch(`/admin/api/user-reports?page=${page}`);
    const text = await res.text();
    let json;
    try {
      json = JSON.parse(text);
    } catch {
      console.error("Invalid JSON:", text);
      loader.textContent = "Invalid server response.";
      loading = false;
      return;
    }

    if (json.data && json.data.length) {
      json.data.forEach(r => {
        const item = document.createElement("div");
        item.className = "bug-box";
        item.onclick = () => openUserModal(r.id);
        item.innerHTML = `
          <h3>Report ID: ${r.id}</h3>
          <p>${r.excerpt || "No reason provided."}</p>
          <div class="meta">
            <span>Reported ID: ${r.reported_id || "Unknown"}</span>
            <span>${r.created_at ? new Date(r.created_at).toLocaleDateString() : "—"}</span>
          </div>
        `;
        list.appendChild(item);
      });
      page++;
      loader.style.display = "none";
    } else {
      loader.textContent = "No more reports found.";
    }
  } catch (err) {
    console.error(err);
    loader.textContent = "Error loading reports.";
  } finally {
    loading = false;
  }
}

// Infinite scroll
window.addEventListener("scroll", () => {
  if (window.innerHeight + window.scrollY >= document.body.offsetHeight - 300) {
    fetchReports();
  }
});

async function openUserModal(id) {
  const modal = document.getElementById("report-modal");
  const content = document.getElementById("report-modal-content");
  modal.setAttribute("aria-hidden", "false");
  content.innerHTML = "Loading report...";

  try {
    const res = await fetch(`/admin/api/user-report?id=${id}`);
    const json = await res.json();

    if (json.data) {
      const r = json.data;
      content.innerHTML = `
        <h2>Report Details</h2>
        <p><strong>Reported User ID:</strong> ${r.reported_user_id || "Unknown"}</p>
        <p><strong>Reported By:</strong> ${r.reported_id || "Unknown"}</p>
        <p><strong>Date:</strong> ${new Date(r.created_at).toLocaleString()}</p>
        <hr>
        <p>${r.reason || "No reason provided."}</p>
      `;
    } else {
      content.innerHTML = "No report data found.";
    }
  } catch (err) {
    console.error(err);
    content.innerHTML = "Error loading report details.";
  }
}

function closeUserModal() {
  document.getElementById("report-modal").setAttribute("aria-hidden", "true");
}

fetchReports();
