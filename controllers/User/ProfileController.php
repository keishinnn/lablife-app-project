<?php

// file path = root/controllers/User/ProfileController.php

namespace Controllers\User;

use Models\User;
use Core\App;

class ProfileController
{

    public function View()
    {
        \Core\Middleware::auth();

        $error = '';
        $user = \Core\Auth::user();

        view('index.view.php', compact('user', 'error'));
    }

    public function ProfileView()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $isLoading = true;
        $error = '';

        $user = User::getCurrentUserProfile($userId);
        $_SESSION['user_id'] = $user->id;

        if (!$user) {
            $error = "Profile not found";
            $isLoading = false;
            $this->ProfileNotFoundView();
        }

        $isLoading = false;

        view('user/profile.view.php', compact('user', 'isLoading', 'error'));
    }

    public function ProfileNotFoundView()
    {
        view('user/profile.view.php', compact('user', 'error', 'isLoading'));
    }

    public function ProfileLoadingView()
    {
        view('user/profile.loading.view.php', compact('user', 'error', 'isLoading'));
    }

}
