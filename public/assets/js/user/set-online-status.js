document.addEventListener("DOMContentLoaded", function () {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // Mark user online when page loads
    fetch("/u/set-online", {
        method: "POST",
        credentials: "include",
        headers: {
            "X-CSRF-Token": csrfToken
        }
    });

    // Mark user offline when page is unloaded
    const setOffline = () => {
        const url = "/u/set-offline";
        const data = new FormData();
        data.append("csrf_token", csrfToken);
        navigator.sendBeacon(url, data);
    };

    // Trigger offline on tab close, browser close, or page reload
    window.addEventListener("beforeunload", setOffline);
});
