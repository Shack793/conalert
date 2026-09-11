<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/site-chrome.php';

if (!get_setting_bool('testimonials_enabled')) {
    http_response_code(404);
    require __DIR__ . '/includes/page-disabled.php';
    exit;
}

$favicon = htmlspecialchars(get_setting('favicon_path'), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Testimonials — ConAlert</title>
<link rel="icon" href="<?= $favicon ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,400;0,500;0,600;1,400&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css">
</head>
<body>

<?php site_header('testimonials'); ?>

<main>
  <section>
    <h1>Testimonials</h1>
    <p class="lede">Stories from people who used ConAlert to get organized, apply pressure, or just feel less
      alone in dealing with a platform that froze their funds. All testimonials are reviewed before publishing.</p>

    <div id="testimonial-list">
      <p class="lede">Loading…</p>
    </div>
  </section>

  <section>
    <h2>Share your experience</h2>
    <form class="case-form" id="testimonial-form" novalidate>
      <div class="honeypot" aria-hidden="true">
        <label for="website_url">Leave this field blank</label>
        <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off">
      </div>

      <div class="field-row">
        <div class="field">
          <label class="field-label" for="t-name">Your name *</label>
          <input type="text" id="t-name" name="name" required>
        </div>
        <div class="field">
          <label class="field-label" for="t-email">Your email (optional, not published)</label>
          <input type="email" id="t-email" name="email">
        </div>
      </div>

      <div class="field">
        <label class="field-label" for="t-context">Context (optional)</label>
        <input type="text" id="t-context" name="role_or_context" placeholder="e.g. Recovered funds from a frozen exchange account">
      </div>

      <div class="field">
        <label class="field-label" for="t-testimonial">Your testimonial *</label>
        <textarea id="t-testimonial" name="testimonial" required placeholder="What was your experience with ConAlert?"></textarea>
      </div>

      <button type="submit" class="btn btn-primary">Submit testimonial</button>
      <div class="form-message" id="testimonial-message" role="status"></div>
    </form>
  </section>
</main>

<?php site_footer(); ?>

<script src="/js/markdown-toolbar.js"></script>
<script>attachMarkdownToolbar(document.getElementById('t-testimonial'));</script>
<script src="/js/testimonials.js"></script>
</body>
</html>
