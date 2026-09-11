<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

$admin = require_role_api(['admin']);
if (!verify_csrf_header()) json_response(403, ['error' => 'Invalid CSRF token']);

$pdo = get_db();
$body = read_json_body();

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = $_GET['id'] ?? $body['id'] ?? '';
    if (!ctype_digit((string)$id)) json_response(400, ['error' => 'Missing id']);
    $count = $pdo->prepare('SELECT COUNT(*) AS n FROM social_links')->fetch()['n'] ?? 0;
    // keep at least 0, allow delete all
    $pdo->prepare('DELETE FROM social_links WHERE id=?')->execute([(int)$id]);
    json_response(200, ['message' => 'Deleted']);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $platform = strtolower(trim($body['platform'] ?? ''));
    $url = trim($body['url'] ?? '');
    $label = trim($body['label'] ?? '');
    $sort = (int)($body['sort_order'] ?? 99);
    if ($platform === '' || !preg_match('/^[a-z0-9-]{2,30}$/', $platform)) json_response(400, ['error' => 'platform required 2-30 alphanum']);
    if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) json_response(400, ['error' => 'Invalid URL']);
    try {
        $pdo->prepare('INSERT INTO social_links (platform,label,url,sort_order,created_by) VALUES (?,?,?,?,?)')->execute([$platform, $label ?: ucfirst($platform), $url, $sort, $admin['id']]);
    } catch (PDOException $e) { json_response(400, ['error' => 'Platform already exists']); }
    json_response(201, ['id' => $pdo->lastInsertId()]);
}
json_response(405, ['error' => 'Method not allowed']);
