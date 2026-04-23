<?php

namespace Core;

class RateLimiter
{
    private const DEFAULT_RULE = [
        'max_attempts' => 20,
        'window_seconds' => 60,
        'message' => 'Too many requests. Please wait a moment and try again.',
    ];

    private const ROUTE_RULES = [
        '/u/submit-setup' => [
            'max_attempts' => 8,
            'window_seconds' => 900,
            'message' => 'You have submitted setup too many times. Please wait a moment and try again.',
            'redirect_to' => '/u/setup-profile',
            'flash_key' => 'setup_error',
        ],
        '/u/submit-finish-setup' => [
            'max_attempts' => 8,
            'window_seconds' => 900,
            'message' => 'You have submitted setup preferences too many times. Please wait a moment and try again.',
            'redirect_to' => '/u/setup-profile-preferences',
            'flash_key' => 'setup_preferences_error',
        ],
        '/u/submit-edit-profile' => [
            'max_attempts' => 12,
            'window_seconds' => 900,
            'message' => 'You have updated your profile too many times. Please wait a moment and try again.',
            'redirect_to' => '/u/profile-edit',
            'flash_key' => 'profile_edit_error',
        ],
        '/u/submit-edit-avatar' => [
            'max_attempts' => 10,
            'window_seconds' => 900,
            'message' => 'You have uploaded your avatar too many times. Please wait a moment and try again.',
        ],
        '/u/submit-edit-preferences' => [
            'max_attempts' => 12,
            'window_seconds' => 900,
            'message' => 'You have updated your preferences too many times. Please wait a moment and try again.',
            'redirect_to' => '/u/profile-preferences-edit',
            'flash_key' => 'profile_preferences_error',
        ],
        '/u/save-personality' => [
            'max_attempts' => 15,
            'window_seconds' => 900,
            'message' => 'You have updated your personality type too many times. Please wait a moment and try again.',
            'redirect_to' => '/u/profile',
            'flash_key' => 'profile_modal_feedback',
            'flash_modal' => 'personality',
        ],
        '/u/save-hobbies' => [
            'max_attempts' => 15,
            'window_seconds' => 900,
            'message' => 'You have updated your hobbies too many times. Please wait a moment and try again.',
            'redirect_to' => '/u/profile',
            'flash_key' => 'profile_modal_feedback',
            'flash_modal' => 'hobbies',
        ],
        '/u/save-interests' => [
            'max_attempts' => 15,
            'window_seconds' => 900,
            'message' => 'You have updated your interests too many times. Please wait a moment and try again.',
            'redirect_to' => '/u/profile',
            'flash_key' => 'profile_modal_feedback',
            'flash_modal' => 'interests',
        ],
        '/u/set-online' => [
            'max_attempts' => 120,
            'window_seconds' => 300,
            'message' => 'Too many presence updates. Please wait a moment and try again.',
        ],
        '/u/set-offline' => [
            'max_attempts' => 120,
            'window_seconds' => 300,
            'message' => 'Too many presence updates. Please wait a moment and try again.',
        ],
        '/u/discover/find-match' => [
            'max_attempts' => 40,
            'window_seconds' => 300,
            'message' => 'Too many match search requests. Please wait a moment and try again.',
        ],
        '/u/discover/start-search' => [
            'max_attempts' => 40,
            'window_seconds' => 300,
            'message' => 'Too many match search requests. Please wait a moment and try again.',
        ],
        '/u/discover/stop-search' => [
            'max_attempts' => 50,
            'window_seconds' => 300,
            'message' => 'Too many match search updates. Please wait a moment and try again.',
        ],
        '/u/discover/set-search-in-match' => [
            'max_attempts' => 50,
            'window_seconds' => 300,
            'message' => 'Too many search state updates. Please wait a moment and try again.',
        ],
        '/u/discover/set-search-expired' => [
            'max_attempts' => 60,
            'window_seconds' => 300,
            'message' => 'Too many search state updates. Please wait a moment and try again.',
        ],
        '/u/discover/set-search-active' => [
            'max_attempts' => 50,
            'window_seconds' => 300,
            'message' => 'Too many search state updates. Please wait a moment and try again.',
        ],
        '/u/discover/like' => [
            'max_attempts' => 25,
            'window_seconds' => 60,
            'message' => 'Too many reactions sent. Please slow down and try again.',
        ],
        '/u/discover/dislike' => [
            'max_attempts' => 25,
            'window_seconds' => 60,
            'message' => 'Too many reactions sent. Please slow down and try again.',
        ],
        '/u/discover/set-expired-session' => [
            'max_attempts' => 40,
            'window_seconds' => 300,
            'message' => 'Too many match session updates. Please wait a moment and try again.',
        ],
        '/u/discover/set-rejected-session' => [
            'max_attempts' => 40,
            'window_seconds' => 300,
            'message' => 'Too many match session updates. Please wait a moment and try again.',
        ],
        '/u/discover/set-matched-session' => [
            'max_attempts' => 40,
            'window_seconds' => 300,
            'message' => 'Too many match session updates. Please wait a moment and try again.',
        ],
        '/u/video/get-video-token' => [
            'max_attempts' => 30,
            'window_seconds' => 300,
            'message' => 'Too many video session requests. Please wait a moment and try again.',
        ],
        '/u/video/initiate-video-call' => [
            'max_attempts' => 10,
            'window_seconds' => 300,
            'message' => 'Too many call attempts. Please wait a moment before trying again.',
        ],
        '/u/video/receive-call' => [
            'max_attempts' => 20,
            'window_seconds' => 300,
            'message' => 'Too many call responses. Please wait a moment and try again.',
        ],
        '/u/video/end-video-call' => [
            'max_attempts' => 30,
            'window_seconds' => 300,
            'message' => 'Too many call updates. Please wait a moment and try again.',
        ],
        '/u/matches/create-channel' => [
            'max_attempts' => 20,
            'window_seconds' => 300,
            'message' => 'Too many chat channel requests. Please wait a moment and try again.',
        ],
        '/u/report/submit' => [
            'max_attempts' => 8,
            'window_seconds' => 3600,
            'message' => 'Too many reports submitted. Please wait before sending another one.',
        ],
        '/u/block-other-user' => [
            'max_attempts' => 10,
            'window_seconds' => 300,
            'message' => 'Too many block requests. Please wait a moment and try again.',
        ],
        '/u/unblock-other-user' => [
            'max_attempts' => 10,
            'window_seconds' => 300,
            'message' => 'Too many unblock requests. Please wait a moment and try again.',
        ],
        '/u/account/set-verified' => [
            'max_attempts' => 5,
            'window_seconds' => 3600,
            'message' => 'Too many verification updates. Please wait and try again later.',
        ],
        '/u/account/increment-fail' => [
            'max_attempts' => 30,
            'window_seconds' => 3600,
            'message' => 'Too many verification attempts. Please wait and try again later.',
        ],
        '/u/account/verify-face' => [
            'max_attempts' => 10,
            'window_seconds' => 3600,
            'message' => 'Too many face verification attempts. Please wait and try again later.',
        ],
    ];

    public static function enforceAuthenticatedUserSubmissionLimit(): void
    {
        if (strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            return;
        }

        $path = current_path();

        if (!str_starts_with($path, '/u')) {
            return;
        }

        if (!Auth::check()) {
            return;
        }

        $rule = self::resolveRule($path);

        try {
            $redis = App::resolve('redis');
            $userId = (string) Auth::user();
            $key = self::buildKey($userId, $path);

            $attempts = (int) $redis->incr($key);
            if ($attempts === 1) {
                $redis->expire($key, (int) $rule['window_seconds']);
            }

            if ($attempts <= (int) $rule['max_attempts']) {
                return;
            }

            $retryAfter = (int) $redis->ttl($key);
            if ($retryAfter < 1) {
                $retryAfter = (int) $rule['window_seconds'];
            }

            self::respondRateLimited($rule, (string) $rule['message'], $retryAfter);
        } catch (\Throwable $e) {
            app_log_exception($e, 'Authenticated user rate limit failed');
        }
    }

    private static function resolveRule(string $path): array
    {
        return self::ROUTE_RULES[$path] ?? self::DEFAULT_RULE;
    }

    private static function buildKey(string $userId, string $path): string
    {
        return sprintf('rate_limit:user:%s:%s', $userId, sha1($path));
    }

    private static function respondRateLimited(array $rule, string $message, int $retryAfter): void
    {
        http_response_code(429);
        header('Retry-After: ' . $retryAfter);

        $secFetchMode = strtolower((string) ($_SERVER['HTTP_SEC_FETCH_MODE'] ?? ''));
        $expectsStructuredResponse = is_json_request() || ($secFetchMode !== '' && $secFetchMode !== 'navigate');

        if ($expectsStructuredResponse) {
            json_response([
                'success' => false,
                'error' => $message,
                'message' => $message,
                'retry_after' => $retryAfter,
            ], 429);
        }

        $redirectTo = (string) ($rule['redirect_to'] ?? '');
        $flashKey = (string) ($rule['flash_key'] ?? '');
        $flashModal = (string) ($rule['flash_modal'] ?? '');

        if ($redirectTo !== '' && $flashKey !== '') {
            $flashValue = $message;

            if ($flashModal !== '') {
                $flashValue = [
                    'message' => $message,
                    'modal' => $flashModal,
                ];
            }

            session_flash_set($flashKey, $flashValue);
            redirect($redirectTo);
        }

        $retryAfter = max(1, $retryAfter);
        $message = $message;
        require base_path('Views/429.php');
        exit;
    }
}
