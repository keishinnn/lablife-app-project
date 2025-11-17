<!-- Views/admin/modals/take.action.modal.view.php -->

<div id="take-action-modal" class="take-action-modal">
    <div class="user-block-confirmation-modal-container">
        <div class="user-block-confirmation-modal-content">

            <button type="button" class="user-block-confirmation-modal-close" id="close-take-action-modal-btn">
                <svg class="user-block-confirmation-modal-close-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                    viewBox="0 0 14 14">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                </svg>
            </button>

            <div class="user-block-confirmation-modal-body">
                <svg class="user-block-confirmation-modal-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 20 20">
                    <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>

                <h3 class="user-block-confirmation-modal-title">Take Action on This User Report</h3>

                <p class="take-action-modal-description">
                    Choose an action for the reported user.
                </p>

                <div class="take-action-modal-buttons">
                    <button type="button" id="ban-reported-user-btn" class="take-action-modal-ban">Ban Reported User</button>
                    <button type="button" id="set-take-action-resolved-btn" class="take-action-modal-resolve">Set as Resolved</button>
                </div>
            </div>

            <!-- Hidden forms -->
            <form id="ban-user-form" action="/admin/ban-user" method="post" style="display:none;">
                <input type="hidden" name="user_report_id" id="ban-report-id">
                <input type="hidden" name="reported_user_id" id="ban-reported-user-id">
            </form>


            <form id="set-resolved-form" action="/admin/set-resolved-user-report" method="post" style="display:none;">
                <input type="hidden" name="user_report_id" id="resolved-report-id">
            </form>
        </div>
    </div>
</div>