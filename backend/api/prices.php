<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

requireLogin();
requireRole(['admin', 'account']);

// Service types whose catalogue entries are names, not numeric ids. Prices for
// these key on service_ref and leave service_id at 0.
// Declared BEFORE the dispatcher: PHP does not hoist const, so a const placed
// after the dispatch block would be undefined when createPrice() runs.
const NAME_KEYED_SERVICE_TYPES = ['radiology', 'lab_test'];

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
                       WHEN 'radiology' THEN sp.service_ref
                       WHEN 'lab_test' THEN sp.service_ref
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
    
    $requiredFields = ['service_type', 'price', 'effective_date'];
    $errors = validateRequired($data, $requiredFields);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $type = $data['service_type'];
    // An exam/test is identified by name (service_ref); everything else by the
    // numeric id of its catalogue row.
    $serviceRef = trim((string)($data['service_ref'] ?? ''));
    $serviceId  = (int)($data['service_id'] ?? 0);
    if (in_array($type, NAME_KEYED_SERVICE_TYPES, true)) {
        if ($serviceRef === '') {
            jsonResponse(['error' => 'Select the service to price'], 400);
        }
        $serviceId = 0;
    } elseif ($serviceId <= 0) {
        jsonResponse(['error' => 'Select the service to price'], 400);
    }

    $priceData = [
        'service_type' => $type,
        'service_id' => $serviceId,
        'service_ref' => $serviceRef === '' ? null : $serviceRef,
        'price' => $data['price'],
        'copay_amount' => $data['copay_amount'] ?? 0.00,
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

    // Allowlist the editable columns so a client cannot rewrite id/created_at.
    $fields = [];
    foreach (['service_type', 'service_id', 'service_ref', 'price', 'copay_amount', 'currency', 'effective_date'] as $key) {
        if (array_key_exists($key, $data)) {
            $fields[$key] = $data[$key];
        }
    }
    if (array_key_exists('is_active', $data)) {
        $fields['is_active'] = (bool)$data['is_active'];
    }

    // Keep the id/name key coherent: name-keyed rows carry the exam in
    // service_ref and a zero service_id.
    if (isset($fields['service_type']) && in_array($fields['service_type'], NAME_KEYED_SERVICE_TYPES, true)) {
        $fields['service_id'] = 0;
        $fields['service_ref'] = trim((string)($fields['service_ref'] ?? '')) ?: null;
    }

    if (empty($fields)) {
        jsonResponse(['error' => 'Nothing to update'], 400);
    }
    
    try {
        $oldData = $db->fetchOne("SELECT * FROM service_prices WHERE id = ?", [$priceId]);
        if (!$oldData) {
            jsonResponse(['error' => 'Price not found'], 404);
        }
        $db->update('service_prices', $fields, 'id = ?', [$priceId]);
        logAudit('UPDATE', 'service_prices', $priceId, $oldData, $fields);
        
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
