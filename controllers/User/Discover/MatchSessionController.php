<?php

namespace Controllers\User\Discover;

use Core\Auth;
use Models\Match\MatchSession;
use Exception;

class MatchSessionController
{

    public function handleSetExpiredSession()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = Auth::user();

        try {
            $stmt = MatchSession::setMatchSessionExpired($userId);

            if ($stmt->rowCount() > 0) {
                json_response(['status' => 'expired', 'message' => 'Match session expired.']);
            } else {
                json_response(['status' => 'none', 'message' => 'No active session found.']);
            }
        } catch (Exception $e) {
            app_log_exception($e, 'Expire session failed');
            json_response(['status' => 'error', 'message' => generic_error_message()], 500);
        }
    }

    public function handleSetRejectedSession()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = Auth::user();

        try {
            $stmt = MatchSession::setMatchSessionRejected($userId);

            if ($stmt->rowCount() > 0) {
                json_response(['status' => 'rejected', 'message' => 'Match session rejected']);
            } else {
                json_response(['status' => 'none', 'message' => 'No active session found.']);
            }
        } catch (Exception $e) {
            app_log_exception($e, 'Reject session failed');
            json_response(['status' => 'error', 'message' => generic_error_message()], 500);
        }
    }

    public function handleSetMatchedSession()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = Auth::user();

        try {
            $stmt = MatchSession::setMatchSessionMatched($userId);

            if ($stmt->rowCount() > 0) {
                json_response(['status' => 'matched', 'message' => 'Match session matched']);
            } else {
                json_response(['status' => 'none', 'message' => 'No active session found.']);
            }
        } catch (Exception $e) {
            app_log_exception($e, 'Match session update failed');
            json_response(['status' => 'error', 'message' => generic_error_message()], 500);
        }
    }
}
