<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/settings.php';

// Already logged in? Skip straight to the dashboard.
if (current_admin()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_post()) {
        $error = 'Your session expired — please try again.';
    } else {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $admin = login_admin($email, $password);
        if ($admin) {
            header('Location: /admin/dashboard.php');
            exit;
        }
        $error = 'Incorrect email or password, or this account has been revoked.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin login — ConAlert</title>
<link rel="icon" href="<?= htmlspecialchars(get_setting('favicon_path'), ENT_QUOTES, 'UTF-8') ?>">
<link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,400;0,500;0,600;1,400&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css">
</head>
<body>

<header class="site-header">
  <div class="header-inner">
    <a class="brand" href="/">
      <span class="brand-mark">CASE&nbsp;FILE</span>
      <span class="brand-name">ConAlert</span>
    </a>
  </div>
</header>

<div class="login-shell">
  <div class="login-card">
    <h1>Admin sign in</h1>
    <?php if ($error): ?>
      <p class="field-error"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="POST" action="/admin/login.php">
      <?= csrf_field() ?>
      <div class="field">
        <label class="field-label" for="email">Email</label>
        <input type="email" id="email" name="email" required autofocus>
      </div>
      <div class="field">
        <label class="field-label" for="password">Password</label>
        <input type="password" id="password" name="password" required>
      </div>
      <button type="submit" class="btn btn-primary">Sign in</button>
    </form>
  </div>
</div>

</body>
</html>
