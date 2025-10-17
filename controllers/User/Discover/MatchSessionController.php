<?php

namespace Controllers\User\Discover;

use Core\Auth;
use Models\Match\MatchSession;
use Exception;

class MatchSessionController
{

    public function handleSetExpiredSession()
    {
        \Core\Middleware::auth();
        $userId = Auth::user();

        header('Content-Type: application/json');

        try {
            $stmt = MatchSession::setMatchSessionExpired($userId);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'stopped', 'message' => 'Active search deleted.']);
            } else {
                echo json_encode(['status' => 'none', 'message' => 'No active search found.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function handleSetRejectedSession()
    {
        \Core\Middleware::auth();
        $userId = Auth::user();

        header('Content-Type: application/json');

        try {
            $stmt = MatchSession::setMatchSessionRejected($userId);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'rejected', 'message' => 'Match session rejected']);
            } else {
                echo json_encode(['status' => 'none', 'message' => 'No active search found.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function handleSetMatchedSession()
    {
        \Core\Middleware::auth();
        $userId = Auth::user();

        header('Content-Type: application/json');

        try {
            $stmt = MatchSession::setMatchSessionMatched($userId);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'matched', 'message' => 'Match session rejected']);
            } else {
                echo json_encode(['status' => 'none', 'message' => 'No active search found.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
