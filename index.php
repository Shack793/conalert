<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/site-chrome.php';
require_once __DIR__ . '/includes/helpers.php';

$testimonialsOn = get_setting_bool('testimonials_enabled');
$featuredTestimonials = [];
if ($testimonialsOn) {
    $stmt = get_db()->query("SELECT name, role_or_context, testimonial FROM testimonials WHERE status = 'approved' ORDER BY created_at DESC LIMIT 3");
    $featuredTestimonials = $stmt->fetchAll();
}
$favicon = htmlspecialchars(get_setting('favicon_path'), ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>ConAlert — Public pressure for frozen crypto & casino withdrawals</title>
<link rel="icon" href="<?= $favicon ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,400;0,500;0,600;1,400&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css">
</head>
<body>

<?php site_header('home'); ?>

<main>
  <section>
    <h1>When an online casino or exchange freezes your money, you're not the only case on file.</h1>
    <p class="lede">ConAlert collects evidence-backed reports of platforms that hold withdrawals hostage, then
      applies public pressure — forum posts, social threads, and direct outreach to the platform — while pointing
      you to the formal channels that can actually get money moved.</p>

    <div class="ledger-strip" id="ledger-strip" aria-live="polite">
      <div class="ledger-item">
        <span class="stat-value" id="stat-cases">—</span>
        <span class="stat-label">published cases</span>
      </div>
      <div class="ledger-item">
        <span class="stat-value" id="stat-amount">—</span>
        <span class="stat-label">total reported frozen</span>
      </div>
    </div>
    <p class="ledger-note">Figures reflect cases submitted to ConAlert with the reporter's consent to publish. They are reported amounts, not independently audited totals.</p>

    <div class="btn-row">
      <a class="btn btn-primary" href="/submit.php">Submit your case</a>
      <a class="btn" href="/cases.php">Browse the case wall</a>
    </div>
  </section>

  <section>
    <h2>How this works</h2>
    <ol class="steps">
      <li>
        <div>
          <h3>You file the details</h3>
          <p>Platform name, amount, dates, and whatever evidence you have — screenshots, ticket threads, transaction
            hashes. The more specific, the more useful.</p>
        </div>
      </li>
      <li>
        <div>
          <h3>We check it before anything goes public</h3>
          <p>An admin reviews the submission, may follow up by email for more evidence, and writes a fact-checked
            summary. Nothing is published without your consent, and nothing accuses anyone of a crime — we describe
            what was reported and what evidence backs it.</p>
        </div>
      </li>
      <li>
        <div>
          <h3>We point you to the channels that can actually move money</h3>
          <p>Card chargebacks, exchange/regulator complaints, national cybercrime units, and consumer-protection
            bodies for your country — see the Resources page. This is where recovered funds actually come from.</p>
        </div>
      </li>
      <li>
        <div>
          <h3>We add public pressure alongside that</h3>
          <p>Verified, consented cases get posted to relevant subreddits, X, and TikTok, and added to the public
            case wall — grouped by platform, so a pattern of selective freezes is visible rather than scattered
            across a hundred one-off complaints.</p>
        </div>
      </li>
    </ol>
  </section>

  <?php if ($testimonialsOn && !empty($featuredTestimonials)): ?>
  <section>
    <h2>What people are saying</h2>
    <?php foreach ($featuredTestimonials as $t): ?>
      <div class="testimonial-card">
        <div class="t-name"><?= htmlspecialchars($t['name'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php if (!empty($t['role_or_context'])): ?>
          <div class="t-role"><?= htmlspecialchars($t['role_or_context'], ENT_QUOTES, 'UTF-8') ?></div>
        <?php endif; ?>
        <div class="rich-text"><?= render_rich_text_inline($t['testimonial']) ?></div>
      </div>
    <?php endforeach; ?>
    <p><a href="/testimonials.php">Read more testimonials →</a></p>
  </section>
  <?php endif; ?>

  <section>
    <h2>What we are — and aren't</h2>
    <div class="callout">
      <p><strong>We are not lawyers, and this is not legal advice.</strong> ConAlert is an evidence-and-pressure
        clearinghouse. We publish what reporters tell us, checked against the evidence they provide, in careful
        language — "reported," "according to the complaint" — because a platform freezing a withdrawal is not
        automatically proven fraud in a legal sense, even when it feels exactly like theft to the person it happened
        to. Platforms named here are welcome to respond, and we'll publish that response too.</p>
    </div>
  </section>
</main>

<?php site_footer(); ?>

<script src="/js/main.js"></script>
</body>
</html>
