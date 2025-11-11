<?php

// file path - root/controllers/Auth/RegisterController.php

namespace Controllers\Auth;

use Core\App;

class RegisterController
{
    public function View()
    {
        \Core\Middleware::redirectAuthUser();

        $turnstileConfig = require base_path('config/turnstile.php');
        $siteKey = $turnstileConfig['site_key'];

        $error = $_SESSION['error'] ?? '';
        $email = $_SESSION['email'] ?? '';

        unset($_SESSION['error'], $_SESSION['email']);

        view("auth/register.view.php", compact('error', 'email', 'siteKey'));
    }

    public function handleRegister()
    {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';

        $turnstileConfig = require base_path('config/turnstile.php');
        $siteKey = $turnstileConfig['site_key'];

        $supabase = App::resolve(\Services\SupabaseService::class);
        $turnstile = App::resolve(\Services\TurnstileService::class);

        $turnstileResponse = $_POST['cf-turnstile-response'] ?? '';

        try {

            // Check if user agreed to privacy policy and terms
            if (empty($_POST['agree_privacy'])) {
                $error = "You must agree to our Privacy Policy and Terms of Service before creating an account.";
                view("auth/register.view.php", compact('error', 'email', 'siteKey'));
                return;
            }

            if (!$turnstile->validate($turnstileResponse, $_SERVER['REMOTE_ADDR'])) {
                $error = "Captcha validation failed. Please try again.";
                view("auth/register.view.php", compact('error', 'email', 'siteKey'));
                return;
            }

            if ($supabase->userExists($email)) {
                $error = "Email already exists. Please login instead.";
                view("auth/register.view.php", compact('error', 'email', 'siteKey'));
                return;
            }

            $response = $supabase->signUp($email, $password);

            // DEBUG: log full response
            error_log("Supabase signUp response: " . print_r($response, true));

            if (isset($response['error'])) {
                // Existing user or other error
                $error = $response['error']['message'] ?? "Something went wrong.";
                view("auth/register.view.php", compact('error', 'email', 'siteKey'));
                return;
            }

            // Successful signup (email confirmation required)
            if (isset($response)) {
                $error = "Account created! Please check your email to confirm your account.";
                view("auth/register.view.php", compact('error', 'email', 'siteKey'));
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
