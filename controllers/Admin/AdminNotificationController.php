<?php

namespace Controllers\Admin;

use Core\Database;

class AdminNotificationController
{
    public function checkNewReports()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'unauthorized']);
            return;
        }

        $config = require base_path('config/config.php');
        $db = new Database($config['database']);

        $since = $_GET['since'] ?? null;

        try {
            $query = "
                SELECT 'bug' AS type, title, created_at FROM bug_reports
                WHERE created_at > :since
                UNION ALL
                SELECT 'user' AS type, reason AS title, created_at FROM user_reports
                WHERE created_at > :since
                ORDER BY created_at DESC
                LIMIT 5
            ";

            $rows = $db->query($query, [':since' => $since ?: '1970-01-01'])->fetchAll();

            header('Content-Type: application/json');
            echo json_encode(['new_reports' => $rows]);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['error' => 'server_error', 'message' => $e->getMessage()]);
        }
    }
}
