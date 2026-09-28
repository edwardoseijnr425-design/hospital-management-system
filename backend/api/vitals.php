<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

requireLogin();
requireRole(['nurse', 'doctor', 'admin']);

if ($method === 'GET') {
    getVitals();
} elseif ($method === 'POST' && $action === 'create') {
    createVitals();
} elseif ($method === 'PUT') {
    updateVitals();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

function getVitals() {
    $filters = [
        'visit_id' => $_GET['visit_id'] ?? null,
        'nurse_id' => $_GET['nurse_id'] ?? null,
        'date_filter' => $_GET['date_filter'] ?? null
    ];
    
    $db = Database::getInstance();
    
    $sql = "SELECT vs.*, v.visit_number, v.patient_id, p.hospital_number,
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   u.full_name as nurse_name
            FROM vital_signs vs
            JOIN patient_visits v ON vs.visit_id = v.id
            JOIN patient_registrations p ON v.patient_id = p.id
            JOIN users u ON vs.nurse_id = u.id
            WHERE 1=1";
    
    $params = [];
    
    if ($filters['visit_id']) {
        $sql .= " AND vs.visit_id = ?";
        $params[] = $filters['visit_id'];
    }
    
    if ($filters['nurse_id']) {
        $sql .= " AND vs.nurse_id = ?";
        $params[] = $filters['nurse_id'];
    }
    
    if ($filters['date_filter']) {
        switch ($filters['date_filter']) {
            case 'today':
                $sql .= " AND DATE(vs.recorded_at) = CURDATE()";
                break;
            case 'week':
                $sql .= " AND vs.recorded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $sql .= " AND vs.recorded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
        }
    }
    
    $sql .= " ORDER BY vs.recorded_at DESC";
    
    $vitals = $db->fetchAll($sql, $params);
    
    jsonResponse([
        'success' => true,
        'vitals' => $vitals
    ]);
}

function createVitals() {
    $data = getPostData();
    
    $requiredFields = ['visit_id'];
    $errors = validateRequired($data, $requiredFields);
    
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }
    
    // Calculate BMI if weight and height are provided
    if (isset($data['weight']) && isset($data['height']) && $data['height'] > 0) {
        $heightInMeters = $data['height'] / 100;
        $data['bmi'] = round($data['weight'] / ($heightInMeters * $heightInMeters), 1);
    }
    
    $vitalsData = [
        'visit_id' => $data['visit_id'],
        'nurse_id' => getCurrentUserId(),
        'temperature' => $data['temperature'] ?? null,
        'blood_pressure_systolic' => $data['blood_pressure_systolic'] ?? null,
        'blood_pressure_diastolic' => $data['blood_pressure_diastolic'] ?? null,
        'heart_rate' => $data['heart_rate'] ?? null,
        'respiratory_rate' => $data['respiratory_rate'] ?? null,
        'oxygen_saturation' => $data['oxygen_saturation'] ?? null,
        'weight' => $data['weight'] ?? null,
        'height' => $data['height'] ?? null,
        'bmi' => $data['bmi'] ?? null,
        'notes' => $data['notes'] ?? null
    ];
    
    $db = Database::getInstance();
    
    try {
        $vitalsId = $db->insert('vital_signs', $vitalsData);
        logAudit('CREATE', 'vital_signs', $vitalsId, null, $vitalsData);
        
        jsonResponse([
            'success' => true,
            'vitals_id' => $vitalsId
        ]);
    } catch (Exception $e) {
        error_log("Vitals creation error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to record vital signs'], 500);
    }
}

function updateVitals() {
    $vitalsId = $_GET['id'] ?? null;
    
    if (!$vitalsId) {
        jsonResponse(['error' => 'Vitals ID is required'], 400);
    }
    
    $data = getPostData();
    $db = Database::getInstance();
    
    try {
        $oldData = $db->fetchOne("SELECT * FROM vital_signs WHERE id = ?", [$vitalsId]);
        
        // Recalculate BMI if weight or height changed
        if (isset($data['weight']) && isset($data['height']) && $data['height'] > 0) {
            $heightInMeters = $data['height'] / 100;
            $data['bmi'] = round($data['weight'] / ($heightInMeters * $heightInMeters), 1);
        }
        
        $db->update('vital_signs', $data, 'id = ?', [$vitalsId]);
        logAudit('UPDATE', 'vital_signs', $vitalsId, $oldData, $data);
        
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Vitals update error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to update vital signs'], 500);
    }
}
?>
