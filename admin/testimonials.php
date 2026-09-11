<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

$admin = require_login(); // admin or staff
$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
<title>Testimonials — ConAlert Admin</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css">
</head>
<body class="admin">

<header class="site-header">
  <div class="header-inner">
    <a class="brand" href="/admin/dashboard.php">
      <span class="brand-mark">ADMIN</span>
      <span class="brand-name">ConAlert — Testimonials</span>
    </a>
    <nav class="tabs">
      <a href="/admin/dashboard.php">Case queue</a>
      <?php if ($admin['role'] === 'admin'): ?>
        <a href="/admin/settings.php">Site settings</a>
        <a href="/admin/users.php">Team logins</a>
      <?php endif; ?>
      <a href="/" target="_blank">View public site ↗</a>
    </nav>
  </div>
</header>

<main>
  <div class="admin-topbar">
    <span>Signed in as <strong><?= htmlspecialchars($admin['name'], ENT_QUOTES, 'UTF-8') ?></strong>
      (<?= htmlspecialchars($admin['role'], ENT_QUOTES, 'UTF-8') ?>)</span>
    <span><a href="/admin/logout.php">Log out</a></span>
  </div>

  <div class="filters" id="status-filters">
    <button type="button" data-status="pending" aria-pressed="true">Pending</button>
    <button type="button" data-status="approved" aria-pressed="false">Approved</button>
    <button type="button" data-status="rejected" aria-pressed="false">Rejected</button>
    <button type="button" data-status="" aria-pressed="false">All</button>
  </div>

  <div id="testimonial-rows">
    <p>Loading…</p>
  </div>
</main>

<script src="/js/admin-testimonials.js"></script>
</body>
</html>
