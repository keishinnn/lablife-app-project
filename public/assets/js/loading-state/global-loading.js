document.addEventListener("DOMContentLoaded", function () {
    const globalLoadingLinks = document.querySelectorAll(
        '#index-nav-discover, ' +
        '#nav-logo-btn, ' +
        'a.nav-link[href="/u/discover"], ' +
        '#nav-start-swiping-btn'
    );

    const globalLoading = document.getElementById("global-loading");
    const pageContent = document.getElementById("page-content");

    if (!globalLoading || !pageContent) return;

    globalLoadingLinks.forEach(link => {
        link.addEventListener("click", function (e) {
            const href = link.getAttribute("href");
            if (!href || href.startsWith("#") || href.startsWith("http")) return;

            pageContent.style.pointerEvents = "none";
            pageContent.style.display = "none";
            globalLoading.style.display = "flex";
        });
    });
});
