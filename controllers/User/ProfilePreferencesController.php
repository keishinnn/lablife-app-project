<?php

namespace Controllers\User;

use Models\User;
use Models\UserPreferences;

class ProfilePreferencesController
{
    public function View()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();

        $user = User::getCurrentUserProfile($userId);
        $preferences = UserPreferences::getPreferences($userId);

        $error = '';
        $message = '';
        $isLoading = false;

        view('user/edit/preferences.edit.view.php', compact('user', 'preferences', 'error', 'message', 'isLoading'));
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
            $_SESSION['flash_error'] = "Failed to update preferences: " . $e->getMessage();
        }

        header("Location: /u/profile");
        exit;
    }
}
