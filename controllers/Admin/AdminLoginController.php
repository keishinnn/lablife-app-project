<?php

namespace Controllers\Admin;

use Core\Database;
use Exception;

class AdminLoginController
{
    public function showLogin()
    {
        view('admin/login.view.php');
    }

    public function login()
    {
        // prevent duplicate session warnings
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $config = require base_path('config/config.php');
        $db = new Database($config['database']);

        $email = trim($_POST['email'] ?? '');
        $password = trim($_POST['password'] ?? '');

        // initialize counters in session if missing
        $_SESSION['failed_login'] = $_SESSION['failed_login'] ?? 0;
        $_SESSION['lockout_until'] = $_SESSION['lockout_until'] ?? 0;

        // check if currently locked out
        if (time() < (int)$_SESSION['lockout_until']) {
            $remaining = (int)$_SESSION['lockout_until'] - time();
            $minutes = floor($remaining / 60);
            $seconds = $remaining % 60;
            $error = "Too many failed attempts. Try again in {$minutes}m {$seconds}s.";
            require base_path('views/admin/login.view.php');
            return;
        }

        // stop if empty
        if (empty($email) || empty($password)) {
            $error = "Please enter your email and password.";
            require base_path('views/admin/login.view.php');
            return;
        }

        // fetch admin from Supabase
        $query = "SELECT * FROM admins WHERE email = :email";
        $stmt = $db->query($query, [':email' => $email]);
        $admin = $stmt->fetch();

        // handle both possible column spellings (password vs passsword)
        $storedPassword = $admin['password'] ?? ($admin['passsword'] ?? null);

        if ($admin && $storedPassword && password_verify($password, $storedPassword)) {
            // successful login: reset counters and harden session
            $_SESSION['failed_login'] = 0;
            $_SESSION['lockout_until'] = 0;

            // regenerate session id to prevent fixation
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_email'] = $admin['email'];

            header('Location: /admin/dashboard');
            exit;
        } else {
            // increment failed attempts
            $_SESSION['failed_login'] += 1;

            // start exponential lockout after 5 failed attempts
            $threshold = 5;
            if ($_SESSION['failed_login'] >= $threshold) {
                $exponent = $_SESSION['failed_login'] - $threshold;
                $baseLock = 60; // 1 minute base
                $lockSeconds = (int)pow(2, $exponent) * $baseLock;
                $maxLock = 24 * 60 * 60; // cap 24 hours
                if ($lockSeconds > $maxLock) {
                    $lockSeconds = $maxLock;
                }
                $_SESSION['lockout_until'] = time() + $lockSeconds;

                $minutes = ceil($lockSeconds / 60);
                $error = "Too many failed attempts. Locked for {$minutes} minute(s).";
            } else {
                sleep(1); // mild delay for early attempts
                $error = "Invalid email or password.";
            }

            require base_path('views/admin/login.view.php');
        }
    }

    public function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        // clear session safely
        $_SESSION = [];
        session_destroy();

        header('Location: /admin/login');
        exit;
    }
}
