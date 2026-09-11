<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/includes/settings.php';
require_once __DIR__ . '/includes/site-chrome.php';
require_once __DIR__ . '/includes/helpers.php';

if (!get_setting_bool('about_enabled')) {
    http_response_code(404);
    require __DIR__ . '/includes/page-disabled.php';
    exit;
}

$favicon = htmlspecialchars(get_setting('favicon_path'), ENT_QUOTES, 'UTF-8');
$content = get_setting('about_content');
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>About us — ConAlert</title>
<link rel="icon" href="<?= $favicon ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Newsreader:ital,wght@0,400;0,500;0,600;1,400&family=IBM+Plex+Mono:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="/css/style.css">
</head>
<body>

<?php site_header('about'); ?>

<main>
  <section>
    <h1>About ConAlert</h1>
    <div class="rich-text lede"><?= render_rich_text($content) ?></div>
  </section>
</main>

<?php site_footer(); ?>

</body>
</html>
