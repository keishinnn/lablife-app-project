<?php

namespace Controllers\User\Messages;

use Models\User\UserBlock;
use Models\Stream\Stream;
use Models\User\User;
use Exception;

class BlockController
{
    public function View()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $user = User::getCurrentUserProfile($userId);
        \Core\Middleware::checkIfUserExist($user);

        $blocked = UserBlock::getAllBlockedUsers($userId);

        $streamToken = Stream::getStreamUserToken($userId, $user->fullName, $user->avatarUrl);

        $blockedUsers = [];

        foreach ($blocked as $block) {
            $blockedId = $block->blockedUserId;

            $blockedUsers[] = User::getCurrentUserProfile($blockedId);
        }

        view('user/profile/profile.blocked.users.view.php', compact('blockedUsers', 'streamToken'));
    }

    public function blockOtherUser()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        \Core\Middleware::verifyCSRFToken();

        $input = json_decode(file_get_contents('php://input'), true);
        $blockedUserId  = $input['other_user_id'] ?? null;

        if (!$blockedUserId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing other_user_id']);
            return;
        }

        try {

            UserBlock::blockOtherUser($userId, $blockedUserId);
            echo json_encode(['success' => true]);
        } catch (Exception $e) {

            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    public function unblockOtherUser()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        \Core\Middleware::verifyCSRFToken();

        $input = json_decode(file_get_contents('php://input'), true);
        $blockedUserId  = $input['blocked_user_id'] ?? null;

        if (!$blockedUserId) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing blocked_user_id']);
            return;
        }

        try {
            UserBlock::unblockOtherUser($userId, $blockedUserId);
            echo json_encode(['success' => true]);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
