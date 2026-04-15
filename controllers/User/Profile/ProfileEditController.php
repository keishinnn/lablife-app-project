<?php

namespace Controllers\User\Profile;

use Models\User\User;

class ProfileEditController
{

    public function View()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $user = User::getCurrentUserProfile($userId);
        \Core\Middleware::checkIfUserExist($user);
        \Core\Middleware::checkNotSetProfile($user);

        $isLoading = false;
        $error = '';
        $message = '';

        $user = User::getCurrentUserProfile($userId);

        view('user/profile/profile.edit.view.php', compact('user', 'error', 'isLoading', 'message'));
    }

    public function handleEditProfile()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();

        $userId = \Core\Auth::user();

        $fullName  = $_POST['full-name'] ?? '';
        $username  = $_POST['username'] ?? '';
        $gender    = $_POST['gender'] ?? '';
        $birthdate = $_POST['birthdate'] ?? '';
        $bio       = $_POST['bio'] ?? '';

        // Update user profile in DB (persist from session + Supabase avatar)
        User::editProfile($userId, [
            "full_name" => $fullName ?? '',
            "username"  => $username ?? '',
            "gender"    => $gender ?? '',
            "birthdate" => $birthdate ?? '',
            "bio"       => $bio ?? '',
            "id"        => $userId
        ]);

        redirect('/u/profile');
    }

    public function handleAvatarUpload()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = \Core\Auth::user();

        if (empty($_FILES['avatar_input'])) {
            json_response(['success' => false, 'message' => 'No file uploaded'], 400);
        }

        try {
            $avatarUrl = User::updateProfilePicture($userId, $_FILES['avatar_input']);
            if ($avatarUrl) {
                json_response(['success' => true, 'avatarUrl' => $avatarUrl]);
            } else {
                json_response(['success' => false, 'message' => 'Upload failed'], 400);
            }
        } catch (\Exception $e) {
            app_log_exception($e, 'Avatar upload failed');
            json_response(['success' => false, 'message' => generic_error_message()], 500);
        }
    }
}
