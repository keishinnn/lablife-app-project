document.addEventListener("DOMContentLoaded", function () {
    const hobbiesModal = document.getElementById("hobbiesModal");
    const hobbiesBtn = document.getElementById("p-hb-edit-btn");
    const hbAddBtn = document.getElementById("p-hb-add-btn");
    const hbCloseBtn = document.getElementById("p-hb-close-btn");
    const hbCancelBtn = document.getElementById("p-hb-cancel-btn");
    const hbForm = document.getElementById("hb-form");
    const loading = document.getElementById("pt-loading");
    const hbSaveBtn = document.getElementById("p-hb-save-btn");

    if (!hobbiesModal) return;

    if (hobbiesModal.dataset.openOnLoad === "true") {
        hobbiesModal.style.display = "flex";
    }

    // Open modal
    if (hobbiesBtn) {
        hobbiesBtn.addEventListener("click", function () {
            hobbiesModal.style.display = "flex";
        });
    }

    if (hbAddBtn) {
        hbAddBtn.addEventListener("click", function () {
            hobbiesModal.style.display = "flex";
        });
    }

    if (hbCancelBtn) {
        hbCancelBtn.addEventListener("click", closeModal);
    }

    // Close modal
    function closeModal() {
        hobbiesModal.style.display = "none";
        hbSaveBtn.disabled = false;
        hbSaveBtn.textContent = "Save";
    }

    hbCloseBtn.addEventListener("click", closeModal);
    hbCancelBtn.addEventListener("click", closeModal);
    window.addEventListener("click", function (e) {
        if (e.target === hobbiesModal) closeModal();
    });

    // Show loading state on form submit
    hbForm.addEventListener("submit", function () {
        loading.style.display = "flex";
        hbSaveBtn.disabled = true;
        hbSaveBtn.textContent = "Saving...";
    });
});

document.querySelectorAll(".hobby-tag").forEach(tag => {
    tag.addEventListener("click", () => {
        const id = tag.dataset.id;
        const form = document.getElementById("hb-form");

        if (tag.classList.contains("active")) {
            // unselect
            tag.classList.remove("active");
            form.querySelector(`input[name="hobbies[]"][value="${id}"]`)?.remove();
        } else {
            // select
            tag.classList.add("active");
            const hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.name = "hobbies[]";
            hidden.value = id;
            form.appendChild(hidden);
        }
    });
});
