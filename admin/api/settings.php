<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_role_api(['admin', 'staff']);
    $settings = [];
    foreach ($pdo->query('SELECT `key`, value, updated_at FROM settings')->fetchAll() as $r) $settings[$r['key']] = $r;
    $socials = $pdo->query('SELECT id, platform, label, url, sort_order FROM social_links ORDER BY sort_order ASC, id ASC')->fetchAll();
    json_response(200, ['settings' => $settings, 'social_links' => $socials]);
}

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $admin = require_role_api(['admin']);
    if (!verify_csrf_header()) json_response(403, ['error' => 'Invalid CSRF token. Refresh.']);
    $body = read_json_body();

    // contact_email
    if (array_key_exists('contact_email', $body)) {
        $email = trim((string)$body['contact_email']);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) json_response(400, ['error' => 'contact_email must be valid email']);
        $pdo->prepare('INSERT INTO settings (`key`, value, updated_by) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value=VALUES(value), updated_by=VALUES(updated_by)')->execute(['contact_email', $email, $admin['id']]);
    }
    // footer_align
    if (array_key_exists('footer_align', $body)) {
        $align = $body['footer_align'];
        if (!in_array($align, ['left','center','space-between'], true)) json_response(400, ['error' => 'footer_align must be left, center, space-between']);
        $pdo->prepare('INSERT INTO settings (`key`, value, updated_by) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE value=VALUES(value), updated_by=VALUES(updated_by)')->execute(['footer_align', $align, $admin['id']]);
    }
    // social_links bulk: array of {id?,platform,label,url,sort_order}
    if (isset($body['social_links']) && is_array($body['social_links'])) {
        foreach ($body['social_links'] as $s) {
            $platform = strtolower(trim($s['platform'] ?? ''));
            $url = trim($s['url'] ?? '');
            $label = trim($s['label'] ?? '');
            $sort = (int)($s['sort_order'] ?? 0);
            if ($platform === '') continue;
            if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) json_response(400, ['error' => "Invalid URL for $platform: $url"]);
            if (!preg_match('/^[a-z0-9-]{2,30}$/', $platform)) json_response(400, ['error' => 'platform 2-30 alphanum/dash']);
            if (isset($s['id']) && ctype_digit((string)$s['id'])) {
                $pdo->prepare('UPDATE social_links SET platform=?, label=?, url=?, sort_order=? WHERE id=?')->execute([$platform, $label ?: ucfirst($platform), $url, $sort, (int)$s['id']]);
            } else {
                // upsert by platform to avoid duplicate
                $existing = $pdo->prepare('SELECT id FROM social_links WHERE platform=?');
                $existing->execute([$platform]);
                if ($row = $existing->fetch()) {
                    $pdo->prepare('UPDATE social_links SET label=?, url=?, sort_order=? WHERE id=?')->execute([$label ?: ucfirst($platform), $url, $sort, $row['id']]);
                } else {
                    $pdo->prepare('INSERT INTO social_links (platform,label,url,sort_order,created_by) VALUES (?,?,?,?,?)')->execute([$platform, $label ?: ucfirst($platform), $url, $sort, $admin['id']]);
                }
            }
        }
    }

    // return fresh
    $settings = [];
    foreach ($pdo->query('SELECT `key`, value FROM settings')->fetchAll() as $r) $settings[$r['key']] = $r['value'];
    $settings['social_links'] = $pdo->query('SELECT id, platform, label, url, sort_order FROM social_links ORDER BY sort_order ASC')->fetchAll();
    json_response(200, $settings);
}

json_response(405, ['error' => 'Method not allowed']);
