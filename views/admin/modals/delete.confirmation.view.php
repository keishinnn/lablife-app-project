<div id="delete-confirmation-modal" class="delete-block-confirmation-modal">
  <div class="user-block-confirmation-modal-container">
    <div class="user-block-confirmation-modal-content">
      <button type="button" id="close-awaiting-bug-report-modal-btn" class="user-block-confirmation-modal-close">
        <svg class="user-block-confirmation-modal-close-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
        </svg>
      </button>

      <div class="user-block-confirmation-modal-body">
        <svg class="user-block-confirmation-modal-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 20 20">
          <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M10 11V6m0 8h.01M19 10a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>

        <h3 class="user-block-confirmation-modal-title">Are you sure you want to delete this Bug Report?</h3>

        <div style="display:flex; margin:1rem 0 2rem 1rem;">
          <input type="checkbox" id="remember-awaiting-bug-report-decision" style="margin-right:1rem;">
          <label for="remember-decision">Remember my decision</label>
        </div>

        <div class="delete-confirmation-modal-buttons">
          <button type="button" id="confirm-delete-awaiting-bug-report-btn" class="user-block-confirmation-modal-confirm">Confirm</button>
          <button type="button" id="cancel-delete-awaiting-bug-report-btn" class="user-block-confirmation-modal-cancel">Cancel</button>
        </div>
      </div>
    </div>
  </div>
</div>
