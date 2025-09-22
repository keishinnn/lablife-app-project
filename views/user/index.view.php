<?php

require ROOT . "/middleware/auth.php";

if (!defined('ROOT')) {
    die('Direct access not allowed.');
}

include(ROOT . "/views/shared/header.php");
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
                <a href="/lablife-app-project/register" class="btn btn-primary">
                    Get Started →
                </a>
                <a href="/lablife-app-project/login" class="btn btn-outline">
                    Explore
                </a>
        </div>
    </section>
</main>