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
<title>Team logins — ConAlert Admin</title>
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
      <a href="/" target="_blank">View public site ↗</a>
    </nav>
  </div>
</header>

<main>
  <div class="admin-topbar">
    <span>Signed in as <strong><?= htmlspecialchars($admin['name'], ENT_QUOTES, 'UTF-8') ?></strong> (admin)</span>
    <span><a href="/admin/logout.php">Log out</a></span>
  </div>

  <h1 style="font-family:'IBM Plex Mono',monospace; font-size:1.2rem;">Team logins</h1>
  <p style="max-width:640px; color:var(--ink-soft);">
    Create logins for assistants or dev team members here. <strong>Staff</strong> accounts can review and update
    cases but can't manage other logins. <strong>Admin</strong> accounts can do everything, including creating
    and revoking other accounts. Revoking an account takes effect immediately, even if that person is
    currently logged in.
  </p>

  <button class="btn btn-primary" id="show-new-user-form">+ Add a login</button>

  <div class="new-user-form" id="new-user-form" style="display:none;">
    <div class="field">
      <label class="field-label" for="nu-name">Name</label>
      <input type="text" id="nu-name">
    </div>
    <div class="field">
      <label class="field-label" for="nu-email">Email</label>
      <input type="email" id="nu-email">
    </div>
    <div class="field">
      <label class="field-label" for="nu-role">Role</label>
      <select id="nu-role">
        <option value="staff">Staff (case management only)</option>
        <option value="admin">Admin (full access, incl. team logins)</option>
      </select>
    </div>
    <button class="btn btn-primary" id="create-user-btn">Create login</button>
    <div class="form-message" id="new-user-message"></div>
  </div>

  <div class="user-table-wrap">
    <table class="user-table">
      <thead>
        <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th>Last login</th><th>Actions</th></tr>
      </thead>
      <tbody id="user-rows">
        <tr><td colspan="7">Loading…</td></tr>
      </tbody>
    </table>
  </div>
</main>

<script src="/js/admin-users.js"></script>
</body>
</html>
