document.addEventListener("DOMContentLoaded", function () {
    const editProfileLink = document.querySelector('.profile-section-ten a[href="/u/profile-edit"], a[href="/u/profile-edit"]');
    const pfLoading = document.getElementById("pf-loading");
    const profileSection = document.getElementById("profile-container-section");
    const profileContainerModify = document.getElementById('profile-modify-style');

    const editPreferencesLink = document.querySelector('.profile-section-ten a[href="/u/profile-preferences-edit"], a[href="/u/profile-preferences-edit"]');

    const editProfileCancelLink = document.querySelector('.profile-edit-page-section-fourteen a[href="/u/profile"]');
    const profileEditSection = document.getElementById("profile-edit-container-section");
    const profileEditContainerModify = document.getElementById('profile-edit-page-modify-style');


    if (editProfileLink && pfLoading && profileSection) {
        editProfileLink.addEventListener("click", function (e) {
            // Hide the profile section and show loader
            profileSection.style.display = "none";
            pfLoading.style.display = "flex";

            profileContainerModify.style.display = "flex";
            profileContainerModify.style.justifyContent = "center";
            profileContainerModify.style.alignItems = "center";
            profileContainerModify.style.flexDirection = "column";
        });
    }

    if (editPreferencesLink && pfLoading && profileSection) {
        editPreferencesLink.addEventListener("click", function (e) {
            profileSection.style.display = "none";
            pfLoading.style.display = "flex";

            profileContainerModify.style.display = "flex";
            profileContainerModify.style.justifyContent = "center";
            profileContainerModify.style.alignItems = "center";
            profileContainerModify.style.flexDirection = "column";
        });
    }

    if (editProfileCancelLink && pfLoading && profileEditSection) {
        editProfileCancelLink.addEventListener("click", function (e) {
            profileEditSection.style.display = "none";
            pfLoading.style.display = "flex";

            profileEditContainerModify.style.display = "flex";
            profileEditContainerModify.style.justifyContent = "center";
            profileEditContainerModify.style.alignItems = "center";
            profileEditContainerModify.style.flexDirection = "column";
        });
    }
});
