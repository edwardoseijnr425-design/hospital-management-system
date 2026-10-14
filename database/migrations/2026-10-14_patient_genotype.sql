-- Patient genotype (sickle-cell status).
--
-- Surfaced by the system-wide Patient Master Folder banner next to the blood
-- group. Optional at registration; nullable for every existing patient.
--
-- Idempotent: safe to re-run (MySQL 8.4 has no ADD COLUMN IF NOT EXISTS).

SET @db := DATABASE();

SET @sql := (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'patient_registrations' AND COLUMN_NAME = 'genotype') = 0,
    "ALTER TABLE patient_registrations ADD COLUMN genotype ENUM('AA','AS','SS','AC','SC','CC') DEFAULT NULL AFTER blood_group",
    'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
