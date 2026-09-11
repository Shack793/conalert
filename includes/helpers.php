<?php

function json_response(int $statusCode, $data): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function read_json_body(): array {
    $raw = file_get_contents('php://input');
    if ($raw === false || trim($raw) === '') {
        return [];
    }
    $decoded = json_decode($raw, true);
    return is_array($decoded) ? $decoded : [];
}

function is_blank($value): bool {
    return $value === null || trim((string)$value) === '';
}

function str_or_null(?string $value, int $maxLen): ?string {
    if ($value === null || trim($value) === '') {
        return null;
    }
    return mb_substr(trim($value), 0, $maxLen);
}

function require_str(string $value, int $maxLen): string {
    return mb_substr(trim($value), 0, $maxLen);
}

function get_setting(string $key, ?string $fallback = null): ?string {
    try {
        $pdo = get_db();
        $stmt = $pdo->prepare('SELECT value FROM settings WHERE `key` = ?');
        $stmt->execute([$key]);
        $row = $stmt->fetch();
        if ($row && $row['value'] !== null && $row['value'] !== '') return $row['value'];
    } catch (Throwable $e) {}
    return $fallback;
}

function get_social_links(): array {
    try {
        $pdo = get_db();
        return $pdo->query('SELECT id, platform, label, url, sort_order FROM social_links ORDER BY sort_order ASC, id ASC')->fetchAll();
    } catch (Throwable $e) { return []; }
}
