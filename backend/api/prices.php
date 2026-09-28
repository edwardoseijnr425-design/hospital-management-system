<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

requireLogin();
requireRole(['admin', 'account']);

if ($method === 'GET') {
    getPrices();
} elseif ($method === 'POST' && $action === 'create') {
    createPrice();
} elseif ($method === 'PUT') {
    updatePrice();
} elseif ($method === 'DELETE') {
    deletePrice();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

function getPrices() {
    $filters = [
        'service_type' => $_GET['service_type'] ?? null,
        'is_active' => $_GET['is_active'] ?? null
    ];
    
    $db = Database::getInstance();
    
    $sql = "SELECT sp.*, 
                   CASE sp.service_type
                       WHEN 'consultation' THEN (SELECT name FROM consultation_services WHERE id = sp.service_id)
                       WHEN 'procedure' THEN (SELECT name FROM procedures WHERE id = sp.service_id)
                       WHEN 'drug' THEN (SELECT drug_name FROM pharmacy_inventory WHERE id = sp.service_id)
                       ELSE 'Unknown'
                   END as service_name
            FROM service_prices sp
            WHERE 1=1";
    
    $params = [];
    
    if ($filters['service_type']) {
        $sql .= " AND sp.service_type = ?";
        $params[] = $filters['service_type'];
    }
    
    if ($filters['is_active'] !== null) {
        $sql .= " AND sp.is_active = ?";
        $params[] = $filters['is_active'];
    }
    
    $sql .= " ORDER BY sp.effective_date DESC, sp.service_type";
    
    $prices = $db->fetchAll($sql, $params);
    
    jsonResponse([
        'success' => true,
        'prices' => $prices
    ]);
}

function createPrice() {
    $data = getPostData();
    
    $requiredFields = ['service_type', 'service_id', 'price', 'effective_date'];
    $errors = validateRequired($data, $requiredFields);
    
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }
    
    $priceData = [
        'service_type' => $data['service_type'],
        'service_id' => $data['service_id'],
        'price' => $data['price'],
        'currency' => $data['currency'] ?? 'GHS',
        'effective_date' => $data['effective_date'],
        'is_active' => true
    ];
    
    $db = Database::getInstance();
    
    try {
        $priceId = $db->insert('service_prices', $priceData);
        logAudit('CREATE', 'service_prices', $priceId, null, $priceData);
        
        jsonResponse([
            'success' => true,
            'price_id' => $priceId
        ]);
    } catch (Exception $e) {
        error_log("Price creation error: " . $e->getMessage());
        jsonResponse(['error' => 'Price creation failed'], 500);
    }
}

function updatePrice() {
    $priceId = $_GET['id'] ?? null;
    
    if (!$priceId) {
        jsonResponse(['error' => 'Price ID is required'], 400);
    }
    
    $data = getPostData();
    $db = Database::getInstance();
    
    try {
        $oldData = $db->fetchOne("SELECT * FROM service_prices WHERE id = ?", [$priceId]);
        $db->update('service_prices', $data, 'id = ?', [$priceId]);
        logAudit('UPDATE', 'service_prices', $priceId, $oldData, $data);
        
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Price update error: " . $e->getMessage());
        jsonResponse(['error' => 'Price update failed'], 500);
    }
}

function deletePrice() {
    requireRole(['admin']);
    
    $priceId = $_GET['id'] ?? null;
    
    if (!$priceId) {
        jsonResponse(['error' => 'Price ID is required'], 400);
    }
    
    $db = Database::getInstance();
    
    try {
        $oldData = $db->fetchOne("SELECT * FROM service_prices WHERE id = ?", [$priceId]);
        
        // Soft delete
        $db->update('service_prices', ['is_active' => false], 'id = ?', [$priceId]);
        
        logAudit('UPDATE', 'service_prices', $priceId, $oldData, ['is_active' => false]);
        
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Price deletion error: " . $e->getMessage());
        jsonResponse(['error' => 'Price deletion failed'], 500);
    }
}
?>
