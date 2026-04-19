<?php

namespace Controllers\User\Messages;

use Core\Database;
use Models\User\UserReportModel;

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

    public function submit()
    {
        \Core\Middleware::auth();
        header('Content-Type: application/json');

        $config = require base_path('config/config.php');
        $db = new Database($config['database']);

        $reporterId = $_SESSION['user_id'] ?? null;

        $input = json_decode(file_get_contents('php://input'), true);
        if (!is_array($input)) {
            echo json_encode(['success' => false, 'message' => 'Invalid request payload.']);
            exit;
        }

        $reportedUserId = trim($input['other_user_id'] ?? '');
        $categoryId = trim($input['category_id'] ?? '');
        $reasonId = trim($input['reason_id'] ?? '');

        $contextMessages = $input['context_messages'] ?? []; 
        $contextJson = null;

        if (!empty($contextMessages)) {
            $contextJson = json_encode($contextMessages, JSON_UNESCAPED_UNICODE);
        }

        if (!$reporterId) {
            echo json_encode(['success' => false, 'message' => 'You must be logged in to report a user.']);
            exit;
        }

        if (empty($reportedUserId) || empty($categoryId)) {
            echo json_encode(['success' => false, 'message' => 'Missing required fields.']);
            exit;
        }

        if ($reporterId === $reportedUserId) {
            echo json_encode(['success' => false, 'message' => 'You cannot report yourself.']);
            exit;
        }

        try {
            $stmt = $db->prepare("
            INSERT INTO user_reports 
                (id, reporter_id, reported_user_id, category_id, reason_id, status_id, context, created_at)
            VALUES 
                (gen_random_uuid(), :reporter_id, :reported_user_id, :category_id, :reason_id, :status_id, :context::jsonb, NOW())
        ");

            $stmt->bindValue(':reporter_id', $reporterId);
            $stmt->bindValue(':reported_user_id', $reportedUserId);
            $stmt->bindValue(':category_id', $categoryId);
            $stmt->bindValue(':reason_id', $reasonId ?: null);
            $stmt->bindValue(':status_id', 'b46a33a6-f4b5-49d0-8307-bf83a1d6e0de');
            $stmt->bindValue(':context', $contextJson, \PDO::PARAM_STR);
            $stmt->execute();

            echo json_encode(['success' => true, 'message' => 'Report submitted successfully.']);
        } catch (\Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error submitting report: ' . $e->getMessage()]);
        }
    }
}
