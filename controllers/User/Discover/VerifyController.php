<?php

namespace Controllers\User\Discover;

use Models\User\User;

use Exception;

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
        $userId = \Core\Auth::user();

        try {

            User::setVerified($userId);

            header('Location: /u/discover');
        } catch (Exception $e) {

            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function handleIncrementFail()
    {
        \Core\Middleware::auth();

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
            echo json_encode([
                'status' => 'locked',
                'remaining_seconds' => $remaining,
            ]);
            return;
        }

        $_SESSION['verify_fail_count']++;

        if ($_SESSION['verify_fail_count'] >= 10) {
            $_SESSION['verify_lock_until'] = time() + (5 * 60);
            $_SESSION['verify_fail_count'] = 0;
            echo json_encode([
                'status' => 'locked',
                'remaining_seconds' => 300,
            ]);
            return;
        }

        echo json_encode([
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

        echo json_encode([
            'fail_count' => $_SESSION['verify_fail_count'] ?? 0,
            'lock_until' => $_SESSION['verify_lock_until'] ?? null,
            'remaining_seconds' => isset($_SESSION['verify_lock_until']) && time() < $_SESSION['verify_lock_until']
                ? $_SESSION['verify_lock_until'] - time()
                : 0
        ]);
    }
}
