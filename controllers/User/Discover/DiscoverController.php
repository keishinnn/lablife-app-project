<?php

namespace Controllers\User\Discover;

use Models\User\User;

class DiscoverController
{

    public function View()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $user = User::getCurrentUserProfile($userId);
        \Core\Middleware::checkIfUserExist($user);

        view('user/discover/index.view.php');
    }

    public function MatchedUserView()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $user = User::getCurrentUserProfile($userId);
        \Core\Middleware::checkIfUserExist($user);

        view('user/discover/matched.view.php', compact('user'));
    }
}
