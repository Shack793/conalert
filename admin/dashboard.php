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
<title>Admin — ConAlert</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css">
</head>
<body class="admin">

<header class="site-header">
  <div class="header-inner">
    <a class="brand" href="/admin/dashboard.php">
      <span class="brand-mark">ADMIN</span>
      <span class="brand-name">ConAlert — Case Queue</span>
    </a>
    <nav class="tabs">
      <a href="/" target="_blank">View public site ↗</a>
      <a href="/admin/testimonials.php">Testimonials</a>
      <?php if ($admin['role'] === 'admin'): ?>
        <a href="/admin/settings.php">Site settings</a>
        <a href="/admin/users.php">Team logins</a>
      <?php endif; ?>
    </nav>
  </div>
</header>

<main>
  <div class="admin-topbar">
    <span>Signed in as <strong><?= htmlspecialchars($admin['name'], ENT_QUOTES, 'UTF-8') ?></strong>
      (<?= htmlspecialchars($admin['role'], ENT_QUOTES, 'UTF-8') ?>)</span>
    <span>
      <a href="#" id="change-password-link">Change my password</a>
      <a href="/admin/logout.php">Log out</a>
    </span>
  </div>

  <div class="admin-summary" id="summary"></div>

  <div class="filters" id="status-filters">
    <button type="button" data-status="" aria-pressed="true">All</button>
    <button type="button" data-status="new" aria-pressed="false">New</button>
    <button type="button" data-status="in_review" aria-pressed="false">In review</button>
    <button type="button" data-status="verified" aria-pressed="false">Verified</button>
    <button type="button" data-status="published" aria-pressed="false">Published</button>
    <button type="button" data-status="resolved" aria-pressed="false">Resolved</button>
    <button type="button" data-status="rejected" aria-pressed="false">Rejected</button>
  </div>

  <table class="case-table">
    <thead>
      <tr>
        <th>Case #</th><th>Received</th><th>Platform</th><th>Type</th><th>Amount</th><th>Reporter</th><th>Status</th><th>Priority</th>
      </tr>
    </thead>
    <tbody id="case-rows">
      <tr><td colspan="8">Loading…</td></tr>
    </tbody>
  </table>
</main>

<div class="drawer" id="drawer" aria-hidden="true">
  <button class="close-btn" id="drawer-close" aria-label="Close">✕</button>
  <h2 id="drawer-title">Case #—</h2>

  <div id="drawer-readonly"></div>

  <label for="drawer-case-number">Case number (shown publicly on the case wall)</label>
  <input type="text" id="drawer-case-number" maxlength="50">

  <label for="drawer-status">Status</label>
  <select id="drawer-status">
    <option value="new">new</option>
    <option value="in_review">in_review</option>
    <option value="verified">verified</option>
    <option value="published">published</option>
    <option value="resolved">resolved</option>
    <option value="rejected">rejected</option>
  </select>

  <label for="drawer-priority">Priority</label>
  <select id="drawer-priority">
    <option value="low">low</option>
    <option value="normal">normal</option>
    <option value="high">high</option>
  </select>

  <label for="drawer-summary">Public summary (shown on case wall if published)</label>
  <textarea id="drawer-summary" placeholder="Write a fact-checked, careful summary — 'reporter states...' not flat accusations."></textarea>

  <label for="drawer-notes">Admin notes (private, never public)</label>
  <textarea id="drawer-notes" placeholder="Follow-up needed, evidence quality, correspondence with the platform, etc."></textarea>

  <button class="btn btn-primary save-btn" id="drawer-save">Save changes</button>
  <div class="form-message" id="drawer-message"></div>

  <div class="event-log">
    <strong>History</strong>
    <ul id="drawer-events"></ul>
  </div>
</div>

<div class="drawer" id="password-drawer" aria-hidden="true">
  <button class="close-btn" id="password-drawer-close" aria-label="Close">✕</button>
  <h2>Change my password</h2>
  <label for="current-password">Current password</label>
  <input type="password" id="current-password">
  <label for="new-password">New password (min. 10 characters)</label>
  <input type="password" id="new-password">
  <button class="btn btn-primary save-btn" id="password-save">Update password</button>
  <div class="form-message" id="password-message"></div>
</div>

<script src="/js/markdown-toolbar.js"></script>
<script src="/js/admin-dashboard.js"></script>
</body>
</html>
