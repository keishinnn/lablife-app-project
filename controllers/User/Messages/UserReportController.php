<?php
namespace Controllers\User\Messages;

use Core\Database;
use Core\Auth;
use Models\UserReportModel;
use Exception;

class UserReportController
{
    // GET /u/report/fetch-options
    public function fetchOptions()
    {
        \Core\Middleware::auth();

        $config = require base_path('config/config.php');
        $db = new Database($config['database']);
        $model = new UserReportModel($db);

        $categories = $model->getCategories();
        $reasons = $model->getReasons();

        header('Content-Type: application/json');
        echo json_encode([
            'categories' => $categories,
            'reasons' => $reasons
        ]);
        exit;
    }

    // POST /u/report/submit
    public function submit()
    {
        \Core\Middleware::auth();
        header('Content-Type: application/json');

        $raw = file_get_contents('php://input');
        $data = json_decode($raw, true);

        $other_user_id = $data['other_user_id'] ?? null;
        $category_id = $data['category_id'] ?? null;
        $reason_id = $data['reason_id'] ?? null;

        if (!$other_user_id || !$category_id) {
            http_response_code(400);
            echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
            exit;
        }

        // CSRF check
        $headers = getallheaders();
        $csrfHeader = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? null;
        if (!$csrfHeader || $csrfHeader !== ($_SESSION['csrf_token'] ?? null)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
            exit;
        }

        $reporter_id = Auth::user();
        $config = require base_path('config/config.php');
        $db = new Database($config['database']);
        $model = new UserReportModel($db);

        try {
            $inserted = $model->insertReport($reporter_id, $other_user_id, $category_id, $reason_id);

            echo json_encode(['success' => true, 'report_id' => $inserted['id'] ?? null]);
        } catch (Exception $e) {
            // Log error on backend
            error_log('Report submit error: ' . $e->getMessage());

            // Send details to client (for debugging only!)
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Internal server error',
                'error'   => $e->getMessage(),
                'trace'   => $e->getTraceAsString()
            ]);
            exit;
        }
    }
}
