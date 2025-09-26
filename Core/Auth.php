<?php

namespace Core;

class Auth
{
    // Check if user is logged in
    public static function check()
    {
        return isset($_SESSION['user_id']);
    }

    // Get the current logged-in user
    public static function user()
    {
        return $_SESSION['user_id'] ?? null;
    }
}
