<?php

// file path - root/controllers/Auth/LoginController.php

namespace Controllers\Auth;

use Core\App;
use PDO;

class LoginController
{
    public function View()
    {
        \Core\Middleware::redirectAuthUser();

        $error = '';
        $email = $_POST['email'] ?? '';
        $isLoading = false;

        if (!empty($email)) {
            $redis = App::resolve('redis');
            $lockKey = "login:lock:email:" . strtolower($email);

            if ($redis->exists($lockKey)) {
                $error = "Too many attempts. Please try again later.";
            }
        }

        view('auth/login.view.php', compact('error', 'email', 'isLoading'));
    }

    public function handleLogin()
    {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $isLoading = true;

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
            $ttlLock = $redis->ttl($lockKey);
            error_log("[LOCK] Email '$email' is currently locked. TTL: $ttlLock seconds.");
            $error = "Too many attempts. Please try again in 5 minutes.";
            $isLoading = false;
            view('auth/login.view.php', compact('error', 'email',  'isLoading'));
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

                $isLoading = false;
                view('auth/login.view.php', compact('error', 'email', 'isLoading'));
                return;
            }

            // If Success -> clear counters ---
            $redis->del([$emailKey, $lockKey]);

            $user = $response['user'] ?? null;
            if ($user) {
                if (empty($user['confirmed_at'])) {
                    $error = "Please confirm your email before logging in.";
                    $isLoading = false;
                    view('auth/login.view.php', compact('error', 'email', 'isLoading'));
                    return;
                }

                // IF NEW USER, REDIRECT TO CREATING DETAILS OF THE ACCOUNT
                $db = App::resolve('Core\Database');
                $stmt = $db->query("SELECT * FROM users WHERE id = :id LIMIT 1", ['id' => $user['id']]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!isset($user['avatar_url'])) {
                    $_SESSION['access_token'] = $response['access_token'] ?? null;
                    $_SESSION['user'] = $user;
                    header("Location: /u/setup-profile");
                    exit;
                }

                // OTHERWISE REDIRECT TO HOME VIEW
                $_SESSION['access_token'] = $response['access_token'] ?? null;
                $_SESSION['user'] = $user;
                $isLoading = false;
                header("Location: /u");
                exit;
            }

            $error = "Invalid email or password.";
            $isLoading = false;
            view('auth/login.view.php', compact('error', 'email',));
        } catch (\Exception $e) {
            $error = $e->getMessage();
            error_log("[ERROR] Exception during login: " . $error);
            $isLoading = false;
            view('auth/login.view.php', compact('error', 'email',  'isLoading'));
        }
    }
}
