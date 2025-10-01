document.addEventListener("DOMContentLoaded", function () {
    const ptModal = document.getElementById("personalityModal");
    const ptBtn = document.getElementById("p-ptypes-edit-btn");
    const ptAddBtn = document.getElementById("p-ptypes-add-btn");
    const closeBtn = document.getElementById("p-pt-close-btn");
    const ptCancelBtn = document.getElementById("p-pt-cancel-btn");
    const ptForm = document.getElementById("pt-form");
    const loading = document.getElementById("pt-loading"); // full-page overlay
    const ptSaveBtn = document.getElementById("pt-save-btn");

    // Open modal
    if (ptBtn) {
        ptBtn.addEventListener("click", function () {
            ptModal.style.display = "flex";
        });
    }

    if (ptAddBtn) {
        ptAddBtn.addEventListener("click", function () {
            ptModal.style.display = "flex";
        });
    }

    if (ptCancelBtn) {
        ptCancelBtn.addEventListener("click", closeModal);
    }

    // Close modal
    function closeModal() {
        ptModal.style.display = "none";
        ptSaveBtn.disabled = false;
        ptSaveBtn.textContent = "Save";
    }

    closeBtn.addEventListener("click", closeModal);
    ptCancelBtn.addEventListener("click", closeModal);
    window.addEventListener("click", function (e) {
        if (e.target === ptModal) closeModal();
    });

    // Show loading state on form submit
    ptForm.addEventListener("submit", function () {
        loading.style.display = "flex";
        ptSaveBtn.disabled = true;
        ptSaveBtn.textContent = "Saving...";
    });
});