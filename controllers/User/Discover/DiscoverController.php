<?php

namespace Controllers\User\Discover;

use Core\App;
use Core\Auth;
use Models\User\User;
use Models\Match\MatchCandidate;
use Models\Match\MatchSearch;
use Models\Match\MatchSession;
use Exception;
use PDO;

class DiscoverController
{

    public function View()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $user = User::getCurrentUserProfile($userId);
        \Core\Middleware::checkIfUserExist($user);

        view('user/discover/index.view.php');
    }

    public function MatchedUserView()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $user = User::getCurrentUserProfile($userId);
        \Core\Middleware::checkIfUserExist($user);

        $partnerId = $_GET['partner'] ?? null;

        // prevent another user from putting another uuid into url
        if (!$partnerId || !$userId) {
            http_response_code(400);
            echo "Missing partner parameter.";
            return;
        }

        $session = MatchSession::checkSession($userId, $partnerId);

        if (!$session) {
            header('Location: /u/discover');
        }

        $partner = \Models\User\User::getCurrentUserProfile($partnerId);
        \Core\Middleware::checkIfUserExist($partner);

        view('user/discover/matched.view.php', compact('partner'));
    }

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

    public function handleSetExpiredSession()
    {
        \Core\Middleware::auth();
        $userId = Auth::user();

        header('Content-Type: application/json');

        try {
            $stmt = MatchSession::setMatchSessionExpired($userId);

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

    public function handleSetRejectedSession()
    {
        \Core\Middleware::auth();
        $userId = Auth::user();

        header('Content-Type: application/json');

        try {
            $stmt = MatchSession::setMatchSessionRejected($userId);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'rejected', 'message' => 'Match session rejected']);
            } else {
                echo json_encode(['status' => 'none', 'message' => 'No active search found.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function handleSetMatchedSession()
    {
        \Core\Middleware::auth();
        $userId = Auth::user();

        header('Content-Type: application/json');

        try {
            $stmt = MatchSession::setMatchSessionMatched($userId);

            if ($stmt->rowCount() > 0) {
                echo json_encode(['status' => 'matched', 'message' => 'Match session rejected']);
            } else {
                echo json_encode(['status' => 'none', 'message' => 'No active search found.']);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function handleLikeOtherUser()
    {
        \Core\Middleware::auth();
        $userId = Auth::user();

        $data = json_decode(file_get_contents('php://input'), true);

        $partnerId = $data['partner'] ?? null;
        $csrfToken = $data['csrf_token'] ?? null;

        if (!$partnerId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing partner ID']);
            return;
        }

        try {
            MatchCandidate::likeOtherUser($userId, $partnerId);
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }


    public function handledisLikeOtherUser()
    {
        \Core\Middleware::auth();
        \Core\Middleware::verifyCSRFToken();
        $userId = Auth::user();

        $data = json_decode(file_get_contents('php://input'), true);

        $partnerId = $data['partner'] ?? null;

        if (!$partnerId) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Missing partner ID']);
            return;
        }

        try {
            MatchCandidate::dislikeOtherUser($userId, $partnerId);
            echo json_encode(['status' => 'success']);
        } catch (\Exception $e) {
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    public function handleFindPotentialMatch()
    {
        \Core\Middleware::auth();
        $userId = \Core\Auth::user();
        $db = \Core\App::resolve('Core\Database')->getConnection();

        // Start the search (insert/update active search)
        MatchSearch::startSearch($userId);

        // Try to find another active user to match
        $activeUsers = MatchSearch::getActiveUsersExcept($userId);

        if (!empty($activeUsers)) {

            $matchedUser = MatchCandidate::findPotentialMatch($userId, $activeUsers);

            if ($matchedUser) {
                $session = MatchSession::createSession($userId, $matchedUser['candidate_id'], 60);

                if ($session) {
                    // Mark both users as matched
                    $stmt = $db->prepare("
                    UPDATE active_match_searches 
                    SET status = 'matched', last_active = NOW()
                    WHERE user_id IN (:a, :b)
                ");
                    $stmt->execute(['a' => $userId, 'b' => $matchedUser['candidate_id']]);

                    echo json_encode([
                        'status' => 'matched',
                        'match_id' => $session['id'],
                        'partner_id' => $matchedUser['candidate_id']
                    ]);
                    return;
                }
            }
        }

        // No match found yet — keep searching
        echo json_encode([
            'status' => 'search_started',
            'user_id' => $userId,
            'message' => 'Waiting for a match...'
        ]);
    }
}
