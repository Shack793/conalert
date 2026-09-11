<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

$id = $_GET['id'] ?? '';
if (!ctype_digit((string)$id)) {
    json_response(400, ['error' => 'Missing or invalid case id.']);
}
$id = (int)$id;

$pdo = get_db();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    require_role_api(['admin', 'staff']);
    get_case($pdo, $id);
} elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $admin = require_role_api(['admin']); // edit: admin only
    if (!verify_csrf_header()) {
        json_response(403, ['error' => 'Invalid or missing CSRF token. Refresh the page and try again.']);
    }
    patch_case($pdo, $id, $admin);
} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $admin = require_role_api(['admin']); // delete: admin only
    if (!verify_csrf_header()) {
        json_response(403, ['error' => 'Invalid or missing CSRF token. Refresh the page and try again.']);
    }
    delete_case($pdo, $id, $admin);
} else {
    json_response(405, ['error' => 'Method not allowed']);
}

function get_case(PDO $pdo, int $id): void {
    $stmt = $pdo->prepare('SELECT * FROM cases WHERE id = ?');
    $stmt->execute([$id]);
    $case = $stmt->fetch();
    if (!$case) {
        json_response(404, ['error' => 'Case not found.']);
    }

    $eventsStmt = $pdo->prepare('SELECT * FROM case_events WHERE case_id = ? ORDER BY created_at ASC');
    $eventsStmt->execute([$id]);
    $case['events'] = $eventsStmt->fetchAll();

    json_response(200, $case);
}

function patch_case(PDO $pdo, int $id, array $admin): void {
    $statuses = ['new', 'in_review', 'verified', 'published', 'resolved', 'rejected'];
    $priorities = ['low', 'normal', 'high'];

    $existingStmt = $pdo->prepare('SELECT * FROM cases WHERE id = ?');
    $existingStmt->execute([$id]);
    $existing = $existingStmt->fetch();
    if (!$existing) {
        json_response(404, ['error' => 'Case not found.']);
    }

    $body = read_json_body();
    $sets = [];
    $params = [];
    $events = [];

    if (isset($body['status'])) {
        if (!in_array($body['status'], $statuses, true)) {
            json_response(400, ['error' => 'status must be one of: ' . implode(', ', $statuses)]);
        }
        if ($body['status'] === 'published' && (int)$existing['consent_to_publish'] !== 1) {
            json_response(400, ['error' => 'This reporter did not consent to publication — cannot mark as published.']);
        }
        $newSummary = $body['public_summary'] ?? null;
        if ($body['status'] === 'published' && is_blank($newSummary) && is_blank($existing['public_summary'])) {
            json_response(400, ['error' => 'Write a public_summary before publishing this case.']);
        }
        $sets[] = 'status = ?';
        $params[] = $body['status'];
        $events[] = ['status_change', $existing['status'] . ' -> ' . $body['status']];
    }

    if (isset($body['priority'])) {
        if (!in_array($body['priority'], $priorities, true)) {
            json_response(400, ['error' => 'priority must be one of: ' . implode(', ', $priorities)]);
        }
        $sets[] = 'priority = ?';
        $params[] = $body['priority'];
    }

    if (isset($body['public_summary'])) {
        $sets[] = 'public_summary = ?';
        $params[] = require_str($body['public_summary'], 4000);
        $events[] = ['summary_edited', 'Public summary updated'];
    }

    if (isset($body['admin_notes'])) {
        $sets[] = 'admin_notes = ?';
        $params[] = require_str($body['admin_notes'], 8000);
        $events[] = ['note_added', 'Admin notes updated'];
    }

    if (isset($body['case_number'])) {
        $cn = trim((string)$body['case_number']);
        if ($cn === '') {
            $sets[] = 'case_number = NULL';
            $events[] = ['case_number_edited', 'case_number cleared'];
        } else {
            if (!preg_match('/^[A-Za-z0-9-]{2,32}$/', $cn)) {
                json_response(400, ['error' => 'case_number 2-32 chars, letters/numbers/dash only']);
            }
            $dup = $pdo->prepare('SELECT id FROM cases WHERE case_number = ? AND id != ?');
            $dup->execute([$cn, $id]);
            if ($dup->fetch()) json_response(400, ['error' => 'case_number already in use']);
            $sets[] = 'case_number = ?';
            $params[] = $cn;
            $events[] = ['case_number_edited', "case_number -> $cn"];
        }
    }

    if (empty($sets)) {
        json_response(400, ['error' => 'Nothing to update — provide status, priority, public_summary, admin_notes, and/or case_number.']);
    }

    $params[] = $id;
    $pdo->prepare('UPDATE cases SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);

    $actor = $admin['name'] . ' <' . $admin['email'] . '>';
    $eventStmt = $pdo->prepare('INSERT INTO case_events (case_id, event_type, detail, actor) VALUES (?, ?, ?, ?)');
    foreach ($events as [$type, $detail]) {
        $eventStmt->execute([$id, $type, $detail, $actor]);
    }

    $refreshed = $pdo->prepare('SELECT * FROM cases WHERE id = ?');
    $refreshed->execute([$id]);
    json_response(200, $refreshed->fetch());
}

function delete_case(PDO $pdo, int $id, array $admin): void {
    $stmt = $pdo->prepare('SELECT id FROM cases WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        json_response(404, ['error' => 'Case not found.']);
    }
    // case_events will cascade delete via FK case_events_ibfk_1 ON DELETE CASCADE
    $pdo->prepare('DELETE FROM cases WHERE id = ?')->execute([$id]);
    json_response(200, ['message' => "Case #$id deleted.", 'id' => $id]);
}
