<?php

// file path = root/Core/Middleware.php

namespace Core;

use Core\Auth;
use Models\User;


class Middleware
{
    public static function auth()
    {
        if (!Auth::check()) {
            header("Location: /login");
            exit;
        }
        return;
    }

    public static function redirectAuthUser()
    {
        if (Auth::check()) {
            header("Location: /u");
            exit;
        }
        return;
    }

    public static function checkNotSetProfile()
    {
        if (Auth::check()) {
            if (!isset($user->avatarUrl)) {
                header('Location: /u/setup-profile');
                exit;
            }
        }
        return;
    }

    public static function verifyCSRFToken()
    {
        // Only check for state-changing requests
        if (
            $_SERVER['REQUEST_METHOD'] === 'POST' ||
            $_SERVER['REQUEST_METHOD'] === 'PUT' ||
            $_SERVER['REQUEST_METHOD'] === 'DELETE'
        ) {

            // Token from form input
            $formToken = $_POST['csrf_token'] ?? null;

            // Token from AJAX header
            $headers = getallheaders();
            $headerToken = $headers['X-CSRF-Token'] ?? null;

            // Compare against session
            $sessionToken = $_SESSION['csrf_token'] ?? null;

            if (
                !$sessionToken ||
                !(
                    ($formToken && hash_equals($sessionToken, $formToken)) ||
                    ($headerToken && hash_equals($sessionToken, $headerToken))
                )
            ) {
                // If invalid, stop request
                http_response_code(403);
                die("CSRF token validation failed");
            }
        }
    }
}
