document.addEventListener("DOMContentLoaded", function () {
    const NAVIGATION_LOADING_FLAG = "__lablifeNavigationLoading";

    const messageLoadingLinks = document.querySelectorAll(
        'a.nav-link[href="/u/messages"], ' +
        '#start-chat-btn, ' +
        'a.matched-message-link'
    );

    const messageLoading = document.getElementById("messages-loading");
    const pageContent = document.getElementById("page-content");

    if (!messageLoading || !pageContent) return;

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

    messageLoadingLinks.forEach(link => {
        link.addEventListener("click", function (e) {
            const href = link.getAttribute("href");
            if (!href || href.startsWith("#") || href.startsWith("http")) return;

            showSingleLoader(messageLoading);
        });
    });
});
