<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Report a Bug | LabLife</title>
  <link rel="stylesheet" href="/assets/css/global.css">
  <link rel="stylesheet" href="/assets/css/bug-report/bug-report.css?v=<?php echo time(); ?>">
</head>
<body>

  <?php require base_path('Views/shared/header.php'); ?>

  <main class="bug-report-container">
    <h1><span>Found a Bug?</span></h1>
    <p>Help us improve LabLife by reporting any issues or glitches you encounter.</p>

    <form action="/submit-bug" method="POST" class="bug-report-form">
      <label for="title">Bug Title</label>
      <input type="text" name="title" id="title" required>

      <label for="description">Description</label>
      <textarea name="description" id="description" required></textarea>

      <div class="submit-wrap">
        <button type="submit">Submit Report</button>
      </div>
    </form>
  </main>

  <?php require base_path('Views/shared/footer.php'); ?>
  <?php if (isset($_GET['limit']) && $_GET['limit'] === 'true'): ?>
  <script>
    alert('You have reached your bug report limit for today (5 reports). Try again tomorrow.');
  </script>
<?php endif; ?>

</body>
</html>
