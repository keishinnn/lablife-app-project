document.addEventListener("DOMContentLoaded", function () {
    const interestModal = document.getElementById("interestsModal");
    const interestsEditBtn = document.getElementById("p-interests-edit-btn");
    const interestsAddBtn = document.getElementById("p-interests-add-btn");
    const interestsCloseBtn = document.getElementById("p-interests-close-btn");
    const interestsCancelBtn = document.getElementById("p-interests-cancel-btn");
    const interestsForm = document.getElementById("interests-form");
    const loading = document.getElementById("pt-loading");
    const interestsSaveBtn = document.getElementById("p-interests-save-btn");

    if (!interestModal) return;

    // Open modal
    if (interestsEditBtn) {
        interestsEditBtn.addEventListener("click", function () {
            interestModal.style.display = "flex";
        });
    }

    if (interestsAddBtn) {
        interestsAddBtn.addEventListener("click", function () {
            interestModal.style.display = "flex";
        });
    }

    if (interestsCancelBtn) {
        interestsCancelBtn.addEventListener("click", closeModal);
    }

    function closeModal() {
        interestModal.style.display = "none";
        interestsSaveBtn.disabled = false;
        interestsSaveBtn.textContent = "Save";
    }

    interestsCloseBtn.addEventListener("click", closeModal);
    interestsCancelBtn.addEventListener("click", closeModal);
    window.addEventListener("click", function (e) {
        if (e.target === interestModal) closeModal();
    });

    // Show loading state on form submit
    interestsForm.addEventListener("submit", function () {
        loading.style.display = "flex";
        interestsSaveBtn.disabled = true;
        interestsSaveBtn.textContent = "Saving...";
    });
});

document.querySelectorAll(".interests-tag").forEach(tag => {
    tag.addEventListener("click", () => {
        const id = tag.dataset.id;
        const form = document.getElementById("interests-form");

        if (tag.classList.contains("active")) {
            // unselect
            tag.classList.remove("active");
            form.querySelector(`input[name="interests[]"][value="${id}"]`)?.remove();
        } else {
            // select
            tag.classList.add("active");
            const hidden = document.createElement("input");
            hidden.type = "hidden";
            hidden.name = "interests[]";
            hidden.value = id;
            form.appendChild(hidden);
        }
    });
});