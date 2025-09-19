<?php
$user = false;
$unreadMessages = 3;
$error = "";
$loading = false;
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../../assets/css/header.css">
    <link rel="stylesheet" href="../../assets/style.css">
    <link rel="stylesheet" href="../../assets/css/login-page.css">
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="container">
                <div class="navbar-inner">
                    <!-- Logo and LabLife text-->
                    <a href="index.php" class="logo">
                        <img src="../../public/logo.png" alt="LabLife Logo" class="logo-img">
                        <span class="logo-text">LabLife</span>
                    </a>

                    <!-- Show the a tags if a user is authenticated -->
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

                    <!-- Show the Sign Out button if a user is authenticated, otherwise show Sign In button -->
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
                        <a href="login.php" class="btn btn-signin">Sign In</a>
                    <?php endif; ?>
                </div>
            </div>
        </nav>
    </header>

    <div class="login-page">
        <div class="login-page-container">
            <div class="login-page-text">
                <h1>Create Your Account</h1>
            </div>


            <form action="" method="post">
                <div class="login-email-field">
                    <label for="email">Email</label>
                    <input
                        type="email"
                        name="email"
                        required
                        placeholder="Enter your email">
                </div>

                <div class="login-password-field">
                    <label for="password">Password</label>
                    <input
                        type="password" name="password" id="password"
                        required
                        placeholder="Enter your password">
                </div>

                <?php if ($error): ?>
                    <div class="login-error">
                        <?php echo $error ?>
                    </div>
                <?php endif; ?>

                <?php if ($loading): ?>
                    <button disabled>
                        Loading...
                    </button>
                <?php else: ?>
                    <button>
                        Sign Up
                    </button>
                <?php endif; ?>

                <div class="login-page-redirect">
                    <a href="#">Already have an account? Sign in</a>
                </div>
            </form>
        </div>

    </div>

    <footer>

    </footer>
</body>

</html>