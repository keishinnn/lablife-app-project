<?php

// file path = root/Views/user/discover/index.view.php
require base_path('views/shared/header.php');
?>

<main class="hero">
    <section id="hero-section">
        <h1 style="margin-bottom: 3rem">
            Find Your Match
        </h1>

        <div style="text-align: center;">
            <form action="/u/discover/find-match" method="POST">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                <button type="submit" class="btn btn-primary">
                    Start Finding →
                </button>
            </form>

        </div>

    </section>
</main>


<?php require base_path('views/shared/footer.php') ?>