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
<title>Submit a case — ConAlert</title>
<link rel="icon" href="<?= $favicon ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,500,600;1,400&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css">
</head>
<body>

<?php site_header('submit'); ?>

<main>
  <section>
    <h1>Tell us what happened</h1>
    <p class="lede">Be as specific as you can — exact amounts, dates, ticket numbers, and links to any evidence
      (screenshots, transaction hashes, support chat exports). An admin reviews every submission by hand and
      will follow up by email if we need more.</p>

    <div class="callout">
      <strong>Before you submit:</strong> save your own copies of everything — screenshots, transaction hashes,
      support ticket numbers, chat logs. Platforms sometimes edit or delete support threads after a dispute
      becomes public.
    </div>

    <form class="case-form" id="case-form" novalidate>
      <div class="honeypot" aria-hidden="true">
        <label for="website_url">Leave this field blank</label>
        <input type="text" id="website_url" name="website_url" tabindex="-1" autocomplete="off">
      </div>

      <div class="field-row">
        <div class="field">
          <label class="field-label" for="full_name">Your name *</label>
          <input type="text" id="full_name" name="full_name" required>
        </div>
        <div class="field">
          <label class="field-label" for="email">Your email *</label>
          <input type="email" id="email" name="email" required>
          <p class="field-hint">We only use this to follow up on your case — never shown publicly.</p>
        </div>
      </div>

      <div class="field">
        <label class="field-label" for="country">Your country</label>
        <input type="text" id="country" name="country" placeholder="e.g. Ghana">
      </div>

      <div class="field-row">
        <div class="field">
          <label class="field-label" for="platform_name">Platform name *</label>
          <input type="text" id="platform_name" name="platform_name" placeholder="e.g. bets.io" required>
        </div>
        <div class="field">
          <label class="field-label" for="platform_type">Platform type *</label>
          <select id="platform_type" name="platform_type" required>
            <option value="">Select one</option>
            <option value="casino">Online casino / betting site</option>
            <option value="exchange">Crypto exchange or swap service</option>
            <option value="other">Other online service</option>
          </select>
        </div>
      </div>

      <div class="field-row">
        <div class="field">
          <label class="field-label" for="amount_usd">Amount frozen / lost (USD equivalent)</label>
          <input type="number" id="amount_usd" name="amount_usd" min="0" step="0.01" placeholder="14000">
        </div>
        <div class="field">
          <label class="field-label" for="currency_lost">Original currency</label>
          <input type="text" id="currency_lost" name="currency_lost" placeholder="e.g. USDT, BTC, USD">
        </div>
      </div>

      <div class="field">
        <label class="field-label" for="incident_date">Date the withdrawal was frozen / blocked</label>
        <input type="date" id="incident_date" name="incident_date">
      </div>

      <div class="field">
        <label class="field-label" for="description">What happened *</label>
        <textarea id="description" name="description" required
          placeholder="Walk through the timeline: when you deposited, when you tried to withdraw, what the platform said, what support tickets you filed, and what response (if any) you got.

Leave a blank line between paragraphs — it'll display properly once reviewed."></textarea>
      </div>

      <div class="field">
        <label class="field-label" for="evidence_links">Links to evidence</label>
        <textarea id="evidence_links" name="evidence_links" placeholder="One link per line: screenshots (e.g. Imgur), transaction hash on a block explorer, support ticket thread, etc."></textarea>
      </div>

      <div class="field checkbox-field">
        <input type="checkbox" id="consent_to_publish" name="consent_to_publish">
        <label for="consent_to_publish">
          You may publish a fact-checked summary of my case (no name or email) on the public case wall and
          reference it in social/forum posts aimed at this platform, once an admin has reviewed it.
        </label>
      </div>

      <button type="submit" class="btn btn-primary">Submit case</button>
      <div class="form-message" id="form-message" role="status"></div>
    </form>
  </section>
</main>

<?php site_footer(); ?>

<script src="/js/markdown-toolbar.js"></script>
<script>
  attachMarkdownToolbar(document.getElementById('description'), {
    hint: 'Leave a blank line between paragraphs. Bold/italic/strikethrough supported.'
  });
</script>
<script src="/js/submit.js"></script>
</body>
</html>
