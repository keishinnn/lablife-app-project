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
        \Core\Middleware::verifyCSRFToken();
        $userId = Auth::user();

        $data = request_json();

        $partnerId = $data['partner'] ?? null;

        if (!$partnerId) {
            json_response(['status' => 'error', 'message' => 'Missing partner ID'], 400);
        }

        try {
            MatchCandidate::likeOtherUser($userId, $partnerId);
            json_response(['status' => 'success']);
        } catch (\Exception $e) {
            app_log_exception($e, 'Like action failed');
            json_response(['status' => 'error', 'message' => generic_error_message()], 500);
        }
    }

    public function handledisLikeOtherUser()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = Auth::user();

        $data = request_json();

        $partnerId = $data['partner'] ?? null;

        if (!$partnerId) {
            json_response(['status' => 'error', 'message' => 'Missing partner ID'], 400);
        }

        try {
            MatchCandidate::dislikeOtherUser($userId, $partnerId);
            json_response(['status' => 'success']);
        } catch (\Exception $e) {
            app_log_exception($e, 'Dislike action failed');
            json_response(['status' => 'error', 'message' => generic_error_message()], 500);
        }
    }
}
