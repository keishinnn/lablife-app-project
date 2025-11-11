<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="/assets/images/logo.png">
  <title>Admin Login | LabLife</title>
  <link rel="stylesheet" href="/assets/css/global.css">
  <link rel="stylesheet" href="/assets/css/admin/admin-login.css?v=<?php echo time(); ?>">
</head>

<body>
  <div class="admin-login-container">
    <div class="admin-login-card">
      <h2>Admin Login</h2>
      <?php if (!empty($error)): ?>
        <p class="error"><?= htmlspecialchars($error) ?></p>
      <?php endif; ?>
      <form method="POST" action="/admin/login" autocomplete="off">
        <label>Email</label>
        <input type="email" name="email" required autocomplete="off">

        <label>Password</label>
        <input type="password" name="password" required autocomplete="new-password">

        <button type="submit">Login</button>
      </form>
    </div>
  </div>
</body>

</html>