<?php
// EHMS — Administrator (System Overview) API
//   GET  ?action=overview   -> counts, users by role, departments, recent activity
//   GET  ?action=tables     -> database table inventory (name + row count)
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'overview';

requireLogin();

if ($method === 'GET' && $action === 'tables') {
    tableInventory();
} elseif ($method === 'GET') {
    overview();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

function overview() {
    $db = Database::getInstance();

    $counts = [
        'users'         => (int)($db->fetchOne("SELECT COUNT(*) c FROM users")['c'] ?? 0),
        'active_users'  => (int)($db->fetchOne("SELECT COUNT(*) c FROM users WHERE is_active = 1")['c'] ?? 0),
        'departments'   => (int)($db->fetchOne("SELECT COUNT(*) c FROM departments")['c'] ?? 0),
        'wards'         => (int)($db->fetchOne("SELECT COUNT(*) c FROM wards")['c'] ?? 0),
        'beds'          => (int)($db->fetchOne("SELECT COUNT(*) c FROM beds")['c'] ?? 0),
        'patients'      => (int)($db->fetchOne("SELECT COUNT(*) c FROM patient_registrations")['c'] ?? 0),
        'visits'        => (int)($db->fetchOne("SELECT COUNT(*) c FROM patient_visits")['c'] ?? 0),
        'consultations' => (int)($db->fetchOne("SELECT COUNT(*) c FROM consultations")['c'] ?? 0),
        'prescriptions' => (int)($db->fetchOne("SELECT COUNT(*) c FROM prescriptions")['c'] ?? 0),
        'invoices'      => (int)($db->fetchOne("SELECT COUNT(*) c FROM invoices")['c'] ?? 0),
        'messages'      => (int)($db->fetchOne("SELECT COUNT(*) c FROM messages")['c'] ?? 0),
        'appointments'  => (int)($db->fetchOne("SELECT COUNT(*) c FROM appointments")['c'] ?? 0),
        'lab_requests'  => (int)($db->fetchOne("SELECT COUNT(*) c FROM lab_requests")['c'] ?? 0),
        'today_visits'  => (int)($db->fetchOne("SELECT COUNT(*) c FROM patient_visits WHERE DATE(visit_date) = CURDATE()")['c'] ?? 0),
    ];

    $usersByRole = $db->fetchAll(
        "SELECT role, COUNT(*) c FROM users GROUP BY role ORDER BY c DESC"
    );

    $departments = $db->fetchAll(
        "SELECT d.name, d.code, COUNT(u.id) staff_count, IFNULL(h.full_name, '-') AS hod
         FROM departments d
         LEFT JOIN users u ON u.department_id = d.id
         LEFT JOIN users h ON h.id = d.head_of_department
         GROUP BY d.id ORDER BY d.name"
    );

    $recentActivity = $db->fetchAll(
        "SELECT a.action, a.table_name, a.created_at, a.ip_address, IFNULL(u.full_name, 'System') AS user_name
         FROM audit_trails a
         LEFT JOIN users u ON u.id = a.user_id
         ORDER BY a.created_at DESC LIMIT 15"
    );

    jsonResponse([
        'success' => true,
        'counts' => $counts,
        'users_by_role' => $usersByRole,
        'departments' => $departments,
        'recent_activity' => $recentActivity,
    ]);
}

function tableInventory() {
    $db = Database::getInstance();
    $rows = $db->fetchAll(
        "SELECT table_name, table_rows FROM information_schema.tables WHERE table_schema = DATABASE() ORDER BY table_name"
    );
    $tables = [];
    foreach ($rows as $row) {
        $tables[] = [
            'name' => $row['table_name'],
            'rows' => (int)$row['table_rows'],
        ];
    }
    jsonResponse(['success' => true, 'tables' => $tables]);
}