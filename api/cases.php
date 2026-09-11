<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    list_cases();
} elseif ($method === 'POST') {
    submit_case();
} else {
    json_response(405, ['error' => 'Method not allowed']);
}

function list_cases(): void {
    $pdo = get_db();
    $sql = "SELECT id, case_number, created_at, country, platform_name, platform_type,
                   amount_usd, currency_lost, incident_date, public_summary, evidence_links
            FROM cases
            WHERE status = 'published' AND consent_to_publish = 1";
    $params = [];

    $platformType = $_GET['platform_type'] ?? '';
    if (in_array($platformType, ['casino', 'exchange', 'other'], true)) {
        $sql .= ' AND platform_type = ?';
        $params[] = $platformType;
    }

    $platform = $_GET['platform'] ?? '';
    if ($platform !== '') {
        $sql .= ' AND platform_name LIKE ?';
        $params[] = '%' . $platform . '%';
    }

    $sql .= ' ORDER BY created_at DESC';

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    json_response(200, $stmt->fetchAll());
}

function submit_case(): void {
    $body = read_json_body();
    if (empty($body)) {
        // Also accept normal form submissions, in case JS is disabled.
        $body = $_POST;
    }

    // Honeypot: real visitors never fill this hidden field in.
    $honeypotTripped = !is_blank($body['website_url'] ?? null);

    $required = ['full_name', 'email', 'platform_name', 'platform_type', 'description'];
    $missing = [];
    foreach ($required as $field) {
        if (is_blank($body[$field] ?? null)) {
            $missing[] = $field;
        }
    }
    if (!empty($missing)) {
        json_response(400, ['error' => 'Missing required field(s): ' . implode(', ', $missing)]);
    }

    if (!in_array($body['platform_type'], ['casino', 'exchange', 'other'], true)) {
        json_response(400, ['error' => 'platform_type must be casino, exchange, or other.']);
    }

    $email = require_str($body['email'], 200);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(400, ['error' => 'Please provide a valid email address so we can follow up.']);
    }

    $amount = null;
    if (isset($body['amount_usd']) && $body['amount_usd'] !== '') {
        if (!is_numeric($body['amount_usd']) || (float)$body['amount_usd'] < 0) {
            json_response(400, ['error' => 'amount_usd must be a positive number.']);
        }
        $amount = (float)$body['amount_usd'];
    }

    $pdo = get_db();

    // Lightweight abuse guard: cap repeat submissions from the same email.
    $recentStmt = $pdo->prepare("SELECT COUNT(*) AS n FROM cases WHERE email = ? AND created_at > (NOW() - INTERVAL 1 HOUR)");
    $recentStmt->execute([$email]);
    if ((int)$recentStmt->fetch()['n'] >= 5) {
        json_response(429, ['error' => 'Too many submissions from this email recently. Please try again later, or email us directly.']);
    }

    $status = $honeypotTripped ? 'rejected' : 'new';

    $insert = $pdo->prepare("
        INSERT INTO cases (
            status, priority,
            full_name, email, country,
            platform_name, platform_type, amount_usd, currency_lost, incident_date,
            description, evidence_links,
            consent_to_publish, honeypot_tripped
        ) VALUES (
            ?, 'normal',
            ?, ?, ?,
            ?, ?, ?, ?, ?,
            ?, ?,
            ?, ?
        )
    ");
    $insert->execute([
        $status,
        require_str($body['full_name'], 200),
        $email,
        str_or_null($body['country'] ?? null, 100),
        require_str($body['platform_name'], 200),
        $body['platform_type'],
        $amount,
        str_or_null($body['currency_lost'] ?? null, 20),
        str_or_null($body['incident_date'] ?? null, 20),
        require_str($body['description'], 8000),
        str_or_null($body['evidence_links'] ?? null, 4000),
        !empty($body['consent_to_publish']) ? 1 : 0,
        $honeypotTripped ? 1 : 0,
    ]);

    $caseId = (int)$pdo->lastInsertId();

    $event = $pdo->prepare("INSERT INTO case_events (case_id, event_type, detail) VALUES (?, 'submitted', ?)");
    $event->execute([$caseId, $honeypotTripped ? 'Flagged by honeypot field' : 'Public submission received']);

    json_response(201, [
        'message' => "Thanks — we've received your case. We review new submissions by hand and will follow up by email.",
        'caseId' => $caseId,
    ]);
}
