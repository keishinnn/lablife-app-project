// file path = root/public/assets/js/login-loading.js

import { setLoadingState } from "./utils.js";

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("login-form");
    const loginBtn = document.getElementById("login-btn");
    const formError = document.getElementById("form-error");

    if (!form || !loginBtn) return;

    form.addEventListener("submit", function (event) {
        const captchaResponse = document.querySelector("[name='cf-turnstile-response']");

        if (captchaResponse && !captchaResponse.value.trim()) {
            event.preventDefault();

            if (formError) {
                formError.textContent = "Please complete the security check.";
                formError.style.display = "block";
            }

            setLoadingState(loginBtn, false, "Sign In");
            return;
        }

        setLoadingState(loginBtn, true);
    });
});
