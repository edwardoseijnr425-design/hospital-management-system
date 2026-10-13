<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

requireLogin();
requireRole(['admin', 'account']);

$type = $_GET['type'] ?? '';
$dateFrom = $_GET['date_from'] ?? null;
$dateTo = $_GET['date_to'] ?? null;

if (!$type) {
    jsonResponse(['error' => 'Report type is required'], 400);
}

// The IPD reports are record-level and are expected to run over the whole
// register, so their date range is optional (blank = all records). The
// aggregate reports are period summaries and still require one.
$optionalRangeTypes = ['ipd_records', 'ward_admission_history', 'discharges'];
if (!in_array($type, $optionalRangeTypes, true) && (!$dateFrom || !$dateTo)) {
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
    // IPD record-level reports. They carry patient/clinical detail, so they are
    // limited to the roles that already own the admissions register
    // (admissions.php); super_admin passes hasRole(). 'account' keeps the
    // aggregate reports but must not see named IPD records.
    case 'ipd_records':
        requireRole(['admin', 'doctor', 'nurse']);
        $result = generateIpdRecordsReport($db, $dateFrom, $dateTo);
        break;
    case 'ward_admission_history':
        requireRole(['admin', 'doctor', 'nurse']);
        $result = generateWardAdmissionHistoryReport($db, $dateFrom, $dateTo);
        break;
    case 'discharges':
        requireRole(['admin', 'doctor', 'nurse']);
        $result = generateDischargesReport($db, $dateFrom, $dateTo);
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
                u.full_name as doctor_name
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
                u.full_name as user_name
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

/* ============================================================================
   IPD RECORD-LEVEL REPORTS
   The three generators below return every matching record (not an aggregate),
   which the reports page renders row-by-row. A blank date range means "all
   history"; when both dates are supplied the relevant date column is filtered.
   ============================================================================ */

// All IPD management records — one row per admission, whatever its status.
function generateIpdRecordsReport($db, $dateFrom, $dateTo) {
    $sql = "SELECT
                a.admission_code AS admission_no,
                CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) AS patient_name,
                p.hospital_number,
                w.ward_name,
                w.ward_type,
                b.bed_number,
                a.admission_date,
                a.admission_type,
                a.admitting_doctor,
                d.name AS department,
                a.diagnosis,
                a.status,
                a.discharged_at,
                a.discharge_outcome,
                a.final_diagnosis,
                a.follow_up_date,
                u.full_name AS admitted_by_name
            FROM admissions a
            JOIN patient_registrations p ON p.id = a.patient_id
            JOIN wards w ON w.id = a.ward_id
            JOIN beds b ON b.id = a.bed_id
            LEFT JOIN departments d ON d.id = a.department_id
            LEFT JOIN users u ON u.id = a.admitted_by
            WHERE 1=1";
    $params = [];
    if ($dateFrom && $dateTo) {
        $sql .= " AND DATE(a.admission_date) BETWEEN ? AND ?";
        $params[] = $dateFrom;
        $params[] = $dateTo;
    }
    $sql .= " ORDER BY a.admission_date DESC, a.id DESC";

    $data = $db->fetchAll($sql, $params);

    $admitted = 0;
    $discharged = 0;
    foreach ($data as $row) {
        if (strcasecmp((string)$row['status'], 'Discharged') === 0) {
            $discharged++;
        } else {
            $admitted++;
        }
    }

    return [
        'success' => true,
        'report_title' => 'IPD Management Records',
        'summary' => [
            'total_ipd_records' => count($data),
            'currently_admitted' => $admitted,
            'discharged' => $discharged,
        ],
        'data' => $data,
    ];
}

// Ward admission history — two sections: admissions recorded against each
// ward, and the ward/bed moves (bed_transfer_history) made during a stay.
function generateWardAdmissionHistoryReport($db, $dateFrom, $dateTo) {
    $hasRange = $dateFrom && $dateTo;

    $sql = "SELECT
                w.ward_name,
                w.ward_type,
                a.admission_code AS admission_no,
                CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) AS patient_name,
                p.hospital_number,
                b.bed_number,
                a.admission_date,
                a.admission_type,
                a.status
            FROM admissions a
            JOIN patient_registrations p ON p.id = a.patient_id
            JOIN wards w ON w.id = a.ward_id
            JOIN beds b ON b.id = a.bed_id
            WHERE 1=1";
    $params = [];
    if ($hasRange) {
        $sql .= " AND DATE(a.admission_date) BETWEEN ? AND ?";
        $params[] = $dateFrom;
        $params[] = $dateTo;
    }
    $sql .= " ORDER BY w.ward_name ASC, a.admission_date DESC";
    $admissions = $db->fetchAll($sql, $params);

    $sql2 = "SELECT
                t.created_at AS moved_at,
                a.admission_code AS admission_no,
                CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) AS patient_name,
                p.hospital_number,
                fw.ward_name AS from_ward,
                fb.bed_number AS from_bed,
                tw.ward_name AS to_ward,
                tb.bed_number AS to_bed,
                t.reason,
                u.full_name AS moved_by_name
            FROM bed_transfer_history t
            JOIN admissions a ON a.id = t.admission_id
            JOIN patient_registrations p ON p.id = t.patient_id
            JOIN wards fw ON fw.id = t.from_ward_id
            JOIN beds fb ON fb.id = t.from_bed_id
            JOIN wards tw ON tw.id = t.to_ward_id
            JOIN beds tb ON tb.id = t.to_bed_id
            LEFT JOIN users u ON u.id = t.moved_by
            WHERE 1=1";
    $params2 = [];
    if ($hasRange) {
        $sql2 .= " AND DATE(t.created_at) BETWEEN ? AND ?";
        $params2[] = $dateFrom;
        $params2[] = $dateTo;
    }
    $sql2 .= " ORDER BY t.created_at DESC";
    $transfers = $db->fetchAll($sql2, $params2);

    $wards = [];
    foreach ($admissions as $row) {
        $wards[$row['ward_name']] = true;
    }

    return [
        'success' => true,
        'report_title' => 'Ward Admission History',
        'summary' => [
            'total_admissions' => count($admissions),
            'total_ward_moves' => count($transfers),
            'wards_involved' => count($wards),
        ],
        'sections' => [
            ['title' => 'Admissions by Ward', 'data' => $admissions],
            ['title' => 'Ward / Bed Transfer History', 'data' => $transfers],
        ],
    ];
}

// Discharges — every discharged admission with stay length and outcome.
function generateDischargesReport($db, $dateFrom, $dateTo) {
    $sql = "SELECT
                a.admission_code AS admission_no,
                CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) AS patient_name,
                p.hospital_number,
                w.ward_name,
                b.bed_number,
                a.admission_date,
                a.discharged_at,
                TIMESTAMPDIFF(DAY, a.admission_date, a.discharged_at) AS length_of_stay_days,
                a.discharge_outcome,
                a.final_diagnosis,
                a.follow_up_date,
                a.discharge_notes
            FROM admissions a
            JOIN patient_registrations p ON p.id = a.patient_id
            JOIN wards w ON w.id = a.ward_id
            JOIN beds b ON b.id = a.bed_id
            WHERE a.status = 'Discharged'";
    $params = [];
    if ($dateFrom && $dateTo) {
        $sql .= " AND DATE(a.discharged_at) BETWEEN ? AND ?";
        $params[] = $dateFrom;
        $params[] = $dateTo;
    }
    $sql .= " ORDER BY a.discharged_at DESC, a.id DESC";

    $data = $db->fetchAll($sql, $params);

    $losSum = 0;
    $losCount = 0;
    $followUps = 0;
    foreach ($data as $row) {
        if ($row['length_of_stay_days'] !== null) {
            $losSum += (int)$row['length_of_stay_days'];
            $losCount++;
        }
        if (!empty($row['follow_up_date'])) {
            $followUps++;
        }
    }

    return [
        'success' => true,
        'report_title' => 'Discharges',
        'summary' => [
            'total_discharges' => count($data),
            'average_length_of_stay_days' => $losCount ? round($losSum / $losCount, 1) : 0,
            'with_follow_up' => $followUps,
        ],
        'data' => $data,
    ];
}
?>
