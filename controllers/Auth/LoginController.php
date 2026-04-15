<?php

// file path - root/controllers/Auth/LoginController.php

namespace Controllers\Auth;

use Core\App;
use Models\User\User;

class LoginController
{
    public function View()
    {
        \Core\Middleware::redirectAuthUser();

        $error = '';
        $email = $_SESSION['old_email'] ?? '';
        $isLoading = false;
        unset($_SESSION['old_email']);

        view('auth/login.view.php', compact('error', 'email', 'isLoading'));
    }

    public function handleLogin()
    {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $isLoading = true;
        $_SESSION['old_email'] = $email;

        $supabase = App::resolve(\Services\SupabaseService::class);
        $redis = App::resolve('redis');

        // --- Brute force configs ---
        $maxAttempts   = 5;
        $windowSeconds = 900;
        $lockSeconds   = 300;

        $emailKey = "login:fail:email:" . $email;
        $lockKey  = "login:lock:email:" . $email;

        // Check if account/email is locked ---
        if ($redis->exists($lockKey)) {
            $error = "Too many attempts. Please try again in 5 minutes.";
            $isLoading = false;
            view('auth/login.view.php', compact('error', 'email',  'isLoading'));
            return;
        }

        try {
            // Authenticate via Supabase ---
            $response = $supabase->signIn($email, $password);

            if (isset($response['error']) || isset($response['msg']) || isset($response['error_description'])) {
                $attempts = $redis->incr($emailKey);
                if ($attempts == 1) {
                    $redis->expire($emailKey, $windowSeconds);
                }

                if ($attempts >= $maxAttempts) {
                    $redis->setex($lockKey, $lockSeconds, 1);
                }

                $error = $response['msg']
                    ?? $response['error_description']
                    ?? ($response['error']['message'] ?? "Invalid email or password.");

                $isLoading = false;
                view('auth/login.view.php', compact('error', 'email', 'isLoading'));
                return;
            }

            // If Success -> clear counters ---
            $redis->del([$emailKey, $lockKey]);

            $userFetch = $response['user'] ?? null;

            if ($userFetch) {
                if (empty($userFetch['confirmed_at'])) {
                    $error = "Please confirm your email before logging in.";
                    $isLoading = false;
                    view('auth/login.view.php', compact('error', 'email', 'isLoading'));
                    return;
                }

                $user = User::getCurrentUserProfile($userFetch['id']);

                // IF NEW USER, REDIRECT TO CREATING DETAILS OF THE ACCOUNT
                if (!isset($user->avatarUrl)) {
                    session_regenerate_id(true);
                    csrf_token();
                    $_SESSION['access_token'] = $response['access_token'] ?? null;
                    $_SESSION['user_id'] = $user->id;
                    unset($_SESSION['old_email']);
                    $isLoading = false;
                    redirect('/u/setup-profile');
                }

                // OTHERWISE REDIRECT TO HOME VIEW
                User::updateIsOnline($userFetch['id']);
                session_regenerate_id(true);
                csrf_token();
                $_SESSION['access_token'] = $response['access_token'] ?? null;
                $_SESSION['user_id'] = $user->id;
                unset($_SESSION['old_email']);
                $isLoading = false;
                redirect('/u');
            }

            $error = "Invalid email or password.";
            $isLoading = false;
            view('auth/login.view.php', compact('error', 'email',));
        } catch (\Exception $e) {
            app_log_exception($e, 'Login failed');
            $error = generic_error_message();
            $isLoading = false;
            view('auth/login.view.php', compact('error', 'email',  'isLoading'));
        }
    }
}
