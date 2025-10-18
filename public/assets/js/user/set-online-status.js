document.addEventListener("DOMContentLoaded", function () {
    // Mark user online when page loads
    fetch("/u/set-online", {
        method: "POST",
        credentials: "include",
    });

    // Mark user offline when page is unloaded
    const setOffline = () => {
        const url = "/u/set-offline";
        const data = new FormData(); 
        navigator.sendBeacon(url, data);
    };

    // Trigger offline on tab close, browser close, or page reload
    window.addEventListener("beforeunload", setOffline);
});
