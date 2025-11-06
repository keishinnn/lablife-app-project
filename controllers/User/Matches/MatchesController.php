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

        $matches = Matches::getAllUserMatches($userId);

        $matchedUsers = [];

        foreach ($matches as $match) {
            if ($match->user1_id === $userId) {
                $otherUserId = $match->user2_id;
            } else {
                $otherUserId = $match->user1_id;
            }

            $matchedUsers[] = User::getCurrentUserProfile($otherUserId);
        }
        view('user/matches/index.view.php', compact('matchedUsers'));
    }

    public function handleCreateOrGetChannel()
    {
        \Core\Middleware::auth();
        $currentUserId = \Core\Auth::user(); // Current user
        $data = json_decode(file_get_contents('php://input'), true);
        $otherUserId = $data['targetUserId'] ?? null;

        if (!$otherUserId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing target user ID']);
            exit;
        }

        try {
            $channel = Stream::createOrgetChannel($currentUserId, $otherUserId);

            echo json_encode([
                'success' => true,
                'channel_id' => $channel['channelId']
            ]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
