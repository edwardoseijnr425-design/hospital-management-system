<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

requireLogin();

$action = $_GET['action'] ?? '';

if ($action === 'stats') {
    getDashboardStats();
} else {
    jsonResponse(['error' => 'Invalid action'], 400);
}

function getDashboardStats() {
    $db = Database::getInstance();
    
    $stats = [];
    
    // Total patients
    $stats['total_patients'] = $db->fetchOne(
        "SELECT COUNT(*) as count FROM patient_registrations WHERE is_active = 1"
    )['count'] ?? 0;
    
    // Today's visits
    $stats['today_visits'] = $db->fetchOne(
        "SELECT COUNT(*) as count FROM patient_visits WHERE DATE(visit_date) = CURDATE()"
    )['count'] ?? 0;
    
    // Active visits
    $stats['active_visits'] = $db->fetchOne(
        "SELECT COUNT(*) as count FROM patient_visits WHERE status IN ('pending', 'in_progress')"
    )['count'] ?? 0;
    
    // Pending lab requests
    $stats['pending_labs'] = $db->fetchOne(
        "SELECT COUNT(*) as count FROM lab_requests WHERE status = 'pending'"
    )['count'] ?? 0;
    
    // Pending radiology requests
    $stats['pending_radiology'] = $db->fetchOne(
        "SELECT COUNT(*) as count FROM radiology_requests WHERE status = 'pending'"
    )['count'] ?? 0;
    
    // Available beds
    $stats['available_beds'] = $db->fetchOne(
        "SELECT COUNT(*) as count FROM beds WHERE status = 'Available'"
    )['count'] ?? 0;
    
    // Total users
    $stats['total_users'] = $db->fetchOne(
        "SELECT COUNT(*) as count FROM users WHERE is_active = 1"
    )['count'] ?? 0;
    
    jsonResponse(['success' => true, 'stats' => $stats]);
}
?>
