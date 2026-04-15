document.addEventListener("DOMContentLoaded", function () {
    const NAVIGATION_LOADING_FLAG = "__lablifeNavigationLoading";

    const globalLoadingLinks = document.querySelectorAll(
        '#index-nav-discover, ' +
        '#nav-logo-btn, ' +
        'a.nav-link[href="/u/discover"], ' +
        '#nav-start-swiping-btn, ' +
        '#start-verify-btn, ' +
        '#blocked-users-nav-btn, ' +
        '#verify-next-btn'
    );

    const globalLoading = document.getElementById("global-loading");
    const pageContent = document.getElementById("page-content");

    if (!globalLoading || !pageContent) return;

    function showSingleLoader(activeLoader) {
        if (window[NAVIGATION_LOADING_FLAG]) {
            return false;
        }

        window[NAVIGATION_LOADING_FLAG] = true;

        document.querySelectorAll(
            "#global-loading, #pf-loading, #messages-loading, #matches-loading"
        ).forEach(loader => {
            if (loader) {
                loader.style.display = "none";
            }
        });

        pageContent.style.pointerEvents = "none";
        pageContent.style.display = "none";
        activeLoader.style.display = "flex";

        return true;
    }

    globalLoadingLinks.forEach(link => {
        link.addEventListener("click", function (e) {
            const href = link.getAttribute("href");
            if (!href || href.startsWith("#") || href.startsWith("http")) return;

            showSingleLoader(globalLoading);
        });
    });
});
