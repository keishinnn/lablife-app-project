document.addEventListener("DOMContentLoaded", function () {
    const messageLoadingLinks = document.querySelectorAll(
        'a.nav-link[href="/u/messages"], ' +
        '#start-chat-btn, ' +
        'a.matched-message-link'
    );

    const messageLoading = document.getElementById("messages-loading");
    const pageContent = document.getElementById("page-content");

    if (!messageLoading || !pageContent) return;

    messageLoadingLinks.forEach(link => {
        link.addEventListener("click", function (e) {
            const href = link.getAttribute("href");
            if (!href || href.startsWith("#") || href.startsWith("http")) return;

            pageContent.style.pointerEvents = "none";
            pageContent.style.display = "none";
            messageLoading.style.display = "flex";
        });
    });
});
