document.addEventListener("DOMContentLoaded", function () {
    const matchesLinks = document.querySelectorAll('a.nav-link[href="/u/matches"]');
    const matchesGlobalLoader = document.getElementById("matches-loading");
    const pageContent = document.getElementById("page-content");

    if (!matchesGlobalLoader || !pageContent) return;

    matchesLinks.forEach(link => {
        link.addEventListener("click", function (e) {
            const href = link.getAttribute("href");
            if (!href || href.startsWith("#") || href.startsWith("http")) return;

            pageContent.style.pointerEvents = "none";
            pageContent.style.display = "none";
            matchesGlobalLoader.style.display = "flex";
        });
    });
});
