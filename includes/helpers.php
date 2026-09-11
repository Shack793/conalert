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

/**
 * Turns plain text written with a small set of Markdown-ish conventions into
 * safe HTML: paragraphs (blank line = new paragraph), single line breaks,
 * **bold**, *italic*, ~~strikethrough~~, and clickable auto-linked URLs.
 *
 * The raw text is HTML-escaped FIRST, and every tag this function adds after
 * that point is one we control — so user input can never inject arbitrary
 * HTML/JS, no matter what they type.
 */
function render_rich_text(?string $raw): string {
    if ($raw === null || trim($raw) === '') {
        return '';
    }

    $text = str_replace(["\r\n", "\r"], "\n", $raw);
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

    $text = autolink_urls($text);
    $text = apply_inline_markdown($text);

    $paragraphs = preg_split('/\n{2,}/', trim($text));
    $html = '';
    foreach ($paragraphs as $para) {
        if (trim($para) === '') {
            continue;
        }
        $html .= '<p>' . nl2br($para, false) . '</p>';
    }
    return $html;
}

/** Same idea as render_rich_text(), but no <p> wrapping — for short, single-block text like a testimonial. */
function render_rich_text_inline(?string $raw): string {
    if ($raw === null || trim($raw) === '') {
        return '';
    }
    $text = str_replace(["\r\n", "\r"], "\n", $raw);
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $text = autolink_urls($text);
    $text = apply_inline_markdown($text);
    return nl2br(trim($text), false);
}

function autolink_urls(string $escapedText): string {
    return preg_replace_callback(
        '/(https?:\/\/[^\s<]+)/i',
        function ($m) {
            $url = $m[1];
            // Don't swallow trailing punctuation into the link.
            $trail = '';
            while ($url !== '' && strpos('.,!?)', substr($url, -1)) !== false) {
                $trail = substr($url, -1) . $trail;
                $url = substr($url, 0, -1);
            }
            return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer nofollow">' . $url . '</a>' . $trail;
        },
        $escapedText
    );
}

function apply_inline_markdown(string $escapedText): string {
    // Bold: **text** or __text__
    $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $escapedText);
    $text = preg_replace('/__(.+?)__/s', '<strong>$1</strong>', $text);
    // Strikethrough: ~~text~~
    $text = preg_replace('/~~(.+?)~~/s', '<del>$1</del>', $text);
    // Italic: *text* or _text_ (single markers, after bold is already consumed)
    $text = preg_replace('/(?<!\*)\*(?!\*)(.+?)(?<!\*)\*(?!\*)/s', '<em>$1</em>', $text);
    $text = preg_replace('/(?<!_)_(?!_)(.+?)(?<!_)_(?!_)/s', '<em>$1</em>', $text);
    return $text;
}
