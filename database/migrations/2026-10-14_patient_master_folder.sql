-- Patient Master Folder Banner (system-wide patient header).
--
-- The master-folder banner surfaces a few demographics that patient_registrations
-- did not hold (nationality), and it can register two clinical events that had no
-- home in the schema at all: a pregnancy/ANC record and a mortality record.
--
-- Idempotent: safe to re-run. MySQL 8.4 has no "ADD COLUMN IF NOT EXISTS" and no
-- "CREATE INDEX IF NOT EXISTS", so those are guarded from information_schema;
-- the tables use CREATE TABLE IF NOT EXISTS.

SET @db := DATABASE();

-- ---- nationality ---------------------------------------------------------
SET @sql := (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.COLUMNS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'patient_registrations' AND COLUMN_NAME = 'nationality') = 0,
    'ALTER TABLE patient_registrations ADD COLUMN nationality VARCHAR(60) DEFAULT NULL AFTER address',
    'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- ---- pregnancy / ANC records --------------------------------------------
CREATE TABLE IF NOT EXISTS patient_pregnancies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    gestational_age INT DEFAULT NULL,
    lmp_date DATE DEFAULT NULL,
    edd_date DATE DEFAULT NULL,
    status ENUM('active', 'delivered', 'closed') NOT NULL DEFAULT 'active',
    recorded_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_registrations(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- ---- mortality records ---------------------------------------------------
CREATE TABLE IF NOT EXISTS patient_deaths (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    death_datetime DATETIME DEFAULT NULL,
    cause_primary VARCHAR(255) DEFAULT NULL,
    certifying_doctor VARCHAR(150) DEFAULT NULL,
    recorded_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_registrations(id),
    FOREIGN KEY (recorded_by) REFERENCES users(id)
);

-- ---- indexes -------------------------------------------------------------
SET @sql := (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'patient_pregnancies' AND INDEX_NAME = 'idx_patient_pregnancies_patient') = 0,
    'CREATE INDEX idx_patient_pregnancies_patient ON patient_pregnancies(patient_id)',
    'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql := (SELECT IF(
    (SELECT COUNT(*) FROM information_schema.STATISTICS
     WHERE TABLE_SCHEMA = @db AND TABLE_NAME = 'patient_deaths' AND INDEX_NAME = 'idx_patient_deaths_patient') = 0,
    'CREATE INDEX idx_patient_deaths_patient ON patient_deaths(patient_id)',
    'DO 0'));
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
