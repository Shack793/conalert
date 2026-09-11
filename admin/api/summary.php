<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';

require_role_api(['admin', 'staff']);

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['error' => 'Method not allowed']);
}

$statuses = ['new', 'in_review', 'verified', 'published', 'resolved', 'rejected'];
$summary = array_fill_keys($statuses, 0);

$stmt = get_db()->query('SELECT status, COUNT(*) AS n FROM cases GROUP BY status');
foreach ($stmt->fetchAll() as $row) {
    $summary[$row['status']] = (int)$row['n'];
}

json_response(200, $summary);
