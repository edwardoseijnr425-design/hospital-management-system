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
        } else {
            getPatients();
        }
        break;
    case 'POST':
        if ($action === 'create') {
            createPatient();
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
    
    $patientModel = new Patient();
    $patients = $patientModel->search($query, $limit);
    
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
                b.id AS bed_id, b.bed_number,
                r.id AS room_id, r.room_number,
                w.id AS ward_id, w.ward_name, w.ward_code
           FROM patient_registrations p
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

    $bedWard = trim(
        ($patient['ward_name'] ? $patient['ward_name'] : '') .
        ($patient['bed_number'] ? ' / Bed ' . $patient['bed_number'] : '')
    );

    jsonResponse([
        'success' => true,
        'patient' => [
            'patient_id'      => (int)$patient['id'],
            'hospital_number' => $patient['hospital_number'],
            'full_name'       => $patient['full_name'],
            'gender'          => $patient['gender'] ? ucfirst($patient['gender']) : '',
            'age'             => $patient['age'],
            'blood_group'     => $patient['blood_group'],
            'phone'           => $patient['phone'],
            'address'         => $patient['address'],
            'bed'             => $patient['bed_number'],
            'room'            => $patient['room_number'],
            'ward'            => $patient['ward_name'],
            'ward_code'       => $patient['ward_code'],
            'bed_ward'        => $bedWard,
            'allergies'       => null, // not captured in the registration schema yet
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
?>
