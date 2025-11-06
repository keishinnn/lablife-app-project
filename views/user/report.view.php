<?php include base_path('views/shared/header.php'); ?>

<link rel="stylesheet" href="/assets/css/user-report/report.css?v=<?php echo time(); ?>">

<div class="report-container">
    <h1>Report User</h1>
    <p>Please describe the reason for reporting this user.</p>

    <form action="/u/submit-user-report" method="POST" class="report-form">
        <input type="hidden" name="reported_user_id" value="<?= htmlspecialchars($_GET['user_id'] ?? '') ?>">

        <label for="reason">Reason for Reporting</label>
        <textarea name="reason" id="reason" rows="4" placeholder="Describe why you're reporting this user..." required></textarea>

        <div class="submit-wrap">
            <button type="submit">Submit Report</button>
        </div>
    </form>
</div>

<?php include base_path('views/shared/footer.php'); ?>
