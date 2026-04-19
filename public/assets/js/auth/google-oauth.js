function createGoogleAuthClient(url, anonKey) {
    return supabase.createClient(url, anonKey, {
        auth: {
            flowType: "pkce",
            persistSession: true,
            autoRefreshToken: false,
            detectSessionInUrl: false,
        },
    });
}

async function startGoogleOAuth(button) {
    const supabaseUrl = button.dataset.supabaseUrl;
    const supabaseAnonKey = button.dataset.supabaseAnonKey;

    if (!supabaseUrl || !supabaseAnonKey) {
        return;
    }

    const client = createGoogleAuthClient(supabaseUrl, supabaseAnonKey);
    await client.auth.signInWithOAuth({
        provider: "google",
        options: {
            redirectTo: `${window.location.origin}/auth/google/callback`,
        },
    });
}

async function finalizeGoogleOAuth() {
    const callbackRoot = document.getElementById("google-auth-callback");
    if (!callbackRoot) {
        return;
    }

    const statusText = document.getElementById("google-auth-status-text");
    const errorBox = document.getElementById("google-auth-error");
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content ?? "";
    const supabaseUrl = callbackRoot.dataset.supabaseUrl;
    const supabaseAnonKey = callbackRoot.dataset.supabaseAnonKey;
    const code = new URL(window.location.href).searchParams.get("code");

    if (!supabaseUrl || !supabaseAnonKey || !code || !csrfToken) {
        if (errorBox) {
            errorBox.textContent = "Google sign-in could not be completed. Please try again.";
            errorBox.style.display = "block";
        }
        if (statusText) {
            statusText.textContent = "Authentication failed.";
        }
        return;
    }

    try {
        const client = createGoogleAuthClient(supabaseUrl, supabaseAnonKey);
        const { data, error } = await client.auth.exchangeCodeForSession(code);

        if (error || !data?.session?.access_token) {
            throw new Error(error?.message || "Missing session after Google sign-in.");
        }

        const response = await fetch("/auth/google/session", {
            method: "POST",
            credentials: "same-origin",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": csrfToken,
            },
            body: JSON.stringify({
                access_token: data.session.access_token,
            }),
        });

        const result = await response.json();
        if (!response.ok || !result.success || !result.redirect_to) {
            throw new Error(result.message || "Failed to establish your LabLife session.");
        }

        window.location.replace(result.redirect_to);
    } catch (error) {
        console.error("Google OAuth callback failed:", error);
        if (statusText) {
            statusText.textContent = "Authentication failed.";
        }
        if (errorBox) {
            errorBox.textContent = error.message || "Google sign-in could not be completed.";
            errorBox.style.display = "block";
        }
    }
}

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll("[data-google-oauth-button]").forEach((button) => {
        button.addEventListener("click", async () => {
            button.disabled = true;

            try {
                await startGoogleOAuth(button);
            } catch (error) {
                console.error("Google OAuth start failed:", error);
                button.disabled = false;
            }
        });
    });

    finalizeGoogleOAuth();
});
