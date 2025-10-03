<?php

// file path = root/Views/user/index.view.php

require base_path('views/shared/header.php');
?>

<main class="hero">
    <section id="hero-section">
        <h1>
            Find Your Perfect
            <span class="gradient-text">LabLife Match</span>
        </h1>
        <p>Connect with fellow introverts who share your passions and interests.</p>

        <div class="btn-group">
            <a href="#" class="btn btn-primary">
                Start Discovering →
            </a>
            <a href="/u/profile" class="btn btn-outline nav-profile">
                View Profile
            </a>
        </div>
    </section>

    <!-- Loader to replace section -->
    <div id="pf-loading" class="profile-get-loading-container">
        <div class="profile-get-loading-section">
            <div class="profile-get-loading-icon"></div>
            <p class="profile-get-loading-text">Loading profile...</p>
        </div>
    </div>
</main>


<?php require base_path('views/shared/footer.php') ?>