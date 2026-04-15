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

        $input = request_json();
        $blockedUserId  = $input['other_user_id'] ?? null;

        if (!$blockedUserId) {
            json_response(['error' => 'Missing other_user_id'], 400);
        }

        try {

            UserBlock::blockOtherUser($userId, $blockedUserId);
            json_response(['success' => true]);
        } catch (Exception $e) {
            app_log_exception($e, 'Block user failed');
            json_response(['error' => generic_error_message()], 500);
        }
    }

    public function unblockOtherUser()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        \Core\Middleware::verifyCSRFToken();

        $input = request_json();
        $blockedUserId  = $input['blocked_user_id'] ?? null;

        if (!$blockedUserId) {
            json_response(['error' => 'Missing blocked_user_id'], 400);
        }

        try {
            UserBlock::unblockOtherUser($userId, $blockedUserId);
            json_response(['success' => true]);
        } catch (\Exception $e) {
            app_log_exception($e, 'Unblock user failed');
            json_response(['error' => generic_error_message()], 500);
        }
    }
}
