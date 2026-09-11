<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

$admin = require_login_api();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(405, ['error' => 'Method not allowed']);
}
if (!verify_csrf_header()) {
    json_response(403, ['error' => 'Invalid or missing CSRF token. Refresh the page and try again.']);
}

$body = read_json_body();
$currentPassword = $body['current_password'] ?? '';
$newPassword = $body['new_password'] ?? '';

if (strlen($newPassword) < 10) {
    json_response(400, ['error' => 'New password must be at least 10 characters.']);
}

$pdo = get_db();
$stmt = $pdo->prepare('SELECT password_hash FROM admins WHERE id = ?');
$stmt->execute([$admin['id']]);
$row = $stmt->fetch();

if (!$row || !password_verify($currentPassword, $row['password_hash'])) {
    json_response(400, ['error' => 'Current password is incorrect.']);
}

$update = $pdo->prepare('UPDATE admins SET password_hash = ? WHERE id = ?');
$update->execute([password_hash($newPassword, PASSWORD_DEFAULT), $admin['id']]);

json_response(200, ['message' => 'Password updated.']);
