document.addEventListener("DOMContentLoaded", function () {
    const btn = document.getElementById("edit-profile-save-btn");
    const form = document.getElementById("edit-profile-form");
    const loading = document.getElementById("pt-loading"); // full-page overlay

    if (btn) {
        form.addEventListener("submit", function () {
            loading.style.display = "flex";
            btn.disabled = true;
            btn.textContent = "Saving...";
        });
    }

});