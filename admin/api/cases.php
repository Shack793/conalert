<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role_api(['admin', 'staff']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['error' => 'Method not allowed']);
}

$statuses = ['new', 'in_review', 'verified', 'published', 'resolved', 'rejected'];
$pdo = get_db();

$sql = 'SELECT * FROM cases';
$params = [];
$status = $_GET['status'] ?? '';
if (in_array($status, $statuses, true)) {
    $sql .= ' WHERE status = ?';
    $params[] = $status;
}
$sql .= ' ORDER BY created_at DESC';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
json_response(200, $stmt->fetchAll());
