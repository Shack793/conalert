<?php
// Run this ONCE after importing db/schema.sql, to create your first admin
// login and seed one illustrative example case.
//
// Option A — if your host gives you SSH/terminal access:
//   php db/seed.php YOUR_NAME your@email.com YOUR_PASSWORD
//
// Option B — if your host has no terminal (common on cheaper shared plans):
//   1. Set SEED_SECRET in config.php to a long random value
//   2. Visit: https://yourdomain.com/db/seed.php?secret=YOUR_SEED_SECRET&name=YOUR_NAME&email=your@email.com&password=YOUR_PASSWORD
//   3. Delete this file (or at least blank out SEED_SECRET) immediately after —
//      leaving it reachable is a real risk since anyone who guesses the
//      secret could create themselves an admin account.
//
// Safe to run more than once: it only creates the admin account if that
// email doesn't already exist, and only seeds the example case if the
// cases table is empty.

require_once __DIR__ . '/../config.php';
require_once __DIR__ . '/../db.php';

$isCli = (php_sapi_name() === 'cli');

if ($isCli) {
    $name = $argv[1] ?? null;
    $email = $argv[2] ?? null;
    $password = $argv[3] ?? null;
} else {
    $providedSecret = $_GET['secret'] ?? '';
    if (empty(SEED_SECRET) || SEED_SECRET === 'change-this-then-remove-it-after-seeding' || !hash_equals(SEED_SECRET, $providedSecret)) {
        http_response_code(403);
        die('Set SEED_SECRET in config.php to a real value, then pass it as ?secret=... to run this.');
    }
    $name = $_GET['name'] ?? null;
    $email = $_GET['email'] ?? null;
    $password = $_GET['password'] ?? null;
}

if (!$name || !$email || !$password) {
    $usage = $isCli
        ? "Usage: php db/seed.php YOUR_NAME your@email.com YOUR_PASSWORD\n"
        : "Usage: ?secret=...&name=...&email=...&password=...\n";
    die($usage);
}
if (strlen($password) < 10) {
    die("Password must be at least 10 characters.\n");
}

$pdo = get_db();

// --- Create the first admin account, if it doesn't already exist ---
$existing = $pdo->prepare('SELECT id FROM admins WHERE email = ?');
$existing->execute([strtolower(trim($email))]);
if ($existing->fetch()) {
    echo "An account with that email already exists — skipping account creation.\n";
} else {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    $insert = $pdo->prepare("INSERT INTO admins (name, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
    $insert->execute([trim($name), strtolower(trim($email)), $hash]);
    echo "Created admin account for " . htmlspecialchars($email) . ".\n";
}

// --- Seed one illustrative example case, only if the table is empty ---
$caseCount = (int)$pdo->query('SELECT COUNT(*) AS n FROM cases')->fetch()['n'];
if ($caseCount > 0) {
    echo "Cases table is not empty — skipping example case.\n";
} else {
    $insertCase = $pdo->prepare("
        INSERT INTO cases (
            status, priority,
            full_name, email, country,
            platform_name, platform_type, amount_usd, currency_lost, incident_date,
            description, evidence_links,
            consent_to_publish, public_summary, admin_notes
        ) VALUES (
            'published', 'normal',
            'Example Reporter (illustrative)', 'example@example.org', 'Not specified',
            'bets.io', 'casino', 14000, 'USDT', '2026-01-01',
            ?, ?,
            1, ?, ?
        )
    ");
    $insertCase->execute([
        'SAMPLE / ILLUSTRATIVE CASE — replace with a real, evidence-backed submission before relying on this. ' .
            'Reporter states that a withdrawal request for approximately $14,000 was frozen by the platform, ' .
            'that their account was subsequently restricted, and that support did not provide a resolution after ' .
            'repeated contact. Include exact dates, ticket numbers, and screenshots for a real case.',
        "https://example.org/replace-with-real-screenshot-link\nhttps://example.org/replace-with-transaction-hash-link",
        'A reporter says a withdrawal of roughly $14,000 from bets.io was frozen and their account restricted, ' .
            'with no resolution after repeated support contact. This is an example entry seeded with this software — ' .
            'it has not been independently verified. Real cases should only be published after the evidence checklist ' .
            'in this file has been followed and the platform has been given a chance to respond.',
        'Seed/demo data. Do not treat as a verified real case. Before publishing anything about a real company: ' .
            'verify evidence, request comment from the company, and get the wording checked.'
    ]);
    echo "Seeded one illustrative example case.\n";
}

echo "Done. Now go delete this file (or blank out SEED_SECRET in config.php) if you ran it over the web.\n";
