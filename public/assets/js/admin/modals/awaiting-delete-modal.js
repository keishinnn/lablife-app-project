document.addEventListener("DOMContentLoaded", () => {
    const deleteBugReportModal = document.getElementById("delete-confirmation-modal");
    const confirmDeleteBugReportBtn = document.getElementById("confirm-delete-awaiting-bug-report-btn");
    const cancelDeleteBugReportBtn = document.getElementById("cancel-delete-awaiting-bug-report-btn");
    const closeDeleteBugReportModalBtn = document.getElementById("close-awaiting-bug-report-modal-btn");
    const rememberDeleteBugReportCheckbox = document.getElementById("remember-awaiting-bug-report-decision");

    let targetBugReportForm = null;

    document.querySelectorAll(".delete-awaiting-bug-report-btn").forEach(button => {
        button.addEventListener("click", (e) => {
            e.preventDefault();
            const form = e.target.closest("form");
            targetBugReportForm = form;

            const rememberedDecision = localStorage.getItem("awaiting_bug_report_delete_decision");
            if (rememberedDecision === "confirm") {
                form.submit();
                return;
            }
            if (rememberedDecision === "cancel") {
                return;
            }

            deleteBugReportModal.style.display = "flex";
        });
    });

    confirmDeleteBugReportBtn.addEventListener("click", () => {
        if (rememberDeleteBugReportCheckbox.checked) {
            localStorage.setItem("awaiting_bug_report_delete_decision", "confirm");
        }
        deleteBugReportModal.style.display = "none";
        if (targetBugReportForm) targetBugReportForm.submit();
    });

    cancelDeleteBugReportBtn.addEventListener("click", () => {
        if (rememberDeleteBugReportCheckbox.checked) {
            localStorage.setItem("awaiting_bug_report_delete_decision", "cancel");
        }
        deleteBugReportModal.style.display = "none";
    });

    closeDeleteBugReportModalBtn.addEventListener("click", () => {
        deleteBugReportModal.style.display = "none";
    });

    window.addEventListener("click", (e) => {
        if (e.target === deleteBugReportModal) deleteBugReportModal.style.display = "none";
    });
});
