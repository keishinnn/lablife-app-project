<?php

// file path - root/controllers/Auth/RegisterController.php

namespace Controllers\Auth;

use Core\App;

class RegisterController
{
    public function View()
    {
        $turnstileConfig = require base_path('config/turnstile.php');
        $siteKey = $turnstileConfig['site_key'];

        $error = $_SESSION['error'] ?? '';
        $message = $_SESSION['message'] ?? '';
        $email = $_SESSION['email'] ?? '';

        unset($_SESSION['error'], $_SESSION['message'], $_SESSION['email']);

        view("auth/register.view.php", compact('error', 'message', 'email', 'siteKey'));
    }

    public function handleRegister()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $turnstileConfig = require base_path('config/turnstile.php');
        $siteKey = $turnstileConfig['site_key'];

        $supabase = App::resolve(\Core\Supabase::class);
        $turnstile = App::resolve(\Core\Turnstile::class);

        try {

            $response = $supabase->signUp($email, $password);

            // DEBUG: log full response
            error_log("Supabase signUp response: " . print_r($response, true));

            // Check for error from Supabase
            if (isset($response['msg']) || isset($response['error'])) {
                $error = $response['msg'] ?? "Something went wrong.";
                view("auth/register.view.php", compact('error', 'email', 'siteKey'));
                return;
            }

            // Successful signup (email confirmation required)
            if (isset($response['user'])) {
                $message = "Account created! Please check your email to confirm your account.";
                view("auth/register.view.php", compact('error', 'message', 'email', 'siteKey'));
                return;
            }

            $_SESSION['email'] = $email;
            header("Location: /register");
            exit;
        } catch (\Exception $e) {
            $error = "Exception: " . $e->getMessage();
            view("auth/register.view.php", compact('error', 'email', 'siteKey'));
        }
    }
}
