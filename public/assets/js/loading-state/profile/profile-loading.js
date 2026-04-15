document.addEventListener("DOMContentLoaded", function () {
    const NAVIGATION_LOADING_FLAG = "__lablifeNavigationLoading";

    const profileLinks = document.querySelectorAll(
        'a.nav-link[href="/u/profile"], ' +
        'a.profile-edit-action[href="/u/profile-preferences-edit"], ' +
        'a.profile-edit-action[href="/u/profile-edit"], ' +
        'a.profile-edit-cancel-btn[href="/u/profile"], ' +
        'a.profile-edit-pref-cancel-btn[href="/u/profile"], ' +
        '#index-nav-profile'
    );

    const profileGlobalLoader = document.getElementById("pf-loading");
    const pageContent = document.getElementById("page-content");

    if (!profileGlobalLoader || !pageContent) return;

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

    profileLinks.forEach(link => {
        link.addEventListener("click", function (e) {
            const href = link.getAttribute("href");
            if (!href || href.startsWith("#") || href.startsWith("http")) return;

            showSingleLoader(profileGlobalLoader);
        });
    });
});
