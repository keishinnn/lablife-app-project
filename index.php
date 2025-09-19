<?php
include("includes/header.php");
$is_loggedIn = false;
?>

<section>
    <h1>
        Find Your Perfect
        <span class="gradient-text">LabLife</span>
    </h1>
    <p>Connect with introverts like you with matching interests.</p>

    <div class="btn-group">
        <!-- if user was not authenticated -->
        <?php if ($is_loggedIn): ?>
            <a href="/matches.html" class="btn btn-primary">
                Start Discovering →
            </a>
            <a href="/profile.html" class="btn btn-outline">
                View Profile
            </a>
        <?php else: ?>
            <a href="/views/login.php" class="btn btn-primary">
                Get Started →
            </a>
            <a href="/profile.html" class="btn btn-outline">
                Explore
            </a>
        <?php endif; ?>
    </div>
</section>
<?php include("includes/footer.php") ?>