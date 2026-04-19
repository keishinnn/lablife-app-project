<?php

// file path - root/controllers/Auth/LoginController.php

namespace Controllers\Auth;

use Core\App;
use Models\User\User;
use Services\TurnstileService;

class LoginController
{
    private const EMAIL_MAX_ATTEMPTS = 5;
    private const EMAIL_WINDOW_SECONDS = 900;
    private const EMAIL_LOCK_SECONDS = 300;
    private const IP_MAX_ATTEMPTS = 12;
    private const IP_WINDOW_SECONDS = 900;
    private const IP_LOCK_SECONDS = 600;
    private const CAPTCHA_THRESHOLD = 3;

    public function View()
    {
        \Core\Middleware::redirectAuthUser();

        $error = '';
        $email = $_SESSION['old_email'] ?? '';
        $isLoading = false;
        $siteKey = $this->getTurnstileSiteKey();
        $requireCaptcha = false;
        unset($_SESSION['old_email']);

        try {
            $redis = App::resolve('redis');
            $requireCaptcha = $this->shouldRequireCaptcha($redis, $email, $this->getClientIp());
        } catch (\Throwable $e) {
            app_log_exception($e, 'Failed to resolve login captcha requirement');
        }

        view('auth/login.view.php', compact('error', 'email', 'isLoading', 'siteKey', 'requireCaptcha'));
    }

    public function handleLogin()
    {
        $email = strtolower(trim($_POST['email'] ?? ''));
        $password = $_POST['password'] ?? '';
        $isLoading = true;
        $_SESSION['old_email'] = $email;
        $siteKey = $this->getTurnstileSiteKey();

        $supabase = App::resolve(\Services\SupabaseService::class);
        $redis = App::resolve('redis');
        $turnstile = App::resolve(TurnstileService::class);
        $clientIp = $this->getClientIp();

        $emailKey = "login:fail:email:" . $email;
        $emailLockKey  = "login:lock:email:" . $email;
        $ipKey = "login:fail:ip:" . $clientIp;
        $ipLockKey = "login:lock:ip:" . $clientIp;
        $requireCaptcha = $this->shouldRequireCaptcha($redis, $email, $clientIp);

        if ($requireCaptcha) {
            $turnstileResponse = trim($_POST['cf-turnstile-response'] ?? '');
            if (!$turnstile->validate($turnstileResponse, $clientIp)) {
                $this->recordFailedLoginAttempt($redis, $emailKey, $emailLockKey, $ipKey, $ipLockKey);
                $error = "Security verification failed. Please try again.";
                $isLoading = false;
                $requireCaptcha = true;
                view('auth/login.view.php', compact('error', 'email', 'isLoading', 'siteKey', 'requireCaptcha'));
                return;
            }
        }

        if ($redis->exists($emailLockKey)) {
            $error = "Too many attempts. Please try again in 5 minutes.";
            $isLoading = false;
            $requireCaptcha = true;
            view('auth/login.view.php', compact('error', 'email',  'isLoading', 'siteKey', 'requireCaptcha'));
            return;
        }

        if ($redis->exists($ipLockKey)) {
            $error = "Too many sign-in attempts from this connection. Please try again in a few minutes.";
            $isLoading = false;
            $requireCaptcha = true;
            view('auth/login.view.php', compact('error', 'email',  'isLoading', 'siteKey', 'requireCaptcha'));
            return;
        }

        try {
            $response = $supabase->signIn($email, $password);

            if (isset($response['error']) || isset($response['msg']) || isset($response['error_description'])) {
                $this->recordFailedLoginAttempt($redis, $emailKey, $emailLockKey, $ipKey, $ipLockKey);

                $error = $response['msg']
                    ?? $response['error_description']
                    ?? ($response['error']['message'] ?? "Invalid email or password.");

                $isLoading = false;
                $requireCaptcha = true;
                view('auth/login.view.php', compact('error', 'email', 'isLoading', 'siteKey', 'requireCaptcha'));
                return;
            }

            $redis->del([$emailKey, $emailLockKey, $ipKey, $ipLockKey]);

            $userFetch = $response['user'] ?? null;

            if ($userFetch) {
                if (empty($userFetch['confirmed_at'])) {
                    $error = "Please confirm your email before logging in.";
                    $isLoading = false;
                    view('auth/login.view.php', compact('error', 'email', 'isLoading', 'siteKey', 'requireCaptcha'));
                    return;
                }

                $user = User::getCurrentUserProfile($userFetch['id']);
                User::updateIsOnline($userFetch['id']);

                if (!isset($user->avatarUrl)) {
                    session_regenerate_id(true);
                    csrf_token();
                    $_SESSION['access_token'] = $response['access_token'] ?? null;
                    $_SESSION['user_id'] = $user->id;
                    unset($_SESSION['old_email']);
                    $isLoading = false;
                    redirect('/u/setup-profile');
                }

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
            $requireCaptcha = true;
            view('auth/login.view.php', compact('error', 'email', 'isLoading', 'siteKey', 'requireCaptcha'));
        } catch (\Exception $e) {
            app_log_exception($e, 'Login failed');
            $error = generic_error_message();
            $isLoading = false;
            $requireCaptcha = $this->shouldRequireCaptcha($redis, $email, $clientIp);
            view('auth/login.view.php', compact('error', 'email',  'isLoading', 'siteKey', 'requireCaptcha'));
        }
    }

    private function recordFailedLoginAttempt($redis, string $emailKey, string $emailLockKey, string $ipKey, string $ipLockKey): void
    {
        $emailAttempts = $redis->incr($emailKey);
        if ($emailAttempts === 1) {
            $redis->expire($emailKey, self::EMAIL_WINDOW_SECONDS);
        }

        if ($emailAttempts >= self::EMAIL_MAX_ATTEMPTS) {
            $redis->setex($emailLockKey, self::EMAIL_LOCK_SECONDS, 1);
        }

        $ipAttempts = $redis->incr($ipKey);
        if ($ipAttempts === 1) {
            $redis->expire($ipKey, self::IP_WINDOW_SECONDS);
        }

        if ($ipAttempts >= self::IP_MAX_ATTEMPTS) {
            $redis->setex($ipLockKey, self::IP_LOCK_SECONDS, 1);
        }
    }

    private function shouldRequireCaptcha($redis, string $email, string $clientIp): bool
    {
        $emailFailures = $email !== '' ? (int) ($redis->get("login:fail:email:" . $email) ?? 0) : 0;
        $ipFailures = (int) ($redis->get("login:fail:ip:" . $clientIp) ?? 0);

        return $emailFailures >= self::CAPTCHA_THRESHOLD || $ipFailures >= self::CAPTCHA_THRESHOLD;
    }

    private function getTurnstileSiteKey(): string
    {
        $turnstileConfig = require base_path('config/turnstile.php');
        return $turnstileConfig['site_key'] ?? '';
    }

    private function getClientIp(): string
    {
        $candidates = [
            $_SERVER['HTTP_CF_CONNECTING_IP'] ?? null,
            $_SERVER['HTTP_X_FORWARDED_FOR'] ?? null,
            $_SERVER['REMOTE_ADDR'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            if (!$candidate) {
                continue;
            }

            $ip = trim(explode(',', $candidate)[0]);
            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                return $ip;
            }
        }

        return 'unknown';
    }
}
