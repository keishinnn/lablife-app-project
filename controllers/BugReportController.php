<?php

namespace Controllers;

use Core\Database;

class BugReportController
{
    public function showForm()
    {
        require base_path('Views/bugreport/form.view.php');
    }

    public function submit()
    {
        $config = require base_path('config/config.php');
        $db = new Database($config['database']);

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $user_id = $_SESSION['user_id'] ?? null;

        if (!$user_id) {
            die('You must be logged in to report a bug.');
        }

        if (empty($title) || empty($description)) {
            die('Missing fields.');
        }

        try {
            // Check how many bug reports this user made today
            $checkQuery = "SELECT COUNT(*) AS report_count 
                           FROM bug_reports 
                           WHERE user_id = :user_id 
                           AND created_at::date = CURRENT_DATE";
            $result = $db->query($checkQuery, [':user_id' => $user_id])->fetch(\PDO::FETCH_ASSOC);

            // Redirect back to form if user has reached limit
            if ($result && $result['report_count'] >= 5) {
                header('Location: /bug-report?limit=true');
                exit;
            }

            // Insert the new report
            $insertQuery = "INSERT INTO bug_reports (user_id, title, description, created_at) 
                            VALUES (:user_id, :title, :description, NOW())";
            $db->query($insertQuery, [
                ':user_id' => $user_id,
                ':title' => $title,
                ':description' => $description
            ]);

            // Show success view
            require base_path('Views/bugreport/success.view.php');

        } catch (\Exception $e) {
            echo "Error submitting bug report: " . $e->getMessage();
        }
    }
}
