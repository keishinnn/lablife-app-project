<?php

namespace Controllers\User\Discover;

use Models\User\User;
use Exception;
use Core\App;

class VerifyController
{

    public function View()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $user = User::getCurrentUserProfile($userId);
        \Core\Middleware::checkIfUserExist($user);

        $isVerified = User::getIsVerified($userId);

        if ($isVerified) {
            view('user/discover/index.view.php', compact('isVerified'));
        } else {
            view('user/discover/verify.next.view.php', compact('isVerified', 'user'));
        }
    }

    public function VerifyView()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $user = User::getCurrentUserProfile($userId);
        \Core\Middleware::checkIfUserExist($user);

        $isVerified = User::getIsVerified($userId);

        if ($isVerified) {
            view('user/discover/index.view.php', compact('isVerified'));
        } else {
            view('user/discover/verify.view.php', compact('isVerified', 'user'));
        }
    }

    public function handleSetVerified()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = \Core\Auth::user();

        try {
            User::setVerified($userId);
            json_response(['success' => true]);
        } catch (Exception $e) {
            app_log_exception($e, 'User verification update failed');
            json_response(['error' => generic_error_message()], 500);
        }
    }

    public function handleIncrementFail()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['verify_fail_count'])) {
            $_SESSION['verify_fail_count'] = 0;
        }
        if (!isset($_SESSION['verify_lock_until'])) {
            $_SESSION['verify_lock_until'] = null;
        }

        if ($_SESSION['verify_lock_until'] && time() < $_SESSION['verify_lock_until']) {
            $remaining = $_SESSION['verify_lock_until'] - time();
            json_response([
                'status' => 'locked',
                'remaining_seconds' => $remaining,
            ]);
        }

        $_SESSION['verify_fail_count']++;

        if ($_SESSION['verify_fail_count'] >= 10) {
            $_SESSION['verify_lock_until'] = time() + (5 * 60);
            $_SESSION['verify_fail_count'] = 0;
            json_response([
                'status' => 'locked',
                'remaining_seconds' => 300,
            ]);
        }

        json_response([
            'status' => 'ok',
            'fail_count' => $_SESSION['verify_fail_count'],
        ]);
    }

    public function getFailStatus()
    {
        \Core\Middleware::auth();
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        json_response([
            'fail_count' => $_SESSION['verify_fail_count'] ?? 0,
            'lock_until' => $_SESSION['verify_lock_until'] ?? null,
            'remaining_seconds' => isset($_SESSION['verify_lock_until']) && time() < $_SESSION['verify_lock_until']
                ? $_SESSION['verify_lock_until'] - time()
                : 0
        ]);
    }

    public function healthCheck()
    {
        \Core\Middleware::auth();

        try {
            $service = App::resolve('Services\IntelligentService');
            $result = $service->health();

            json_response($result['body'], $result['status']);
        } catch (Exception $e) {
            app_log_exception($e, 'Intelligent service health check failed');
            json_response([
                'success' => false,
                'message' => 'Verification service unavailable.',
            ], 503);
        }
    }

    public function handleVerifyFace()
    {
        \Core\Middleware::auth();

        $userId = (string) (\Core\Auth::user() ?? '');
        $user = User::getCurrentUserProfile($userId);
        \Core\Middleware::checkIfUserExist($user);

        $profileUrl = trim((string) ($user->avatarUrl ?? ''));
        $frameFiles = $this->normalizeFrameFiles($_FILES['frames'] ?? null);

        if ($userId === '') {
            json_response(['success' => false, 'message' => 'Unauthorized request.'], 401);
        }

        if ($profileUrl === '') {
            json_response(['success' => false, 'message' => 'Missing profile_url.'], 400);
        }

        if (empty($frameFiles)) {
            json_response(['success' => false, 'message' => 'Missing frames.'], 400);
        }

        try {
            $service = App::resolve('Services\IntelligentService');
            $result = $service->verifyUser($userId, $profileUrl, $frameFiles);

            json_response($result['body'], $result['status']);
        } catch (Exception $e) {
            app_log_exception($e, 'Verification proxy request failed');
            json_response([
                'success' => false,
                'message' => 'Verification service unavailable.',
            ], 503);
        }
    }

    private function normalizeFrameFiles($frames): array
    {
        if (!is_array($frames) || !isset($frames['tmp_name'])) {
            return [];
        }

        if (!is_array($frames['tmp_name'])) {
            return [$frames];
        }

        $normalized = [];
        $count = count($frames['tmp_name']);

        for ($index = 0; $index < $count; $index += 1) {
            $normalized[] = [
                'name' => $frames['name'][$index] ?? "frame{$index}.jpg",
                'type' => $frames['type'][$index] ?? 'image/jpeg',
                'tmp_name' => $frames['tmp_name'][$index] ?? '',
                'error' => $frames['error'][$index] ?? UPLOAD_ERR_NO_FILE,
                'size' => $frames['size'][$index] ?? 0,
            ];
        }

        return array_values(array_filter($normalized, function ($file) {
            return ($file['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_OK;
        }));
    }
}
