<?php

namespace Controllers\User\Discover;

use Core\Auth;
use Models\Match\MatchCandidate;
use Exception;

class ReactionController
{

    public function handleLikeOtherUser()
    {
        \Core\Middleware::auth();
        $userId = Auth::user();

        $data = json_decode(file_get_contents('php://input'), true);

        $partnerId = $data['partner'] ?? null;
        $csrfToken = $data['csrf_token'] ?? null;

        if (!$partnerId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing partner ID']);
            return;
        }

        try {
            MatchCandidate::likeOtherUser($userId, $partnerId);
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function handledisLikeOtherUser()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = Auth::user();

        $data = json_decode(file_get_contents('php://input'), true);

        $partnerId = $data['partner'] ?? null;

        if (!$partnerId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing partner ID']);
            return;
        }

        try {
            MatchCandidate::dislikeOtherUser($userId, $partnerId);
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
