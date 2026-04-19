document.addEventListener("DOMContentLoaded", function () {
    const NAVIGATION_LOADING_FLAG = "__lablifeNavigationLoading";
    const pageContent = document.getElementById("page-content");
    const globalLoading = document.getElementById("global-loading");
    const profileLoading = document.getElementById("pf-loading");
    const matchesLoading = document.getElementById("matches-loading");
    const messagesLoading = document.getElementById("messages-loading");

    if (!pageContent) return;

    const fallbackLoaders = [
        globalLoading,
        profileLoading,
        matchesLoading,
        messagesLoading
    ].filter(Boolean);

    if (fallbackLoaders.length === 0) return;

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

    function chooseLoader(pathname) {
        if (pathname.startsWith("/u/messages")) {
            return messagesLoading || globalLoading || fallbackLoaders[0];
        }

        if (pathname === "/u/matches") {
            return matchesLoading || globalLoading || fallbackLoaders[0];
        }

        if (
            pathname === "/u/profile" ||
            pathname === "/u/profile-edit" ||
            pathname === "/u/profile-preferences-edit" ||
            pathname === "/u/profile/blocked-users"
        ) {
            return profileLoading || globalLoading || fallbackLoaders[0];
        }

        return globalLoading || fallbackLoaders[0];
    }

    document.addEventListener("click", function (event) {
        if (
            event.defaultPrevented ||
            event.button !== 0 ||
            event.metaKey ||
            event.ctrlKey ||
            event.shiftKey ||
            event.altKey
        ) {
            return;
        }

        const link = event.target.closest("a[href]");
        if (!link) return;

        const href = link.getAttribute("href");
        if (
            !href ||
            href.startsWith("#") ||
            href.startsWith("javascript:") ||
            href.startsWith("mailto:") ||
            href.startsWith("tel:")
        ) {
            return;
        }

        if (link.target === "_blank" || link.hasAttribute("download")) {
            return;
        }

        let targetUrl;
        try {
            targetUrl = new URL(href, window.location.href);
        } catch (error) {
            return;
        }

        if (targetUrl.origin !== window.location.origin) {
            return;
        }

        if (
            targetUrl.pathname === window.location.pathname &&
            targetUrl.search === window.location.search
        ) {
            return;
        }

        const loader = chooseLoader(targetUrl.pathname);
        if (!loader) return;

        showSingleLoader(loader);
    }, true);
});
