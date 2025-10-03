<?php
require(base_path("views/shared/header.php"));
?>
<div class="profile-null-container">
    <div class="profile-null-section">
        <div class="profile-null-symbol">
            <span class="profile-null-text">❌</span>
        </div>
        <h2>Profile not found</h2>
        <p class="profile-null-error-text"><?php echo $error || "Unable to load your profile. Please try again." ?></p>
        <a href="u/profile" class="profile-null-button">Retry</a>
    </div>
</div>
<?php require(base_path("views/shared/footer.php")) ?>