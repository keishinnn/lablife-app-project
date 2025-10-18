document.addEventListener("DOMContentLoaded", function () {
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

    profileLinks.forEach(link => {
        link.addEventListener("click", function (e) {
            const href = link.getAttribute("href");
            if (!href || href.startsWith("#") || href.startsWith("http")) return;

            pageContent.style.pointerEvents = "none";
            pageContent.style.display = "none";
            profileGlobalLoader.style.display = "flex";
        });
    });
});
