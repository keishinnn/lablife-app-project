<?php

// file path = root/controllers/User/ProfileController.php

namespace Controllers\User\Profile;

use Models\User\User;
use Models\User\UserPersonality;
use Models\User\UserPreferences;
use Models\User\UserHobbies;
use Models\User\UserInterests;

class ProfileController
{

    public function View()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $user = User::getCurrentUserProfile($userId);
        \Core\Middleware::checkIfUserExist($user);

        view('user/index.view.php', compact('user'));
    }

    public function ProfileView()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $user = User::getCurrentUserProfile($userId);
        \Core\Middleware::checkIfUserExist($user);
        \Core\Middleware::checkNotSetProfile($user);

        $isLoading = true;
        $error = '';

        $user = User::getCurrentUserProfile($userId);

        $ptypes = UserPersonality::getAllPersonalityTypes();
        $hobbies =  UserHobbies::getAllHobbies();
        $interests =  UserInterests::getAllInterests();

        $userPreferences = UserPreferences::getCurrentUserPreferences($userId) ?? null;
        $personalityType = UserPersonality::getCurrentUserPersonality($userId) ?? null;
        $userHobbies = UserHobbies::getCurrentUserHobbies($userId) ?? null;
        $userInterests = UserInterests::getCurrentUserInterests($userId) ?? null;

        $_SESSION['user_id'] = $user->id;
        $isLoading = false;

        view('user/profile/profile.view.php', compact('user', 'isLoading', 'error', 'userPreferences', 'personalityType', 'ptypes', 'hobbies', 'userHobbies', 'interests', 'userInterests'));
    }

    public function handleGetPTypes()
    {
        \Core\Middleware::auth();

        $ptypes = UserPersonality::getAllPersonalityTypes();

        header('Content-Type: application/json');
        echo json_encode($ptypes);
        exit;
    }

    public function handleSetPersonalityType()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        \Core\Middleware::verifyCSRFToken();

        $ptId = $_POST['personality_id'];

        UserPersonality::setUserPersonality($userId, $ptId);

        header('Location: /u/profile');
        exit;
    }

    public function handleSetHobbies()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        \Core\Middleware::verifyCSRFToken();

        $hobbyIds = $_POST['hobbies'] ?? []; // multiple hobbies submitted

        $error = '';

        try {
            \Models\User\UserHobbies::syncUserHobbies($userId, $hobbyIds);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        if ($error) {
            $user = \Models\User\User::getCurrentUserProfile($userId);
            $userPreferences = \Models\User\UserPreferences::getCurrentUserPreferences($userId);
            $personalityType = \Models\User\UserPersonality::getCurrentUserPersonality($userId);
            $userHobbies = \Models\User\UserHobbies::getCurrentUserHobbies($userId);
            $userInterests = UserInterests::getCurrentUserInterests($userId);
            $ptypes = \Models\User\UserPersonality::getAllPersonalityTypes();
            $hobbies = \Models\User\UserHobbies::getAllHobbies();
            $interests = \Models\User\UserInterests::getAllInterests();

            view('user/profile.view.php', compact(
                'user',
                'userPreferences',
                'personalityType',
                'ptypes',
                'hobbies',
                'userHobbies',
                'error',
                'userInterests',
                'interests'
            ));
            return;
        }

        header('Location: /u/profile');
        exit;
    }

    public function handleSetInterests()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        \Core\Middleware::verifyCSRFToken();

        $interestIds = $_POST['interests'] ?? [];

        $error = '';

        try {
            \Models\User\UserInterests::syncUserInterests($userId, $interestIds);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        if ($error) {
            $user = \Models\User\User::getCurrentUserProfile($userId);
            $userPreferences = \Models\User\UserPreferences::getCurrentUserPreferences($userId);
            $personalityType = \Models\User\UserPersonality::getCurrentUserPersonality($userId);
            $userHobbies = \Models\User\UserHobbies::getCurrentUserHobbies($userId);
            $userInterests = UserInterests::getCurrentUserInterests($userId);
            $ptypes = \Models\User\UserPersonality::getAllPersonalityTypes();
            $hobbies = \Models\User\UserHobbies::getAllHobbies();
            $interests = \Models\User\UserInterests::getAllInterests();

            view('user/profile/profile.view.php', compact(
                'user',
                'userPreferences',
                'personalityType',
                'ptypes',
                'hobbies',
                'userHobbies',
                'error',
                'userInterests',
                'interests'
            ));
            return;
        }

        header('Location: /u/profile');
        exit;
    }
}
