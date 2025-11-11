document.addEventListener("DOMContentLoaded", () => {
    const setResolvedModal = document.getElementById("set-resolved-confirmation-modal");
    const confirmSetResolvedBtn = document.getElementById("confirm-set-resolved-bug-report-btn");
    const cancelSetResolvedBtn = document.getElementById("cancel-set-resolved-bug-report-btn");
    const closeSetResolvedModalBtn = document.getElementById("close-set-resolved-bug-report-modal-btn");
    const rememberSetResolvedCheckbox = document.getElementById("remember-set-resolved-bug-report-decision");

    let targetResolvedForm = null;

    // Attach event to all "Set Resolve" buttons
    document.querySelectorAll(".set-resolved-bug-report-btn").forEach(button => {
        button.addEventListener("click", (e) => {
            e.preventDefault();
            const form = e.target.closest("form");
            targetResolvedForm = form;

            const rememberedDecision = localStorage.getItem("set_resolved_bug_report_decision");
            if (rememberedDecision === "confirm") {
                form.submit();
                return;
            }
            if (rememberedDecision === "cancel") {
                return;
            }

            setResolvedModal.style.display = "flex";
        });
    });

    confirmSetResolvedBtn.addEventListener("click", () => {
        if (rememberSetResolvedCheckbox.checked) {
            localStorage.setItem("set_resolved_bug_report_decision", "confirm");
        }
        setResolvedModal.style.display = "none";
        if (targetResolvedForm) targetResolvedForm.submit();
    });

    cancelSetResolvedBtn.addEventListener("click", () => {
        if (rememberSetResolvedCheckbox.checked) {
            localStorage.setItem("set_resolved_bug_report_decision", "cancel");
        }
        setResolvedModal.style.display = "none";
    });

    closeSetResolvedModalBtn.addEventListener("click", () => {
        setResolvedModal.style.display = "none";
    });

    window.addEventListener("click", (e) => {
        if (e.target === setResolvedModal) setResolvedModal.style.display = "none";
    });
});

