<?php

namespace Core;

use Services\SupabaseService;

class Auth
{
    // Check if user was logged in
    public static function check()
    {
        return isset($_SESSION['user_id']) && (isset($_SESSION['access_token']) || isset($_SESSION['refresh_token']));
    }

    // Get the current logged-in user
    public static function user()
    {
        return $_SESSION['user_id'] ?? null;
    }

    public static function getAccessToken()
    {
        return $_SESSION['access_token'] ?? null;
    }

    public static function getRefreshToken()
    {
        return $_SESSION['refresh_token'] ?? null;
    }

    public static function getValidAccessToken(int $minimumTtlSeconds = 60)
    {
        $accessToken = self::getAccessToken();

        if ($accessToken && !self::tokenExpiresSoon($accessToken, $minimumTtlSeconds)) {
            return $accessToken;
        }

        $refreshToken = self::getRefreshToken();
        if (!$refreshToken) {
            return $accessToken;
        }

        try {
            /** @var SupabaseService $supabase */
            $supabase = App::resolve(SupabaseService::class);
            $session = $supabase->refreshSession($refreshToken);

            $newAccessToken = $session['access_token'] ?? null;
            $newRefreshToken = $session['refresh_token'] ?? null;

            if ($newAccessToken) {
                $_SESSION['access_token'] = $newAccessToken;
            }

            if ($newRefreshToken) {
                $_SESSION['refresh_token'] = $newRefreshToken;
            }

            return $_SESSION['access_token'] ?? $accessToken;
        } catch (\Throwable $e) {
            app_log_exception($e, 'Failed to refresh Supabase access token');
            return $accessToken;
        }
    }

    private static function tokenExpiresSoon(string $token, int $minimumTtlSeconds): bool
    {
        $payload = self::decodeJwtPayload($token);
        $expiresAt = is_array($payload) ? ($payload['exp'] ?? null) : null;

        if (!is_numeric($expiresAt)) {
            return false;
        }

        return ((int) $expiresAt - time()) <= $minimumTtlSeconds;
    }

    private static function decodeJwtPayload(string $token): ?array
    {
        $parts = explode('.', $token);
        if (count($parts) < 2) {
            return null;
        }

        $payload = strtr($parts[1], '-_', '+/');
        $padding = strlen($payload) % 4;
        if ($padding > 0) {
            $payload .= str_repeat('=', 4 - $padding);
        }

        $decoded = base64_decode($payload, true);
        if ($decoded === false) {
            return null;
        }

        $data = json_decode($decoded, true);
        return is_array($data) ? $data : null;
    }
}
