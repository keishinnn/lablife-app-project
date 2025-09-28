<?php

namespace Core;

class Auth
{
    // Check if user was logged in
    public static function check()
    {
       return isset($_SESSION['access_token']);
    }

    // Get the current logged-in user
    public static function user()
    {
        return $_SESSION['user_id'] ?? null;
    }
}
