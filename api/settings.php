<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['error' => 'Method not allowed']);
}

// Public allow-list: footer data only
$pdo = get_db();
$settings = [];
$stmt = $pdo->query("SELECT `key`, value FROM settings WHERE `key` IN ('contact_email','footer_align')");
foreach ($stmt->fetchAll() as $row) $settings[$row['key']] = $row['value'];

// fallback to config/constants if DB empty
if (empty($settings['contact_email'])) $settings['contact_email'] = defined('CONTACT_EMAIL') ? CONTACT_EMAIL : 'cases@conalert.org';
if (empty($settings['footer_align'])) $settings['footer_align'] = 'space-between';

// only return non-empty social links for public footer
$socials = [];
foreach (get_social_links() as $s) {
    if (!empty($s['url']) && filter_var($s['url'], FILTER_VALIDATE_URL)) {
        $socials[] = ['platform' => $s['platform'], 'label' => $s['label'] ?: ucfirst($s['platform']), 'url' => $s['url']];
    }
}
$settings['social_links'] = $socials;

json_response(200, $settings);
