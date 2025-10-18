document.addEventListener("DOMContentLoaded", function () {
    const prefSaveBtn = document.getElementById("edit-pref-save-btn");
    const prefForm = document.getElementById("edit-preferences-form");
    const loading = document.getElementById("pt-loading");

    if (prefSaveBtn) {
        prefForm.addEventListener("submit", function () {
            loading.style.display = "flex";
            prefSaveBtn.disabled = true;
            prefSaveBtn.textContent = "Saving...";
        });
    }
});