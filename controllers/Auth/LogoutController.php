<?php

namespace Controllers\Auth;

use Models\User\User;
use Core\Auth;

class LogoutController
{
    public function logout()
    {
        \Core\Middleware::auth();

        // Start session if not started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        User::updateIsOffline(Auth::user());

        // Clear session data
        $_SESSION = [];
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params["path"],
                $params["domain"],
                $params["secure"],
                $params["httponly"]
            );
        }

        session_destroy();

        // Redirect back to home
        header("Location: /");
        exit;
    }
}
