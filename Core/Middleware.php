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
            redirect('/login');
        }
    }

    /* 
        If a user was suddenly deleted from the database
        we need to have a function to check if User Object is not null
    */
    public static function checkIfUserExist($user)
    {
        if (!$user) {
            session_destroy();
            redirect('/');
        }
    }

    public static function redirectAuthUser()
    {
        if (Auth::check()) {
            redirect('/u');
        }
        return;
    }

    public static function checkNotSetProfile($user)
    {
        if (Auth::check()) {
            if (empty($user->avatarUrl) || empty($user->bio)) {
                redirect('/u/setup-profile');
            }
        }
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
                if (is_json_request()) {
                    json_response(['error' => 'Invalid CSRF token.'], 403);
                }

                http_response_code(403);
                exit('CSRF token validation failed');
            }
        }
    }
}
