<div id="user-control-modal" class="select-modal-overlay" tabindex="-1" aria-hidden="true">
    <div class="select-modal-container">
        <!-- Modal content -->
        <div class="select-modal-content">
            <!-- Modal header -->
            <div class="select-modal-header">
                <h3>User Controls</h3>
                <button type="button" class="close-user-control-modal-button" data-modal-toggle="select-modal">
                    <svg class="select-modal-close-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                    <span style="display: none;">Close modal</span>
                </button>
            </div>

            <!-- Modal body -->
            <div class="select-modal-body">
                <p>Select an action:</p>

                <ul class="select-modal-list">
                    <li class="select-modal-list-item">
                        <input type="radio" id="job-2" name="user-control-type" value="report-user" class="hidden">
                        <label for="job-2" class="select-modal-radio-label">
                            <div class="select-modal-label-text">
                                <div class="select-modal-label-title">Report User</div>
                                <div class="select-modal-label-subtitle">Report for inappropriate behavior.</div>
                            </div>
                            <svg class="select-modal-label-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 14 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
                            </svg>
                        </label>
                    </li>

                    <li class="select-modal-list-item">
                        <input type="radio" id="job-3" name="user-control-type" value="block-user" class="hidden">
                        <label for="job-3" class="select-modal-radio-label">
                            <div class="select-modal-label-text">
                                <div class="select-modal-label-title">Block User</div>
                                <div class="select-modal-label-subtitle">Permanently block this user.</div>
                            </div>
                            <svg class="select-modal-label-icon" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="none" viewBox="0 0 14 10">
                                <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round"
                                    stroke-width="2" d="M1 5h12m0 0L9 1m4 4L9 9" />
                            </svg>
                        </label>
                    </li>
                </ul>

                <button class="select-modal-submit-button">
                    Next
                </button>
            </div>
        </div>
    </div>
</div>