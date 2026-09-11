-- Migration for existing conalert DB: editable case numbers, footer settings, social links
-- Run via phpMyAdmin -> SQL tab, or Import this file

ALTER TABLE cases ADD COLUMN case_number VARCHAR(32) NULL UNIQUE AFTER id;
CREATE INDEX idx_cases_case_number ON cases(case_number);
UPDATE cases SET case_number = LPAD(id, 4, '0') WHERE case_number IS NULL;

CREATE TABLE IF NOT EXISTS settings (
  `key` VARCHAR(80) NOT NULL PRIMARY KEY,
  value TEXT NULL,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  updated_by INT NULL,
  FOREIGN KEY (updated_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO settings (`key`, value) VALUES
  ('contact_email','cases@conalert.org'),
  ('footer_align','space-between');

CREATE TABLE IF NOT EXISTS social_links (
  id INT AUTO_INCREMENT PRIMARY KEY,
  platform VARCHAR(40) NOT NULL UNIQUE,
  label VARCHAR(80) NULL,
  url TEXT NULL,
  sort_order INT NOT NULL DEFAULT 0,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  created_by INT NULL,
  FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO social_links (platform, label, url, sort_order) VALUES
  ('instagram','Instagram','',1),
  ('twitter','Twitter','',2);
