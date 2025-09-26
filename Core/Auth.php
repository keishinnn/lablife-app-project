<?php

namespace Core;

class Auth
{
    // Check if user is logged in
    public static function check()
    {
        return isset($_SESSION['user']);
    }

    // Get the current logged-in user
    public static function user()
    {
        return $_SESSION['user'] ?? null;
    }
}
