<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';

// Only the 'admin' role can view or manage the team login list. 'staff'
// accounts (assistants/dev team) can work cases but never reach this file.
$currentAdmin = require_role_api(['admin']);

$pdo = get_db();
$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    list_users($pdo);
} elseif ($method === 'POST') {
    if (!verify_csrf_header()) {
        json_response(403, ['error' => 'Invalid or missing CSRF token. Refresh the page and try again.']);
    }
    create_user($pdo, $currentAdmin);
} elseif ($method === 'PATCH') {
    if (!verify_csrf_header()) {
        json_response(403, ['error' => 'Invalid or missing CSRF token. Refresh the page and try again.']);
    }
    update_user($pdo, $currentAdmin);
} else {
    json_response(405, ['error' => 'Method not allowed']);
}

function list_users(PDO $pdo): void {
    $stmt = $pdo->query('SELECT id, name, email, role, status, created_at, last_login_at FROM admins ORDER BY created_at ASC');
    json_response(200, $stmt->fetchAll());
}

function generate_temp_password(): string {
    // Readable-ish random password: e.g. "b7f2-9k3q-r8m1"
    $chars = 'abcdefghjkmnpqrstuvwxyz23456789'; // no ambiguous 0/o/1/l/i
    $groups = [];
    for ($g = 0; $g < 3; $g++) {
        $group = '';
        for ($i = 0; $i < 4; $i++) {
            $group .= $chars[random_int(0, strlen($chars) - 1)];
        }
        $groups[] = $group;
    }
    return implode('-', $groups);
}

function create_user(PDO $pdo, array $currentAdmin): void {
    $body = read_json_body();

    $name = require_str($body['name'] ?? '', 150);
    $email = strtolower(trim($body['email'] ?? ''));
    $role = $body['role'] ?? 'staff';

    if ($name === '' || $email === '') {
        json_response(400, ['error' => 'Name and email are required.']);
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(400, ['error' => 'Please provide a valid email address.']);
    }
    if (!in_array($role, ['admin', 'staff'], true)) {
        json_response(400, ['error' => "role must be 'admin' or 'staff'."]);
    }

    $existing = $pdo->prepare('SELECT id FROM admins WHERE email = ?');
    $existing->execute([$email]);
    if ($existing->fetch()) {
        json_response(409, ['error' => 'An account with that email already exists.']);
    }

    $tempPassword = generate_temp_password();
    $hash = password_hash($tempPassword, PASSWORD_DEFAULT);

    $insert = $pdo->prepare('INSERT INTO admins (name, email, password_hash, role, created_by) VALUES (?, ?, ?, ?, ?)');
    $insert->execute([$name, $email, $hash, $role, $currentAdmin['id']]);

    json_response(201, [
        'message' => 'Account created. Share this temporary password with them securely (it will not be shown again) — they should change it after logging in.',
        'id' => (int)$pdo->lastInsertId(),
        'email' => $email,
        'temporary_password' => $tempPassword,
    ]);
}

function update_user(PDO $pdo, array $currentAdmin): void {
    $id = $_GET['id'] ?? '';
    if (!ctype_digit((string)$id)) {
        json_response(400, ['error' => 'Missing or invalid user id.']);
    }
    $id = (int)$id;

    $targetStmt = $pdo->prepare('SELECT * FROM admins WHERE id = ?');
    $targetStmt->execute([$id]);
    $target = $targetStmt->fetch();
    if (!$target) {
        json_response(404, ['error' => 'Account not found.']);
    }

    $body = read_json_body();
    $sets = [];
    $params = [];

    // --- status: revoke / reactivate ---
    if (isset($body['status'])) {
        if (!in_array($body['status'], ['active', 'revoked'], true)) {
            json_response(400, ['error' => "status must be 'active' or 'revoked'."]);
        }
        if ($body['status'] === 'revoked') {
            if ((int)$target['id'] === (int)$currentAdmin['id']) {
                json_response(400, ['error' => "You can't revoke your own account. Have another admin do it."]);
            }
            if ($target['role'] === 'admin') {
                $activeAdminsStmt = $pdo->prepare("SELECT COUNT(*) AS n FROM admins WHERE role = 'admin' AND status = 'active' AND id != ?");
                $activeAdminsStmt->execute([$id]);
                if ((int)$activeAdminsStmt->fetch()['n'] === 0) {
                    json_response(400, ['error' => 'You cannot revoke the last remaining admin account.']);
                }
            }
        }
        $sets[] = 'status = ?';
        $params[] = $body['status'];
    }

    // --- role change ---
    if (isset($body['role'])) {
        if (!in_array($body['role'], ['admin', 'staff'], true)) {
            json_response(400, ['error' => "role must be 'admin' or 'staff'."]);
        }
        if ((int)$target['id'] === (int)$currentAdmin['id'] && $body['role'] !== 'admin') {
            json_response(400, ['error' => "You can't demote your own account. Have another admin do it."]);
        }
        if ($target['role'] === 'admin' && $body['role'] === 'staff') {
            $activeAdminsStmt = $pdo->prepare("SELECT COUNT(*) AS n FROM admins WHERE role = 'admin' AND status = 'active' AND id != ?");
            $activeAdminsStmt->execute([$id]);
            if ((int)$activeAdminsStmt->fetch()['n'] === 0) {
                json_response(400, ['error' => 'You cannot demote the last remaining admin account.']);
            }
        }
        $sets[] = 'role = ?';
        $params[] = $body['role'];
    }

    // --- name update ---
    if (isset($body['name']) && trim($body['name']) !== '') {
        $sets[] = 'name = ?';
        $params[] = require_str($body['name'], 150);
    }

    $responseExtra = [];

    // --- override / reset password ---
    if (!empty($body['reset_password'])) {
        $tempPassword = generate_temp_password();
        $sets[] = 'password_hash = ?';
        $params[] = password_hash($tempPassword, PASSWORD_DEFAULT);
        $responseExtra['temporary_password'] = $tempPassword;
        $responseExtra['message'] = 'Password reset. Share this new temporary password with them securely — it will not be shown again.';
    }

    if (empty($sets)) {
        json_response(400, ['error' => 'Nothing to update.']);
    }

    $params[] = $id;
    $pdo->prepare('UPDATE admins SET ' . implode(', ', $sets) . ' WHERE id = ?')->execute($params);

    $refreshed = $pdo->prepare('SELECT id, name, email, role, status, created_at, last_login_at FROM admins WHERE id = ?');
    $refreshed->execute([$id]);
    json_response(200, array_merge($refreshed->fetch(), $responseExtra));
}
