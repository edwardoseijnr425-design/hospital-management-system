-- Migration: service_ref on service_prices
--
-- Radiology exams (and lab tests) are catalogue strings, not rows in a
-- catalogue table, so they have no numeric id to price against. This adds a
-- name key so a price can be attached to an exam:
--
--   service_type = 'radiology', service_ref = 'Chest X-Ray (PA & Lateral)',
--   service_id   = 0
--
-- A radiology price is then auto-posted to the patient's bill when the study is
-- ordered (Radiology module and Doctor Station).
--
-- Fresh installs get this column automatically from database/schema.sql.
-- Existing installations that were set up before this migration must run this
-- file once:
--
--   mysql -u root hms_db < database/migrations/2026-10-12_service_prices_service_ref.sql
--
-- MySQL does not support ADD COLUMN IF NOT EXISTS, so the procedure below is
-- idempotent: it simply skips the column if it is already present.

DELIMITER $$
DROP PROCEDURE IF EXISTS migrate_service_prices_service_ref$$
CREATE PROCEDURE migrate_service_prices_service_ref()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'service_prices'
          AND COLUMN_NAME = 'service_ref'
    ) THEN
        ALTER TABLE service_prices
            ADD COLUMN service_ref VARCHAR(120) DEFAULT NULL AFTER service_id;
    END IF;
END$$
DELIMITER ;

CALL migrate_service_prices_service_ref();
DROP PROCEDURE IF EXISTS migrate_service_prices_service_ref;
