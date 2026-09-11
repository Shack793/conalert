-- ConAlert database schema (MySQL / MariaDB — matches what cPanel shared
-- hosting gives you). Import this via phpMyAdmin, or:
--   mysql -u yourdbuser -p yourdbname < db/schema.sql

CREATE TABLE IF NOT EXISTS admins (
  id             INT AUTO_INCREMENT PRIMARY KEY,
  name           VARCHAR(150) NOT NULL,
  email          VARCHAR(190) NOT NULL UNIQUE,
  password_hash  VARCHAR(255) NOT NULL,
  role           ENUM('admin','staff') NOT NULL DEFAULT 'staff',
  status         ENUM('active','revoked') NOT NULL DEFAULT 'active',
  created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  created_by     INT NULL,
  last_login_at  DATETIME NULL,
  FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS cases (
  id                  INT AUTO_INCREMENT PRIMARY KEY,
  created_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at          DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  status              ENUM('new','in_review','verified','published','resolved','rejected') NOT NULL DEFAULT 'new',
  priority            ENUM('low','normal','high') NOT NULL DEFAULT 'normal',

  -- Reporter (private — never returned by public endpoints)
  full_name           VARCHAR(200) NOT NULL,
  email               VARCHAR(200) NOT NULL,
  country             VARCHAR(100) NULL,

  -- What happened
  platform_name       VARCHAR(200) NOT NULL,
  platform_type       ENUM('casino','exchange','other') NOT NULL,
  amount_usd          DECIMAL(14,2) NULL,
  currency_lost       VARCHAR(20) NULL,
  incident_date       VARCHAR(20) NULL,
  description         TEXT NOT NULL,
  evidence_links      TEXT NULL,          -- newline-separated URLs

  -- Publication
  consent_to_publish  TINYINT(1) NOT NULL DEFAULT 0,
  public_summary      TEXT NULL,          -- admin-written, fact-checked, safe to publish
  admin_notes         TEXT NULL,          -- private, never shown publicly

  -- Anti-spam
  honeypot_tripped    TINYINT(1) NOT NULL DEFAULT 0,

  INDEX idx_cases_status (status),
  INDEX idx_cases_platform (platform_name),
  INDEX idx_cases_email (email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS case_events (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  case_id     INT NOT NULL,
  created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  event_type  VARCHAR(50) NOT NULL,
  detail      TEXT NULL,
  actor       VARCHAR(150) NULL,          -- admin name/email who made the change, if any
  FOREIGN KEY (case_id) REFERENCES cases(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
