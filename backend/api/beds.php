<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

requireLogin();
requireRole(['admin', 'doctor', 'nurse']);

if ($method === 'GET') {
    getBeds();
} elseif ($method === 'POST' && $action === 'create') {
    requireRole(['admin']);
    createBed();
} elseif ($method === 'POST' && $action === 'assign') {
    assignBed();
} elseif ($method === 'POST' && $action === 'release') {
    releaseBed();
} elseif ($method === 'PUT') {
    requireRole(['admin']);
    updateBed();
} elseif ($method === 'DELETE') {
    requireRole(['admin']);
    deleteBed();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

function getBeds() {
    $filters = [
        'ward_id' => $_GET['ward_id'] ?? null,
        'status'  => $_GET['status'] ?? null,
        'q'       => $_GET['q'] ?? null
    ];

    $db = Database::getInstance();

    $sql = "SELECT b.*, w.ward_name, w.ward_code, r.room_number, r.room_type,
                   p.hospital_number,
                   CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) as patient_name
            FROM beds b
            JOIN wards w ON b.ward_id = w.id
            LEFT JOIN rooms r ON b.room_id = r.id
            LEFT JOIN patient_registrations p ON b.current_patient_id = p.id
            WHERE 1=1";

    $params = [];

    if ($filters['ward_id']) {
        $sql .= " AND b.ward_id = ?";
        $params[] = $filters['ward_id'];
    }

    if ($filters['status']) {
        $sql .= " AND b.status = ?";
        $params[] = $filters['status'];
    }

    if ($filters['q']) {
        $sql .= " AND (CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) LIKE ?
                      OR p.hospital_number LIKE ?
                      OR b.bed_number LIKE ?)";
        $like = '%' . $filters['q'] . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    $sql .= " ORDER BY w.ward_name, b.bed_number";

    $beds = $db->fetchAll($sql, $params);

    jsonResponse([
        'success' => true,
        'beds' => $beds
    ]);
}

function createBed() {
    $data = getPostData();

    $errors = validateRequired($data, ['ward_id', 'bed_number']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $bedData = [
        'ward_id'    => (int)$data['ward_id'],
        'room_id'    => !empty($data['room_id']) ? (int)$data['room_id'] : null,
        'bed_number' => trim($data['bed_number']),
        'status'     => $data['status'] ?? 'Available',
    ];

    $db = Database::getInstance();

    try {
        $bedId = $db->insert('beds', $bedData);
        logAudit('CREATE', 'beds', $bedId, null, $bedData);
        jsonResponse(['success' => true, 'bed_id' => $bedId]);
    } catch (Exception $e) {
        error_log("Bed creation error: " . $e->getMessage());
        jsonResponse(['error' => 'Bed creation failed'], 500);
    }
}

function assignBed() {
    $data = getPostData();

    $errors = validateRequired($data, ['bed_id', 'patient_id']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $db = Database::getInstance();

    $bed = $db->fetchOne("SELECT * FROM beds WHERE id = ?", [(int)$data['bed_id']]);
    if (!$bed) {
        jsonResponse(['error' => 'Bed not found'], 404);
    }

    $patient = $db->fetchOne("SELECT id FROM patient_registrations WHERE id = ?", [(int)$data['patient_id']]);
    if (!$patient) {
        jsonResponse(['error' => 'Patient not found'], 404);
    }

    try {
        // Free any other bed currently assigned to this patient
        $db->update('beds', ['current_patient_id' => null, 'status' => 'Available'],
            "current_patient_id = ? AND status = 'Occupied'", [(int)$data['patient_id']]);

        $db->update('beds', ['current_patient_id' => (int)$data['patient_id'], 'status' => 'Occupied'],
            'id = ?', [(int)$data['bed_id']]);

        logAudit('CREATE', 'bed_assignments', (int)$data['bed_id'], null,
            ['bed_id' => (int)$data['bed_id'], 'patient_id' => (int)$data['patient_id']]);

        jsonResponse(['success' => true, 'message' => 'Bed assigned successfully']);
    } catch (Exception $e) {
        error_log("Bed assignment error: " . $e->getMessage());
        jsonResponse(['error' => 'Bed assignment failed'], 500);
    }
}

function releaseBed() {
    $data = getPostData();

    $errors = validateRequired($data, ['bed_id']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $db = Database::getInstance();

    $bed = $db->fetchOne("SELECT * FROM beds WHERE id = ?", [(int)$data['bed_id']]);
    if (!$bed) {
        jsonResponse(['error' => 'Bed not found'], 404);
    }

    try {
        $db->update('beds', ['current_patient_id' => null, 'status' => 'Available'], 'id = ?', [(int)$data['bed_id']]);
        logAudit('UPDATE', 'beds', (int)$data['bed_id'], $bed, ['status' => 'Available', 'current_patient_id' => null]);
        jsonResponse(['success' => true, 'message' => 'Bed released successfully']);
    } catch (Exception $e) {
        error_log("Bed release error: " . $e->getMessage());
        jsonResponse(['error' => 'Bed release failed'], 500);
    }
}

function updateBed() {
    $bedId = $_GET['id'] ?? null;

    if (!$bedId) {
        jsonResponse(['error' => 'Bed ID is required'], 400);
    }

    $data = getPostData();
    $db = Database::getInstance();

    try {
        $oldData = $db->fetchOne("SELECT * FROM beds WHERE id = ?", [$bedId]);
        $db->update('beds', $data, 'id = ?', [$bedId]);
        logAudit('UPDATE', 'beds', $bedId, $oldData, $data);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Bed update error: " . $e->getMessage());
        jsonResponse(['error' => 'Bed update failed'], 500);
    }
}

function deleteBed() {
    $bedId = $_GET['id'] ?? null;

    if (!$bedId) {
        jsonResponse(['error' => 'Bed ID is required'], 400);
    }

    $db = Database::getInstance();

    $bed = $db->fetchOne("SELECT * FROM beds WHERE id = ?", [$bedId]);

    if (!$bed) {
        jsonResponse(['error' => 'Bed not found'], 404);
    }

    if ($bed['status'] === 'Occupied') {
        jsonResponse(['error' => 'Cannot delete occupied bed'], 400);
    }

    try {
        $oldData = $bed;
        $db->delete('beds', 'id = ?', [$bedId]);
        logAudit('DELETE', 'beds', $bedId, $oldData, null);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Bed deletion error: " . $e->getMessage());
        jsonResponse(['error' => 'Bed deletion failed'], 500);
    }
}
?>