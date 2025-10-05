document.addEventListener("DOMContentLoaded", function () {
    const prefSaveBtn = document.getElementById("edit-pref-save-btn");
    const prefForm = document.getElementById("edit-preferences-form");
    const pfLoading = document.getElementById("pf-loading");
    const loading = document.getElementById("pt-loading");// full-page overlay

    const prefCancelBtn = document.querySelector('.profile-edit-page-section-fourteen a[href="/u/profile"]');
    const profilePrefEditSection = document.getElementById("profile-edit-pref-section");
    const profileEditContainerModify = document.getElementById('profile-edit-pref-modify-style');

    if (prefSaveBtn) {
        prefForm.addEventListener("submit", function () {
            loading.style.display = "flex";
            prefSaveBtn.disabled = true;
            prefSaveBtn.textContent = "Saving...";
        });
    }

    if (prefCancelBtn && pfLoading && profilePrefEditSection) {
        prefCancelBtn.addEventListener("click", function (e) {
            profilePrefEditSection.style.display = "none";
            pfLoading.style.display = "flex";

            profileEditContainerModify.style.display = "flex";
            profileEditContainerModify.style.justifyContent = "center";
            profileEditContainerModify.style.alignItems = "center";
            profileEditContainerModify.style.flexDirection = "column";
        });
    }

});