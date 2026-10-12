-- Radiology Desk: Cancel or Reschedule a study with reasons.
--
-- radiology_requests had no place to record why a study was cancelled or when
-- it was moved to, so the Cancel / Reschedule action had nothing to persist.
-- These columns hold the reason, the new date/time (reschedule only) and who
-- last changed the request's status. Kept identical to the lab_requests
-- migration (2026-10-12_lab_cancel_reschedule.sql) so both departments behave
-- the same way.
--
-- Fresh installs get these columns automatically from database/schema.sql.
-- Existing installations that were set up before this migration must run this
-- file once:
--
--   mysql -u root hms_db < database/migrations/2026-10-12_radiology_cancel_reschedule.sql
--
-- Idempotent: safe to re-run. MySQL 8.4 has no "ADD COLUMN IF NOT EXISTS", so
-- each column is added only when the information_schema shows it is missing.

SET @db := DATABASE();

SET @sql := (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'radiology_requests' AND COLUMN_NAME = 'status_reason') = 0,
    'ALTER TABLE radiology_requests ADD COLUMN status_reason VARCHAR(255) DEFAULT NULL AFTER status',
    'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'radiology_requests' AND COLUMN_NAME = 'rescheduled_to') = 0,
    'ALTER TABLE radiology_requests ADD COLUMN rescheduled_to DATETIME DEFAULT NULL AFTER status_reason',
    'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'radiology_requests' AND COLUMN_NAME = 'status_updated_by') = 0,
    'ALTER TABLE radiology_requests ADD COLUMN status_updated_by INT DEFAULT NULL AFTER rescheduled_to',
    'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'radiology_requests' AND COLUMN_NAME = 'status_updated_at') = 0,
    'ALTER TABLE radiology_requests ADD COLUMN status_updated_at TIMESTAMP NULL DEFAULT NULL AFTER status_updated_by',
    'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
