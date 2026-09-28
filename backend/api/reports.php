<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

requireLogin();
requireRole(['admin', 'account']);

$type = $_GET['type'] ?? '';
$dateFrom = $_GET['date_from'] ?? null;
$dateTo = $_GET['date_to'] ?? null;

if (!$type || !$dateFrom || !$dateTo) {
    jsonResponse(['error' => 'Report type and date range are required'], 400);
}

$db = Database::getInstance();
$result = ['success' => false];

switch ($type) {
    case 'patient_registrations':
        $result = generatePatientRegistrationsReport($db, $dateFrom, $dateTo);
        break;
    case 'visits_summary':
        $result = generateVisitsSummaryReport($db, $dateFrom, $dateTo);
        break;
    case 'consultations_summary':
        $result = generateConsultationsSummaryReport($db, $dateFrom, $dateTo);
        break;
    case 'department_stats':
        $result = generateDepartmentStatsReport($db, $dateFrom, $dateTo);
        break;
    case 'revenue_summary':
        $result = generateRevenueSummaryReport($db, $dateFrom, $dateTo);
        break;
    case 'bed_occupancy':
        $result = generateBedOccupancyReport($db, $dateFrom, $dateTo);
        break;
    case 'lab_summary':
        $result = generateLabSummaryReport($db, $dateFrom, $dateTo);
        break;
    case 'audit_log':
        $result = generateAuditLogReport($db, $dateFrom, $dateTo);
        break;
    default:
        jsonResponse(['error' => 'Invalid report type'], 400);
}

echo json_encode($result);

function generatePatientRegistrationsReport($db, $dateFrom, $dateTo) {
    $sql = "SELECT 
                DATE(registration_date) as date,
                COUNT(*) as total_registrations,
                SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END) as male,
                SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END) as female,
                SUM(CASE WHEN sponsor_id IS NOT NULL THEN 1 ELSE 0 END) as insured
            FROM patient_registrations
            WHERE DATE(registration_date) BETWEEN ? AND ?
            GROUP BY DATE(registration_date)
            ORDER BY date";
    
    $data = $db->fetchAll($sql, [$dateFrom, $dateTo]);
    
    $summary = [
        'total_registrations' => array_sum(array_column($data, 'total_registrations')),
        'total_male' => array_sum(array_column($data, 'male')),
        'total_female' => array_sum(array_column($data, 'female')),
        'total_insured' => array_sum(array_column($data, 'insured'))
    ];
    
    return [
        'success' => true,
        'report_title' => 'Patient Registrations Report',
        'summary' => $summary,
        'data' => $data
    ];
}

function generateVisitsSummaryReport($db, $dateFrom, $dateTo) {
    $sql = "SELECT 
                DATE(visit_date) as date,
                visit_type,
                COUNT(*) as total_visits,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                d.name as department
            FROM patient_visits v
            JOIN departments d ON v.department_id = d.id
            WHERE DATE(visit_date) BETWEEN ? AND ?
            GROUP BY DATE(visit_date), visit_type, d.name
            ORDER BY date, visit_type";
    
    $data = $db->fetchAll($sql, [$dateFrom, $dateTo]);
    
    $summary = [
        'total_visits' => array_sum(array_column($data, 'total_visits')),
        'completed_visits' => array_sum(array_column($data, 'completed')),
        'pending_visits' => array_sum(array_column($data, 'pending'))
    ];
    
    return [
        'success' => true,
        'report_title' => 'Visits Summary Report',
        'summary' => $summary,
        'data' => $data
    ];
}

function generateConsultationsSummaryReport($db, $dateFrom, $dateTo) {
    $sql = "SELECT 
                DATE(consultation_date) as date,
                consultation_type,
                COUNT(*) as total_consultations,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                CONCAT(u.first_name, ' ', u.last_name) as doctor_name
            FROM consultations c
            JOIN users u ON c.doctor_id = u.id
            WHERE DATE(consultation_date) BETWEEN ? AND ?
            GROUP BY DATE(consultation_date), consultation_type, u.id
            ORDER BY date, consultation_type";
    
    $data = $db->fetchAll($sql, [$dateFrom, $dateTo]);
    
    $summary = [
        'total_consultations' => array_sum(array_column($data, 'total_consultations')),
        'completed_consultations' => array_sum(array_column($data, 'completed'))
    ];
    
    return [
        'success' => true,
        'report_title' => 'Consultations Summary Report',
        'summary' => $summary,
        'data' => $data
    ];
}

function generateDepartmentStatsReport($db, $dateFrom, $dateTo) {
    $sql = "SELECT 
                d.name as department,
                d.code as department_code,
                COUNT(DISTINCT v.id) as total_visits,
                COUNT(DISTINCT c.id) as total_consultations,
                COUNT(DISTINCT lr.id) as total_lab_requests,
                COUNT(DISTINCT rr.id) as total_radiology_requests
            FROM departments d
            LEFT JOIN patient_visits v ON d.id = v.department_id AND DATE(v.visit_date) BETWEEN ? AND ?
            LEFT JOIN consultations c ON v.id = c.visit_id
            LEFT JOIN lab_requests lr ON v.id = lr.visit_id
            LEFT JOIN radiology_requests rr ON v.id = rr.visit_id
            GROUP BY d.id, d.name, d.code
            ORDER BY total_visits DESC";
    
    $data = $db->fetchAll($sql, [$dateFrom, $dateTo]);
    
    $summary = [
        'total_departments' => count($data),
        'total_visits' => array_sum(array_column($data, 'total_visits')),
        'total_consultations' => array_sum(array_column($data, 'total_consultations'))
    ];
    
    return [
        'success' => true,
        'report_title' => 'Department Statistics Report',
        'summary' => $summary,
        'data' => $data
    ];
}

function generateRevenueSummaryReport($db, $dateFrom, $dateTo) {
    $sql = "SELECT 
                DATE(i.created_at) as date,
                COUNT(*) as total_invoices,
                SUM(i.net_amount) as total_revenue,
                SUM(CASE WHEN i.status = 'paid' THEN i.net_amount ELSE 0 END) as paid_amount,
                SUM(CASE WHEN i.status = 'pending' THEN i.net_amount ELSE 0 END) as pending_amount,
                s.name as sponsor
            FROM invoices i
            LEFT JOIN sponsors s ON i.sponsor_id = s.id
            WHERE DATE(i.created_at) BETWEEN ? AND ?
            GROUP BY DATE(i.created_at), s.id
            ORDER BY date";
    
    $data = $db->fetchAll($sql, [$dateFrom, $dateTo]);
    
    $summary = [
        'total_invoices' => array_sum(array_column($data, 'total_invoices')),
        'total_revenue' => array_sum(array_column($data, 'total_revenue')),
        'paid_amount' => array_sum(array_column($data, 'paid_amount')),
        'pending_amount' => array_sum(array_column($data, 'pending_amount'))
    ];
    
    return [
        'success' => true,
        'report_title' => 'Revenue Summary Report',
        'summary' => $summary,
        'data' => $data
    ];
}

function generateBedOccupancyReport($db, $dateFrom, $dateTo) {
    $sql = "SELECT 
                w.ward_name as ward,
                w.ward_code as ward_code,
                w.capacity,
                COUNT(CASE WHEN b.status = 'Available' THEN 1 END) as available_beds,
                COUNT(CASE WHEN b.status = 'Occupied' THEN 1 END) as occupied_beds,
                ROUND((COUNT(CASE WHEN b.status = 'Occupied' THEN 1 END) * 100.0 / w.capacity), 2) as occupancy_rate
            FROM wards w
            LEFT JOIN beds b ON w.id = b.ward_id
            WHERE w.status = 'Active'
            GROUP BY w.id, w.ward_name, w.ward_code, w.capacity
            ORDER BY occupancy_rate DESC";
    
    $data = $db->fetchAll($sql);
    
    $summary = [
        'total_wards' => count($data),
        'total_capacity' => array_sum(array_column($data, 'capacity')),
        'total_available' => array_sum(array_column($data, 'available_beds')),
        'total_occupied' => array_sum(array_column($data, 'occupied_beds'))
    ];
    
    return [
        'success' => true,
        'report_title' => 'Bed Occupancy Report',
        'summary' => $summary,
        'data' => $data
    ];
}

function generateLabSummaryReport($db, $dateFrom, $dateTo) {
    $sql = "SELECT 
                DATE(requested_at) as date,
                COUNT(*) as total_requests,
                SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN urgency = 'emergency' THEN 1 ELSE 0 END) as emergency
            FROM lab_requests
            WHERE DATE(requested_at) BETWEEN ? AND ?
            GROUP BY DATE(requested_at)
            ORDER BY date";
    
    $data = $db->fetchAll($sql, [$dateFrom, $dateTo]);
    
    $summary = [
        'total_requests' => array_sum(array_column($data, 'total_requests')),
        'completed_requests' => array_sum(array_column($data, 'completed')),
        'pending_requests' => array_sum(array_column($data, 'pending')),
        'emergency_requests' => array_sum(array_column($data, 'emergency'))
    ];
    
    return [
        'success' => true,
        'report_title' => 'Laboratory Summary Report',
        'summary' => $summary,
        'data' => $data
    ];
}

function generateAuditLogReport($db, $dateFrom, $dateTo) {
    $sql = "SELECT 
                DATE(created_at) as date,
                action,
                table_name,
                COUNT(*) as total_actions,
                CONCAT(u.first_name, ' ', u.last_name) as user_name
            FROM audit_trails a
            JOIN users u ON a.user_id = u.id
            WHERE DATE(a.created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at), action, table_name, u.id
            ORDER BY date DESC, action";
    
    $data = $db->fetchAll($sql, [$dateFrom, $dateTo]);
    
    $summary = [
        'total_actions' => array_sum(array_column($data, 'total_actions')),
        'unique_users' => count(array_unique(array_column($data, 'user_name')))
    ];
    
    return [
        'success' => true,
        'report_title' => 'Audit Log Report',
        'summary' => $summary,
        'data' => $data
    ];
}
?>
