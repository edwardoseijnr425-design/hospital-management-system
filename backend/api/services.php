<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

requireLogin();

$type = $_GET['type'] ?? '';

if (!$type) {
    jsonResponse(['error' => 'Service type is required'], 400);
}

$db = Database::getInstance();
$services = [];

switch ($type) {
    case 'consultation':
        $services = $db->fetchAll("SELECT id, name, code FROM consultation_services WHERE is_active = 1 ORDER BY name");
        break;
    case 'procedure':
        $services = $db->fetchAll("SELECT id, name, code FROM procedures WHERE is_active = 1 ORDER BY name");
        break;
    case 'drug':
        $services = $db->fetchAll("SELECT id, drug_name as name, drug_code as code FROM pharmacy_inventory WHERE is_active = 1 ORDER BY drug_name");
        break;
    case 'lab_test':
        // For lab tests, we might need to create a separate table or use a generic approach
        $services = [];
        break;
    case 'radiology':
        // For radiology, similar to lab tests
        $services = [];
        break;
    default:
        jsonResponse(['error' => 'Invalid service type'], 400);
}

jsonResponse([
    'success' => true,
    'services' => $services
]);
?>
