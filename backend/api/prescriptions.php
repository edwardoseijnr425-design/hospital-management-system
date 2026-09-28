<?php
// Prescriptions API — read-only, used by the Clinical Patient Care & Treatment Sheet card.
//   GET ?patient_id=N             -> prescriptions for a patient (across all visits)
//   GET ?visit_id=N [&status=X]   -> prescriptions for one visit
//   GET ?id=N                     -> one prescription
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    jsonResponse(['error' => 'Invalid request'], 400);
}

requireLogin();
requireRole(['nurse', 'doctor', 'admin']);

$db = Database::getInstance();

$sql = "SELECT pr.*,
               pi.drug_name,
               pi.generic_name,
               pi.unit,
               u.full_name AS doctor_name,
               v.visit_number,
               p.hospital_number
        FROM prescriptions pr
        JOIN pharmacy_inventory pi ON pi.id = pr.drug_id
        JOIN users u ON u.id = pr.doctor_id
        JOIN patient_visits v ON v.id = pr.visit_id
        JOIN patient_registrations p ON p.id = v.patient_id
        WHERE 1=1";
$params = [];

if (!empty($_GET['id'])) {
    $sql .= " AND pr.id = ?";
    $params[] = (int)$_GET['id'];
} elseif (!empty($_GET['visit_id'])) {
    $sql .= " AND pr.visit_id = ?";
    $params[] = (int)$_GET['visit_id'];
} elseif (!empty($_GET['patient_id'])) {
    $sql .= " AND v.patient_id = ?";
    $params[] = (int)$_GET['patient_id'];
} else {
    jsonResponse(['error' => 'Provide patient_id, visit_id or id'], 400);
}

if (!empty($_GET['status']) && in_array($_GET['status'], ['pending', 'dispensed', 'cancelled'], true)) {
    $sql .= " AND pr.status = ?";
    $params[] = $_GET['status'];
}

$sql .= " ORDER BY pr.prescribed_at DESC";

$rows = $db->fetchAll($sql, $params);
jsonResponse(['success' => true, 'prescriptions' => $rows]);