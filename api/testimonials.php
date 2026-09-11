<?php
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/settings.php';

if (!get_setting_bool('testimonials_enabled')) {
    json_response(404, ['error' => 'Testimonials are not currently enabled on this site.']);
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    list_testimonials();
} elseif ($method === 'POST') {
    submit_testimonial();
} else {
    json_response(405, ['error' => 'Method not allowed']);
}

function list_testimonials(): void {
    $stmt = get_db()->query("SELECT id, name, role_or_context, testimonial, created_at FROM testimonials WHERE status = 'approved' ORDER BY created_at DESC");
    $rows = $stmt->fetchAll();
    foreach ($rows as &$row) {
        $row['testimonial_html'] = render_rich_text_inline($row['testimonial']);
    }
    unset($row);
    json_response(200, $rows);
}

function submit_testimonial(): void {
    $body = read_json_body();

    // Simple honeypot, same pattern as the case submission form.
    if (!is_blank($body['website_url'] ?? null)) {
        json_response(201, ['message' => 'Thanks for sharing your experience!']); // pretend success, don't tip off the bot
    }

    $name = require_str($body['name'] ?? '', 150);
    $testimonial = require_str($body['testimonial'] ?? '', 3000);

    if ($name === '' || $testimonial === '') {
        json_response(400, ['error' => 'Please fill in your name and your testimonial.']);
    }

    $email = str_or_null($body['email'] ?? null, 200);
    if ($email !== null && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        json_response(400, ['error' => 'That email address doesn\'t look valid — you can also leave it blank.']);
    }

    $pdo = get_db();

    // Cap repeat submissions from the same name+testimonial-ish burst (no email required, so keep this light).
    $recent = $pdo->query('SELECT COUNT(*) AS n FROM testimonials WHERE created_at > (NOW() - INTERVAL 1 HOUR)')->fetch();
    if ((int)$recent['n'] >= 30) {
        json_response(429, ['error' => 'Too many submissions right now — please try again later.']);
    }

    $insert = $pdo->prepare('INSERT INTO testimonials (name, email, role_or_context, testimonial) VALUES (?, ?, ?, ?)');
    $insert->execute([
        $name,
        $email,
        str_or_null($body['role_or_context'] ?? null, 200),
        $testimonial,
    ]);

    json_response(201, ['message' => "Thanks for sharing your experience! An admin will review it before it's published."]);
}
