<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

requireLogin();

switch ($method) {
    case 'GET':
        if ($action === 'search') {
            searchPatients();
        } elseif ($action === 'stats') {
            getPatientStats();
        } elseif ($action === 'summary') {
            getPatientSummary();
        } elseif ($action === 'clinical-history') {
            getPatientClinicalHistory();
        } else {
            getPatients();
        }
        break;
    case 'POST':
        if ($action === 'create') {
            createPatient();
        } elseif ($action === 'pregnancy') {
            savePregnancy();
        } elseif ($action === 'mortality') {
            saveMortality();
        } else {
            jsonResponse(['error' => 'Invalid action'], 400);
        }
        break;
    case 'PUT':
        updatePatient();
        break;
    case 'DELETE':
        deletePatient();
        break;
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

function getPatients() {
    $filters = [
        'sponsor_id' => $_GET['sponsor_id'] ?? null,
        'gender' => $_GET['gender'] ?? null,
        'date_from' => $_GET['date_from'] ?? null,
        'date_to' => $_GET['date_to'] ?? null,
        'search' => $_GET['search'] ?? null
    ];
    
    $page = $_GET['page'] ?? 1;
    $perPage = $_GET['per_page'] ?? ITEMS_PER_PAGE;
    
    $patientModel = new Patient();
    $patients = $patientModel->getAll($filters, $page, $perPage);
    
    jsonResponse([
        'success' => true,
        'patients' => $patients
    ]);
}

function searchPatients() {
    $query = $_GET['q'] ?? '';
    $limit = $_GET['limit'] ?? 20;
    $field = $_GET['field'] ?? '';
    
    $patientModel = new Patient();
    $patients = $patientModel->search($query, $limit, $field);
    
    jsonResponse([
        'success' => true,
        'patients' => $patients
    ]);
}

function createPatient() {
    $data = getPostData();
    
    $requiredFields = ['first_name', 'last_name', 'date_of_birth', 'gender'];
    $errors = validateRequired($data, $requiredFields);
    
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }
    
    $patientModel = new Patient();
    
    try {
        $patientId = $patientModel->register($data);
        $patient = $patientModel->getById($patientId);
        
        jsonResponse([
            'success' => true,
            'patient_id' => $patientId,
            'patient' => $patient
        ]);
    } catch (Exception $e) {
        error_log("Patient creation error: " . $e->getMessage());
        jsonResponse(['error' => 'Patient registration failed'], 500);
    }
}

function updatePatient() {
    $patientId = $_GET['id'] ?? null;
    
    if (!$patientId) {
        jsonResponse(['error' => 'Patient ID is required'], 400);
    }
    
    $data = getPostData();
    $patientModel = new Patient();
    
    try {
        $patientModel->update($patientId, $data);
        $patient = $patientModel->getById($patientId);
        
        jsonResponse([
            'success' => true,
            'patient' => $patient
        ]);
    } catch (Exception $e) {
        error_log("Patient update error: " . $e->getMessage());
        jsonResponse(['error' => 'Patient update failed'], 500);
    }
}

function deletePatient() {
    requireRole(['super_admin', 'admin']);
    
    $patientId = $_GET['id'] ?? null;
    
    if (!$patientId) {
        jsonResponse(['error' => 'Patient ID is required'], 400);
    }
    
    $patientModel = new Patient();
    
    try {
        // Soft delete by setting is_active to false
        $patientModel->update($patientId, ['is_active' => false]);
        
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Patient deletion error: " . $e->getMessage());
        jsonResponse(['error' => 'Patient deletion failed'], 500);
    }
}

function getPatientStats() {
    $dateFrom = $_GET['date_from'] ?? null;
    $dateTo = $_GET['date_to'] ?? null;
    
    $patientModel = new Patient();
    $stats = $patientModel->getStats($dateFrom, $dateTo);
    
    jsonResponse([
        'success' => true,
        'stats' => $stats
    ]);
}

// Register / open an ANC (pregnancy) record for a patient, from the
// system-wide Patient Master Folder banner's "Tick if Pregnant" action.
function savePregnancy() {
    requireRole(['admin', 'records', 'nurse', 'doctor']);
    $data = getPostData();

    $patientId = (int)($data['patient_id'] ?? 0);
    if (!$patientId) {
        jsonResponse(['success' => false, 'error' => 'Patient ID is required'], 400);
    }

    $db = Database::getInstance();
    $patient = $db->fetchOne("SELECT id, gender FROM patient_registrations WHERE id = ?", [$patientId]);
    if (!$patient) {
        jsonResponse(['success' => false, 'error' => 'Patient not found'], 404);
    }
    if (strtolower($patient['gender']) !== 'female') {
        jsonResponse(['success' => false, 'error' => 'Pregnancy records can only be added for female patients.'], 422);
    }

    $gestAge = ($data['gestational_age'] ?? '') !== '' ? (int)$data['gestational_age'] : null;
    $lmp     = !empty($data['lmp_date']) ? $data['lmp_date'] : null;
    $edd     = !empty($data['edd_date']) ? $data['edd_date'] : null;
    // Derive the expected delivery date (LMP + 280 days) when it was not given.
    if (!$edd && $lmp) {
        $edd = date('Y-m-d', strtotime($lmp . ' +280 days'));
    }

    // Only one pregnancy is "current" at a time; retire any open record first.
    $db->query("UPDATE patient_pregnancies SET status = 'closed' WHERE patient_id = ? AND status = 'active'", [$patientId]);

    $id = $db->insert('patient_pregnancies', [
        'patient_id'      => $patientId,
        'gestational_age' => $gestAge,
        'lmp_date'        => $lmp,
        'edd_date'        => $edd,
        'status'          => 'active',
        'recorded_by'     => getCurrentUserId(),
    ]);

    logAudit('CREATE', 'patient_pregnancies', $id, null, [
        'patient_id' => $patientId, 'gestational_age' => $gestAge, 'lmp_date' => $lmp, 'edd_date' => $edd,
    ]);

    jsonResponse(['success' => true, 'id' => $id]);
}

// Register a mortality (death) record for a patient, from the system-wide
// Patient Master Folder banner's "Tick if Deceased / Died" action.
function saveMortality() {
    requireRole(['admin', 'records', 'nurse', 'doctor']);
    $data = getPostData();

    $patientId = (int)($data['patient_id'] ?? 0);
    if (!$patientId) {
        jsonResponse(['success' => false, 'error' => 'Patient ID is required'], 400);
    }

    $db = Database::getInstance();
    $patient = $db->fetchOne("SELECT id FROM patient_registrations WHERE id = ?", [$patientId]);
    if (!$patient) {
        jsonResponse(['success' => false, 'error' => 'Patient not found'], 404);
    }

    $deathDateTime = !empty($data['death_datetime']) ? str_replace('T', ' ', $data['death_datetime']) : null;
    $cause         = trim($data['cause_primary'] ?? '');
    $doctor        = trim($data['certifying_doctor'] ?? '');
    if ($cause === '') {
        jsonResponse(['success' => false, 'error' => 'Primary cause of death is required.'], 422);
    }

    $id = $db->insert('patient_deaths', [
        'patient_id'        => $patientId,
        'death_datetime'    => $deathDateTime,
        'cause_primary'     => $cause,
        'certifying_doctor' => $doctor !== '' ? $doctor : null,
        'recorded_by'       => getCurrentUserId(),
    ]);

    logAudit('CREATE', 'patient_deaths', $id, null, [
        'patient_id' => $patientId, 'death_datetime' => $deathDateTime, 'cause_primary' => $cause,
    ]);

    jsonResponse(['success' => true, 'id' => $id]);
}

// Patient identification summary (old EHMS patient bar): demographics + current bed/ward + latest vitals/diagnosis/prescription
function getPatientSummary() {
    $patientId = $_GET['id'] ?? null;
    if (!$patientId) {
        jsonResponse(['error' => 'Patient ID is required'], 400);
    }

    $db = Database::getInstance();

    $patient = $db->fetchOne(
        "SELECT p.*,
                CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) AS full_name,
                TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age,
                s.name AS sponsor_name, s.type AS sponsor_type,
                b.id AS bed_id, b.bed_number,
                r.id AS room_id, r.room_number,
                w.id AS ward_id, w.ward_name, w.ward_code
           FROM patient_registrations p
           LEFT JOIN sponsors s ON s.id = p.sponsor_id
           LEFT JOIN beds b ON b.current_patient_id = p.id
           LEFT JOIN rooms r ON r.id = b.room_id
           LEFT JOIN wards w ON w.id = b.ward_id
          WHERE p.id = ?",
        [(int)$patientId]
    );

    if (!$patient) {
        jsonResponse(['error' => 'Patient not found'], 404);
    }

    // Latest vital signs across the patient's visits
    $vital = $db->fetchOne(
        "SELECT v.* FROM vital_signs v
          JOIN patient_visits pv ON pv.id = v.visit_id
         WHERE pv.patient_id = ?
         ORDER BY v.recorded_at DESC
         LIMIT 1",
        [(int)$patientId]
    );

    // Latest consultation (diagnosis / notes)
    $consultation = $db->fetchOne(
        "SELECT c.* FROM consultations c
          JOIN patient_visits pv ON pv.id = c.visit_id
         WHERE pv.patient_id = ?
         ORDER BY c.consultation_date DESC
         LIMIT 1",
        [(int)$patientId]
    );

    // Latest prescription with drug name
    $rx = $db->fetchOne(
        "SELECT pr.*, pi.drug_name FROM prescriptions pr
          JOIN patient_visits pv ON pv.id = pr.visit_id
          JOIN pharmacy_inventory pi ON pi.id = pr.drug_id
         WHERE pv.patient_id = ?
         ORDER BY pr.prescribed_at DESC
         LIMIT 1",
        [(int)$patientId]
    );

    // Latest admission - drives the discharge-status / finalisation badge.
    // Exposes status (Admitted|Discharged) plus discharge_outcome, which is
    // free text (e.g. Discharged, Died, Absconded) recorded at finalisation.
    $admission = $db->fetchOne(
        "SELECT a.admission_code, a.admission_date, a.status,
                a.discharged_at, a.discharge_outcome
           FROM admissions a
          WHERE a.patient_id = ?
          ORDER BY a.admission_date DESC
          LIMIT 1",
        [(int)$patientId]
    );

    $bedWard = trim(
        ($patient['ward_name'] ? $patient['ward_name'] : '') .
        ($patient['bed_number'] ? ' / Bed ' . $patient['bed_number'] : '')
    );

    // Patient Master Folder additions -----------------------------------------
    // The current open pregnancy (if any) and the mortality record (if any).
    // Existence of a patient_deaths row is what marks a patient as deceased.
    $pregnancy = $db->fetchOne(
        "SELECT id, gestational_age, lmp_date, edd_date, status, created_at
           FROM patient_pregnancies
          WHERE patient_id = ? AND status = 'active'
          ORDER BY created_at DESC
          LIMIT 1",
        [(int)$patientId]
    );

    $death = $db->fetchOne(
        "SELECT id, death_datetime, cause_primary, certifying_doctor, created_at
           FROM patient_deaths
          WHERE patient_id = ?
          ORDER BY created_at DESC
          LIMIT 1",
        [(int)$patientId]
    );

    // Exact age as years/months/days ("71Y 11M 11D") from the date of birth.
    $ageParts = ['years' => null, 'months' => null, 'days' => null];
    if (!empty($patient['date_of_birth'])) {
        try {
            $diff = (new DateTime($patient['date_of_birth']))->diff(new DateTime());
            $ageParts = ['years' => $diff->y, 'months' => $diff->m, 'days' => $diff->d];
        } catch (Exception $e) {
            // leave nulls on an unparseable date
        }
    }

    // NHIS patient when an NHIA number is on file or the sponsor is the NHIA.
    $paymentType = (!empty($patient['nhia_number']) || ($patient['sponsor_type'] ?? '') === 'nhia')
        ? 'NHIS' : 'Self Pay';

    // Adult vs Child (paediatric threshold: under 18).
    $patientType = (($patient['age'] !== null) && (int)$patient['age'] < 18) ? 'Child' : 'Adult';

    jsonResponse([
        'success' => true,
        'patient' => [
            'patient_id'      => (int)$patient['id'],
            'hospital_number' => $patient['hospital_number'],
            'full_name'       => $patient['full_name'],
            'gender'          => $patient['gender'] ? ucfirst($patient['gender']) : '',
            'age'             => $patient['age'],
            'age_parts'       => $ageParts,
            'age_text'        => ($ageParts['years'] !== null)
                                    ? $ageParts['years'] . 'Y ' . $ageParts['months'] . 'M ' . $ageParts['days'] . 'D'
                                    : null,
            'patient_type'    => $patientType,
            'blood_group'     => $patient['blood_group'],
            // Master-folder demographics captured at registration.
            'first_name'      => $patient['first_name'],
            'middle_name'     => $patient['middle_name'],
            'last_name'       => $patient['last_name'],
            'date_of_birth'   => $patient['date_of_birth'],
            'registration_date' => $patient['registration_date'],
            'marital_status'  => $patient['marital_status'],
            'occupation'      => $patient['occupation'],
            // WhatsApp is stored in the generic secondary phone column.
            'whatsapp'        => $patient['secondary_phone'],
            'email'           => $patient['email'],
            'nationality'     => $patient['nationality'],
            'emergency_contact_name'  => $patient['emergency_contact_name'],
            'emergency_contact_phone' => $patient['emergency_contact_phone'],
            'sponsor_name'    => $patient['sponsor_name'],
            'payment_type'    => $paymentType,
            'is_pregnant'     => (bool)$pregnancy,
            'pregnancy'       => $pregnancy ? [
                'id'              => (int)$pregnancy['id'],
                'gestational_age' => $pregnancy['gestational_age'],
                'lmp_date'        => $pregnancy['lmp_date'],
                'edd_date'        => $pregnancy['edd_date'],
                'status'          => $pregnancy['status'],
                'recorded_at'     => $pregnancy['created_at'],
            ] : null,
            'is_deceased'     => (bool)$death,
            'death'           => $death ? [
                'id'                => (int)$death['id'],
                'death_datetime'    => $death['death_datetime'],
                'cause_primary'     => $death['cause_primary'],
                'certifying_doctor' => $death['certifying_doctor'],
                'recorded_at'       => $death['created_at'],
            ] : null,
            'nhia_number'     => $patient['nhia_number'],
            // NHIS card data captured at registration. nhis_active is derived:
            // the patient is on cover when an NHIA number is on file and the
            // card has not expired (a missing expiry is treated as open-ended).
            'claim_check_code'=> $patient['claim_check_code'],
            'nhis_expiry_date'=> $patient['nhis_expiry_date'],
            'nhis_active'     => (bool)($patient['nhia_number']
                                    && (empty($patient['nhis_expiry_date'])
                                        || $patient['nhis_expiry_date'] >= date('Y-m-d'))),
            'is_active'       => (int)$patient['is_active'],
            'phone'           => $patient['phone'],
            'address'         => $patient['address'],
            'bed'             => $patient['bed_number'],
            'room'            => $patient['room_number'],
            'ward'            => $patient['ward_name'],
            'ward_code'       => $patient['ward_code'],
            'bed_ward'        => $bedWard,
            'admission'       => $admission ? [
                'admission_code'    => $admission['admission_code'],
                'admission_date'    => $admission['admission_date'],
                'status'            => $admission['status'],
                'discharged_at'     => $admission['discharged_at'],
                'discharge_outcome' => $admission['discharge_outcome'],
            ] : null,
            'allergies'       => $patient['allergies'] ?: null,
            'vitals'          => $vital ? [
                'blood_pressure'  => ($vital['blood_pressure_systolic'] && $vital['blood_pressure_diastolic'])
                    ? $vital['blood_pressure_systolic'] . '/' . $vital['blood_pressure_diastolic'] : null,
                'pulse'           => $vital['heart_rate'],
                'spo2'            => $vital['oxygen_saturation'],
                'temperature'     => $vital['temperature'],
                'respiratory_rate'=> $vital['respiratory_rate'],
                'notes'           => $vital['notes'],
                'recorded_at'     => $vital['recorded_at']
            ] : null,
            'diagnosis'       => $consultation['diagnosis'] ?? null,
            'consultation_notes' => $consultation['notes'] ?? null,
            'latest_rx'       => $rx ? ($rx['drug_name'] . ($rx['dosage'] ? ' ' . $rx['dosage'] : '')) : null
        ]
    ]);
}

// Read-only clinical timeline for one patient (doctor consultations + clinical
// notes), used by the laboratory workspace's "Clinical Care Entries" and
// "Doctor Entry / Diagnosis" panels. getPatientSummary() above returns only the
// single latest consultation; a technician processing a specimen often needs
// the running history, so both tables are projected into one date-sorted feed.
// Like summary, this is a read projection and needs nothing more than a login.
function getPatientClinicalHistory() {
    $patientId = (int)($_GET['id'] ?? 0);
    if (!$patientId) {
        jsonResponse(['error' => 'Patient ID is required'], 400);
    }

    $db = Database::getInstance();

    // Doctor consultations: structured diagnosis + free-text notes, tied to a visit.
    $consultations = $db->fetchAll(
        "SELECT c.id,
                c.consultation_date AS entry_date,
                c.consultation_type AS entry_kind,
                c.diagnosis,
                c.notes,
                c.status,
                IFNULL(u.full_name, '') AS author,
                v.visit_number,
                v.visit_type,
                v.chief_complaint,
                NULL AS clinical_note
           FROM consultations c
           JOIN patient_visits v ON v.id = c.visit_id
           LEFT JOIN users u ON u.id = c.doctor_id
          WHERE v.patient_id = ?
          ORDER BY c.consultation_date DESC",
        [$patientId]
    );

    // Clinical notes: single free-text note_type entry (OPD / IPD / progress / discharge).
    $notes = $db->fetchAll(
        "SELECT n.id,
                n.created_at AS entry_date,
                n.note_type AS entry_kind,
                NULL AS diagnosis,
                NULL AS notes,
                NULL AS status,
                IFNULL(u.full_name, '') AS author,
                NULL AS visit_number,
                NULL AS visit_type,
                NULL AS chief_complaint,
                n.clinical_note
           FROM clinical_notes n
           LEFT JOIN users u ON u.id = n.doctor_id
          WHERE n.patient_id = ?
          ORDER BY n.created_at DESC",
        [$patientId]
    );

    $entries = array_merge($notes, $consultations);
    usort($entries, function ($a, $b) {
        return strcmp((string)$b['entry_date'], (string)$a['entry_date']);
    });

    jsonResponse(['success' => true, 'entries' => $entries]);
}
?>
