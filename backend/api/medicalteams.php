<?php
// EHMS — Medical Teams API (clinical duty teams for appointment assignment)
//   GET ?action=list  -> active medical teams (with linked department)
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

requireLogin();

if ($method === 'GET' && $action === 'list') {
    listMedicalTeams();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

function listMedicalTeams() {
    $db = Database::getInstance();
    $teams = $db->fetchAll(
        "SELECT mt.*, IFNULL(d.name, '') AS department_name
         FROM medical_teams mt
         LEFT JOIN departments d ON d.id = mt.department_id
         WHERE mt.is_active = 1
         ORDER BY mt.name"
    );
    jsonResponse([
        'success' => true,
        'medical_teams' => $teams
    ]);
}