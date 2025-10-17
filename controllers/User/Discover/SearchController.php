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
        $userId = Auth::user();

        header('Content-Type: application/json');

        try {
            $stmt = MatchSearch::stopSearch($userId);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'stopped', 'message' => 'Active search deleted.']);
            } else {
                echo json_encode(['status' => 'none', 'message' => 'No active search found.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function handleSetSearchInMatch()
    {
        \Core\Middleware::auth();
        $userId = Auth::user();

        header('Content-Type: application/json');

        try {
            $stmt = MatchSearch::setStatusInMatch($userId);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'stopped', 'message' => 'Active search deleted.']);
            } else {
                echo json_encode(['status' => 'none', 'message' => 'No active search found.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function handleSetSearchExpired()
    {
        \Core\Middleware::auth();
        $userId = Auth::user();

        header('Content-Type: application/json');

        try {
            $stmt = MatchSearch::setStatusExpired($userId);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'stopped', 'message' => 'Active search deleted.']);
            } else {
                echo json_encode(['status' => 'none', 'message' => 'No active search found.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function handleSetSearchActive()
    {
        \Core\Middleware::auth();
        $userId = Auth::user();

        header('Content-Type: application/json');

        try {
            $stmt = MatchSearch::setStatusActive($userId);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'stopped', 'message' => 'Active search deleted.']);
            } else {
                echo json_encode(['status' => 'none', 'message' => 'No active search found.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }
}
