document.addEventListener("DOMContentLoaded", function () {
    const loading =
        document.getElementById("pt-loading") ||
        document.getElementById("setup-preferences-loading");

    if (!loading) return;

    [
        {
            buttonId: "setup-button",
            formId: "setup-form"
        },
        {
            buttonId: "finish-preferences-button",
            formId: "preferences-form"
        }
    ].forEach(({ buttonId, formId }) => {
        const setupBtn = document.getElementById(buttonId);
        const setupForm = document.getElementById(formId);

        if (!setupBtn || !setupForm) {
            return;
        }

        setupForm.addEventListener("submit", function () {
            loading.style.display = "flex";
            setupBtn.disabled = true;
            setupBtn.textContent = "Saving...";
        });
    });
});
