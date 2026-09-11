<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

$admin = require_role_api(['admin', 'staff']);
$pdo = get_db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    list_testimonials($pdo);
} elseif ($method === 'PATCH') {
    if (!verify_csrf_header()) {
        json_response(403, ['error' => 'Invalid or missing CSRF token. Refresh the page and try again.']);
    }
    update_testimonial($pdo, $admin);
} elseif ($method === 'DELETE') {
    if (!verify_csrf_header()) {
        json_response(403, ['error' => 'Invalid or missing CSRF token. Refresh the page and try again.']);
    }
    delete_testimonial($pdo);
} else {
    json_response(405, ['error' => 'Method not allowed']);
}

function list_testimonials(PDO $pdo): void {
    $statuses = ['pending', 'approved', 'rejected'];
    $status = $_GET['status'] ?? '';
    $sql = 'SELECT * FROM testimonials';
    $params = [];
    if (in_array($status, $statuses, true)) {
        $sql .= ' WHERE status = ?';
        $params[] = $status;
    }
    $sql .= ' ORDER BY created_at DESC';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    json_response(200, $stmt->fetchAll());
}

function update_testimonial(PDO $pdo, array $admin): void {
    $id = $_GET['id'] ?? '';
    if (!ctype_digit((string)$id)) {
        json_response(400, ['error' => 'Missing or invalid testimonial id.']);
    }

    $existing = $pdo->prepare('SELECT id FROM testimonials WHERE id = ?');
    $existing->execute([$id]);
    if (!$existing->fetch()) {
        json_response(404, ['error' => 'Testimonial not found.']);
    }

    $body = read_json_body();
    if (!isset($body['status']) || !in_array($body['status'], ['pending', 'approved', 'rejected'], true)) {
        json_response(400, ['error' => "status must be 'pending', 'approved', or 'rejected'."]);
    }

    $actor = $admin['name'] . ' <' . $admin['email'] . '>';
    $stmt = $pdo->prepare('UPDATE testimonials SET status = ?, reviewed_at = NOW(), reviewed_by = ? WHERE id = ?');
    $stmt->execute([$body['status'], $actor, $id]);

    $refreshed = $pdo->prepare('SELECT * FROM testimonials WHERE id = ?');
    $refreshed->execute([$id]);
    json_response(200, $refreshed->fetch());
}

function delete_testimonial(PDO $pdo): void {
    $id = $_GET['id'] ?? '';
    if (!ctype_digit((string)$id)) {
        json_response(400, ['error' => 'Missing or invalid testimonial id.']);
    }
    $pdo->prepare('DELETE FROM testimonials WHERE id = ?')->execute([$id]);
    json_response(200, ['message' => 'Deleted.']);
}
