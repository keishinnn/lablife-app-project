<?php

namespace Controllers\Admin;

use Core\Database;
use Models\User\User;
use Models\Reports\UserReports;
use Models\Reports\BugReports;
use Models\Match\Matches;

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

        $activeUsersCount = User::getAllActiveUsersCount();
        $userReportsCount = UserReports::getAllUserReportsCount();
        $bugReportsCount = BugReports::getAllBugReportsCount();
        $allReportsCount = UserReports::getAllReportsCount();

        $recentBugReports = BugReports::getRecentBugReports();
        $recentUserReports = UserReports::getRecentUserReports();

        $allResolvedReportsCount = UserReports::getAllResolvedReportsCount();
        $allMatchesCount = Matches::getAllMatchesCount();

        view('admin/dashboard.view.php', compact('activeUsersCount', 'userReportsCount', 'bugReportsCount', 'allReportsCount', 'recentBugReports', 'recentUserReports', 'allResolvedReportsCount', 'allMatchesCount'));
    }
}
