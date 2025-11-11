<div id="user-block-confirmation-modal" tabindex="-1" class="user-block-confirmation-modal">
    <div class="user-block-confirmation-modal-container">
        <div class="user-block-confirmation-modal-content">
            <button type="button" class="user-block-confirmation-modal-close">
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

                <h3 class="user-block-confirmation-modal-title">
                    Are you sure you want to block this user?
                </h3>

                <form method="post" id="user-block-form-submit">
                    <div class="user-block-confirmation-modal-buttons">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        <input type="hidden" id="other_user_id" name="other_user_id" value="">
                        <button type="submit" class="user-block-confirmation-modal-confirm">
                            Yes, block user
                        </button>
                        <button type="button" class="user-block-confirmation-modal-cancel">
                            No, cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>