// file path = root/public/assets/js/login-loading.js

import { setLoadingState } from "./utils.js";

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("login-form");
    const loginBtn = document.getElementById("login-btn");

    if (!form || !loginBtn) return;

    form.addEventListener("submit", function () {
        setLoadingState(loginBtn, true);
    });
});
