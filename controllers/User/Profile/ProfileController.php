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
        $bundle = User::getProfileBundle($userId);
        $user = $bundle['user'];
        \Core\Middleware::checkIfUserExist($user);
        \Core\Middleware::checkNotSetProfile($user);

        $isLoading = true;
        $error = '';

        $ptypes = UserPersonality::getAllPersonalityTypes();
        $hobbies =  UserHobbies::getAllHobbies();
        $interests =  UserInterests::getAllInterests();

        $userPreferences = $bundle['preferences'] ?? null;
        $personalityType = $bundle['personalityType'] ?? null;
        $userHobbies = $bundle['userHobbies'] ?? null;
        $userInterests = $bundle['userInterests'] ?? null;
        $profileModalFeedback = session_flash_pull('profile_modal_feedback', null);
        $legacyProfileModalError = session_flash_pull('profile_modal_error', '');
        $profileModalError = '';
        $profileModalToOpen = '';

        if (is_array($profileModalFeedback)) {
            $profileModalError = (string) ($profileModalFeedback['message'] ?? '');
            $profileModalToOpen = (string) ($profileModalFeedback['modal'] ?? '');
        } elseif (is_string($profileModalFeedback)) {
            $profileModalError = $profileModalFeedback;
        } elseif (is_string($legacyProfileModalError)) {
            $profileModalError = $legacyProfileModalError;
        }

        $_SESSION['user_id'] = $user->id;
        $isLoading = false;

        view('user/profile/profile.view.php', compact('user', 'isLoading', 'error', 'userPreferences', 'personalityType', 'ptypes', 'hobbies', 'userHobbies', 'interests', 'userInterests', 'profileModalError', 'profileModalToOpen'));
    }

    public function handleGetPTypes()
    {
        \Core\Middleware::auth();

        $ptypes = UserPersonality::getAllPersonalityTypes();

        json_response($ptypes);
    }

    public function handleSetPersonalityType()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        \Core\Middleware::verifyCSRFToken();

        $ptId = $_POST['personality_id'];

        UserPersonality::setUserPersonality($userId, $ptId);

        redirect('/u/profile');
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
            app_log_exception($e, 'Update hobbies failed');
            $error = generic_error_message();
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

        redirect('/u/profile');
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
            app_log_exception($e, 'Update interests failed');
            $error = generic_error_message();
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

        redirect('/u/profile');
    }

    public function handleSetOffline()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = \Core\Auth::user();

        User::updateIsOffline($userId);
        json_response(['success' => true]);
    }

    public function handleSetOnline()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = \Core\Auth::user();

        User::updateIsOnline($userId);
        json_response(['success' => true]);
    }
}
