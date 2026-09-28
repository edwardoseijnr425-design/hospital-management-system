-- Hospital Management System Database Schema
-- Inspired by LHIMS workflow
-- Compatible with XAMPP/MySQL

-- Drop existing tables if they exist
DROP TABLE IF EXISTS audit_trails;
DROP TABLE IF EXISTS billing_items;
DROP TABLE IF EXISTS invoices;
DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS messages;
DROP TABLE IF EXISTS bed_assignments;
DROP TABLE IF EXISTS beds;
DROP TABLE IF EXISTS wards;
DROP TABLE IF EXISTS lab_results;
DROP TABLE IF EXISTS lab_requests;
DROP TABLE IF EXISTS radiology_results;
DROP TABLE IF EXISTS radiology_requests;
DROP TABLE IF EXISTS prescriptions;
DROP TABLE IF EXISTS medication_dispensing;
DROP TABLE IF EXISTS pharmacy_inventory;
DROP TABLE IF EXISTS requisitions;
DROP TABLE IF EXISTS vital_signs;
DROP TABLE IF EXISTS consultations;
DROP TABLE IF EXISTS patient_visits;
DROP TABLE IF EXISTS patient_registrations;
DROP TABLE IF EXISTS sponsors;
DROP TABLE IF EXISTS service_prices;
DROP TABLE IF EXISTS procedures;
DROP TABLE IF EXISTS consultation_services;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS user_profiles;
DROP TABLE IF EXISTS users;

-- Users table with role-based access
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) UNIQUE,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('super_admin', 'admin', 'it', 'records', 'nurse', 'doctor', 'pharmacy', 'lab', 'radiology', 'account', 'revenue') NOT NULL,
    department_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- User profiles for additional information
CREATE TABLE user_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNIQUE NOT NULL,
    phone VARCHAR(20),
    address TEXT,
    license_number VARCHAR(50),
    specialization VARCHAR(100),
    profile_image VARCHAR(255),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Departments
CREATE TABLE departments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    head_of_department INT,
    status ENUM('Active', 'Frozen', 'Deactivated') DEFAULT 'Active' NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (head_of_department) REFERENCES users(id)
);

-- Medical Teams (clinical duty teams used for appointment assignment)
CREATE TABLE medical_teams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    department_id INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Consultation Services
CREATE TABLE consultation_services (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    department_id INT,
    description TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Procedures
CREATE TABLE procedures (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    category ENUM('surgery', 'diagnostic', 'therapeutic', 'other') NOT NULL,
    department_id INT,
    description TEXT,
    duration_minutes INT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Service Prices (Consultations, Surgeries, Drugs)
CREATE TABLE service_prices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    service_type ENUM('consultation', 'procedure', 'drug', 'lab_test', 'radiology', 'other') NOT NULL,
    service_id INT NOT NULL,
    price DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'GHS',
    effective_date DATE NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Sponsors (NHIA and other insurance)
CREATE TABLE sponsors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    type ENUM('nhia', 'private_insurance', 'corporate', 'individual', 'government') NOT NULL,
    contact_person VARCHAR(100),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    nhia_status ENUM('active', 'inactive', 'suspended') NULL,
    expiry_date DATE NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Patient Registrations
CREATE TABLE patient_registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hospital_number VARCHAR(20) UNIQUE NOT NULL,
    first_name VARCHAR(50) NOT NULL,
    middle_name VARCHAR(50),
    last_name VARCHAR(50) NOT NULL,
    date_of_birth DATE NOT NULL,
    gender ENUM('male', 'female', 'other') NOT NULL,
    title VARCHAR(10),
    occupation VARCHAR(100),
    marital_status ENUM('Single', 'Married', 'Divorced', 'Widowed'),
    secondary_phone VARCHAR(20),
    phone VARCHAR(20),
    email VARCHAR(100),
    address TEXT,
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    blood_group ENUM('A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'),
    sponsor_id INT,
    nhia_number VARCHAR(50),
    registration_date DATE NOT NULL,
    registered_by INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sponsor_id) REFERENCES sponsors(id),
    FOREIGN KEY (registered_by) REFERENCES users(id)
);

-- Patient Visits (OPD/IPD)
CREATE TABLE patient_visits (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    visit_number VARCHAR(20) NOT NULL,
    visit_type ENUM('opd', 'ipd', 'emergency') NOT NULL,
    visit_date DATETIME NOT NULL,
    department_id INT NOT NULL,
    chief_complaint TEXT,
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_registrations(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    UNIQUE KEY unique_visit (patient_id, visit_number)
);

-- Consultations
CREATE TABLE consultations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    doctor_id INT NOT NULL,
    consultation_type ENUM('new', 'follow_up', 'emergency') NOT NULL,
    diagnosis TEXT,
    notes TEXT,
    consultation_date DATETIME NOT NULL,
    status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES patient_visits(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id)
);

-- Vital Signs (Nurses Station)
CREATE TABLE vital_signs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    nurse_id INT NOT NULL,
    temperature DECIMAL(4, 1),
    blood_pressure_systolic INT,
    blood_pressure_diastolic INT,
    heart_rate INT,
    respiratory_rate INT,
    oxygen_saturation DECIMAL(4, 1),
    weight DECIMAL(5, 2),
    height DECIMAL(5, 2),
    bmi DECIMAL(4, 1),
    notes TEXT,
    recorded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES patient_visits(id),
    FOREIGN KEY (nurse_id) REFERENCES users(id)
);

-- Pharmacy Inventory
CREATE TABLE pharmacy_inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    drug_name VARCHAR(100) NOT NULL,
    generic_name VARCHAR(100),
    drug_code VARCHAR(20) UNIQUE NOT NULL,
    store_type ENUM('MEDICAL', 'GENERAL') NOT NULL DEFAULT 'MEDICAL',
    category VARCHAR(50),
    manufacturer VARCHAR(100),
    batch_number VARCHAR(50),
    expiry_date DATE,
    quantity_in_stock INT DEFAULT 0,
    reorder_level INT DEFAULT 10,
    unit VARCHAR(20),
    storage_location VARCHAR(50),
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Stock Requisitions (header: staff/departments/wards request from Medical/General store)
CREATE TABLE inventory_requisitions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    req_code VARCHAR(50) NOT NULL UNIQUE,
    store_type ENUM('MEDICAL', 'GENERAL') NOT NULL,
    department_id INT DEFAULT NULL,
    ward_id INT DEFAULT NULL,
    requested_by INT DEFAULT NULL,
    remarks TEXT,
    status ENUM('Pending', 'Approved', 'Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (ward_id) REFERENCES wards(id),
    FOREIGN KEY (requested_by) REFERENCES users(id),
    INDEX idx_requisitions_status (status),
    INDEX idx_requisitions_requester (requested_by)
);

-- Stock Requisition Line Items
CREATE TABLE requisition_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    requisition_id INT NOT NULL,
    item_id INT NOT NULL,
    requested_qty INT NOT NULL,
    FOREIGN KEY (requisition_id) REFERENCES inventory_requisitions(id) ON DELETE CASCADE,
    FOREIGN KEY (item_id) REFERENCES pharmacy_inventory(id)
);

-- Prescriptions
CREATE TABLE prescriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    doctor_id INT NOT NULL,
    drug_id INT NOT NULL,
    dosage VARCHAR(50),
    frequency VARCHAR(50),
    duration VARCHAR(50),
    instructions TEXT,
    status ENUM('pending', 'dispensed', 'cancelled') DEFAULT 'pending',
    prescribed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES patient_visits(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id),
    FOREIGN KEY (drug_id) REFERENCES pharmacy_inventory(id)
);

-- Medication Dispensing
CREATE TABLE medication_dispensing (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prescription_id INT NOT NULL,
    pharmacist_id INT NOT NULL,
    quantity_dispensed INT NOT NULL,
    batch_number VARCHAR(50),
    expiry_date DATE,
    dispensed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT,
    FOREIGN KEY (prescription_id) REFERENCES prescriptions(id),
    FOREIGN KEY (pharmacist_id) REFERENCES users(id)
);

-- Laboratory Requests
CREATE TABLE lab_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    doctor_id INT NOT NULL,
    test_type VARCHAR(100) NOT NULL,
    test_description TEXT,
    urgency ENUM('routine', 'urgent', 'emergency') DEFAULT 'routine',
    status ENUM('pending', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES patient_visits(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id)
);

-- Laboratory Results
CREATE TABLE lab_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lab_request_id INT NOT NULL UNIQUE,
    technician_id INT NOT NULL,
    results TEXT NOT NULL,
    normal_range TEXT,
    interpretation TEXT,
    status ENUM('draft', 'final', 'amended') DEFAULT 'draft',
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_by INT,
    verified_at TIMESTAMP NULL,
    FOREIGN KEY (lab_request_id) REFERENCES lab_requests(id),
    FOREIGN KEY (technician_id) REFERENCES users(id),
    FOREIGN KEY (verified_by) REFERENCES users(id)
);

-- Radiology Requests
CREATE TABLE radiology_requests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    doctor_id INT NOT NULL,
    exam_type VARCHAR(100) NOT NULL,
    body_part VARCHAR(100),
    clinical_indication TEXT,
    urgency ENUM('routine', 'urgent', 'emergency') DEFAULT 'routine',
    status ENUM('pending', 'scheduled', 'in_progress', 'completed', 'cancelled') DEFAULT 'pending',
    requested_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    scheduled_at TIMESTAMP NULL,
    FOREIGN KEY (visit_id) REFERENCES patient_visits(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id)
);

-- Radiology Results
CREATE TABLE radiology_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    radiology_request_id INT NOT NULL UNIQUE,
    radiologist_id INT NOT NULL,
    findings TEXT NOT NULL,
    impression TEXT,
    status ENUM('draft', 'final', 'amended') DEFAULT 'draft',
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    verified_by INT,
    verified_at TIMESTAMP NULL,
    image_path VARCHAR(255),
    FOREIGN KEY (radiology_request_id) REFERENCES radiology_requests(id),
    FOREIGN KEY (radiologist_id) REFERENCES users(id),
    FOREIGN KEY (verified_by) REFERENCES users(id)
);

-- Wards (IPD Management)
CREATE TABLE wards (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ward_code VARCHAR(50) NOT NULL UNIQUE,
    ward_name VARCHAR(100) NOT NULL,
    ward_type ENUM('Male', 'Female', 'Pediatric', 'ICU', 'Maternity', 'General') NOT NULL,
    floor_level VARCHAR(50) DEFAULT 'Floor 1',
    capacity INT NOT NULL DEFAULT 10,
    status ENUM('Active', 'Maintenance', 'Full') DEFAULT 'Active',
    is_frozen TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Rooms
CREATE TABLE rooms (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ward_id INT NOT NULL,
    room_number VARCHAR(50) NOT NULL,
    room_type ENUM('Standard', 'VIP', 'Isolation', 'Semi-Private') DEFAULT 'Standard',
    status ENUM('Available', 'Occupied', 'Cleaning', 'Maintenance') DEFAULT 'Available',
    FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE CASCADE
);

-- Beds
CREATE TABLE beds (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ward_id INT NOT NULL,
    room_id INT DEFAULT NULL,
    bed_number VARCHAR(50) NOT NULL,
    status ENUM('Available', 'Occupied', 'Reserved', 'Maintenance') DEFAULT 'Available',
    current_patient_id INT DEFAULT NULL,
    FOREIGN KEY (ward_id) REFERENCES wards(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL
);

-- Admissions Register (inpatient admissions)
CREATE TABLE admissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    admission_code VARCHAR(40) UNIQUE NOT NULL,
    patient_id INT NOT NULL,
    ward_id INT NOT NULL,
    bed_id INT NOT NULL,
    admission_date DATETIME NOT NULL,
    admission_type ENUM('Emergency', 'Routine', 'Elective', 'Transfer', 'Maternity') DEFAULT 'Routine',
    admitting_doctor VARCHAR(100) DEFAULT '',
    department_id INT DEFAULT NULL,
    diagnosis VARCHAR(255) DEFAULT '',
    referred_by VARCHAR(100) DEFAULT '',
    notes TEXT,
    admitted_by INT,
    status ENUM('Admitted', 'Discharged') DEFAULT 'Admitted',
    discharged_at DATETIME DEFAULT NULL,
    discharge_notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_registrations(id),
    FOREIGN KEY (ward_id) REFERENCES wards(id),
    FOREIGN KEY (bed_id) REFERENCES beds(id),
    FOREIGN KEY (department_id) REFERENCES departments(id)
);

-- Invoices
CREATE TABLE invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    visit_id INT NOT NULL,
    invoice_number VARCHAR(30) UNIQUE NOT NULL,
    patient_id INT NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    discount_amount DECIMAL(10, 2) DEFAULT 0,
    tax_amount DECIMAL(10, 2) DEFAULT 0,
    net_amount DECIMAL(10, 2) NOT NULL,
    sponsor_id INT,
    status ENUM('draft', 'pending', 'partial', 'paid', 'cancelled') DEFAULT 'pending',
    payment_method VARCHAR(40) DEFAULT NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (visit_id) REFERENCES patient_visits(id),
    FOREIGN KEY (patient_id) REFERENCES patient_registrations(id),
    FOREIGN KEY (sponsor_id) REFERENCES sponsors(id),
    FOREIGN KEY (created_by) REFERENCES users(id)
);

-- Billing Items (Line items for invoices)
CREATE TABLE billing_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    invoice_id INT NOT NULL,
    item_type ENUM('consultation', 'procedure', 'drug', 'lab_test', 'radiology', 'bed', 'other') NOT NULL,
    item_id INT NOT NULL,
    description VARCHAR(255),
    quantity INT DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_price DECIMAL(10, 2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (invoice_id) REFERENCES invoices(id)
);

-- Messages & Alerts (staff -> staff / patients)
CREATE TABLE messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    recipient_type ENUM('patient', 'staff') NOT NULL DEFAULT 'staff',
    recipient_id INT NOT NULL,
    subject VARCHAR(200) NOT NULL,
    message TEXT NOT NULL,
    channel ENUM('internal', 'sms', 'email') DEFAULT 'internal',
    patient_context_id INT,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id),
    FOREIGN KEY (patient_context_id) REFERENCES patient_registrations(id),
    INDEX idx_messages_recipient (recipient_type, recipient_id),
    INDEX idx_messages_sent (sent_at)
);

-- Appointments (Appointment Calendar)
CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    patient_id INT NOT NULL,
    appointment_date DATETIME NOT NULL,
    department_id INT,
    doctor_id INT,
    reason TEXT,
    consultation_type ENUM('OPD', 'ENT', 'EYE', 'EMERGENCY', 'GENERAL', 'SPECIALIST', 'FOLLOWUP', 'PEDIATRIC', 'DENTAL') DEFAULT 'OPD',
    visit_type ENUM('New', 'Review') DEFAULT 'New',
    status ENUM('scheduled', 'confirmed', 'completed', 'cancelled', 'no_show') DEFAULT 'scheduled',
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (patient_id) REFERENCES patient_registrations(id),
    FOREIGN KEY (department_id) REFERENCES departments(id),
    FOREIGN KEY (doctor_id) REFERENCES users(id),
    FOREIGN KEY (created_by) REFERENCES users(id),
    INDEX idx_appointments_date (appointment_date),
    INDEX idx_appointments_status (status)
);

-- Audit Trail
CREATE TABLE audit_trails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    action VARCHAR(50) NOT NULL,
    table_name VARCHAR(50) NOT NULL,
    record_id INT,
    old_values TEXT,
    new_values TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- Create indexes for performance
CREATE INDEX idx_patient_registrations_hospital_number ON patient_registrations(hospital_number);
CREATE INDEX idx_patient_registrations_sponsor ON patient_registrations(sponsor_id);
CREATE INDEX idx_patient_visits_patient ON patient_visits(patient_id);
CREATE INDEX idx_patient_visits_date ON patient_visits(visit_date);
CREATE INDEX idx_patient_visits_status ON patient_visits(status);
CREATE INDEX idx_consultations_visit ON consultations(visit_id);
CREATE INDEX idx_consultations_doctor ON consultations(doctor_id);
CREATE INDEX idx_vital_signs_visit ON vital_signs(visit_id);
CREATE INDEX idx_prescriptions_visit ON prescriptions(visit_id);
CREATE INDEX idx_lab_requests_visit ON lab_requests(visit_id);
CREATE INDEX idx_lab_requests_status ON lab_requests(status);
CREATE INDEX idx_radiology_requests_visit ON radiology_requests(visit_id);
CREATE INDEX idx_radiology_requests_status ON radiology_requests(status);
CREATE INDEX idx_bed_assignments_bed ON bed_assignments(bed_id);
CREATE INDEX idx_bed_assignments_status ON bed_assignments(status);
CREATE INDEX idx_invoices_visit ON invoices(visit_id);
CREATE INDEX idx_invoices_patient ON invoices(patient_id);
CREATE INDEX idx_invoices_status ON invoices(status);
CREATE INDEX idx_audit_trails_user ON audit_trails(user_id);
CREATE INDEX idx_audit_trails_action ON audit_trails(action);
CREATE INDEX idx_audit_trails_table ON audit_trails(table_name);
CREATE INDEX idx_audit_trails_created ON audit_trails(created_at);

-- Insert default departments (old EHMS roster)
INSERT INTO departments (name, code, description) VALUES
('IT', 'IT', 'Technical support and system maintenance'),
('Records', 'REC', 'Patient registration and records management'),
('Revenue / Account', 'REVACC', 'Revenue collection and account management'),
('Accountant', 'ACC', 'Accounting and billing services'),
('Eye', 'EYE', 'Ophthalmology / eye care services'),
('ENT', 'ENT', 'Ear, nose and throat services'),
('Pharmacy', 'PHA', 'Medication dispensing and inventory'),
('Theatre', 'THT', 'Operating theatre services'),
('Anaesthesia', 'ANE', 'Anaesthesia services'),
('Health Information', 'HIS', 'Health information management'),
('Females', 'FEM', 'General female ward'),
('Males', 'MAL', 'General male ward'),
('Paediatrics', 'PAE', 'Paediatric care'),
('NICU', 'NICU', 'Neonatal intensive care'),
('Labour', 'LABR', 'Labour and delivery'),
('Lying-In', 'LYI', 'Postnatal / lying-in ward'),
('Surgical', 'SUR', 'Surgical services'),
('Mental Health', 'MEN', 'Mental health services'),
('Physiotherapy', 'PHY', 'Physiotherapy services'),
('Oncology', 'ONC', 'Oncology and cancer care'),
('Lab', 'LAB', 'Laboratory tests and diagnostics'),
('Scan', 'SCAN', 'Ultrasound / scanning services'),
('X-Ray', 'XRAY', 'X-ray and imaging services');

-- Default medical teams (duty/clinical teams; mapped to receiving departments)
INSERT INTO medical_teams (name, department_id) VALUES
('OPD Duty Medical Team A', NULL),
('Surgical Duty Team', (SELECT id FROM departments WHERE name = 'Surgical')),
('Pediatric Specialist Team', (SELECT id FROM departments WHERE name = 'Paediatrics')),
('Obstetrics & Gynaecology Team', (SELECT id FROM departments WHERE name = 'Labour')),
('Emergency Resuscitation Team', NULL),
('Internal Medicine Team B', NULL);

-- Insert default consultation services
INSERT INTO consultation_services (name, code, department_id, description) VALUES
('General Consultation', 'GC001', (SELECT id FROM departments WHERE code = 'SUR'), 'General medical consultation'),
('Specialist Consultation', 'SC001', (SELECT id FROM departments WHERE code = 'SUR'), 'Specialist medical consultation'),
('Emergency Consultation', 'EC001', (SELECT id FROM departments WHERE code = 'SUR'), 'Emergency medical consultation'),
('Follow-up Consultation', 'FC001', (SELECT id FROM departments WHERE code = 'SUR'), 'Follow-up medical consultation');

-- Insert default procedures
INSERT INTO procedures (name, code, category, department_id, description) VALUES
('Appendectomy', 'AP001', 'surgery', (SELECT id FROM departments WHERE code = 'SUR'), 'Appendix removal surgery'),
('C-section', 'CS001', 'surgery', (SELECT id FROM departments WHERE code = 'SUR'), 'Cesarean section delivery'),
('X-Ray', 'XR001', 'diagnostic', (SELECT id FROM departments WHERE code = 'XRAY'), 'X-ray imaging'),
('CT Scan', 'CT001', 'diagnostic', (SELECT id FROM departments WHERE code = 'XRAY'), 'CT scan imaging'),
('MRI', 'MR001', 'diagnostic', (SELECT id FROM departments WHERE code = 'XRAY'), 'MRI imaging'),
('Blood Test', 'BT001', 'diagnostic', (SELECT id FROM departments WHERE code = 'LAB'), 'Complete blood count'),
('Urine Analysis', 'UA001', 'diagnostic', (SELECT id FROM departments WHERE code = 'LAB'), 'Urine analysis test');

-- Insert default sponsor (NHIA)
INSERT INTO sponsors (name, code, type, nhia_status) VALUES
('National Health Insurance Authority', 'NHIA', 'nhia', 'active');
