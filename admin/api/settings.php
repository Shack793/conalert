<?php
require_once __DIR__ . '/../../db.php';
require_once __DIR__ . '/../../includes/helpers.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../includes/csrf.php';
require_once __DIR__ . '/../../includes/settings.php';

require_role_api(['admin']);

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    json_response(200, get_all_settings());
}

if ($method !== 'POST') {
    json_response(405, ['error' => 'Method not allowed']);
}

// File uploads arrive as multipart/form-data, so this endpoint reads
// $_POST + $_FILES directly rather than a JSON body. The CSRF token comes
// along as a regular form field in that case.
$submittedToken = $_POST['csrf_token'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
start_secure_session();
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $submittedToken)) {
    json_response(403, ['error' => 'Invalid or missing CSRF token. Refresh the page and try again.']);
}

$textFields = [
    'footer_about', 'contact_email', 'contact_phone',
    'social_x', 'social_facebook', 'social_instagram', 'social_tiktok', 'social_linkedin',
    'about_content',
];
foreach ($textFields as $field) {
    if (isset($_POST[$field])) {
        set_setting($field, require_str($_POST[$field], 8000));
    }
}

foreach (['about_enabled', 'testimonials_enabled'] as $toggle) {
    set_setting($toggle, !empty($_POST[$toggle]) ? '1' : '0');
}

// --- Branding uploads (optional — only replace if a new file was sent) ---
$uploadDir = __DIR__ . '/../../uploads/branding/';
$allowedExtensions = ['svg', 'png', 'ico'];

foreach (['logo' => 'logo_path', 'favicon' => 'favicon_path'] as $inputName => $settingKey) {
    if (!empty($_FILES[$inputName]['name']) && $_FILES[$inputName]['error'] === UPLOAD_ERR_OK) {
        $tmpPath = $_FILES[$inputName]['tmp_name'];
        $size = $_FILES[$inputName]['size'];
        $originalName = $_FILES[$inputName]['name'];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if ($size > 1024 * 1024) {
            json_response(400, ['error' => ucfirst($inputName) . ' file is too large (max 1MB).']);
        }
        if (!in_array($ext, $allowedExtensions, true)) {
            json_response(400, ['error' => ucfirst($inputName) . ' must be an .svg, .png, or .ico file.']);
        }
        if ($ext === 'svg') {
            $contents = file_get_contents($tmpPath);
            if ($contents === false || stripos($contents, '<script') !== false || stripos($contents, 'onload=') !== false) {
                json_response(400, ['error' => 'That SVG file contains scripting and was rejected for safety. Please use a plain vector export.']);
            }
        }

        $filename = $inputName . '-' . time() . '.' . $ext;
        $destination = $uploadDir . $filename;

        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        if (!move_uploaded_file($tmpPath, $destination)) {
            json_response(500, ['error' => 'Could not save the uploaded ' . $inputName . ' file.']);
        }

        set_setting($settingKey, '/uploads/branding/' . $filename);
    }
}

json_response(200, ['message' => 'Settings saved.', 'settings' => get_all_settings()]);
