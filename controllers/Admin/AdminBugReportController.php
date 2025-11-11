<?php

namespace Controllers\Admin;

use Models\Reports\BugReports;

class AdminBugReportController
{
    public function View()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }

        $awaitingBugReports = BugReports::getAllAwaitingBugReports();
        $inProgressBugReports = BugReports::getAllInProgressBugReports();
        $resolvedBugReports = BugReports::getAllResolvedBugReports();

        view('admin/bugreports.view.php', compact('awaitingBugReports', 'inProgressBugReports', 'resolvedBugReports'));
    }

    public function handleDeleteAwaitingBugReport()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }

        $awaitingBugReportId = $_POST['awaiting_bug_report_id'];

        BugReports::deleteAwaitingBugReport($awaitingBugReportId);

        header('Location: /admin/bug-reports');
        exit;
    }

    public function handleSetResolvedBugReport()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }

        $inProgressBugReportId = $_POST['in_progress_bug_report_id'];

        BugReports::setResolvedBugReport($inProgressBugReportId);

        header('Location: /admin/bug-reports');
        exit;
    }
}
