document.addEventListener("DOMContentLoaded", function () {
  const links = document.querySelectorAll(".admin-sidebar .nav-link");
  const currentPath = window.location.pathname;

  // Highlight the current page link on load
  links.forEach((link) => {
    if (link.getAttribute("href") === currentPath) {
      link.classList.add("active");
    }

    // Also handle clicks to toggle active state instantly
    link.addEventListener("click", function () {
      links.forEach((l) => l.classList.remove("active"));
      this.classList.add("active");
    });
  });
});