<?php
// user.report.view.php
// Minimal view partial for the multi-step report popup.
// Included by your messages index view (only include once).
?>

<div id="user-report-modal" class="select-modal-overlay" tabindex="-1" aria-hidden="true" style="display:none;">
  <div class="select-modal-container">
    <div class="select-modal-content">
      <div class="select-modal-header">
        <h3>Report User</h3>
        <button type="button" class="close-user-report-modal-button" data-modal-toggle="user-report-modal">
          <svg class="select-modal-close-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
            <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
          </svg>
          <span style="display:none;">Close modal</span>
        </button>
      </div>

      <div class="select-modal-body">
        <!-- Steps wrapper -->
        <div id="report-steps">

          <!-- Step 1: Choose Category -->
          <div class="report-step" data-step="1">
            <p class="report-step-title">What are you reporting?</p>
            <div id="report-categories-container" class="report-options-list">
              <!-- populated by JS -->
              <p class="loading-text">Loading options...</p>
            </div>
            <div class="report-step-actions">
              <button class="report-next-button" data-next-step="2">Next</button>
            </div>
          </div>

          <!-- Step 2: Choose Reason (skipped if category has no reasons) -->
          <div class="report-step" data-step="2" style="display:none;">
            <p class="report-step-title">Choose the reason</p>
            <div id="report-reasons-container" class="report-options-list">
              <!-- populated by JS -->
            </div>
            <div class="report-step-actions">
              <button class="report-back-button" data-prev-step="1">Back</button>
              <button class="report-next-button" data-next-step="3">Next</button>
            </div>
          </div>


          <!-- Step 3: Confirmation -->
          <div class="report-step" data-step="3" style="display:none;">
            <p class="report-step-title">Review report</p>
            <div id="report-summary">
              <!-- populated by JS -->
            </div>
            <div class="report-step-actions">
              <button class="report-back-button" data-prev-step="3">Back</button>
              <button id="report-submit-button" class="report-submit-button">Submit Report</button>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>

  <!-- Hidden fields pulled from index view -->
  <input type="hidden" id="other_user_id" name="other_user_id" value="">
  <input type="hidden" id="csrf_token_input" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
</div>
