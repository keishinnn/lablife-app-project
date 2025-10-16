document.addEventListener("DOMContentLoaded", function () {
    const profileLink = document.querySelector('.nav-link[href="/u/profile"], .nav-profile');
    const pfLoading = document.getElementById("pf-loading");
    const heroSection = document.getElementById("hero-section");

    if (profileLink && pfLoading && heroSection) {
        profileLink.addEventListener("click", function (e) {
            // Hide the hero section and show loader
            heroSection.style.display = "none";
            pfLoading.style.display = "flex";
        });
    }
});
