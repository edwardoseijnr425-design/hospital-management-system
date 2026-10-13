<?php
// Flat, display-ready patient search.
//
// Used by the laboratory workspace sidebar (frontend/lab_management.php) so a
// technician can find a patient across any of the eight registration fields
// without knowing which one to pick. Every supplied field is OR'd together and
// the row shape is pre-joined for direct rendering (patient_number,
// patient_name, nhis_number, gender, age, mobile, area).
//
// This is a read-only projection of patient_registrations — the underlying
// records are still owned by backend/api/patients.php.
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');
requireLogin();

$name    = trim($_GET['name'] ?? '');
$no      = trim($_GET['patient_no'] ?? '');
$nhis    = trim($_GET['nhis_no'] ?? '');
$area    = trim($_GET['area'] ?? '');
$mobile  = trim($_GET['mobile'] ?? '');
$cName   = trim($_GET['contact_name'] ?? '');
$cNo     = trim($_GET['contact_no'] ?? '');
$cEmail  = trim($_GET['contact_email'] ?? '');

$conds  = [];
$params = [];

if ($name !== '') {
    $conds[]  = "CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) LIKE ?";
    $params[] = '%' . $name . '%';
}
if ($no !== '') {
    $conds[]  = "p.hospital_number LIKE ?";
    $params[] = '%' . $no . '%';
}
if ($nhis !== '') {
    $conds[]  = "p.nhia_number LIKE ?";
    $params[] = '%' . $nhis . '%';
}
if ($area !== '') {
    $conds[]  = "p.address LIKE ?";
    $params[] = '%' . $area . '%';
}
if ($mobile !== '') {
    // Mobile can be stored in either phone column.
    $conds[]  = "(p.phone LIKE ? OR p.secondary_phone LIKE ?)";
    $params[] = '%' . $mobile . '%';
    $params[] = '%' . $mobile . '%';
}
if ($cName !== '') {
    $conds[]  = "p.emergency_contact_name LIKE ?";
    $params[] = '%' . $cName . '%';
}
if ($cNo !== '') {
    $conds[]  = "p.emergency_contact_phone LIKE ?";
    $params[] = '%' . $cNo . '%';
}
if ($cEmail !== '') {
    $conds[]  = "p.email LIKE ?";
    $params[] = '%' . $cEmail . '%';
}

// Nothing typed yet — return an empty set rather than the whole register.
if (empty($conds)) {
    jsonResponse(['success' => true, 'patients' => []]);
}

$sql = "SELECT p.id,
               p.hospital_number AS patient_number,
               CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) AS patient_name,
               p.nhia_number AS nhis_number,
               p.gender,
               TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age,
               COALESCE(p.phone, p.secondary_phone) AS mobile,
               p.address AS area
        FROM patient_registrations p
        WHERE p.is_active = 1
          AND (" . implode(' OR ', $conds) . ")
        ORDER BY p.first_name ASC, p.last_name ASC
        LIMIT 10";

$rows = Database::getInstance()->fetchAll($sql, $params);

jsonResponse(['success' => true, 'patients' => $rows]);
