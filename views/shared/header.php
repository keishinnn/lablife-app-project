<?php
$user = false;
$unreadMessages = 3;
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="assets/css/header.css">
    <link rel="stylesheet" href="assets/style.css">
    <link rel="stylesheet" href="assets/css/main.css">
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="navbar-inner">
                    <a href="index.php" class="logo">
                        <img src="public/logo.png" alt="LabLife Logo" class="logo-img">
                        <span class="logo-text">LabLife</span>
                    </a>

                    <?php if ($user): ?>
                        <div class="nav-links">
                            <a href="/matches" class="nav-link">Discover</a>
                            <a href="/matches/list" class="nav-link">Matches</a>
                            <a href="/chat" class="nav-link">
                                Messages
                                <?php if ($unreadMessages > 0): ?>
                                    <span class="badge">(<?= $unreadMessages ?>)</span>
                                <?php endif; ?>
                            </a>
                            <a href="/profile" class="nav-link">Profile</a>
                        </div>
                    <?php endif; ?>

                    <?php if ($user): ?>
                        <button class="btn btn-signout">
                            <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 
                                 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 
                                 3 0 013 3v1" />
                            </svg>
                            Sign Out
                        </button>
                    <?php else: ?>
                        <a href="views/auth/login.php" class="btn btn-signin">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>