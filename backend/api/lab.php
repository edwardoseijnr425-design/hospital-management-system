<?php
// EHMS — Laboratory Management API
//   GET  ?action=requests                   -> lab requests (with patient info)
//   GET  ?action=results                    -> finalised results
//   GET  ?action=stats                      -> pending / in-progress / completed
//   POST ?action=request                    -> place a lab request
//   POST ?action=result                     -> record a result for a request
//   PUT  ?action=status&id=N                -> update request status
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'requests';

requireLogin();

if ($method === 'GET' && $action === 'stats') {
    labStats();
} elseif ($method === 'GET' && $action === 'results') {
    labResults();
} elseif ($method === 'GET') {
    labRequests();
} elseif ($method === 'POST' && $action === 'request') {
    createLabRequest();
} elseif ($method === 'POST' && $action === 'result') {
    recordLabResult();
} elseif ($method === 'PUT' && $action === 'status') {
    updateRequestStatus();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

function labRequests() {
    $db = Database::getInstance();
    $sql = "SELECT r.*,
                   CONCAT(TRIM(CONCAT(p.first_name, ' ', IFNULL(p.middle_name, ''))), ' ', p.last_name) AS patient_name,
                   p.hospital_number,
                   v.visit_number,
                   IFNULL(u.full_name, '') AS doctor_name,
                   CASE WHEN lr.id IS NOT NULL THEN lr.status END AS result_status
            FROM lab_requests r
            JOIN patient_visits v ON v.id = r.visit_id
            JOIN patient_registrations p ON p.id = v.patient_id
            LEFT JOIN users u ON u.id = r.doctor_id
            LEFT JOIN lab_results lr ON lr.lab_request_id = r.id
            WHERE 1=1";
    $params = [];

    if (!empty($_GET['status'])) {
        $sql .= " AND r.status = ?";
        $params[] = $_GET['status'];
    }
    if (!empty($_GET['q'])) {
        $sql .= " AND (p.first_name LIKE ? OR p.last_name LIKE ? OR p.hospital_number LIKE ?)";
        $like = '%' . $_GET['q'] . '%';
        $params = array_merge($params, [$like, $like, $like]);
    }
    $sql .= " ORDER BY r.requested_at DESC";
    $rows = $db->fetchAll($sql, $params);
    jsonResponse(['success' => true, 'requests' => $rows]);
}

function labResults() {
    $db = Database::getInstance();
    $sql = "SELECT lr.*, r.test_type, r.urgency,
                   CONCAT(TRIM(CONCAT(p.first_name, ' ', IFNULL(p.middle_name, ''))), ' ', p.last_name) AS patient_name,
                   p.hospital_number,
                   IFNULL(t.full_name, '') AS technician_name,
                   IFNULL(vr.full_name, '') AS verified_name
            FROM lab_results lr
            JOIN lab_requests r ON r.id = lr.lab_request_id
            JOIN patient_visits v ON v.id = r.visit_id
            JOIN patient_registrations p ON p.id = v.patient_id
            LEFT JOIN users t ON t.id = lr.technician_id
            LEFT JOIN users vr ON vr.id = lr.verified_by
            ORDER BY lr.completed_at DESC";
    $rows = $db->fetchAll($sql);
    jsonResponse(['success' => true, 'results' => $rows]);
}

function labStats() {
    $db = Database::getInstance();
    $stats = [
        'pending'     => (int)($db->fetchOne("SELECT COUNT(*) c FROM lab_requests WHERE status = 'pending'")['c'] ?? 0),
        'in_progress' => (int)($db->fetchOne("SELECT COUNT(*) c FROM lab_requests WHERE status = 'in_progress'")['c'] ?? 0),
        'completed'   => (int)($db->fetchOne("SELECT COUNT(*) c FROM lab_results WHERE status = 'final'")['c'] ?? 0),
    ];
    jsonResponse(['success' => true, 'stats' => $stats]);
}

function createLabRequest() {
    $data = getPostData();
    $required = ['visit_id', 'test_type'];
    $errors = validateRequired($data, $required);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }
    $urgency = in_array($data['urgency'] ?? '', ['routine', 'urgent', 'emergency'], true) ? $data['urgency'] : 'routine';

    $db = Database::getInstance();
    try {
        $id = $db->insert('lab_requests', [
            'visit_id'         => (int)$data['visit_id'],
            'doctor_id'        => getCurrentUserId(),
            'test_type'        => trim($data['test_type']),
            'test_description' => trim($data['test_description'] ?? ''),
            'urgency'          => $urgency,
        ]);
        logAudit('CREATE', 'lab_requests', $id, null, ['test_type' => $data['test_type']]);
        jsonResponse(['success' => true, 'request_id' => $id]);
    } catch (Exception $e) {
        error_log("Lab request error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to create lab request'], 500);
    }
}

function recordLabResult() {
    $data = getPostData();
    $required = ['lab_request_id', 'results'];
    $errors = validateRequired($data, $required);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $db = Database::getInstance();
    $req = $db->fetchOne("SELECT * FROM lab_requests WHERE id = ?", [$data['lab_request_id']]);
    if (!$req) {
        jsonResponse(['error' => 'Lab request not found'], 404);
    }

    $status = in_array($data['status'] ?? '', ['draft', 'final', 'amended'], true) ? $data['status'] : 'final';
    $existing = $db->fetchOne("SELECT * FROM lab_results WHERE lab_request_id = ?", [$data['lab_request_id']]);

    try {
        if ($existing) {
            $db->update('lab_results', [
                'results'       => trim($data['results']),
                'normal_range'  => trim($data['normal_range'] ?? ''),
                'interpretation'=> trim($data['interpretation'] ?? ''),
                'status'        => $status,
                'verified_by'   => (($status === 'final') ? getCurrentUserId() : null),
                'verified_at'   => (($status === 'final') ? date('Y-m-d H:i:s') : null),
            ], 'lab_request_id = ?', [$data['lab_request_id']]);
            logAudit('UPDATE', 'lab_results', $existing['id'], $existing, ['status' => $status]);
        } else {
            $db->insert('lab_results', [
                'lab_request_id' => (int)$data['lab_request_id'],
                'technician_id'  => getCurrentUserId(),
                'results'        => trim($data['results']),
                'normal_range'   => trim($data['normal_range'] ?? ''),
                'interpretation' => trim($data['interpretation'] ?? ''),
                'status'         => $status,
                'verified_by'    => (($status === 'final') ? getCurrentUserId() : null),
                'verified_at'    => (($status === 'final') ? date('Y-m-d H:i:s') : null),
            ]);
            logAudit('CREATE', 'lab_results', (int)$data['lab_request_id'], null, ['status' => $status]);
        }
        $db->update('lab_requests', ['status' => ($status === 'final' ? 'completed' : 'in_progress')], 'id = ?', [$data['lab_request_id']]);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Lab result error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to save result'], 500);
    }
}

function updateRequestStatus() {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'Request ID required'], 400);

    $data = getPostData();
    $status = $data['status'] ?? null;
    $valid = ['pending', 'in_progress', 'completed', 'cancelled'];
    if (!in_array($status, $valid, true)) {
        jsonResponse(['error' => 'Invalid status'], 400);
    }

    $db = Database::getInstance();
    $old = $db->fetchOne("SELECT * FROM lab_requests WHERE id = ?", [$id]);
    if (!$old) jsonResponse(['error' => 'Lab request not found'], 404);

    try {
        $db->update('lab_requests', ['status' => $status], 'id = ?', [$id]);
        logAudit('UPDATE', 'lab_requests', $id, $old, ['status' => $status]);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        jsonResponse(['error' => 'Failed to update status'], 500);
    }
}