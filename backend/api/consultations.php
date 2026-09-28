<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

requireLogin();
requireRole(['doctor', 'admin']);

if ($method === 'GET') {
    getConsultations();
} elseif ($method === 'POST' && $action === 'create') {
    createConsultation();
} elseif ($method === 'PUT') {
    updateConsultation();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

function getConsultations() {
    $filters = [
        'visit_id' => $_GET['visit_id'] ?? null,
        'doctor_id' => $_GET['doctor_id'] ?? null,
        'status' => $_GET['status'] ?? null,
        'consultation_type' => $_GET['consultation_type'] ?? null,
        'date_from' => $_GET['date_from'] ?? null,
        'date_to' => $_GET['date_to'] ?? null
    ];
    
    $db = Database::getInstance();
    
    $sql = "SELECT c.*, v.visit_number, v.patient_id, p.hospital_number,
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   CONCAT(u.first_name, ' ', u.last_name) as doctor_name
            FROM consultations c
            JOIN patient_visits v ON c.visit_id = v.id
            JOIN patient_registrations p ON v.patient_id = p.id
            JOIN users u ON c.doctor_id = u.id
            WHERE 1=1";
    
    $params = [];
    
    if ($filters['visit_id']) {
        $sql .= " AND c.visit_id = ?";
        $params[] = $filters['visit_id'];
    }
    
    if ($filters['doctor_id']) {
        $sql .= " AND c.doctor_id = ?";
        $params[] = $filters['doctor_id'];
    }
    
    if ($filters['status']) {
        $sql .= " AND c.status = ?";
        $params[] = $filters['status'];
    }
    
    if ($filters['consultation_type']) {
        $sql .= " AND c.consultation_type = ?";
        $params[] = $filters['consultation_type'];
    }
    
    if ($filters['date_from']) {
        $sql .= " AND DATE(c.consultation_date) >= ?";
        $params[] = $filters['date_from'];
    }
    
    if ($filters['date_to']) {
        $sql .= " AND DATE(c.consultation_date) <= ?";
        $params[] = $filters['date_to'];
    }
    
    $sql .= " ORDER BY c.consultation_date DESC";
    
    $consultations = $db->fetchAll($sql, $params);
    
    jsonResponse([
        'success' => true,
        'consultations' => $consultations
    ]);
}

function createConsultation() {
    $data = getPostData();
    
    $requiredFields = ['visit_id', 'consultation_type'];
    $errors = validateRequired($data, $requiredFields);
    
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }
    
    $consultationData = [
        'visit_id' => $data['visit_id'],
        'doctor_id' => getCurrentUserId(),
        'consultation_type' => $data['consultation_type'],
        'diagnosis' => $data['diagnosis'] ?? null,
        'notes' => $data['notes'] ?? null,
        'consultation_date' => date('Y-m-d H:i:s'),
        'status' => 'in_progress'
    ];
    
    $db = Database::getInstance();
    
    try {
        $consultationId = $db->insert('consultations', $consultationData);
        
        // Update visit status
        $db->update('patient_visits', ['status' => 'in_progress'], 'id = ?', [$data['visit_id']]);
        
        logAudit('CREATE', 'consultations', $consultationId, null, $consultationData);
        
        jsonResponse([
            'success' => true,
            'consultation_id' => $consultationId
        ]);
    } catch (Exception $e) {
        error_log("Consultation creation error: " . $e->getMessage());
        jsonResponse(['error' => 'Consultation creation failed'], 500);
    }
}

function updateConsultation() {
    $consultationId = $_GET['id'] ?? null;
    
    if (!$consultationId) {
        jsonResponse(['error' => 'Consultation ID is required'], 400);
    }
    
    $data = getPostData();
    $db = Database::getInstance();
    
    try {
        $oldData = $db->fetchOne("SELECT * FROM consultations WHERE id = ?", [$consultationId]);
        
        $updateData = [];
        
        if (isset($data['diagnosis'])) {
            $updateData['diagnosis'] = $data['diagnosis'];
        }
        
        if (isset($data['notes'])) {
            $updateData['notes'] = $data['notes'];
        }
        
        if (isset($data['status'])) {
            $updateData['status'] = $data['status'];
            
            // If consultation is completed, update visit status
            if ($data['status'] === 'completed') {
                $visitId = $oldData['visit_id'];
                $db->update('patient_visits', ['status' => 'completed'], 'id = ?', [$visitId]);
            }
        }
        
        if (!empty($updateData)) {
            $db->update('consultations', $updateData, 'id = ?', [$consultationId]);
            logAudit('UPDATE', 'consultations', $consultationId, $oldData, $updateData);
        }
        
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Consultation update error: " . $e->getMessage());
        jsonResponse(['error' => 'Consultation update failed'], 500);
    }
}
?>
