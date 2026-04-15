<?php

namespace Controllers\User\Profile;

use Models\User\User;
use Models\User\UserPreferences;

class ProfilePreferencesController
{
    public function View()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();

        $user = User::getCurrentUserProfile($userId);
        $preferences = UserPreferences::getCurrentUserPreferences($userId);

        $error = '';
        $message = '';
        $isLoading = false;

        view('user/profile/profile.edit.pref.view.php', compact('user', 'preferences', 'error', 'message', 'isLoading'));
    }

    public function Update()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();

        $userId = \Core\Auth::user();

        $data = [
            'age_range' => [
                'min' => $_POST['age_min'] ?? 18,
                'max' => $_POST['age_max'] ?? 35
            ],
            'distance' => $_POST['distance'] ?? 50,
            'gender_preference' => $_POST['gender_preference'] ?? 'other'
        ];

        try {
            $preferences = new UserPreferences($data);
            $preferences->savePreferences($userId);
            $_SESSION['flash_message'] = "Preferences successfully updated.";
        } catch (\Exception $e) {
            app_log_exception($e, 'Update preferences failed');
            $_SESSION['flash_error'] = generic_error_message();
        }

        redirect('/u/profile');
    }
}
