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
            <a href="u/discover" class="btn btn-primary" id="index-nav-discover">
                Start Discovering →
            </a>
            <a href="/u/profile" class="btn btn-outline nav-profile" id="index-nav-profile">
                View Profile
            </a>
        </div>
    </section>
</main>

<?php require base_path('views/shared/footer.php') ?>