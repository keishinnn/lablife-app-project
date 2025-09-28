<?php

// root/views/.shared/header.php
use Core\Auth;

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="/assets/global.css">
    <link rel="stylesheet" href="/assets/css/header.css">
    <link rel="stylesheet" href="/assets/css/auth-page.css">
    <link rel="stylesheet" href="/assets/css/index-page.css">
    <link rel="stylesheet" href="/assets/css/profile-page/setup-user.css">
    <link rel="stylesheet" href="/assets/css/profile-page/profile-null.css">
    <link rel="stylesheet" href="/assets/css/profile-page/profile-loading.css">
    <link rel="stylesheet" href="/assets/css/profile-page/profile-page.css">
    <link rel="stylesheet" href="/assets/css/profile-page/edit/profile-edit-page.css">
</head>

<body>

    <header>
        <nav class="navbar">
            <div class="container">
                <div class="navbar-inner">
                    <!-- Logo and LabLife text-->
                    <a href="<?php echo !Auth::check() ? '/' : '/u'; ?>" class="logo">
                        <img src="assets/images/logo.png" alt="LabLife Logo" class="logo-img">
                        <span class="logo-text">LabLife</span>
                    </a>

                    <!-- Show the a tags if user is authenticated -->
                    <?php if (Auth::check()) : ?>
                        <div class="nav-links">
                            <a href="#" class="nav-link">Discover</a>
                            <a href="#" class="nav-link">Matches</a>
                            <a href="#" class="nav-link">
                                Messages
                                <span class="badge"></span>
                            </a>
                            <a href="/u/profile" class="nav-link">Profile</a>
                        </div>
                    <?php endif; ?>

                    <!-- Show the Sign Out button if a user is authenticated, otherwise show Sign In button -->
                    <?php if (Auth::check()): ?>
                        <form action="/logout" method="post">
                            <button class="btn btn-signout" type="submit">
                                <svg class="icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 
                            0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 
                            0 013 3v1" />
                                </svg>
                                Sign Out
                            </button>
                        </form>
                    <?php else: ?>
                        <a href="/login" class="btn btn-signin">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>