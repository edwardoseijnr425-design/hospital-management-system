<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

requireLogin();

if ($method === 'GET') {
    if ($action === 'summary') {
        getWardSummary();
    } elseif ($action === 'rooms') {
        getRooms();
    } elseif ($action === 'beds') {
        getBeds();
    } else {
        getWards();
    }
} elseif ($method === 'POST') {
    switch ($action) {
        case 'create':
            requireRole(['admin']);
            createWard();
            break;
        case 'update':
            requireRole(['admin']);
            updateWard();
            break;
        case 'delete':
            requireRole(['admin']);
            deleteWard();
            break;
        case 'create_room':
            requireRole(['admin']);
            createRoom();
            break;
        case 'delete_room':
            requireRole(['admin']);
            deleteRoom();
            break;
        case 'create_bed':
            requireRole(['admin']);
            createBed();
            break;
        case 'delete_bed':
            requireRole(['admin']);
            deleteBed();
            break;
        case 'assign_bed':
            requireRole(['admin', 'nurse', 'doctor']);
            assignBed();
            break;
        case 'release_bed':
            requireRole(['admin', 'nurse', 'doctor']);
            releaseBed();
            break;
        default:
            jsonResponse(['error' => 'Invalid action'], 400);
    }
} else {
    jsonResponse(['error' => 'Method not allowed'], 405);
}

function getWards() {
    $db = Database::getInstance();

    $sql = "SELECT w.*,
                   (SELECT COUNT(*) FROM rooms r WHERE r.ward_id = w.id) AS room_count,
                   (SELECT COUNT(*) FROM beds b WHERE b.ward_id = w.id) AS total_beds,
                   (SELECT COUNT(*) FROM beds b WHERE b.ward_id = w.id AND b.status = 'Available') AS available_beds,
                   (SELECT COUNT(*) FROM beds b WHERE b.ward_id = w.id AND b.status = 'Occupied') AS occupied_beds
            FROM wards w
            WHERE 1=1";
    $params = [];

    if (!empty($_GET['ward_type'])) {
        $sql .= " AND w.ward_type = ?";
        $params[] = $_GET['ward_type'];
    }

    if (!empty($_GET['status'])) {
        $sql .= " AND w.status = ?";
        $params[] = $_GET['status'];
    }

    $sql .= " ORDER BY w.ward_name";

    $wards = $db->fetchAll($sql, $params);

    foreach ($wards as &$ward) {
        $occupied = (int)$ward['occupied_beds'];
        $total = (int)$ward['total_beds'];
        $ward['occupancy_rate'] = $total > 0 ? round(($occupied * 100) / $total, 1) : 0;
    }

    jsonResponse(['success' => true, 'wards' => $wards]);
}

function getWardSummary() {
    $db = Database::getInstance();

    $row = $db->fetchOne("SELECT
        (SELECT COUNT(*) FROM wards) AS total_wards,
        (SELECT COUNT(*) FROM beds) AS total_beds,
        (SELECT COUNT(*) FROM beds WHERE status = 'Available') AS available_beds,
        (SELECT COUNT(*) FROM beds WHERE status = 'Occupied') AS occupied_beds");

    $totalBeds = (int)($row['total_beds'] ?? 0);
    $occupiedBeds = (int)($row['occupied_beds'] ?? 0);

    $row['occupancy_rate'] = $totalBeds > 0 ? round(($occupiedBeds * 100) / $totalBeds, 1) : 0;

    jsonResponse(['success' => true, 'summary' => $row]);
}

function getRooms() {
    $db = Database::getInstance();

    $sql = "SELECT r.*, w.ward_name, w.ward_code
            FROM rooms r
            JOIN wards w ON r.ward_id = w.id
            WHERE 1=1";
    $params = [];

    if (!empty($_GET['ward_id'])) {
        $sql .= " AND r.ward_id = ?";
        $params[] = (int)$_GET['ward_id'];
    }

    $sql .= " ORDER BY w.ward_name, r.room_number";

    jsonResponse(['success' => true, 'rooms' => $db->fetchAll($sql, $params)]);
}

function getBeds() {
    $db = Database::getInstance();

    $sql = "SELECT b.*, w.ward_name, w.ward_code, r.room_number,
                   CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) AS patient_name
            FROM beds b
            JOIN wards w ON b.ward_id = w.id
            LEFT JOIN rooms r ON b.room_id = r.id
            LEFT JOIN patient_registrations p ON b.current_patient_id = p.id
            WHERE 1=1";
    $params = [];

    if (!empty($_GET['ward_id'])) {
        $sql .= " AND b.ward_id = ?";
        $params[] = (int)$_GET['ward_id'];
    }

    if (!empty($_GET['status'])) {
        $sql .= " AND b.status = ?";
        $params[] = $_GET['status'];
    }

    $sql .= " ORDER BY w.ward_name, b.bed_number";

    jsonResponse(['success' => true, 'beds' => $db->fetchAll($sql, $params)]);
}

function createWard() {
    $data = getPostData();

    $errors = validateRequired($data, ['ward_code', 'ward_name', 'ward_type']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $db = Database::getInstance();

    try {
        $wardId = $db->insert('wards', [
            'ward_code'  => trim($data['ward_code']),
            'ward_name'  => trim($data['ward_name']),
            'ward_type'  => $data['ward_type'],
            'floor_level' => trim($data['floor_level'] ?? 'Floor 1'),
            'capacity'   => max((int)($data['capacity'] ?? 10), 1),
            'status'     => $data['status'] ?? 'Active',
        ]);
        logAudit('CREATE', 'wards', $wardId, null, ['ward_code' => $data['ward_code'], 'ward_name' => $data['ward_name']]);
        jsonResponse(['success' => true, 'ward_id' => $wardId, 'message' => 'Ward created and saved successfully!']);
    } catch (Exception $e) {
        error_log("Ward creation error: " . $e->getMessage());
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            jsonResponse(['error' => 'Ward code already exists'], 409);
        }
        jsonResponse(['error' => 'Ward creation failed'], 500);
    }
}

function updateWard() {
    $wardId = $_GET['id'] ?? null;

    if (!$wardId) {
        jsonResponse(['error' => 'Ward ID is required'], 400);
    }

    $data = getPostData();
    $db = Database::getInstance();

    $updates = [];
    if (isset($data['ward_code'])) $updates['ward_code'] = trim($data['ward_code']);
    if (isset($data['ward_name'])) $updates['ward_name'] = trim($data['ward_name']);
    if (isset($data['ward_type'])) $updates['ward_type'] = $data['ward_type'];
    if (isset($data['floor_level'])) $updates['floor_level'] = trim($data['floor_level']);
    if (isset($data['capacity'])) $updates['capacity'] = max((int)$data['capacity'], 1);
    if (isset($data['status'])) $updates['status'] = $data['status'];
    if (isset($data['is_frozen'])) $updates['is_frozen'] = (int)(bool)$data['is_frozen'];

    if (empty($updates)) {
        jsonResponse(['error' => 'Nothing to update'], 400);
    }

    try {
        $oldData = $db->fetchOne("SELECT * FROM wards WHERE id = ?", [$wardId]);
        $db->update('wards', $updates, 'id = ?', [$wardId]);
        logAudit('UPDATE', 'wards', $wardId, $oldData, $updates);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Ward update error: " . $e->getMessage());
        jsonResponse(['error' => 'Ward update failed'], 500);
    }
}

function deleteWard() {
    $wardId = $_GET['id'] ?? null;

    if (!$wardId) {
        jsonResponse(['error' => 'Ward ID is required'], 400);
    }

    $db = Database::getInstance();

    $occupiedBeds = $db->fetchOne(
        "SELECT COUNT(*) as count FROM beds WHERE ward_id = ? AND status = 'Occupied'",
        [$wardId]
    )['count'];

    if ($occupiedBeds > 0) {
        jsonResponse(['error' => 'Cannot delete ward with occupied beds'], 400);
    }

    try {
        $oldData = $db->fetchOne("SELECT * FROM wards WHERE id = ?", [$wardId]);
        // Hard delete; rooms and beds cascade
        $db->delete('wards', 'id = ?', [$wardId]);
        logAudit('DELETE', 'wards', $wardId, $oldData, null);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Ward deletion error: " . $e->getMessage());
        jsonResponse(['error' => 'Ward deletion failed'], 500);
    }
}

function createRoom() {
    $data = getPostData();

    $errors = validateRequired($data, ['ward_id', 'room_number']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $db = Database::getInstance();

    try {
        $roomId = $db->insert('rooms', [
            'ward_id'    => (int)$data['ward_id'],
            'room_number' => trim($data['room_number']),
            'room_type'  => $data['room_type'] ?? 'Standard',
            'status'     => $data['status'] ?? 'Available',
        ]);
        logAudit('CREATE', 'rooms', $roomId, null, ['room_number' => $data['room_number'], 'ward_id' => $data['ward_id']]);
        jsonResponse(['success' => true, 'room_id' => $roomId]);
    } catch (Exception $e) {
        error_log("Room creation error: " . $e->getMessage());
        jsonResponse(['error' => 'Room creation failed'], 500);
    }
}

function deleteRoom() {
    $roomId = $_GET['id'] ?? null;

    if (!$roomId) {
        jsonResponse(['error' => 'Room ID is required'], 400);
    }

    $db = Database::getInstance();

    $beds = $db->fetchOne("SELECT COUNT(*) as count FROM beds WHERE room_id = ?", [$roomId])['count'];
    if ($beds > 0) {
        jsonResponse(['error' => 'Cannot delete room that still has beds'], 400);
    }

    try {
        $oldData = $db->fetchOne("SELECT * FROM rooms WHERE id = ?", [$roomId]);
        $db->delete('rooms', 'id = ?', [$roomId]);
        logAudit('DELETE', 'rooms', $roomId, $oldData, null);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Room deletion error: " . $e->getMessage());
        jsonResponse(['error' => 'Room deletion failed'], 500);
    }
}

function createBed() {
    $data = getPostData();

    $errors = validateRequired($data, ['ward_id', 'bed_number']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $db = Database::getInstance();

    try {
        $bedId = $db->insert('beds', [
            'ward_id'    => (int)$data['ward_id'],
            'room_id'    => !empty($data['room_id']) ? (int)$data['room_id'] : null,
            'bed_number' => trim($data['bed_number']),
            'status'     => $data['status'] ?? 'Available',
        ]);
        logAudit('CREATE', 'beds', $bedId, null, ['bed_number' => $data['bed_number'], 'ward_id' => $data['ward_id']]);
        jsonResponse(['success' => true, 'bed_id' => $bedId]);
    } catch (Exception $e) {
        error_log("Bed creation error: " . $e->getMessage());
        jsonResponse(['error' => 'Bed creation failed'], 500);
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
        jsonResponse(['error' => 'Cannot delete an occupied bed'], 400);
    }

    try {
        $db->delete('beds', 'id = ?', [$bedId]);
        logAudit('DELETE', 'beds', $bedId, $bed, null);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Bed deletion error: " . $e->getMessage());
        jsonResponse(['error' => 'Bed deletion failed'], 500);
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

    // Frozen wards block new bed assignments (existing occupants are unaffected)
    $ward = $db->fetchOne("SELECT is_frozen FROM wards WHERE id = ?", [$bed['ward_id']]);
    if ($ward && (int)$ward['is_frozen'] === 1) {
        jsonResponse(['error' => 'Ward is frozen — new bed assignments are blocked'], 403);
    }

    $patient = $db->fetchOne("SELECT * FROM patient_registrations WHERE id = ?", [(int)$data['patient_id']]);
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

        jsonResponse(['success' => true, 'message' => 'Bed assigned to patient successfully']);
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
?>