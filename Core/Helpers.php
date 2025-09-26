<?php

use \Core\App;

function base_path($path)
{
    return BASE_PATH . $path;
}

function view($path, $data = [])
{
    extract($data);
    require base_path('views/' . $path);
}

/* function authorize($condition, $status = 403)
{
    if (!$condition) {
        abort($status);
    }

    return true;
} */

function current_user()
{
    // session must be started already
    if (isset($_SESSION['user']) && $_SESSION['user']) {
        return $_SESSION['user'];
    }

    if (!empty($_SESSION['access_token'])) {
        try {
            $supabase = App::resolve(\Core\Supabase::class); // adjust class namespace/name
            $resp = $supabase->getUser($_SESSION['access_token']); // should return ['user'=>...]
            if (!empty($resp['user'])) {
                // cache in session to avoid network call every request
                $_SESSION['user'] = $resp['user'];
                return $resp['user'];
            }
        } catch (Throwable $e) {
            // fail silently; return null
            return null;
        }
    }

    return null;
}
