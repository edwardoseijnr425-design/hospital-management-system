-- Doctor Station: "Other" modality on the Request Test block.
--
-- Lab requests live in lab_requests and imaging (radiology + ultrasound /
-- sonography) lives in radiology_requests, but an "Other" test (ECG,
-- spirometry, specialist procedure...) had nowhere to go. This table gives the
-- Other modality a home so those orders stay visible in the Doctor Station's
-- Investigations table like lab and imaging orders do.
--
-- Fresh installs get this table automatically from database/schema.sql.
-- Existing installations that were set up before this migration must run this
-- file once:
--
--     mysql -uroot hms_db < database/migrations/2026-10-12_other_requests.sql

CREATE TABLE IF NOT EXISTS other_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    doctor_id INT NOT NULL,
    request_type VARCHAR(100) NOT NULL,
    notes TEXT,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES patient_visits(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id),
    INDEX idx_other_requests_visit (visit_id),
    INDEX idx_other_requests_status (status)
);