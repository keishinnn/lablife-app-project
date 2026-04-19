<?php

namespace Controllers\User\Matches;

use Models\User\User;
use Models\Match\Matches;
use Models\Stream\Stream;

class MatchesController
{

    public function View()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $user = User::getCurrentUserProfile($userId);
        \Core\Middleware::checkIfUserExist($user);

        $matchedUsers = Matches::getMatchedUsers($userId);
        view('user/matches/index.view.php', compact('matchedUsers'));
    }

    public function handleCreateOrGetChannel()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $currentUserId = \Core\Auth::user(); // Current user
        $data = request_json();
        $otherUserId = $data['targetUserId'] ?? null;

        if (!$otherUserId) {
            json_response(['error' => 'Missing target user ID'], 400);
        }

        try {
            $channel = Stream::createOrGetChannel($currentUserId, $otherUserId);

            json_response([
                'success' => true,
                'channel_id' => $channel['channelId']
            ]);
        } catch (\Throwable $e) {
            app_log_exception($e, 'Create/get channel failed');
            json_response(['error' => generic_error_message()], 500);
        }
    }
}
