<?php

namespace Controllers\Admin;

use Core\Database;

class AdminUserReportController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }

        require base_path('views/admin/user-reports.view.php');
    }

    // ✅ API endpoint for infinite scroll
    public function listJson()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'unauthorized']);
            return;
        }

        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 10;
        $offset = ($page - 1) * $limit;

        $config = require base_path('config/config.php');
        $db = new Database($config['database']);

        try {
            $sql = "
                SELECT id, reason, created_at, reporter_id, reported_user_id
                FROM user_reports
                ORDER BY created_at DESC
                LIMIT $limit OFFSET $offset
            ";
            $stmt = $db->query($sql);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            $cleanRows = [];
            foreach ($rows as $r) {
                $excerpt = mb_substr($r['reason'] ?? '', 0, 120);
                if (strlen($r['reason'] ?? '') > 120) {
                    $excerpt .= '...';
                }
                $cleanRows[] = [
                    'id' => $r['id'] ?? '',
                    'reason' => $r['reason'] ?? '',
                    'created_at' => $r['created_at'] ?? '',
                    'reporter_id' => $r['reporter_id'] ?? '',
                    'reported_user_id' => $r['reported_user_id'] ?? '',
                    'excerpt' => $excerpt,
                ];
            }

            header('Content-Type: application/json');
            echo json_encode(['data' => $cleanRows], JSON_UNESCAPED_UNICODE);

        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'server_error',
                'message' => $e->getMessage()
            ]);
        }
    }

    // ✅ Popup detail view
    public function detailJson()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_id'])) {
            http_response_code(401);
            echo json_encode(['error' => 'unauthorized']);
            return;
        }

        $id = $_GET['id'] ?? null;
        if (!$id) {
            http_response_code(400);
            echo json_encode(['error' => 'invalid_id']);
            return;
        }

        $config = require base_path('config/config.php');
        $db = new Database($config['database']);

        try {
            $stmt = $db->query(
                "SELECT id, reason, created_at, reporter_id, reported_user_id
                 FROM user_reports
                 WHERE id = :id",
                [':id' => $id]
            );
            $data = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$data) {
                http_response_code(404);
                echo json_encode(['error' => 'not_found']);
                return;
            }

            header('Content-Type: application/json');
            echo json_encode(['data' => $data], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode([
                'error' => 'server_error',
                'message' => $e->getMessage()
            ]);
        }
    }
}
