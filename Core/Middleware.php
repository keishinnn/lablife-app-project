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
}
