<?php
// EHMS — Admissions Register API
//   GET                          ?status=ALL|Admitted|Discharged&ward_id=N&q=text  -> admissions list
//   GET  ?action=available_beds&ward_id=N                                          -> available beds for a ward
//   POST ?action=admit                                                             -> admit a patient (creates admission + occupies the bed)
//   POST ?action=discharge&id=N                                                    -> discharge (outcome + final diagnosis,
//                                                                                    optional follow-up date, releases the bed)
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

requireLogin();
requireRole(['admin', 'doctor', 'nurse']);

if ($method === 'GET' && $action === 'available_beds') {
    availableBeds();
} elseif ($method === 'GET') {
    listAdmissions();
} elseif ($method === 'POST' && $action === 'admit') {
    admitPatient();
} elseif ($method === 'POST' && $action === 'discharge') {
    dischargePatient();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

function listAdmissions() {
    $db = Database::getInstance();
    $status = strtoupper($_GET['status'] ?? 'ALL');
    $wardId = $_GET['ward_id'] ?? null;
    $q = trim($_GET['q'] ?? '');

    $sql = "SELECT a.*,
                   CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) AS patient_name,
                   p.hospital_number,
                   w.ward_name,
                   b.bed_number,
                   r.room_type AS bed_type,
                   u.full_name AS admitted_by_name
            FROM admissions a
            JOIN patient_registrations p ON p.id = a.patient_id
            JOIN wards w ON w.id = a.ward_id
            JOIN beds b ON b.id = a.bed_id
            LEFT JOIN rooms r ON r.id = b.room_id
            LEFT JOIN users u ON u.id = a.admitted_by
            WHERE 1=1";
    $params = [];

    if ($status === 'ADMITTED' || $status === 'DISCHARGED') {
        $sql .= " AND a.status = ?";
        $params[] = ucfirst(strtolower($status));
    }
    if (!empty($wardId)) {
        $sql .= " AND a.ward_id = ?";
        $params[] = (int)$wardId;
    }
    if ($q !== '') {
        $like = '%' . $q . '%';
        $sql .= " AND (p.hospital_number LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR a.admission_code LIKE ?)";
        array_push($params, $like, $like, $like, $like);
    }
    $sql .= " ORDER BY a.admission_date DESC, a.id DESC";

    $rows = $db->fetchAll($sql, $params);
    jsonResponse(['success' => true, 'admissions' => $rows]);
}

function availableBeds() {
    $wardId = (int)($_GET['ward_id'] ?? 0);
    if (!$wardId) {
        jsonResponse(['error' => 'Ward ID is required'], 400);
    }

    $db = Database::getInstance();
    $beds = $db->fetchAll(
        "SELECT b.id, b.bed_number, r.room_type AS bed_type
         FROM beds b
         LEFT JOIN rooms r ON r.id = b.room_id
         WHERE b.ward_id = ? AND b.status = 'Available'
         ORDER BY b.bed_number",
        [$wardId]
    );
    jsonResponse(['success' => true, 'beds' => $beds]);
}

function admitPatient() {
    $data = getPostData();
    $errors = validateRequired($data, ['patient_id', 'ward_id', 'bed_id']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $db = Database::getInstance();

    $patient = $db->fetchOne("SELECT id, hospital_number FROM patient_registrations WHERE id = ?", [(int)$data['patient_id']]);
    if (!$patient) {
        jsonResponse(['error' => 'Patient not found'], 404);
    }

    $bed = $db->fetchOne("SELECT * FROM beds WHERE id = ?", [(int)$data['bed_id']]);
    if (!$bed) {
        jsonResponse(['error' => 'Bed not found'], 404);
    }
    if ((int)$bed['ward_id'] !== (int)$data['ward_id']) {
        jsonResponse(['error' => 'Bed does not belong to the selected ward'], 400);
    }
    if (strtolower($bed['status']) !== 'available') {
        jsonResponse(['error' => 'Selected bed is not available'], 409);
    }

    $admissionDate = trim($data['admission_date'] ?? '');
    if ($admissionDate !== '') {
        $admissionDate = str_replace('T', ' ', $admissionDate); // datetime-local -> MySQL format
    } else {
        $admissionDate = date('Y-m-d H:i:s');
    }

    $type = trim($data['admission_type'] ?? 'Routine');
    if (!in_array($type, ['Emergency', 'Routine', 'Elective', 'Transfer', 'Maternity'], true)) {
        $type = 'Routine';
    }

    $codeSuffix = $patient['hospital_number'] ? substr(trim($patient['hospital_number']), -4) : 'P';
    $admissionCode = 'ADM-' . date('YmdHis') . '-' . strtoupper(trim($codeSuffix));

    try {
        // A patient can only occupy one bed at a time — free any other bed they currently hold
        $db->update('beds', ['current_patient_id' => null, 'status' => 'Available'],
            "current_patient_id = ? AND status = 'Occupied'", [(int)$data['patient_id']]);

        // Close any earlier active admission for the same patient (superseded by this one)
        $db->update('admissions', [
            'status'          => 'Discharged',
            'discharged_at'   => date('Y-m-d H:i:s'),
            'discharge_notes' => 'Superseded by new admission ' . $admissionCode,
        ], "patient_id = ? AND status = 'Admitted'", [(int)$data['patient_id']]);

        $fields = [
            'admission_code'   => $admissionCode,
            'patient_id'       => (int)$data['patient_id'],
            'ward_id'          => (int)$data['ward_id'],
            'bed_id'           => (int)$data['bed_id'],
            'admission_date'   => $admissionDate,
            'admission_type'   => $type,
            'admitting_doctor' => trim($data['admitting_doctor'] ?? ''),
            'department_id'    => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'diagnosis'        => trim($data['diagnosis'] ?? ''),
            'referred_by'      => trim($data['referred_by'] ?? ''),
            'notes'            => trim($data['notes'] ?? ''),
            'admitted_by'      => getCurrentUserId(),
            'status'           => 'Admitted',
        ];
        $admissionId = $db->insert('admissions', $fields);

        $db->update('beds', ['current_patient_id' => (int)$data['patient_id'], 'status' => 'Occupied'],
            'id = ?', [(int)$data['bed_id']]);

        logAudit('CREATE', 'admissions', $admissionId, null, [
            'admission_code' => $admissionCode,
            'patient_id'     => (int)$data['patient_id'],
            'bed_id'         => (int)$data['bed_id']
        ]);

        jsonResponse(['success' => true, 'admission_id' => $admissionId, 'admission_code' => $admissionCode]);
    } catch (Exception $e) {
        error_log("Admission create error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to admit patient'], 500);
    }
}

function dischargePatient() {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) {
        jsonResponse(['error' => 'Admission ID required'], 400);
    }

    $data = getPostData();
    $db = Database::getInstance();

    $admission = $db->fetchOne("SELECT * FROM admissions WHERE id = ?", [$id]);
    if (!$admission) {
        jsonResponse(['error' => 'Admission not found'], 404);
    }
    if (strtolower($admission['status']) !== 'admitted') {
        jsonResponse(['error' => 'Admission already discharged'], 400);
    }

    // Clinical discharge summary. The outcome and final diagnosis are recorded
    // on the admission itself; the follow-up date is optional.
    $outcome = trim($data['discharge_outcome'] ?? '');
    $finalDx = trim($data['final_diagnosis'] ?? '');
    $followUp = trim($data['follow_up_date'] ?? '');

    $allowedOutcomes = [
        'Improved', 'Unchanged', 'Referred', 'Absconded',
        'Transferred Out', 'Died', 'Discharged on Medical Advice',
    ];
    if ($outcome === '' || !in_array($outcome, $allowedOutcomes, true)) {
        jsonResponse(['error' => 'Select a valid discharge outcome'], 400);
    }
    if ($finalDx === '') {
        jsonResponse(['error' => 'Final diagnosis is required'], 400);
    }
    if (strlen($finalDx) > 255) {
        jsonResponse(['error' => 'Final diagnosis is too long (max 255 characters)'], 400);
    }
    if ($followUp !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $followUp)) {
        jsonResponse(['error' => 'Follow-up date is not valid'], 400);
    }

    try {
        $db->update('admissions', [
            'status'            => 'Discharged',
            'discharged_at'     => date('Y-m-d H:i:s'),
            'discharge_notes'   => trim($data['discharge_notes'] ?? ''),
            'discharge_outcome' => $outcome,
            'final_diagnosis'   => $finalDx,
            'follow_up_date'    => $followUp !== '' ? $followUp : null,
        ], 'id = ?', [$id]);

        // Release the bed so it can be used again
        if (!empty($admission['bed_id'])) {
            $db->update('beds', ['current_patient_id' => null, 'status' => 'Available'],
                'id = ?', [(int)$admission['bed_id']]);
        }

        logAudit('UPDATE', 'admissions', $id, $admission, [
            'status'            => 'Discharged',
            'discharge_outcome' => $outcome,
            'final_diagnosis'   => $finalDx,
            'follow_up_date'    => $followUp !== '' ? $followUp : null,
        ]);
        jsonResponse(['success' => true, 'message' => 'Patient discharged successfully']);
    } catch (Exception $e) {
        error_log("Discharge error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to discharge patient'], 500);
    }
}