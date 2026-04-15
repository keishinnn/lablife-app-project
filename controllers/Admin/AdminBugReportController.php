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

    public function handleDeleteBugReport()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }

        $bugReportId = $_POST['bug_report_id'];

        BugReports::deleteBugReport($bugReportId);

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

        $inProgressBugReportId = $_POST['bug_report_id'];

        BugReports::setResolvedBugReport($inProgressBugReportId);

        header('Location: /admin/bug-reports');
        exit;
    }

    public function handleSetInProgressBugReport()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }

        $awaitingBugReportId = $_POST['bug_report_id'];

        BugReports::setInProgressBugReport($awaitingBugReportId);

        header('Location: /admin/bug-reports');
        exit;
    }
}
