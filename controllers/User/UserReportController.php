<?php

namespace Controllers\User;

use Core\Database;

class UserReportController
{
    public function showForm()
    {
        $reportedUserId = $_GET['user_id'] ?? null;

        if (!$reportedUserId) {
            die('Invalid request. Missing user ID.');
        }

        require base_path('views/user/report.view.php');
    }

    public function submit()
    {
        $config = require base_path('config/config.php');
        $db = new Database($config['database']);

        $reporterId = $_SESSION['user_id'] ?? null;
        $reportedUserId = trim($_POST['reported_user_id'] ?? '');
        $reason = trim($_POST['reason'] ?? '');

        if (!$reporterId) {
            die('You must be logged in to report a user.');
        }

        if (empty($reportedUserId) || empty($reason)) {
            die('Missing fields.');
        }

        if ($reporterId === $reportedUserId) {
            die('You cannot report yourself.');
        }

        try {
            // Optional: prevent duplicate reports per day
            $checkQuery = "SELECT COUNT(*) AS report_count 
                           FROM user_reports 
                           WHERE reporter_id = :reporter_id
                           AND reported_user_id = :reported_user_id
                           AND created_at::date = CURRENT_DATE";
            $result = $db->query($checkQuery, [
                ':reporter_id' => $reporterId,
                ':reported_user_id' => $reportedUserId
            ])->fetch(\PDO::FETCH_ASSOC);

            if ($result && $result['report_count'] >= 1) {
                header('Location: /u/report-user?duplicate=true');
                exit;
            }

            // Insert report
            $insertQuery = "INSERT INTO user_reports (reporter_id, reported_user_id, reason, created_at)
                            VALUES (:reporter_id, :reported_user_id, :reason, NOW())";
            $db->query($insertQuery, [
                ':reporter_id' => $reporterId,
                ':reported_user_id' => $reportedUserId,
                ':reason' => $reason
            ]);

            require base_path('views/user/success.view.php');

        } catch (\Exception $e) {
            echo "Error submitting user report: " . $e->getMessage();
        }
    }
}
