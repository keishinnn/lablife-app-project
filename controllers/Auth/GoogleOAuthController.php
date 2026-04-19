<?php

namespace Controllers\Auth;

use Core\App;
use Models\User\User;

class GoogleOAuthController
{
    public function callbackView()
    {
        $supabaseConfig = require base_path('config/supabase.php');
        $supabaseUrl = $supabaseConfig['url'] ?? '';
        $supabaseAnonKey = $supabaseConfig['anon_key'] ?? '';

        $error = '';
        view('auth/google.callback.view.php', compact('supabaseUrl', 'supabaseAnonKey', 'error'));
    }

    public function establishSession()
    {
        \Core\Middleware::verifyCSRFToken();
        header('Content-Type: application/json');

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) {
            json_response(['success' => false, 'message' => 'Invalid OAuth payload.'], 422);
        }

        $accessToken = trim($payload['access_token'] ?? '');
        if ($accessToken === '') {
            json_response(['success' => false, 'message' => 'Missing access token.'], 422);
        }

        try {
            $supabase = App::resolve(\Services\SupabaseService::class);
            $userFetch = $supabase->getUser($accessToken);
            $userId = $userFetch['id'] ?? null;

            if (!$userId) {
                json_response(['success' => false, 'message' => 'Unable to verify Google sign-in.'], 401);
            }

            $user = User::getCurrentUserProfile($userId);
            if (!$user) {
                json_response([
                    'success' => false,
                    'message' => 'Your account record is not ready yet. Please try again in a moment.'
                ], 409);
            }

            User::updateIsOnline($userId);

            session_regenerate_id(true);
            csrf_token();
            $_SESSION['access_token'] = $accessToken;
            $_SESSION['user_id'] = $userId;
            unset($_SESSION['old_email']);

            $redirectTo = !isset($user->avatarUrl) ? '/u/setup-profile' : '/u';

            json_response([
                'success' => true,
                'redirect_to' => $redirectTo,
            ]);
        } catch (\Throwable $e) {
            app_log_exception($e, 'Google OAuth session establish failed');
            json_response([
                'success' => false,
                'message' => generic_error_message(),
            ], 500);
        }
    }
}
