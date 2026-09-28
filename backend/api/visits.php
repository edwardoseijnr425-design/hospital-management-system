<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

requireLogin();

switch ($method) {
    case 'GET':
        if ($action === 'active') {
            getActiveVisits();
        } else {
            getVisits();
        }
        break;
    case 'POST':
        if ($action === 'create') {
            createVisit();
        } else {
            jsonResponse(['error' => 'Invalid action'], 400);
        }
        break;
    case 'PUT':
        updateVisit();
        break;
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

function getVisits() {
    $filters = [
        'patient_id' => $_GET['patient_id'] ?? null,
        'department_id' => $_GET['department_id'] ?? null,
        'status' => $_GET['status'] ?? null,
        'visit_type' => $_GET['visit_type'] ?? null,
        'date_from' => $_GET['date_from'] ?? null,
        'date_to' => $_GET['date_to'] ?? null
    ];
    
    $page = $_GET['page'] ?? 1;
    $perPage = $_GET['per_page'] ?? ITEMS_PER_PAGE;
    $offset = ($page - 1) * $perPage;
    
    $db = Database::getInstance();
    
    $sql = "SELECT v.*, p.hospital_number, CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   d.name as department_name
            FROM patient_visits v
            JOIN patient_registrations p ON v.patient_id = p.id
            JOIN departments d ON v.department_id = d.id
            WHERE 1=1";
    
    $params = [];
    
    if ($filters['patient_id']) {
        $sql .= " AND v.patient_id = ?";
        $params[] = $filters['patient_id'];
    }
    
    if ($filters['department_id']) {
        $sql .= " AND v.department_id = ?";
        $params[] = $filters['department_id'];
    }
    
    if ($filters['status']) {
        $sql .= " AND v.status = ?";
        $params[] = $filters['status'];
    }
    
    if ($filters['visit_type']) {
        $sql .= " AND v.visit_type = ?";
        $params[] = $filters['visit_type'];
    }
    
    if ($filters['date_from']) {
        $sql .= " AND DATE(v.visit_date) >= ?";
        $params[] = $filters['date_from'];
    }
    
    if ($filters['date_to']) {
        $sql .= " AND DATE(v.visit_date) <= ?";
        $params[] = $filters['date_to'];
    }
    
    $sql .= " ORDER BY v.visit_date DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    
    $visits = $db->fetchAll($sql, $params);
    
    jsonResponse([
        'success' => true,
        'visits' => $visits
    ]);
}

function getActiveVisits() {
    $departmentId = $_GET['department_id'] ?? null;
    
    $patientModel = new Patient();
    $visits = $patientModel->getActiveVisits($departmentId);
    
    jsonResponse([
        'success' => true,
        'visits' => $visits
    ]);
}

function createVisit() {
    $data = getPostData();
    
    $requiredFields = ['patient_id', 'visit_type', 'department_id'];
    $errors = validateRequired($data, $requiredFields);
    
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }
    
    $patientModel = new Patient();
    
    try {
        $visitId = $patientModel->createVisit($data['patient_id'], $data);
        $visit = $patientModel->getVisitById($visitId);
        
        jsonResponse([
            'success' => true,
            'visit_id' => $visitId,
            'visit' => $visit
        ]);
    } catch (Exception $e) {
        error_log("Visit creation error: " . $e->getMessage());
        jsonResponse(['error' => 'Visit creation failed'], 500);
    }
}

function updateVisit() {
    $visitId = $_GET['id'] ?? null;
    
    if (!$visitId) {
        jsonResponse(['error' => 'Visit ID is required'], 400);
    }
    
    $data = getPostData();
    $db = Database::getInstance();
    
    try {
        $oldData = $db->fetchOne("SELECT * FROM patient_visits WHERE id = ?", [$visitId]);
        $db->update('patient_visits', $data, 'id = ?', [$visitId]);
        logAudit('UPDATE', 'patient_visits', $visitId, $oldData, $data);
        
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Visit update error: " . $e->getMessage());
        jsonResponse(['error' => 'Visit update failed'], 500);
    }
}
?>
