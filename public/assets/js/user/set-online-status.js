document.addEventListener("DOMContentLoaded", () => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? "";
    const logoutForms = document.querySelectorAll('form[action="/logout"]');
    const onlineFlagKey = "lablife:presence-online";

    if (!csrfToken) {
        return;
    }

    const postPresence = async (url) => {
        const response = await fetch(url, {
            method: "POST",
            credentials: "include",
            headers: {
                "X-CSRF-Token": csrfToken,
                "Accept": "application/json",
            },
        });

        if (!response.ok) {
            throw new Error(`Presence update failed for ${url}`);
        }
    };

    const markOnline = async (force = false) => {
        if (!force && sessionStorage.getItem(onlineFlagKey) === "1") {
            return;
        }

        try {
            await postPresence("/u/set-online");
            sessionStorage.setItem(onlineFlagKey, "1");
        } catch (error) {
            console.warn("Failed to set user online:", error);
        }
    };

    const markOfflineOnLogout = () => {
        try {
            const payload = new FormData();
            payload.append("csrf_token", csrfToken);
            navigator.sendBeacon("/u/set-offline", payload);
        } catch (error) {
            console.warn("Failed to queue offline update:", error);
        } finally {
            sessionStorage.removeItem(onlineFlagKey);
        }
    };

    if (document.visibilityState === "visible") {
        markOnline();
    }

    document.addEventListener("visibilitychange", () => {
        if (document.visibilityState === "visible") {
            markOnline(true);
        }
    });

    logoutForms.forEach((form) => {
        form.addEventListener("submit", markOfflineOnLogout);
    });
});
