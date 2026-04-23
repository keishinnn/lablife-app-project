<?php

namespace Controllers\User\Profile;

use Models\User\User;

class ProfileSetupController
{
    private const ALLOWED_IMAGE_TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/heic' => 'heic',
        'image/heif' => 'heif',
    ];

    public function SetupProfileView()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $user = User::getCurrentUserProfile($userId);
        \Core\Middleware::checkIfUserExist($user);

        $error = $_SESSION['setup_error'] ?? '';
        unset($_SESSION['setup_error']);

        view('user/profile-setup/setup.view.php', compact('error'));
    }

    public function SetupProfilePreferencesView()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $user = User::getCurrentUserProfile($userId);
        \Core\Middleware::checkIfUserExist($user);

        view('user/profile-setup/setup.pref.view.php');
    }

    public function handleStepOneSetup()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        // If step one is already completed, just redirect (don’t overwrite)
        if (!empty($_SESSION['step_one_completed']) && $_SESSION['step_one_completed'] === true) {
            redirect('/u/setup-profile-preferences');
        }

        // Save text values
        $_SESSION['full-name'] = $_POST['full-name'] ?? '';
        $_SESSION['gender']    = $_POST['gender'] ?? '';
        $_SESSION['birthdate'] = $_POST['birthdate'] ?? '';
        $_SESSION['bio']       = $_POST['bio'] ?? '';

        // Handle avatar upload (temporary storage)
        if (!empty($_FILES['avatar_input']) && $_FILES['avatar_input']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['avatar_input'];
            $uploadDir = base_path('public/uploads/tmp/');
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            if (($file['size'] ?? 0) > 5 * 1024 * 1024) {
                $_SESSION['setup_error'] = 'Avatar must be less than 5MB.';
                redirect('/u/setup-profile');
            }

            $mimeType = $this->detectImageMimeType($file['tmp_name'], $file['name'] ?? '');
            if (!isset(self::ALLOWED_IMAGE_TYPES[$mimeType])) {
                $_SESSION['setup_error'] = 'Invalid file type. Only JPG, PNG, WEBP, HEIC, and HEIF are allowed.';
                redirect('/u/setup-profile');
            }

            $fileName = bin2hex(random_bytes(16)) . '.' . self::ALLOWED_IMAGE_TYPES[$mimeType];
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($file['tmp_name'], $targetFile)) {
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
        redirect('/u/setup-profile-preferences');
    }

    public function handleFinishSetup()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = \Core\Auth::user();

        // Make sure step one was completed
        if (empty($_SESSION['step_one_completed'])) {
            redirect('/u/setup-profile');
        }

        // Save preferences
        $preferences = new \Models\User\UserPreferences([
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
            $tmpPath = base_path('public/uploads/tmp/' . $_SESSION['avatar_temp']);

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
        // Delete local tmp avatar if uploaded to Supabase successfully
        try {
            $avatarUrl = User::setupProfile($userId, [
                'full_name' => $_SESSION['full-name'] ?? '',
                'gender'    => $_SESSION['gender'] ?? '',
                'birthdate' => $_SESSION['birthdate'] ?? '',
                'bio'       => $_SESSION['bio'] ?? ''
            ], $avatarFile);
        } finally {
            if ($avatarFile && file_exists($avatarFile['tmp_name'])) {
                unlink($avatarFile['tmp_name']);
            }
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

        redirect('/u');
    }

    private function detectImageMimeType(string $tmpPath, string $originalName = ''): string
    {
        $mimeType = mime_content_type($tmpPath) ?: '';

        if ($mimeType === 'application/octet-stream' || $mimeType === '') {
            $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

            return match ($extension) {
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'webp' => 'image/webp',
                'heic' => 'image/heic',
                'heif' => 'image/heif',
                default => $mimeType,
            };
        }

        return $mimeType;
    }
}
