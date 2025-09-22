<?php

namespace Controllers\Auth;

use Core\App;

class LoginController
{
    public function View()
    {
        $error = '';
        $email = '';
        view('auth/login.view.php', compact('error', 'email'));
    }

    public function handleLogin()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $supabase = App::resolve(\Core\Supabase::class);
        $response = $supabase->signIn($email, $password);

        if (!empty($response['access_token'])) {
            $_SESSION['access_token'] = $response['access_token'];
            $_SESSION['user'] = $response['user'] ?? null;
            header("Location: /u");
            exit;
        }

        $error = $response['error_description'] ?? "Login failed";
        view('auth/login.view.php', compact('error', 'email'));
    }
}
