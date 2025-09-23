<?php

// file path - root/controllers/Auth/LoginController.php

namespace Controllers\Auth;

use Core\App;

class LoginController
{
    public function View()
    {
        $error = '';
        $email = $_POST['email'] ?? '';
        $isLocked = false;
        $isLoading = false;

        if (!empty($email)) {
            $redis = App::resolve('redis');
            $lockKey = "login:lock:email:" . strtolower($email);

            if ($redis->exists($lockKey)) {
                $error = "Too many attempts. Please try again later.";
                $isLocked = true;
            }
        }

        view('auth/login.view.php', compact('error', 'email', 'isLocked', 'isLoading'));
    }

    public function handleLogin()
    {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $isLocked = false;

        $supabase = App::resolve(\Core\Supabase::class);
        $redis = App::resolve('redis');

        // --- Brute force configs ---
        $maxAttempts   = 5;
        $windowSeconds = 900;  
        $lockSeconds   = 300; 

        $emailKey = "login:fail:email:" . $email;
        $lockKey  = "login:lock:email:" . $email;

        // Check if account/email is locked ---
        if ($redis->exists($lockKey)) {
            $ttlLock = $redis->ttl($lockKey);
            error_log("[LOCK] Email '$email' is currently locked. TTL: $ttlLock seconds.");
            $error = "Too many attempts. Please try again later.";
            $isLocked = true;
            view('auth/login.view.php', compact('error', 'email', 'isLocked'));
            return;
        }

        try {
            // Authenticate via Supabase ---
            $response = $supabase->signIn($email, $password);
            error_log("[DEBUG] Supabase response: " . print_r($response, true));

            if (isset($response['error']) || isset($response['msg'])) {
                $attempts = $redis->incr($emailKey);
                if ($attempts == 1) {
                    $redis->expire($emailKey, $windowSeconds);
                }

                if ($attempts >= $maxAttempts) {
                    $redis->setex($lockKey, $lockSeconds, 1);
                }

                $error = $response['msg'] ?? "Invalid email or password.";
                $isLocked = $attempts >= $maxAttempts;

                error_log("[FAILED LOGIN] $email - attempt $attempts. Lock: $isLocked");
                view('auth/login.view.php', compact('error', 'email', 'isLocked'));
                return;
            }

            // If Success -> clear counters ---
            $redis->del([$emailKey, $lockKey]);

            $user = $response['user'] ?? null;
            if ($user) {
                if (empty($user['confirmed_at'])) {
                    $error = "Please confirm your email before logging in.";
                    view('auth/login.view.php', compact('error', 'email', 'isLocked'));
                    return;
                }

                $_SESSION['access_token'] = $response['access_token'] ?? null;
                $_SESSION['user'] = $user;

                header("Location: /u");
                exit;
            }

            $error = "Invalid email or password.";
            view('auth/login.view.php', compact('error', 'email', 'isLocked'));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            error_log("[ERROR] Exception during login: " . $error);
            view('auth/login.view.php', compact('error', 'email', 'isLocked'));
        }
    }
}
