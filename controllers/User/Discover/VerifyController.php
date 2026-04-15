<?php

namespace Controllers\User\Discover;

use Models\User\User;
use Models\Match\MatchCandidate;
use Models\Match\MatchSearch;
use Models\Match\MatchSession;

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
            view('user/discover/verify.view.php', compact('isVerified', 'user'));
        }
    }
}
