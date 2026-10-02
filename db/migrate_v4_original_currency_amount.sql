-- Run this ONCE on an existing ConAlert database (phpMyAdmin -> Import, or
--   mysql -u yourdbuser -p yourdbname < db/migrate_v4_original_currency_amount.sql)
-- Back up the database first.
--
-- Adds amount_original: the amount in the reporter's original currency
-- (e.g. 0.75 BTC, 14000 USDT, 2500 EUR). Crypto prices move, so the exact
-- original amount is stored alongside the USD value at the time of report.
-- Existing cases are untouched (amount_original stays empty until an admin
-- fills it in from the case drawer).
--
-- If you're setting ConAlert up fresh, skip this — db/schema.sql already
-- includes the column.

ALTER TABLE cases ADD COLUMN IF NOT EXISTS amount_original DECIMAL(30,8) NULL AFTER amount_usd;
