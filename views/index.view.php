<?php
include(base_path("views/shared/header.php"));
$is_loggedIn = false;
?>

<main class="hero">
    <section>
        <h1>
            Find Your Perfect
            <span class="gradient-text">LabLife</span>
        </h1>
        <p>Connect with introverts like you with matching interests.</p>

        <div class="btn-group">
            <!-- if user was not authenticated -->
            <?php if ($is_loggedIn): ?>
                <a href="/register" class="btn btn-primary">
                    Start Discovering →
                </a>
                <a href="/login" class="btn btn-outline">
                    View Profile
                </a>
            <?php else: ?>
                <a href="/register" class="btn btn-primary">
                    Get Started →
                </a>
                <a href="/login" class="btn btn-outline">
                    Explore
                </a>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php include(base_path("views/shared/footer.php")); ?>