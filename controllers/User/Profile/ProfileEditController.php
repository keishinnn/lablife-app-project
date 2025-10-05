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

        header('Location: /u/profile');
        exit;
    }

    public function handleAvatarUpload()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = \Core\Auth::user();

        if (empty($_FILES['avatar_input'])) {
            echo json_encode(['success' => false, 'message' => 'No file uploaded']);
            return;
        }

        try {
            $avatarUrl = User::updateProfilePicture($userId, $_FILES['avatar_input']);
            if ($avatarUrl) {
                echo json_encode(['success' => true, 'avatarUrl' => $avatarUrl]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Upload failed']);
            }
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }
}
