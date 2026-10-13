-- Doctor Station: add "Progress Report Note" entry type.
--
-- The Doctor Clinical Round Notes & Discharge Summary form gained a fourth
-- entry type (Progress Report Note) alongside the existing OPD consultation,
-- IPD daily round and discharge summary entries. clinical_notes.note_type is
-- an ENUM, so the new value has to be added to the column definition.
--
-- Fresh installs get the widened ENUM automatically from database/schema.sql.
-- Existing installations that were set up before this migration must run this
-- file once:
--
--     mysql -uroot hms_db < database/migrations/2026-10-13_clinical_note_progress_type.sql
--
-- Idempotent: the ALTER only runs while the column type does not already list
-- PROGRESS_NOTE.

SET @ddl := 'ALTER TABLE clinical_notes MODIFY note_type ENUM(''OPD_NOTE'',''IPD_NOTE'',''DISCHARGE_SUMMARY'',''PROGRESS_NOTE'') NOT NULL';

SET @sql := (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'clinical_notes'
       AND COLUMN_TYPE NOT LIKE '%PROGRESS_NOTE%') > 0,
    @ddl, 'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;