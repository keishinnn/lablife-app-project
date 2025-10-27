<?php

namespace Controllers\User\Messages;

use Models\User\User;
use Models\Stream\Stream;

class MessagesController
{

    public function View()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $user = User::getCurrentUserProfile($userId);
        \Core\Middleware::checkIfUserExist($user);

        $channelId = $_GET['channelId'] ?? null;

        $streamToken = Stream::getStreamUserToken($userId, $user->fullName, $user->avatarUrl);

        view('user/messages/index.view.php', compact('streamToken', 'channelId'));
    }


}
