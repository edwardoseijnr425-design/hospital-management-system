<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/lab_tests.php';

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
        // Lab tests are name-keyed, like radiology: there is no lab_tests table,
        // so the test name IS the identifier (returned as `id`). The catalogue
        // is the shared labTestCatalogue() (same list the Doctor Station orders
        // from) unioned with any names that already have a price, so a test that
        // was priced before it entered the catalogue still shows up. Prices key
        // on service_ref for service_type = 'lab_test'.
        $labNames = labTestCatalogue();
        $labRows = $db->fetchAll(
            "SELECT DISTINCT service_ref FROM service_prices
             WHERE service_type = 'lab_test' AND is_active = 1
               AND service_ref IS NOT NULL AND TRIM(service_ref) <> ''"
        );
        foreach ($labRows as $labRow) {
            $labNames[] = $labRow['service_ref'];
        }
        $labNames = array_values(array_unique($labNames));
        sort($labNames);
        foreach ($labNames as $labName) {
            $services[] = [
                'id'   => $labName,
                'name' => $labName,
                'code' => 'LAB',
            ];
        }
        break;
    case 'radiology':
        // Imaging exams are catalogue strings, not table rows, so the exam name
        // itself is the identifier (returned as `id`). Prices key on service_ref.
        foreach (radiologyCatalogueFlat() as $exam) {
            $services[] = [
                'id'   => $exam['exam_type'],
                'name' => $exam['exam_type'],
                'code' => strtoupper($exam['modality']),
            ];
        }
        break;
    default:
        jsonResponse(['error' => 'Invalid service type'], 400);
}

jsonResponse([
    'success' => true,
    'services' => $services
]);
?>
