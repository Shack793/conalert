<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/settings.php';

$admin = require_role(['admin']);
$token = csrf_token();
$settings = get_all_settings();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
<title>Site settings — ConAlert Admin</title>
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css">
</head>
<body class="admin">

<header class="site-header">
  <div class="header-inner">
    <a class="brand" href="/admin/dashboard.php">
      <span class="brand-mark">ADMIN</span>
      <span class="brand-name">ConAlert — Site Settings</span>
    </a>
    <nav class="tabs">
      <a href="/admin/dashboard.php">Case queue</a>
      <a href="/admin/testimonials.php">Testimonials</a>
      <a href="/admin/users.php">Team logins</a>
      <a href="/" target="_blank">View public site ↗</a>
    </nav>
  </div>
</header>

<main>
  <div class="admin-topbar">
    <span>Signed in as <strong><?= htmlspecialchars($admin['name'], ENT_QUOTES, 'UTF-8') ?></strong> (admin)</span>
    <span><a href="/admin/logout.php">Log out</a></span>
  </div>

  <form id="settings-form">

    <div class="settings-section">
      <h2>Branding</h2>
      <div class="branding-preview">
        <img id="logo-preview" src="<?= htmlspecialchars($settings['logo_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Current logo">
        <img class="fav" id="favicon-preview" src="<?= htmlspecialchars($settings['favicon_path'], ENT_QUOTES, 'UTF-8') ?>" alt="Current favicon">
      </div>
      <div class="field">
        <label class="field-label" for="logo-file">Replace logo (SVG or PNG, shown on navy backgrounds — use a light-colored logo)</label>
        <input type="file" id="logo-file" name="logo" accept=".svg,.png">
      </div>
      <div class="field">
        <label class="field-label" for="favicon-file">Replace favicon (SVG, PNG, or ICO)</label>
        <input type="file" id="favicon-file" name="favicon" accept=".svg,.png,.ico">
      </div>
    </div>

    <div class="settings-section">
      <h2>Footer &amp; contact</h2>
      <div class="field">
        <label class="field-label" for="footer_about">About blurb (shown in the footer)</label>
        <textarea id="footer_about" name="footer_about"><?= htmlspecialchars($settings['footer_about'], ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
      <div class="field-row">
        <div class="field">
          <label class="field-label" for="contact_email">Contact email</label>
          <input type="email" id="contact_email" name="contact_email" value="<?= htmlspecialchars($settings['contact_email'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="field">
          <label class="field-label" for="contact_phone">Contact phone (optional)</label>
          <input type="text" id="contact_phone" name="contact_phone" value="<?= htmlspecialchars($settings['contact_phone'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>
      <div class="field-row">
        <div class="field">
          <label class="field-label" for="social_x">X / Twitter URL</label>
          <input type="text" id="social_x" name="social_x" value="<?= htmlspecialchars($settings['social_x'], ENT_QUOTES, 'UTF-8') ?>" placeholder="https://x.com/...">
        </div>
        <div class="field">
          <label class="field-label" for="social_facebook">Facebook URL</label>
          <input type="text" id="social_facebook" name="social_facebook" value="<?= htmlspecialchars($settings['social_facebook'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>
      <div class="field-row">
        <div class="field">
          <label class="field-label" for="social_instagram">Instagram URL</label>
          <input type="text" id="social_instagram" name="social_instagram" value="<?= htmlspecialchars($settings['social_instagram'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
        <div class="field">
          <label class="field-label" for="social_tiktok">TikTok URL</label>
          <input type="text" id="social_tiktok" name="social_tiktok" value="<?= htmlspecialchars($settings['social_tiktok'], ENT_QUOTES, 'UTF-8') ?>">
        </div>
      </div>
      <div class="field">
        <label class="field-label" for="social_linkedin">LinkedIn URL</label>
        <input type="text" id="social_linkedin" name="social_linkedin" value="<?= htmlspecialchars($settings['social_linkedin'], ENT_QUOTES, 'UTF-8') ?>">
      </div>
      <p class="field-hint">Leave any social field blank to hide that icon from the footer.</p>
    </div>

    <div class="settings-section">
      <h2>About Us page</h2>
      <div class="toggle-row">
        <input type="checkbox" id="about_enabled" name="about_enabled" <?= $settings['about_enabled'] === '1' ? 'checked' : '' ?>>
        <label for="about_enabled">Show the About Us page and nav link</label>
      </div>
      <div class="field">
        <label class="field-label" for="about_content">Page content</label>
        <textarea id="about_content" name="about_content" style="min-height:220px;"><?= htmlspecialchars($settings['about_content'], ENT_QUOTES, 'UTF-8') ?></textarea>
      </div>
    </div>

    <div class="settings-section">
      <h2>Testimonials page</h2>
      <div class="toggle-row">
        <input type="checkbox" id="testimonials_enabled" name="testimonials_enabled" <?= $settings['testimonials_enabled'] === '1' ? 'checked' : '' ?>>
        <label for="testimonials_enabled">Show the Testimonials page, nav link, and homepage section</label>
      </div>
      <p class="field-hint">Manage submitted testimonials (approve/reject) from the <a href="/admin/testimonials.php">Testimonials</a> page.</p>
    </div>

    <button type="submit" class="btn btn-primary">Save settings</button>
    <div class="form-message" id="settings-message"></div>
  </form>
</main>

<script src="/js/markdown-toolbar.js"></script>
<script>
  attachMarkdownToolbar(document.getElementById('footer_about'));
  attachMarkdownToolbar(document.getElementById('about_content'));
</script>
<script src="/js/admin-settings.js"></script>
</body>
</html>
