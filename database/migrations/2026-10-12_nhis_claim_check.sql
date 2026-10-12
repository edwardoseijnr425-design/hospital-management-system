-- Migration: NHIA tariff compliance fields on patient_registrations
--
-- Adds the Claim Check Code (CCC) and the NHIS expiry date captured by the
-- "Verify NHIS & Get CCC" component on Patient Registration.
--
-- Fresh installs get these columns automatically from database/schema.sql.
-- Existing installations that were set up before this migration must run this
-- file once:
--
--   mysql -u root hms_db < database/migrations/2026-10-12_nhis_claim_check.sql
--
-- MySQL does not support ADD COLUMN IF NOT EXISTS, so the procedure below is
-- idempotent: it simply skips columns that are already present.

DELIMITER $$
DROP PROCEDURE IF EXISTS migrate_nhis_claim_check$$
CREATE PROCEDURE migrate_nhis_claim_check()
BEGIN
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'patient_registrations'
          AND COLUMN_NAME = 'claim_check_code'
    ) THEN
        ALTER TABLE patient_registrations
            ADD COLUMN claim_check_code VARCHAR(20) NULL AFTER nhia_number;
    END IF;

    IF NOT EXISTS (
        SELECT 1 FROM information_schema.COLUMNS
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'patient_registrations'
          AND COLUMN_NAME = 'nhis_expiry_date'
    ) THEN
        ALTER TABLE patient_registrations
            ADD COLUMN nhis_expiry_date DATE NULL AFTER claim_check_code;
    END IF;
END$$
DELIMITER ;

CALL migrate_nhis_claim_check();
DROP PROCEDURE IF EXISTS migrate_nhis_claim_check;