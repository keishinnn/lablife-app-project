<?php

namespace Controllers\Admin;

use Core\Database;
use Models\Reports\UserReports;

class AdminUserReportController
{
    public function View()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }

        $awaitingUserReports = UserReports::getAllAwaitingUserReports();
        $inProgressUserReports = UserReports::getAllInProgressUserReports();
        $resolvedUserReports = UserReports::getAllResolvedUserReports();

        view('admin/user-reports.view.php', compact('awaitingUserReports', 'inProgressUserReports', 'resolvedUserReports'));
    }

    public function handleSetResolvedUserReport()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }

        $inProgressUserReportId = $_POST['user_report_id'];

        UserReports::setResolvedUserReport($inProgressUserReportId);

        header('Location: /admin/user-reports');
        exit;
    }

    public function handleSetInProgressUserReport()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }

        $awaitingUserReportId = $_POST['user_report_id'];

        UserReports::setInProgressUserReport($awaitingUserReportId);

        header('Location: /admin/user-reports');
        exit;
    }

    public function handleDeleteUserReport()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }

        $userReportId = $_POST['user_report_id'];

        UserReports::deleteUserReport($userReportId);

        header('Location: /admin/user-reports');
        exit;
    }

    public function handleDeleteUser()
    {
        if (session_status() === PHP_SESSION_NONE) session_start();
        if (empty($_SESSION['admin_id'])) {
            header('Location: /admin/login');
            exit;
        }

        $reportedUserId = $_POST['reported_user_id'];
        $inProgressUserReportId = $_POST['user_report_id'];

        UserReports::deleteUser($reportedUserId);
        UserReports::setResolvedUserReport($inProgressUserReportId);

        header('Location: /admin/user-reports');
        exit;
    }
}
