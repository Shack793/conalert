<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/helpers.php';

function start_secure_session(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_name('conalert_admin_session');
    session_start();
}

/**
 * Returns the logged-in admin's row (id, name, email, role) if the session
 * is valid AND the account is still 'active' in the database right now —
 * this DB check on every call is what makes "revoke at any time" actually
 * instant, instead of waiting for the revoked user's session to expire.
 */
function current_admin(): ?array {
    start_secure_session();

    if (empty($_SESSION['admin_id'])) {
        return null;
    }

    $stmt = get_db()->prepare('SELECT id, name, email, role, status FROM admins WHERE id = ?');
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();

    if (!$admin || $admin['status'] !== 'active') {
        // Account was revoked (or deleted) since login — kill the session now.
        session_unset();
        session_destroy();
        return null;
    }

    unset($admin['status']);
    return $admin;
}

function login_admin(string $email, string $password): ?array {
    $stmt = get_db()->prepare('SELECT id, name, email, password_hash, role, status FROM admins WHERE email = ? LIMIT 1');
    $stmt->execute([trim(strtolower($email))]);
    $admin = $stmt->fetch();

    if (!$admin || $admin['status'] !== 'active' || !password_verify($password, $admin['password_hash'])) {
        return null;
    }

    start_secure_session();
    session_regenerate_id(true);
    $_SESSION['admin_id'] = $admin['id'];

    $update = get_db()->prepare('UPDATE admins SET last_login_at = NOW() WHERE id = ?');
    $update->execute([$admin['id']]);

    unset($admin['password_hash'], $admin['status']);
    return $admin;
}

function logout_admin(): void {
    start_secure_session();
    session_unset();
    session_destroy();
}

/** For HTML admin pages: redirects to login if not authenticated. */
function require_login(): array {
    $admin = current_admin();
    if (!$admin) {
        header('Location: /admin/login.php');
        exit;
    }
    return $admin;
}

/** For HTML admin pages: require_login plus a role check. */
function require_role(array $roles): array {
    $admin = require_login();
    if (!in_array($admin['role'], $roles, true)) {
        http_response_code(403);
        echo 'You do not have permission to view this page.';
        exit;
    }
    return $admin;
}

/** For JSON admin endpoints: same checks, but returns a JSON error instead of redirecting. */
function require_login_api(): array {
    $admin = current_admin();
    if (!$admin) {
        json_response(401, ['error' => 'Not logged in.']);
    }
    return $admin;
}

function require_role_api(array $roles): array {
    $admin = require_login_api();
    if (!in_array($admin['role'], $roles, true)) {
        json_response(403, ['error' => 'You do not have permission to do that.']);
    }
    return $admin;
}
