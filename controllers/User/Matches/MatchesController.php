<?php

namespace Controllers\User\Matches;

use Models\User\User;
use Models\Match\Matches;

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
}
