<?php

require (base_path("views/shared/header.php"));

use Core\Auth;

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
            <?php if (Auth::check()): ?>
                <a href="#" class="btn btn-primary">
                    Start Discovering →
                </a>
                <a href="#" class="btn btn-outline">
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

<?php require (base_path("views/shared/footer.php")) ?>