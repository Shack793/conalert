<?php
require_once __DIR__ . '/../db.php';

const SETTINGS_DEFAULTS = [
    'footer_about'        => 'ConAlert collects evidence-backed reports about online casinos, crypto exchanges, and similar platforms that freeze or withhold user funds, then applies public pressure alongside pointing people to the formal channels that can actually help.',
    'contact_email'       => 'cases@conalert.org',
    'contact_phone'       => '',
    'social_x'            => '',
    'social_facebook'     => '',
    'social_instagram'    => '',
    'social_tiktok'       => '',
    'social_linkedin'     => '',
    'logo_path'           => '/assets/logo.svg',
    'favicon_path'        => '/assets/favicon.ico',
    'about_enabled'       => '1',
    'about_content'       => "ConAlert exists because too many people lose money to platforms that freeze withdrawals with no real recourse.\n\nWe're not lawyers and we're not a regulator — we're a small case-tracking and public-pressure project. We review submissions, check them against the evidence provided, and (with the reporter's consent) publish careful, fact-checked summaries and point people toward the channels that actually have power to help: chargebacks, regulators, and cybercrime units.",
    'testimonials_enabled' => '1',
];

function get_all_settings(): array {
    static $cached = null;
    if ($cached !== null) {
        return $cached;
    }
    $settings = SETTINGS_DEFAULTS;
    $stmt = get_db()->query('SELECT setting_key, setting_value FROM site_settings');
    foreach ($stmt->fetchAll() as $row) {
        if ($row['setting_value'] !== null) {
            $settings[$row['setting_key']] = $row['setting_value'];
        }
    }
    $cached = $settings;
    return $cached;
}

function get_setting(string $key): string {
    $settings = get_all_settings();
    return $settings[$key] ?? (SETTINGS_DEFAULTS[$key] ?? '');
}

function get_setting_bool(string $key): bool {
    return get_setting($key) === '1';
}

function set_setting(string $key, string $value): void {
    $stmt = get_db()->prepare('
        INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
        ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)
    ');
    $stmt->execute([$key, $value]);
}
