<?php

namespace Controllers\Admin;

use Core\Database;

class AdminDashboardController
{
    public function index()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();

        if (empty($_SESSION['admin_id'])) {
            header("Location: /admin/login");
            exit;
        }

        $config = require base_path('config/config.php');
        $db = new Database($config['database']);

        $data = [
            'total_reports' => 0,
            'user_reports' => 0,
            'bug_reports' => 0,
            'finished_reports' => 0,
            'recent_user_reports' => [],
            'recent_bug_reports' => [],
            'active_users' => 0,
            'matches_trend' => []
        ];

        try {
            // --- Count Reports ---
            $data['user_reports'] = (int)($db->query("SELECT COUNT(*) AS c FROM user_reports")->fetch()['c'] ?? 0);
            $data['bug_reports']  = (int)($db->query("SELECT COUNT(*) AS c FROM bug_reports")->fetch()['c'] ?? 0);
            $data['total_reports'] = $data['user_reports'] + $data['bug_reports'];

            // --- Finished reports placeholder ---
            $data['finished_reports'] = 0;

            // --- Recent user reports ---
            $data['recent_user_reports'] = $db->query("
                SELECT id, reason, created_at
                FROM user_reports
                ORDER BY created_at DESC
                LIMIT 5
            ")->fetchAll();

            // --- Recent bug reports ---
            $data['recent_bug_reports'] = $db->query("
                SELECT id, title, description, created_at
                FROM bug_reports
                ORDER BY created_at DESC
                LIMIT 5
            ")->fetchAll();

            // --- Active users count ---
            $row = $db->query("SELECT COUNT(*) AS active FROM users WHERE is_online = TRUE")->fetch();
            $data['active_users'] = (int)($row['active'] ?? 0);

            // --- Matches trend (past 7 days) ---
            $data['matches_trend'] = $db->query("
                SELECT 
                    TO_CHAR(created_at::date, 'Mon DD') AS date,
                    COUNT(*) AS count
                FROM matches
                WHERE created_at >= NOW() - INTERVAL '7 days'
                GROUP BY created_at::date
                ORDER BY created_at::date ASC
            ")->fetchAll();

        } catch (\Throwable $e) {
            error_log('AdminDashboard error: ' . $e->getMessage());
        }

        require base_path('views/admin/dashboard.view.php');
    }
}
