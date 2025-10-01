document.addEventListener("DOMContentLoaded", function () {
    const setupBtn = document.getElementById("finish-preferences-button");
    const setupForm = document.getElementById("preferences-form");
    const loading = document.getElementById("pt-loading"); // full-page overlay

    if (setupBtn) {
        setupForm.addEventListener("submit", function () {
            loading.style.display = "flex";
            setupBtn.disabled = true;
            setupBtn.textContent = "Saving...";
        });
    }

});