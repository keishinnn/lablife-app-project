<footer>
    <p>&copy; <?= date('Y') ?> LabLife. All rights reserved.</p>
</footer>

<script type="module" src="/assets/js/register-validation.js"></script>
<script type="module" src="/assets/js/login-loading.js"></script>
<script type="module" src="/assets/js/modals/ptype-modal.js"></script>
<script type="module" src="/assets/js/loading-state/edit-profile-loading.js"></script>
<script type="module" src="/assets/js/modals/hobbies-modal.js"></script>
<script type="module" src="/assets/js/modals/interests-modal.js"></script>
<script type="module" src="/assets/js/loading-state/setup-profile-loading.js"></script>
<script type="module" src="/assets/js/loading-state/profile-nav-loading.js"></script>
<script type="module" src="/assets/js/loading-state/profile-edit-loading.js?v=<?php echo time(); ?>"></script>
<script type="module" src="/assets/js/upload-photo/profile-edit-upload-photo.js"></script>
<script type="module" src="/assets/js/loading-state/edit-preferences-loading.js"></script>

<!-- Cloudflare Turnstile JS -->
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>

<!-- Loader inside modal -->
<div id="pt-loading" class="profile-loading-container">
    <div class="profile-loading-section">
        <div class="profile-loading-icon"></div>
        <p class="profile-loading-text">Saving...</p>
    </div>
</div>


</body>

</html>