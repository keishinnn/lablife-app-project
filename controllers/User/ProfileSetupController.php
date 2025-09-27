<?php

namespace Controllers\User;

use Models\User;
use Core\App;

class ProfileSetupController
{

    public function SetupProfileView()
    {

        \Core\Middleware::auth();
        $user = \Core\Auth::user();

        $error = '';
        $isLoading = false;

        view('user/setup.view.php', compact('error', 'isLoading'));
    }

    public function SetupProfilePreferencesView()
    {

        view('user/setup.pref.view.php');
    }

    public function handleStepOneSetup()
    {
        // If step one is already completed, just redirect (don’t overwrite)
        if (!empty($_SESSION['step_one_completed']) && $_SESSION['step_one_completed'] === true) {
            header('Location: /u/setup-profile-preferences');
            exit;
        }

        // Save text values
        $_SESSION['full-name'] = $_POST['full-name'] ?? '';
        $_SESSION['gender']    = $_POST['gender'] ?? '';
        $_SESSION['birthdate'] = $_POST['birthdate'] ?? '';
        $_SESSION['bio']       = $_POST['bio'] ?? '';

        // Handle avatar upload (temporary storage)
        if (!empty($_FILES['avatar_input']) && $_FILES['avatar_input']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../../public/uploads/tmp/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName   = uniqid() . '_' . basename($_FILES['avatar_input']['name']);
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['avatar_input']['tmp_name'], $targetFile)) {
                // If user had a previous temp file, optionally delete it
                if (!empty($_SESSION['avatar_temp'])) {
                    $oldFile = $uploadDir . $_SESSION['avatar_temp'];
                    if (file_exists($oldFile)) {
                        unlink($oldFile); // cleanup old temp file
                    }
                }

                $_SESSION['avatar_temp'] = $fileName; // store new filename
            }
        }


        // Mark step one as completed
        $_SESSION['step_one_completed'] = true;

        // Redirect to step two
        header('Location: /u/setup-profile-preferences');
        exit;
    }

    public function handleFinishSetup()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();

        // Make sure step one was completed
        if (empty($_SESSION['step_one_completed'])) {
            header('Location: /u/setup-profile');
            exit;
        }

        // Save preferences
        $preferences = new \Models\UserPreferences([
            'age_range' => [
                'min' => $_POST['age_min'] ?? 18,
                'max' => $_POST['age_max'] ?? 35,
            ],
            'distance' => $_POST['distance'] ?? 50,
            'gender_preference' => $_POST['gender_preference'] ?? 'other'
        ]);
        $preferences->savePreferences($userId);

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

        // Update user profile in DB (persist from session + Supabase avatar)
        $avatarUrl = User::updateProfile($userId, [
            'full_name' => $_SESSION['full-name'] ?? '',
            'gender'    => $_SESSION['gender'] ?? '',
            'birthdate' => $_SESSION['birthdate'] ?? '',
            'bio'       => $_SESSION['bio'] ?? ''
        ], $avatarFile);

        // Delete local tmp avatar if uploaded to Supabase successfully
        if ($avatarFile && file_exists($avatarFile['tmp_name'])) {
            unlink($avatarFile['tmp_name']);
        }

        // Clear setup sessions
        unset(
            $_SESSION['step_one_completed'],
            $_SESSION['full-name'],
            $_SESSION['gender'],
            $_SESSION['birthdate'],
            $_SESSION['bio'],
            $_SESSION['avatar_temp']
        );

        // Redirect to home
        header('Location: /u');
        exit;
    }
}
