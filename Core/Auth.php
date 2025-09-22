<?php

namespace Core;

class Auth
{
    public static function check()
    {
        return isset($_SESSION['access_token']);
    }

    public static function user()
    {
        if (!self::check()) return null;
        $supabase = App::resolve(Supabase::class);
        return $supabase->getUser($_SESSION['access_token']);
    }
}
