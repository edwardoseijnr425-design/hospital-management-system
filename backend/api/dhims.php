<?php
/**
 * DHIMS Report API — monthly aggregated data for the District Health
 * Information Management System dashboard and DHIMS2/NHIA export tools.
 * Read-only aggregations over existing tables (registration, visits,
 * consultations, invoices/sponsors). No fabricated data.
 */
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

requireLogin();
requireRole(['admin', 'account']);

$action = $_GET['action'] ?? '';
$month  = $_GET['month'] ?? date('Y-m');
$deptId = isset($_GET['department_id']) && $_GET['department_id'] !== '' ? (int)$_GET['department_id'] : null;

if ($action !== 'monthly') {
    jsonResponse(['error' => 'Invalid action'], 400);
}
if (!preg_match('/^\d{4}-\d{2}$/', $month)) {
    jsonResponse(['error' => 'Invalid month format (expected YYYY-MM)'], 400);
}

$db = Database::getInstance();

/* Optional department / clinic filter (validated against the departments table). */
$deptClause = '';
$visitParams = [$month];
if ($deptId !== null) {
    $dept = $db->fetchOne("SELECT id, name FROM departments WHERE id = ?", [$deptId]);
    if (!$dept) {
        jsonResponse(['error' => 'Invalid department_id'], 400);
    }
    $deptClause = ' AND department_id = ' . (int)$deptId;
    $visitParams[] = $deptId;
}

/* ---- Patient registrations in the month ---- */
$reg = $db->fetchOne(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(CASE WHEN gender = 'male' THEN 1 ELSE 0 END), 0) AS male,
            COALESCE(SUM(CASE WHEN gender = 'female' THEN 1 ELSE 0 END), 0) AS female,
            COALESCE(SUM(CASE WHEN sponsor_id IS NOT NULL THEN 1 ELSE 0 END), 0) AS insured
       FROM patient_registrations
      WHERE DATE_FORMAT(registration_date, '%Y-%m') = ?",
    [$month]
);

/* ---- Registrations per day (for the monthly trend) ---- */
$regDaily = $db->fetchAll(
    "SELECT DAY(registration_date) AS day_num, COUNT(*) AS total
       FROM patient_registrations
      WHERE DATE_FORMAT(registration_date, '%Y-%m') = ?
      GROUP BY DAY(registration_date)
      ORDER BY day_num",
    [$month]
);

/* ---- Visits by type (OPD / IPD / Emergency) ---- */
$visitsByType = $db->fetchAll(
    "SELECT visit_type, COUNT(*) AS total
       FROM patient_visits
      WHERE DATE_FORMAT(visit_date, '%Y-%m') = ?
      GROUP BY visit_type
      ORDER BY visit_type",
    [$month]
);

/* ---- Visits by status ---- */
$visitsByStatus = $db->fetchAll(
    "SELECT status, COUNT(*) AS total
       FROM patient_visits
      WHERE DATE_FORMAT(visit_date, '%Y-%m') = ?
      GROUP BY status
      ORDER BY total DESC",
    [$month]
);

/* ---- Visits per day (for the monthly trend) ---- */
$visitsDaily = $db->fetchAll(
    "SELECT DAY(visit_date) AS day_num, COUNT(*) AS total
       FROM patient_visits
      WHERE DATE_FORMAT(visit_date, '%Y-%m') = ?
      GROUP BY DAY(visit_date)
      ORDER BY day_num",
    [$month]
);

/* ---- Consultations by type + completed count ---- */
$consByType = $db->fetchAll(
    "SELECT consultation_type,
            COUNT(*) AS total,
            COALESCE(SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END), 0) AS completed
       FROM consultations
      WHERE DATE_FORMAT(consultation_date, '%Y-%m') = ?
      GROUP BY consultation_type
      ORDER BY consultation_type",
    [$month]
);

/* ---- Disease surveillance : top diagnoses of the month ---- */
$diagnoses = $db->fetchAll(
    "SELECT TRIM(diagnosis) AS diagnosis, COUNT(*) AS counts
       FROM consultations
      WHERE DATE_FORMAT(consultation_date, '%Y-%m') = ?
        AND diagnosis IS NOT NULL
        AND TRIM(diagnosis) <> ''
      GROUP BY TRIM(diagnosis)
      ORDER BY counts DESC
      LIMIT 10",
    [$month]
);

/* ---- Revenue summary (invoices) ---- */
$inv = $db->fetchOne(
    "SELECT COUNT(*) AS invoices,
            COALESCE(SUM(net_amount), 0) AS revenue,
            COALESCE(SUM(CASE WHEN status = 'paid' THEN net_amount ELSE 0 END), 0) AS paid,
            COALESCE(SUM(CASE WHEN status IN ('pending', 'partial') THEN net_amount ELSE 0 END), 0) AS outstanding
       FROM invoices
      WHERE DATE_FORMAT(created_at, '%Y-%m') = ?",
    [$month]
);

/* ---- NHIA / sponsor claims of the month ---- */
$sponsorClaims = $db->fetchAll(
    "SELECT COALESCE(s.name, 'Self-Pay') AS sponsor,
            COUNT(i.id) AS invoices,
            COALESCE(SUM(i.net_amount), 0) AS amount
       FROM invoices i
       LEFT JOIN sponsors s ON s.id = i.sponsor_id
      WHERE DATE_FORMAT(i.created_at, '%Y-%m') = ?
      GROUP BY COALESCE(s.name, 'Self-Pay')
      ORDER BY amount DESC",
    [$month]
);

$visitRollup = ['opd' => 0, 'ipd' => 0, 'emergency' => 0];
foreach ($visitsByType as $v) {
    if (isset($visitRollup[$v['visit_type']])) {
        $visitRollup[$v['visit_type']] = (int)$v['total'];
    }
}
$visitTotal = array_sum($visitRollup);
foreach ($visitsByType as $v) {
    if (!isset($visitRollup[$v['visit_type']])) {
        $visitTotal += (int)$v['total'];
    }
}

$consRollup = ['new' => ['total' => 0, 'completed' => 0], 'follow_up' => ['total' => 0, 'completed' => 0], 'emergency' => ['total' => 0, 'completed' => 0]];
foreach ($consByType as $c) {
    if (isset($consRollup[$c['consultation_type']])) {
        $consRollup[$c['consultation_type']]['total'] = (int)$c['total'];
        $consRollup[$c['consultation_type']]['completed'] = (int)$c['completed'];
    }
}
$consTotal = array_sum(array_map(function ($r) { return $r['total']; }, $consRollup));
$consCompleted = array_sum(array_map(function ($r) { return $r['completed']; }, $consRollup));

$monthLabel = date('F Y', strtotime($month . '-01'));

jsonResponse([
    'success' => true,
    'month'   => $month,
    'month_label' => $monthLabel,
    'summary' => [
        'registrations'        => (int)($reg['total'] ?? 0),
        'registrations_male'   => (int)($reg['male'] ?? 0),
        'registrations_female' => (int)($reg['female'] ?? 0),
        'registrations_insured'=> (int)($reg['insured'] ?? 0),
        'visits'               => $visitTotal,
        'visits_opd'           => $visitRollup['opd'],
        'visits_ipd'           => $visitRollup['ipd'],
        'visits_emergency'     => $visitRollup['emergency'],
        'consultations'        => $consTotal,
        'consultations_completed' => $consCompleted,
        'invoices'             => (int)($inv['invoices'] ?? 0),
        'revenue'              => (float)($inv['revenue'] ?? 0),
        'revenue_paid'         => (float)($inv['paid'] ?? 0),
        'revenue_outstanding'  => (float)($inv['outstanding'] ?? 0)
    ],
    'visits_by_type'   => $visitRollup,
    'visits_by_status' => $visitsByStatus,
    'consultations_by_type' => $consRollup,
    'registrations_daily' => $regDaily,
    'visits_daily'     => $visitsDaily,
    'diagnoses'        => $diagnoses,
    'sponsor_claims'   => $sponsorClaims
]);