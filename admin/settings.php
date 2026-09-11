<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

$admin = require_role(['admin']);
$token = csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
<title>Settings — ConAlert Admin</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css">
</head>
<body class="admin">

<header class="site-header">
  <div class="header-inner">
    <a class="brand" href="/admin/dashboard.php">
      <img src="/img/logo.png" alt="ConAlert" class="brand-logo">
    </a>
    <nav class="tabs">
      <a href="/admin/dashboard.php">Case queue</a>
      <a href="/admin/users.php">Team logins</a>
      <a href="/admin/settings.php" aria-current="page">Settings</a>
      <a href="/" target="_blank">View public site ↗</a>
    </nav>
  </div>
</header>

<main>
  <div class="admin-topbar">
    <span>Signed in as <strong><?= htmlspecialchars($admin['name'], ENT_QUOTES, 'UTF-8') ?></strong> (admin)</span>
    <span><a href="/admin/logout.php">Log out</a></span>
  </div>

  <h1 style="font-family:'IBM Plex Mono',monospace; font-size:1.2rem;">Site settings</h1>

  <div class="new-user-form" style="max-width:640px;">
    <div class="field">
      <label class="field-label" for="contact-email">Contact email (footer)</label>
      <input type="email" id="contact-email-input" placeholder="cases@conalert.org">
    </div>
    <div class="field">
      <label class="field-label" for="footer-align">Footer align</label>
      <select id="footer-align">
        <option value="space-between">Space between (email left, icons right)</option>
        <option value="center">Center</option>
        <option value="left">Left</option>
      </select>
    </div>
    <button class="btn btn-primary" id="save-settings">Save settings</button>
    <div class="form-message" id="settings-message"></div>
  </div>

  <h2 style="font-family:'IBM Plex Mono',monospace; font-size:1rem; margin-top:36px;">Social links</h2>
  <p style="color:var(--ink-soft); font-size:13px;">Default 2: Instagram + Twitter (X). Leave URL blank to hide. Admin can add more — icon maps to platform name (instagram, twitter, x, facebook, youtube, tiktok, linkedin).</p>

  <div id="social-list" style="margin:16px 0;"></div>

  <div class="new-user-form" style="max-width:640px;">
    <h3 style="font-size:0.9rem;">Add social link</h3>
    <div class="field">
      <label class="field-label" for="new-platform">Platform (e.g. instagram, twitter, youtube)</label>
      <input type="text" id="new-platform" placeholder="instagram">
    </div>
    <div class="field">
      <label class="field-label" for="new-url">URL</label>
      <input type="text" id="new-url" placeholder="https://instagram.com/...">
    </div>
    <div class="field">
      <label class="field-label" for="new-label">Label (optional)</label>
      <input type="text" id="new-label" placeholder="Instagram">
    </div>
    <button class="btn" id="add-social">Add / Update</button>
    <div class="form-message" id="social-message"></div>
  </div>
</main>

<script src="/js/admin-settings.js"></script>
</body>
</html>
