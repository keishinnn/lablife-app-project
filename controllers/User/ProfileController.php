<?php

// file path = root/controllers/User/ProfileController.php

namespace Controllers\User;

use Core\App;

class ProfileController
{

    public function View()
    {

        \Core\Middleware::auth();
        $user = \Core\Auth::user();

        view('index.view.php', compact('user'));
    }

    public function ProfileView()
    {
        \Core\Middleware::auth();
        $user = \Core\Auth::user();
        $isLoading = false;

        view('user/profile.view.php', compact('user', 'isLoading'));
    }

    public function SetUpProfileView()
    {

        \Core\Middleware::auth();
        $user = \Core\Auth::user();

        $fullName = $_POST['full-name'] ?? '';
        $gender = $_POST['gender'] ?? '';
        $birthdate = $_POST['birthdate'] ?? '';
        $avatarUrl = $_POST['avatar_input'] ?? null;
        $bio = $_POST['bio'] ?? '';

        $error = '';
        $isLoading = false;

        view('user/setup.view.php', compact('user', 'error', 'isLoading', 'fullName', 'gender', 'birthdate', 'avatarUrl', 'bio'));
    }

    public function handleSetupProfile()
    {
        \Core\Middleware::auth();
        $user = \Core\Auth::user();

        $fullName  = $_POST['full-name'] ?? '';
        $gender    = $_POST['gender'] ?? '';
        $birthdate = $_POST['birthdate'] ?? '';
        $bio       = $_POST['bio'] ?? '';
        $avatarUrl = null;
        $error     = '';
        $isLoading = true;

        try {
            // handle file upload
            if (isset($_FILES['avatar_input']) && $_FILES['avatar_input']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['avatar_input'];

                // size check
                if ($file['size'] > 5 * 1024 * 1024) {
                    $error = "Avatar must be less than 5MB.";
                    error_log("[UPLOAD] File too large: {$file['size']} bytes for user {$user['id']}");
                    $isLoading = false;
                    view('user/setup.view.php', compact('user', 'error', 'isLoading', 'fullName', 'gender', 'birthdate', 'avatarUrl', 'bio'));
                    return;
                }

                // type check
                $allowed = ['image/jpeg', 'image/png', 'image/webp'];
                if (!in_array($file['type'], $allowed)) {
                    $error = "Invalid file type. Only JPG, PNG, WEBP allowed.";
                    error_log("[UPLOAD] Invalid file type: {$file['type']} for user {$user['id']}");
                    $isLoading = false;
                    view('user/setup.view.php', compact('user', 'error', 'isLoading', 'fullName', 'gender', 'birthdate', 'avatarUrl', 'bio'));
                    return;
                }

                $userId   = $user['id'] ?? uniqid("guest_");
                $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filePath = "{$userId}_" . time() . ".{$ext}";

                try {
                    $supabase = App::resolve(\Services\SupabaseService::class);

                    error_log("[UPLOAD] Uploading file for user {$userId} to {$filePath}");

                    $result = $supabase->uploadFile(
                        "profile-photos",
                        $filePath,
                        file_get_contents($file['tmp_name']),
                        $file['type']
                    );

                    error_log("[UPLOAD] Supabase upload result: " . print_r($result, true));

                    $avatarUrl = $result['url'];
                } catch (\Exception $e) {
                    $error = "Upload failed: " . $e->getMessage();
                    error_log("[ERROR] Supabase upload failed for user {$userId}: " . $e->getMessage());
                    $isLoading = false;
                    view('user/setup.view.php', compact('user', 'error', 'isLoading', 'fullName', 'gender', 'birthdate', 'avatarUrl', 'bio'));
                    return;
                }
            }

            // save to db
            $db = App::resolve('Core\Database');

            error_log("[DB] Updating profile for user {$user['id']}");

            $db->query(
                "UPDATE users 
             SET full_name = :name, gender = :gender, birthdate = :birthdate, bio = :bio, avatar_url = :avatar 
             WHERE id = :id",
                [
                    "name" => $fullName,
                    "gender" => $gender,
                    "birthdate" => $birthdate,
                    "bio" => $bio,
                    "avatar" => $avatarUrl,
                    "id" => $user['id']
                ]
            );

            $isLoading = false;
            header("Location: /u");
            exit;
        } catch (\Exception $e) {
            $error = "Something went wrong: " . $e->getMessage();
            error_log("[ERROR] Exception in handleSetupProfile: " . $e->getMessage());
            $isLoading = false;
            view('user/setup.view.php', compact('user', 'error', 'isLoading', 'fullName', 'gender', 'birthdate', 'avatarUrl', 'bio'));
        }
    }
}
