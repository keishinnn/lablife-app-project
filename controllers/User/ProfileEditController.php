<?php

namespace Controllers\User;

use Models\User;

class ProfileEditController
{

    public function View()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $isLoading = false;
        $error = '';
        $message = '';

        $user = User::getCurrentUserProfile($userId);

        view('user/edit/profile.edit.view.php', compact('user', 'error', 'isLoading', 'message'));
    }

    public function handleEditProfile()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();

        $error = '';
        $message = '';
        $isLoading = true;

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

        $user = User::getCurrentUserProfile($userId);
        $message = "Profile successfully edited";
        $isLoading = false;
        view('user/edit/profile.edit.view.php', compact('user', 'error', 'isLoading', 'message'));
    }

    public function handleCancelEdit()
    {
        \Core\Middleware::auth();

        // Prepare avatar file from tmp if available
        $avatarFile = null;
        if (!empty($_SESSION['avatar_temp'])) {
            $tmpPath = __DIR__ . '/../../public/uploads/tmp/' . $_SESSION['avatar_temp'];

            if (file_exists($tmpPath)) {
                $avatarFile = [
                    'name'     => $_SESSION['avatar_temp'],
                    'type'     => mime_content_type($tmpPath),
                    'tmp_name' => $tmpPath,
                    'error'    => UPLOAD_ERR_OK,
                    'size'     => filesize($tmpPath)
                ];
            }
        }

        if ($avatarFile && file_exists($avatarFile['tmp_name'])) {
            unlink($avatarFile['tmp_name']);
        }

        header('Location: /u/profile');
        exit;
    }

    public function handleAvatarUpload()
    {
        \Core\Middleware::auth();
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
