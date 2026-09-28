<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

requireLogin();

$action = $_GET['action'] ?? '';

if ($action === 'recent') {
    getRecentAuditLogs();
} elseif ($action === 'list') {
    getAuditLogs();
} else {
    jsonResponse(['error' => 'Invalid action'], 400);
}

function getRecentAuditLogs() {
    $limit = $_GET['limit'] ?? 10;
    
    $db = Database::getInstance();
    
    $sql = "SELECT a.*, u.full_name 
            FROM audit_trails a
            JOIN users u ON a.user_id = u.id
            ORDER BY a.created_at DESC
            LIMIT ?";
    
    $logs = $db->fetchAll($sql, [$limit]);
    
    jsonResponse(['success' => true, 'audit_logs' => $logs]);
}

function getAuditLogs() {
    $page = $_GET['page'] ?? 1;
    $perPage = $_GET['per_page'] ?? ITEMS_PER_PAGE;
    $offset = ($page - 1) * $perPage;
    
    $filters = [
        'user_id' => $_GET['user_id'] ?? null,
        'action' => trim($_GET['filter_action'] ?? ''),
        'table_name' => $_GET['table_name'] ?? null,
        'date_from' => $_GET['date_from'] ?? null,
        'date_to' => $_GET['date_to'] ?? null,
        'q' => trim($_GET['q'] ?? '')
    ];
    
    $db = Database::getInstance();
    
    $sql = "SELECT a.*, u.full_name 
            FROM audit_trails a
            JOIN users u ON a.user_id = u.id
            WHERE 1=1";
    
    $params = [];
    
    if ($filters['user_id']) {
        $sql .= " AND a.user_id = ?";
        $params[] = $filters['user_id'];
    }
    
    if ($filters['action']) {
        $sql .= " AND a.action = ?";
        $params[] = $filters['action'];
    }
    
    if ($filters['table_name']) {
        $sql .= " AND a.table_name = ?";
        $params[] = $filters['table_name'];
    }

    if ($filters['q'] !== '') {
        $sql .= " AND (a.action LIKE ? OR a.table_name LIKE ? OR u.full_name LIKE ? OR a.ip_address LIKE ?)";
        $like = '%' . $filters['q'] . '%';
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }
    
    if ($filters['date_from']) {
        $sql .= " AND a.created_at >= ?";
        $params[] = $filters['date_from'];
    }
    
    if ($filters['date_to']) {
        $sql .= " AND a.created_at <= ?";
        $params[] = $filters['date_to'];
    }
    
    $sql .= " ORDER BY a.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $perPage;
    $params[] = $offset;
    
    $logs = $db->fetchAll($sql, $params);
    
    // Get total count
    $countSql = str_replace('SELECT a.*, u.full_name', 'SELECT COUNT(*) AS count', $sql);
    $countSql = preg_replace('/LIMIT \? OFFSET \?$/', '', $countSql);
    $countParams = array_slice($params, 0, -2);
    $total = $db->fetchOne($countSql, $countParams)['count'] ?? 0;
    
    jsonResponse([
        'success' => true,
        'audit_logs' => $logs,
        'total' => $total,
        'page' => $page,
        'per_page' => $perPage
    ]);
}
?>
