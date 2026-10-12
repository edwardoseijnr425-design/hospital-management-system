<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

requireLogin();

switch ($method) {
    case 'GET':
        if ($action === 'list') {
            listSponsors();
        } else {
            getSponsor();
        }
        break;
    case 'POST':
        if ($action === 'create') {
            createSponsor();
        } else {
            jsonResponse(['error' => 'Invalid action'], 400);
        }
        break;
    case 'PUT':
        updateSponsor();
        break;
    case 'DELETE':
        deleteSponsor();
        break;
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

function listSponsors() {
    $db = Database::getInstance();
    
    $filters = [
        'type' => $_GET['type'] ?? null,
        'is_active' => $_GET['is_active'] ?? null,
        'nhia_status' => $_GET['nhia_status'] ?? null
    ];
    
    $sql = "SELECT * FROM sponsors WHERE 1=1";
    $params = [];
    
    if ($filters['type']) {
        $sql .= " AND type = ?";
        $params[] = $filters['type'];
    }
    
    if ($filters['is_active'] !== null) {
        $sql .= " AND is_active = ?";
        $params[] = $filters['is_active'];
    }
    
    if ($filters['nhia_status']) {
        $sql .= " AND nhia_status = ?";
        $params[] = $filters['nhia_status'];
    }
    
    $sql .= " ORDER BY name";
    
    $sponsors = $db->fetchAll($sql, $params);
    
    jsonResponse([
        'success' => true,
        'sponsors' => $sponsors
    ]);
}

function getSponsor() {
    $sponsorId = $_GET['id'] ?? null;
    
    if (!$sponsorId) {
        jsonResponse(['error' => 'Sponsor ID is required'], 400);
    }
    
    $db = Database::getInstance();
    $sponsor = $db->fetchOne("SELECT * FROM sponsors WHERE id = ?", [$sponsorId]);
    
    if (!$sponsor) {
        jsonResponse(['error' => 'Sponsor not found'], 404);
    }
    
    jsonResponse([
        'success' => true,
        'sponsor' => $sponsor
    ]);
}

/* Columns this API is allowed to write. Whitelisting keeps arbitrary
   client-supplied keys out of the generated SQL and gives one place to
   normalise values. */
function sponsorWriteData($data) {
    $columns  = ['name', 'code', 'type', 'contact_person', 'phone', 'email', 'address', 'is_active', 'nhia_status', 'expiry_date'];
    /* MySQL runs in STRICT_TRANS_TABLES, which rejects '' for the nhia_status
       ENUM and the expiry_date DATE column. The HTML form sends "" for every
       untouched optional input, so trim and convert those to NULL. */
    $nullable = ['contact_person', 'phone', 'email', 'address', 'nhia_status', 'expiry_date'];

    $out = [];
    foreach ($columns as $col) {
        if (!array_key_exists($col, $data)) {
            continue;
        }
        $value = $data[$col];
        if (is_string($value)) {
            $value = trim($value);
            if ($value === '' && in_array($col, $nullable, true)) {
                $value = null;
            }
        }
        $out[$col] = $value;
    }
    return $out;
}

function createSponsor() {
    requireRole(['super_admin', 'admin']);
    
    $data = sponsorWriteData(getPostData());
    
    $requiredFields = ['name', 'code', 'type'];
    $errors = validateRequired($data, $requiredFields);
    
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }
    
    $db = Database::getInstance();
    
    try {
        $sponsorId = $db->insert('sponsors', $data);
        logAudit('CREATE', 'sponsors', $sponsorId, null, $data);
        
        jsonResponse([
            'success' => true,
            'sponsor_id' => $sponsorId
        ]);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? null) === 1062) {
            jsonResponse(['error' => 'A sponsor with that code already exists.'], 409);
        }
        error_log("Sponsor creation error: " . $e->getMessage());
        jsonResponse(['error' => 'Sponsor creation failed'], 500);
    }
}

function updateSponsor() {
    requireRole(['super_admin', 'admin']);
    
    $sponsorId = $_GET['id'] ?? null;
    
    if (!$sponsorId) {
        jsonResponse(['error' => 'Sponsor ID is required'], 400);
    }
    
    $data = sponsorWriteData(getPostData());
    if (empty($data)) {
        jsonResponse(['error' => 'No valid fields supplied'], 400);
    }
    $db = Database::getInstance();
    
    try {
        $oldData = $db->fetchOne("SELECT * FROM sponsors WHERE id = ?", [$sponsorId]);
        $db->update('sponsors', $data, 'id = ?', [$sponsorId]);
        logAudit('UPDATE', 'sponsors', $sponsorId, $oldData, $data);
        
        jsonResponse(['success' => true]);
    } catch (PDOException $e) {
        if (($e->errorInfo[1] ?? null) === 1062) {
            jsonResponse(['error' => 'A sponsor with that code already exists.'], 409);
        }
        error_log("Sponsor update error: " . $e->getMessage());
        jsonResponse(['error' => 'Sponsor update failed'], 500);
    }
}

function deleteSponsor() {
    requireRole(['super_admin']);
    
    $sponsorId = $_GET['id'] ?? null;
    
    if (!$sponsorId) {
        jsonResponse(['error' => 'Sponsor ID is required'], 400);
    }
    
    $db = Database::getInstance();
    
    try {
        $oldData = $db->fetchOne("SELECT * FROM sponsors WHERE id = ?", [$sponsorId]);
        $db->delete('sponsors', 'id = ?', [$sponsorId]);
        logAudit('DELETE', 'sponsors', $sponsorId, $oldData, null);
        
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Sponsor deletion error: " . $e->getMessage());
        jsonResponse(['error' => 'Sponsor deletion failed'], 500);
    }
}
?>
