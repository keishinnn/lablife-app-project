<?php

namespace Controllers\User\Discover;

use Core\Auth;
use Models\Match\MatchSearch;
use Exception;

class SearchController
{

    public function handleStopSearch()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = Auth::user();

        try {
            $stmt = MatchSearch::stopSearch($userId);

            if ($stmt->rowCount() > 0) {
                json_response(['status' => 'stopped', 'message' => 'Active search deleted.']);
            } else {
                json_response(['status' => 'none', 'message' => 'No active search found.']);
            }
        } catch (Exception $e) {
            app_log_exception($e, 'Stop search failed');
            json_response(['status' => 'error', 'message' => generic_error_message()], 500);
        }
    }

    public function handleSetSearchInMatch()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = Auth::user();

        try {
            $stmt = MatchSearch::setStatusInMatch($userId);

            if ($stmt->rowCount() > 0) {
                json_response(['status' => 'updated', 'message' => 'Search marked as in match.']);
            } else {
                json_response(['status' => 'none', 'message' => 'No active search found.']);
            }
        } catch (Exception $e) {
            app_log_exception($e, 'Set search in match failed');
            json_response(['status' => 'error', 'message' => generic_error_message()], 500);
        }
    }

    public function handleSetSearchExpired()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = Auth::user();

        try {
            $stmt = MatchSearch::setStatusExpired($userId);

            if ($stmt->rowCount() > 0) {
                json_response(['status' => 'expired', 'message' => 'Search expired.']);
            } else {
                json_response(['status' => 'none', 'message' => 'No active search found.']);
            }
        } catch (Exception $e) {
            app_log_exception($e, 'Expire search failed');
            json_response(['status' => 'error', 'message' => generic_error_message()], 500);
        }
    }

    public function handleSetSearchActive()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = Auth::user();

        try {
            $stmt = MatchSearch::setStatusActive($userId);

            if ($stmt->rowCount() > 0) {
                json_response(['status' => 'active', 'message' => 'Search is active.']);
            } else {
                json_response(['status' => 'none', 'message' => 'No active search found.']);
            }
        } catch (Exception $e) {
            app_log_exception($e, 'Activate search failed');
            json_response(['status' => 'error', 'message' => generic_error_message()], 500);
        }
    }
}
