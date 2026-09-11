<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    json_response(405, ['error' => 'Method not allowed']);
}

$pdo = get_db();

$totals = $pdo->query("
    SELECT
        COUNT(*) AS published_cases,
        COALESCE(SUM(amount_usd), 0) AS total_amount_usd
    FROM cases
    WHERE status = 'published' AND consent_to_publish = 1
")->fetch();

$byPlatform = $pdo->query("
    SELECT platform_name, COUNT(*) AS case_count, COALESCE(SUM(amount_usd), 0) AS amount_usd
    FROM cases
    WHERE status = 'published' AND consent_to_publish = 1
    GROUP BY platform_name
    ORDER BY amount_usd DESC
    LIMIT 10
")->fetchAll();

json_response(200, [
    'published_cases' => (int)$totals['published_cases'],
    'total_amount_usd' => (float)$totals['total_amount_usd'],
    'byPlatform' => $byPlatform,
]);
