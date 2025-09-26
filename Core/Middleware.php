<?php

// file path = root/Core/Middleware.php

namespace Core;

use Core\Auth;

class Middleware
{
    public static function auth()
    {
        if (!Auth::check()) {
            header("Location: /login");
            exit;
        }
    }

    public static function redirectAuthUser()
    {
        if (Auth::check()) {
            header("Location: /u");
            exit;
        }
    }
}
