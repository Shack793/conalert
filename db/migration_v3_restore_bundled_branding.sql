-- Restore the bundled blue/yellow ConAlert logo after a custom branding upload.
UPDATE site_settings
SET setting_value = '/assets/logo.svg'
WHERE setting_key = 'logo_path';

UPDATE site_settings
SET setting_value = '/assets/favicon.svg'
WHERE setting_key = 'favicon_path';