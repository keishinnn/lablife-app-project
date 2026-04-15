let page = 1;
let loading = false;
const list = document.getElementById("bug-report-list");
const loader = document.getElementById("loader");

async function fetchReports() {
  if (loading) return;  loading = true;
  loader.style.display = "block";
  loader.textContent = "Loading...";

  try {
    const res = await fetch(`/admin/api/bug-reports?page=${page}`);
    const text = await res.text();

    // Defensive JSON parsing
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
        item.onclick = () => openBugModal(r.id);
        item.innerHTML = `
          <h3>${r.title || "Untitled Report"}</h3>
          <p>${r.excerpt || "No description provided."}</p>
          <div class="meta">
            <span>User ID: ${r.user_id || "Unknown"}</span>
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

async function openBugModal(id) {
  const modal = document.getElementById("report-modal");
  const content = document.getElementById("report-modal-content");
  modal.setAttribute("aria-hidden", "false");
  content.innerHTML = "Loading report...";

  try {
    const res = await fetch(`/admin/api/bug-report?id=${id}`);
    const json = await res.json();
    if (json.data) {
      const r = json.data;
      content.innerHTML = `
        <h2>${r.title || "Untitled Report"}</h2>
        <p><strong>User ID:</strong> ${r.user_id || "Unknown"}</p>
        <p><strong>Created:</strong> ${new Date(r.created_at).toLocaleString()}</p>
        <hr>
        <p>${r.description || "No description provided."}</p>
      `;
    } else {
      content.innerHTML = "No report data found.";
    }
  } catch (err) {
    console.error(err);
    content.innerHTML = "Error loading report details.";
  }
}

function closeBugModal() {
  document.getElementById("report-modal").setAttribute("aria-hidden", "true");
}

// Initial load
fetchReports();
