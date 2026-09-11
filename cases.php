<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/site-chrome.php';
$favicon = htmlspecialchars(get_setting('favicon_path'), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Case wall — ConAlert</title>
<link rel="icon" href="<?= $favicon ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,400;0,500;0,600;1,400&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css">
</head>
<body>

<?php site_header('cases'); ?>

<main>
  <section>
    <h1>Case wall</h1>
    <p class="lede">Every case here was submitted by the person it happened to, checked by an admin, and
      published with that person's consent. Summaries are written from the evidence provided — see each case's
      note on what's been verified.</p>

    <div class="callout">
      A platform being named here reflects a reported, evidence-backed complaint — not a court finding.
      If you represent a platform listed here and want to respond or dispute a case, contact us
      (see the footer) and we'll publish your response alongside it.
    </div>

    <div class="filters" id="filters">
      <button type="button" data-filter="" aria-pressed="true">All</button>
      <button type="button" data-filter="casino" aria-pressed="false">Casinos</button>
      <button type="button" data-filter="exchange" aria-pressed="false">Exchanges</button>
      <button type="button" data-filter="other" aria-pressed="false">Other</button>
    </div>

    <div id="case-list">
      <p class="lede">Loading cases…</p>
    </div>
  </section>
</main>

<?php site_footer(); ?>

<script src="/js/cases.js"></script>
</body>
</html>
