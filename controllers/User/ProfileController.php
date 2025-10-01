<?php

// file path = root/controllers/User/ProfileController.php

namespace Controllers\User;

use Models\User;
use Models\UserPersonality;
use Models\UserPreferences;
use Models\UserHobbies;

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

        $userPreferences = UserPreferences::getCurrentUserPreferences($userId);
        $personalityType = UserPersonality::getCurrentUserPersonality($userId);
        $userHobbies = UserHobbies::getCurrentUserHobbies($userId);

        $_SESSION['user_id'] = $user->id;
        $isLoading = false;

        view('user/profile.view.php', compact('user', 'isLoading', 'error', 'userPreferences', 'personalityType', 'ptypes', 'hobbies', 'userHobbies'));
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
            \Models\UserHobbies::syncUserHobbies($userId, $hobbyIds);
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        if ($error) {
            $user = \Models\User::getCurrentUserProfile($userId);
            $userPreferences = \Models\UserPreferences::getCurrentUserPreferences($userId);
            $personalityType = \Models\UserPersonality::getCurrentUserPersonality($userId);
            $ptypes = \Models\UserPersonality::getAllPersonalityTypes();
            $hobbies = \Models\UserHobbies::getAllHobbies();
            $userHobbies = \Models\UserHobbies::getCurrentUserHobbies($userId);

            view('user/profile.view.php', compact(
                'user',
                'userPreferences',
                'personalityType',
                'ptypes',
                'hobbies',
                'userHobbies',
                'error'
            ));
            return;
        }

        header('Location: /u/profile');
        exit;
    }
}
