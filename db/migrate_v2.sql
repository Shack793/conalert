-- Run this ONCE if you already deployed the earlier version of ConAlert and
-- have real data in it. Safe to run via phpMyAdmin -> Import, or:
--   mysql -u yourdbuser -p yourdbname < db/migrate_v2.sql
--
-- If you're setting ConAlert up fresh, you don't need this file — just
-- import db/schema.sql, which already includes everything below.

-- Add the admin-editable case reference number, and back-fill it with the
-- existing internal id so nothing breaks for cases you already published.
ALTER TABLE cases ADD COLUMN IF NOT EXISTS case_number VARCHAR(50) NULL AFTER id;
UPDATE cases SET case_number = CAST(id AS CHAR) WHERE case_number IS NULL;

CREATE TABLE IF NOT EXISTS site_settings (
  setting_key    VARCHAR(100) PRIMARY KEY,
  setting_value  TEXT NULL,
  updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS testimonials (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  name            VARCHAR(150) NOT NULL,
  email           VARCHAR(200) NULL,
  role_or_context VARCHAR(200) NULL,
  testimonial     TEXT NOT NULL,
  status          ENUM('pending','approved','rejected') NOT NULL DEFAULT 'pending',
  reviewed_at     DATETIME NULL,
  reviewed_by     VARCHAR(150) NULL,
  INDEX idx_testimonials_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
