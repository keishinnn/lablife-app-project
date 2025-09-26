// file path = root/public/assets/js/setup-profile-loading.js

import { setLoadingState } from "./utils.js";

document.addEventListener("DOMContentLoaded", function () {
    const form = document.getElementById("setup-form");
    const setupBtn = document.getElementById("setup-button");

    if (!form || !setupBtn) return;

    form.addEventListener("submit", function () {
        setLoadingState(setupBtn, true);
    });
});
