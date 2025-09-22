<?php

if (!\Core\Auth::check()) {
    header("Location: /login");
    exit;
}

$user = \Core\Auth::user();

require base_path('views/shared/header.php');
?>

<main class="hero">
    <section>
        <h1>
            Find Your Perfect
            <span class="gradient-text">LabLife</span>
        </h1>
        <p>Connect with introverts like you with matching interests.</p>

        <div class="btn-group">
            <a href="/register" class="btn btn-primary">
                Start Discovering →
            </a>
            <a href="/login" class="btn btn-outline">
                View Profile
            </a>
        </div>
    </section>
</main>

<?php require base_path('views/shared/footer.php') ?>