<?php
require_once __DIR__ . '/auth.php';

function csrf_token(): string {
    start_secure_session();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field(): string {
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8');
    return '<input type="hidden" name="csrf_token" value="' . $token . '">';
}

/** Checks a token from a regular form POST ($_POST['csrf_token']). */
function verify_csrf_post(): bool {
    start_secure_session();
    $submitted = $_POST['csrf_token'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $submitted);
}

/** Checks a token sent as an X-CSRF-Token header (used by the fetch()-based admin API calls). */
function verify_csrf_header(): bool {
    start_secure_session();
    $submitted = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $submitted);
}
