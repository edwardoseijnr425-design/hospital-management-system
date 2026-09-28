<?php
// EHMS — Appointments API (Appointment Calendar)
//   GET  ?action=list                       -> appointments (filters: status, from, to)
//   GET  ?action=stats                      -> today / upcoming / completed counts
//   POST ?action=create                     -> schedule appointment
//   PUT  ?id=N                              -> update appointment (fields incl. status)
//   DELETE ?id=N                            -> delete appointment
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

requireLogin();

if ($method === 'GET' && $action === 'stats') {
    appointmentStats();
} elseif ($method === 'GET') {
    listAppointments();
} elseif ($method === 'POST' && $action === 'create') {
    createAppointment();
} elseif ($method === 'PUT') {
    updateAppointment();
} elseif ($method === 'DELETE') {
    deleteAppointment();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

function listAppointments() {
    $db = Database::getInstance();
    $sql = "SELECT a.*,
                   CONCAT(TRIM(CONCAT(p.first_name, ' ', IFNULL(p.middle_name, ''))), ' ', p.last_name) AS patient_name,
                   p.hospital_number,
                   IFNULL(d.name, '') AS department_name,
                   IFNULL(u.full_name, '') AS doctor_name
            FROM appointments a
            JOIN patient_registrations p ON p.id = a.patient_id
            LEFT JOIN departments d ON d.id = a.department_id
            LEFT JOIN users u ON u.id = a.doctor_id
            WHERE 1=1";
    $params = [];

    if (!empty($_GET['status'])) {
        $sql .= " AND a.status = ?";
        $params[] = $_GET['status'];
    }
    if (!empty($_GET['from'])) {
        $sql .= " AND a.appointment_date >= ?";
        $params[] = $_GET['from'];
    }
    if (!empty($_GET['to'])) {
        $sql .= " AND a.appointment_date <= ?";
        $params[] = $_GET['to'];
    }
    if (!empty($_GET['patient_id'])) {
        $sql .= " AND a.patient_id = ?";
        $params[] = (int)$_GET['patient_id'];
    }

    $sql .= " ORDER BY a.appointment_date";
    $appointments = $db->fetchAll($sql, $params);
    jsonResponse(['success' => true, 'appointments' => $appointments]);
}

function appointmentStats() {
    $db = Database::getInstance();
    $stats = [
        'today'     => (int)($db->fetchOne("SELECT COUNT(*) c FROM appointments WHERE DATE(appointment_date) = CURDATE() AND status NOT IN ('cancelled','no_show')")['c'] ?? 0),
        'upcoming'  => (int)($db->fetchOne("SELECT COUNT(*) c FROM appointments WHERE appointment_date >= CURDATE() AND status IN ('scheduled','confirmed')")['c'] ?? 0),
        'completed' => (int)($db->fetchOne("SELECT COUNT(*) c FROM appointments WHERE status = 'completed'")['c'] ?? 0),
        'cancelled' => (int)($db->fetchOne("SELECT COUNT(*) c FROM appointments WHERE status IN ('cancelled','no_show')")['c'] ?? 0),
    ];
    jsonResponse(['success' => true, 'stats' => $stats]);
}

function createAppointment() {
    $data = getPostData();

    $required = ['patient_id', 'appointment_date'];
    $errors = validateRequired($data, $required);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $status = in_array($data['status'] ?? '', ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'], true)
        ? $data['status'] : 'scheduled';

    $validConsultation = ['OPD', 'ENT', 'EYE', 'EMERGENCY', 'GENERAL', 'SPECIALIST', 'FOLLOWUP', 'PEDIATRIC', 'DENTAL'];
    $consultationType = strtoupper(trim($data['consultation_type'] ?? 'OPD'));
    if (!in_array($consultationType, $validConsultation, true)) {
        $consultationType = 'OPD';
    }
    $visitType = ucfirst(strtolower(trim($data['visit_type'] ?? 'New')));
    $visitType = in_array($visitType, ['New', 'Review'], true) ? $visitType : 'New';

    $db = Database::getInstance();
    try {
        $id = $db->insert('appointments', [
            'patient_id'        => (int)$data['patient_id'],
            'appointment_date'  => $data['appointment_date'],
            'department_id'     => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'doctor_id'         => !empty($data['doctor_id']) ? (int)$data['doctor_id'] : null,
            'reason'            => trim($data['reason'] ?? ''),
            'consultation_type' => $consultationType,
            'visit_type'        => $visitType,
            'status'            => $status,
            'created_by'        => getCurrentUserId(),
        ]);
        logAudit('CREATE', 'appointments', $id, null, ['patient_id' => $data['patient_id']]);
        jsonResponse(['success' => true, 'appointment_id' => $id]);
    } catch (Exception $e) {
        error_log("Appointment create error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to schedule appointment'], 500);
    }
}

function updateAppointment() {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        jsonResponse(['error' => 'Appointment ID is required'], 400);
    }

    $data = getPostData();
    $db = Database::getInstance();

    $old = $db->fetchOne("SELECT * FROM appointments WHERE id = ?", [$id]);
    if (!$old) {
        jsonResponse(['error' => 'Appointment not found'], 404);
    }

    $fields = ['patient_id', 'appointment_date', 'department_id', 'doctor_id', 'reason', 'consultation_type', 'visit_type', 'status'];
    $toUpdate = [];
    foreach ($fields as $f) {
        if (array_key_exists($f, $data)) {
            $toUpdate[$f] = is_int($data[$f]) ? $data[$f] : $data[$f];
        }
    }
    if (!empty($toUpdate['status'])) {
        $valid = ['scheduled', 'confirmed', 'completed', 'cancelled', 'no_show'];
        if (!in_array($toUpdate['status'], $valid, true)) {
            jsonResponse(['error' => 'Invalid status'], 400);
        }
    }
    if (array_key_exists('consultation_type', $toUpdate)) {
        $toUpdate['consultation_type'] = strtoupper(trim($toUpdate['consultation_type']));
        $validConsultation = ['OPD', 'ENT', 'EYE', 'EMERGENCY', 'GENERAL', 'SPECIALIST', 'FOLLOWUP', 'PEDIATRIC', 'DENTAL'];
        if (!in_array($toUpdate['consultation_type'], $validConsultation, true)) {
            jsonResponse(['error' => 'Invalid consultation type'], 400);
        }
    }
    if (array_key_exists('visit_type', $toUpdate)) {
        $toUpdate['visit_type'] = ucfirst(strtolower(trim($toUpdate['visit_type'])));
        if (!in_array($toUpdate['visit_type'], ['New', 'Review'], true)) {
            jsonResponse(['error' => 'Invalid visit type'], 400);
        }
    }

    if (empty($toUpdate)) {
        jsonResponse(['success' => true]);
    }

    try {
        $db->update('appointments', $toUpdate, 'id = ?', [$id]);
        logAudit('UPDATE', 'appointments', $id, $old, $toUpdate);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Appointment update error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to update appointment'], 500);
    }
}

function deleteAppointment() {
    $id = $_GET['id'] ?? null;
    if (!$id) {
        jsonResponse(['error' => 'Appointment ID is required'], 400);
    }

    $db = Database::getInstance();
    $old = $db->fetchOne("SELECT * FROM appointments WHERE id = ?", [$id]);
    if (!$old) {
        jsonResponse(['error' => 'Appointment not found'], 404);
    }

    try {
        $db->delete('appointments', 'id = ?', [$id]);
        logAudit('DELETE', 'appointments', $id, $old, null);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Appointment delete error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to delete appointment'], 500);
    }
}