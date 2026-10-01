<?php
// EHMS — Radiology / Imaging Management API
// Mirrors lab.php, extended with the two things radiology worklists actually
// have and lab does not: a SCHEDULE (radiology_requests.scheduled_at) and a
// findings/impression report split rather than a single results blob.
//
//   GET  ?action=requests             -> imaging requests (with patient info)
//   GET  ?action=request&id=N         -> one request joined to its report
//   GET  ?action=results              -> recorded reports
//   GET  ?action=stats                -> pending / scheduled / in progress / reported / verified
//   GET  ?action=catalogue            -> modality catalogue (single source of truth)
//   POST ?action=request              -> order an imaging study
//   POST ?action=result               -> record / amend a report for a request
//   PUT  ?action=status&id=N          -> update request status (+ optional scheduled_at)
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'requests';

requireLogin();

if ($method === 'GET' && $action === 'stats') {
    radiologyStats();
} elseif ($method === 'GET' && $action === 'results') {
    radiologyResults();
} elseif ($method === 'GET' && $action === 'request') {
    radiologyRequestOne();
} elseif ($method === 'GET' && $action === 'catalogue') {
    radiologyCatalogue();
} elseif ($method === 'GET') {
    radiologyRequests();
} elseif ($method === 'POST' && $action === 'request') {
    createRadiologyRequest();
} elseif ($method === 'POST' && $action === 'result') {
    recordRadiologyResult();
} elseif ($method === 'PUT' && $action === 'status') {
    updateRequestStatus();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

// radiology_requests.status — 'scheduled' sits between pending and in_progress.
const RAD_REQUEST_STATUSES = ['pending', 'scheduled', 'in_progress', 'completed', 'cancelled'];
const RAD_URGENCIES        = ['routine', 'urgent', 'emergency'];
const RAD_RESULT_STATUSES  = ['draft', 'final', 'amended'];

/**
 * Modality catalogue: single source of truth for both the order form's
 * modality -> study dropdown and the SCHEDULING SUGGESTION. Kept here so the
 * API can expose it; the page renders its own copy for the dropdown but the
 * suggestion is generated server-side from this list.
 */
function radiologyCatalogue() {
    $cat = [
        'xray' => [
            ['Chest X-Ray (PA & Lateral)', 'Chest'],
            ['Chest X-Ray (Single View)', 'Chest'],
            ['Abdominal X-Ray (Plain)', 'Abdomen'],
            ['Pelvic X-Ray', 'Pelvis'],
            ['X-Ray of the Skull', 'Head'],
            ['Spine X-Ray (Lumbar)', 'Lumbar Spine'],
            ['Extremity X-Ray', 'Extremity'],
        ],
        'ultrasound' => [
            ['Abdominal Ultrasound', 'Abdomen'],
            ['Obstetric Ultrasound', 'Abdomen / Pelvis'],
            ['Pelvic Ultrasound', 'Pelvis'],
            ['Thyroid Ultrasound', 'Neck'],
            ['Breast Ultrasound', 'Breast'],
            ['Renal Ultrasound (KUB)', 'Abdomen'],
        ],
        'ct' => [
            ['CT Head (Brain)', 'Head'],
            ['CT Chest (High Resolution)', 'Chest'],
            ['CT Abdomen & Pelvis (Contrast)', 'Abdomen / Pelvis'],
            ['CT Spine (Lumbar / Cervical)', 'Spine'],
        ],
        'mri' => [
            ['MRI Brain', 'Head'],
            ['MRI Lumbar Spine', 'Lumbar Spine'],
            ['MRI Knee', 'Knee'],
            ['MRI Abdomen', 'Abdomen'],
        ],
        'mammography' => [
            ['Mammogram (Bilateral)', 'Breast'],
            ['Mammogram (Unilateral)', 'Breast'],
        ],
        'fluoroscopy' => [
            ['Barium Swallow', 'Chest / Oesophagus'],
            ['Barium Meal', 'Abdomen'],
            ['IV Urography', 'Abdomen / Pelvis'],
        ],
    ];
    $out = [];
    foreach ($cat as $mod => $studies) {
        $out[$mod] = [];
        foreach ($studies as $s) {
            $out[$mod][] = ['exam_type' => $s[0], 'body_part' => $s[1]];
        }
    }
    jsonResponse(['success' => true, 'catalogue' => $out]);
}

function radiologyRequests() {
    $db = Database::getInstance();
    $sql = "SELECT r.*,
                   CONCAT(TRIM(CONCAT(p.first_name, ' ', IFNULL(p.middle_name, ''))), ' ', p.last_name) AS patient_name,
                   p.hospital_number,
                   p.gender,
                   TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age,
                   v.visit_number,
                   IFNULL(u.full_name, '') AS doctor_name,
                   IFNULL(rad.full_name, '') AS radiologist_name,
                   rr.id AS result_id,
                   rr.findings,
                   rr.impression,
                   rr.status AS result_status,
                   rr.completed_at,
                   rr.image_path,
                   IFNULL(ver.full_name, '') AS verified_name
            FROM radiology_requests r
            JOIN patient_visits v ON v.id = r.visit_id
            JOIN patient_registrations p ON p.id = v.patient_id
            LEFT JOIN users u ON u.id = r.doctor_id
            LEFT JOIN radiology_results rr ON rr.radiology_request_id = r.id
            LEFT JOIN users rad ON rad.id = rr.radiologist_id
            LEFT JOIN users ver ON ver.id = rr.verified_by
            WHERE 1=1";
    $params = [];

    if (!empty($_GET['status'])) {
        $sql .= " AND r.status = ?";
        $params[] = $_GET['status'];
    }
    if (!empty($_GET['urgency'])) {
        $sql .= " AND r.urgency = ?";
        $params[] = $_GET['urgency'];
    }
    if (!empty($_GET['q'])) {
        $sql .= " AND (p.first_name LIKE ? OR p.last_name LIKE ? OR p.hospital_number LIKE ?
                      OR r.exam_type LIKE ? OR r.body_part LIKE ? OR r.clinical_indication LIKE ?)";
        $like = '%' . $_GET['q'] . '%';
        $params = array_merge($params, [$like, $like, $like, $like, $like, $like]);
    }
    // Emergency work first, then routine newest-first: matches how a radiology
    // desk actually triages a list.
    $sql .= " ORDER BY FIELD(r.urgency, 'emergency', 'urgent', 'routine'), r.requested_at DESC";
    $rows = $db->fetchAll($sql, $params);
    jsonResponse(['success' => true, 'requests' => $rows]);
}

function radiologyRequestOne() {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'Request ID required'], 400);

    $db = Database::getInstance();
    $row = $db->fetchOne(
        "SELECT r.*,
                CONCAT(TRIM(CONCAT(p.first_name, ' ', IFNULL(p.middle_name, ''))), ' ', p.last_name) AS patient_name,
                p.hospital_number, p.gender,
                TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age,
                v.visit_number,
                IFNULL(u.full_name, '') AS doctor_name,
                IFNULL(rad.full_name, '') AS radiologist_name,
                rr.id AS result_id, rr.findings, rr.impression,
                rr.status AS result_status, rr.completed_at, rr.image_path,
                IFNULL(ver.full_name, '') AS verified_name
           FROM radiology_requests r
           JOIN patient_visits v ON v.id = r.visit_id
           JOIN patient_registrations p ON p.id = v.patient_id
           LEFT JOIN users u ON u.id = r.doctor_id
           LEFT JOIN radiology_results rr ON rr.radiology_request_id = r.id
           LEFT JOIN users rad ON rad.id = rr.radiologist_id
           LEFT JOIN users ver ON ver.id = rr.verified_by
          WHERE r.id = ?",
        [(int)$id]
    );
    if (!$row) jsonResponse(['error' => 'Radiology request not found'], 404);
    jsonResponse(['success' => true, 'request' => $row]);
}

function radiologyResults() {
    $db = Database::getInstance();
    $sql = "SELECT rr.*, r.exam_type, r.body_part, r.urgency, r.status AS request_status,
                   CONCAT(TRIM(CONCAT(p.first_name, ' ', IFNULL(p.middle_name, ''))), ' ', p.last_name) AS patient_name,
                   p.hospital_number,
                   IFNULL(rad.full_name, '') AS radiologist_name,
                   IFNULL(ver.full_name, '') AS verified_name
            FROM radiology_results rr
            JOIN radiology_requests r ON r.id = rr.radiology_request_id
            JOIN patient_visits v ON v.id = r.visit_id
            JOIN patient_registrations p ON p.id = v.patient_id
            LEFT JOIN users rad ON rad.id = rr.radiologist_id
            LEFT JOIN users ver ON ver.id = rr.verified_by
            ORDER BY rr.completed_at DESC";
    $rows = $db->fetchAll($sql);
    jsonResponse(['success' => true, 'results' => $rows]);
}

function radiologyStats() {
    $db = Database::getInstance();
    $c = function ($sql) use ($db) {
        return (int)($db->fetchOne($sql)['c'] ?? 0);
    };
    $stats = [
        'total'       => $c("SELECT COUNT(*) c FROM radiology_requests WHERE status <> 'cancelled'"),
        'pending'     => $c("SELECT COUNT(*) c FROM radiology_requests WHERE status = 'pending'"),
        'scheduled'   => $c("SELECT COUNT(*) c FROM radiology_requests WHERE status = 'scheduled'"),
        'in_progress' => $c("SELECT COUNT(*) c FROM radiology_requests WHERE status = 'in_progress'"),
        // "Reported but not yet verified" = a draft exists.
        'reported'    => $c("SELECT COUNT(*) c FROM radiology_results WHERE status = 'draft'"),
        'verified'    => $c("SELECT COUNT(*) c FROM radiology_results WHERE status IN ('final','amended')"),
        'emergency'   => $c("SELECT COUNT(*) c FROM radiology_requests WHERE urgency = 'emergency' AND status IN ('pending','scheduled','in_progress')"),
    ];
    jsonResponse(['success' => true, 'stats' => $stats]);
}

function createRadiologyRequest() {
    $data = getPostData();
    $required = ['visit_id', 'exam_type'];
    $errors = validateRequired($data, $required);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }
    $urgency = in_array($data['urgency'] ?? '', RAD_URGENCIES, true) ? $data['urgency'] : 'routine';
    $bodyPart = trim($data['body_part'] ?? '');
    $indication = trim($data['clinical_indication'] ?? '');

    $db = Database::getInstance();
    try {
        $id = $db->insert('radiology_requests', [
            'visit_id'            => (int)$data['visit_id'],
            'doctor_id'           => getCurrentUserId(),
            'exam_type'           => trim($data['exam_type']),
            'body_part'           => $bodyPart === '' ? null : $bodyPart,
            'clinical_indication' => $indication === '' ? null : $indication,
            'urgency'             => $urgency,
            'status'              => 'pending',
        ]);
        logAudit('CREATE', 'radiology_requests', $id, null, ['exam_type' => $data['exam_type']]);
        jsonResponse(['success' => true, 'request_id' => $id]);
    } catch (Exception $e) {
        error_log("Radiology request error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to create radiology request'], 500);
    }
}

function recordRadiologyResult() {
    $data = getPostData();
    $required = ['radiology_request_id', 'findings'];
    $errors = validateRequired($data, $required);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $db = Database::getInstance();
    $reqId = (int)$data['radiology_request_id'];
    $req = $db->fetchOne("SELECT * FROM radiology_requests WHERE id = ?", [$reqId]);
    if (!$req) {
        jsonResponse(['error' => 'Radiology request not found'], 404);
    }
    if ($req['status'] === 'cancelled') {
        jsonResponse(['error' => 'This request was cancelled - a report cannot be recorded against it'], 409);
    }

    $status = in_array($data['status'] ?? '', RAD_RESULT_STATUSES, true) ? $data['status'] : 'draft';
    $existing = $db->fetchOne("SELECT * FROM radiology_results WHERE radiology_request_id = ?", [$reqId]);

    $findings = trim($data['findings']);
    // "Technique" is a real radiology report header line. It belongs with the
    // findings text, not in its own column, so it is prefixed inline rather
    // than adding a schema column.
    $technique = trim($data['technique'] ?? '');
    if ($technique !== '') {
        $findings = 'Technique: ' . $technique . "\n" . $findings;
    }

    $isFinal = ($status === 'final');

    try {
        if ($existing) {
            // A draft being made final records who verified it. Re-saving an
            // already-final report is an amendment, so keep the original
            // verifier and mark the report amended instead of silently
            // re-attributing it.
            $amendingFinal = ($existing['status'] === 'final' && !$isFinal);
            $fields = [
                'findings'     => $findings,
                'impression'   => trim($data['impression'] ?? ''),
                'image_path'   => trim($data['image_path'] ?? '') ?: null,
                'status'       => $status,
                'completed_at' => date('Y-m-d H:i:s'),
            ];
            if ($isFinal) {
                $fields['verified_by'] = getCurrentUserId();
                $fields['verified_at'] = date('Y-m-d H:i:s');
            } elseif ($amendingFinal) {
                $fields['status'] = 'amended';
            }
            $db->update('radiology_results', $fields, 'radiology_request_id = ?', [$reqId]);
            logAudit('UPDATE', 'radiology_results', $existing['id'], $existing, ['status' => $fields['status']]);
        } else {
            $id = $db->insert('radiology_results', [
                'radiology_request_id' => $reqId,
                'radiologist_id'       => getCurrentUserId(),
                'findings'             => $findings,
                'impression'           => trim($data['impression'] ?? ''),
                'image_path'           => trim($data['image_path'] ?? '') ?: null,
                'status'               => $status,
                'verified_by'          => $isFinal ? getCurrentUserId() : null,
                'verified_at'          => $isFinal ? date('Y-m-d H:i:s') : null,
            ]);
            logAudit('CREATE', 'radiology_results', $id, null, ['status' => $status]);
        }

        // Request lifecycle follows the report: a final report completes the
        // request, anything short of final means images are still being worked.
        $requestStatus = $isFinal ? 'completed' : 'in_progress';
        $db->update('radiology_requests', ['status' => $requestStatus], 'id = ?', [$reqId]);
        jsonResponse(['success' => true, 'status' => $status]);
    } catch (Exception $e) {
        error_log("Radiology result error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to save radiology report'], 500);
    }
}

function updateRequestStatus() {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'Request ID required'], 400);

    $data = getPostData();
    $status = $data['status'] ?? null;
    if (!in_array($status, RAD_REQUEST_STATUSES, true)) {
        jsonResponse(['error' => 'Invalid status'], 400);
    }

    $db = Database::getInstance();
    $old = $db->fetchOne("SELECT * FROM radiology_requests WHERE id = ?", [(int)$id]);
    if (!$old) jsonResponse(['error' => 'Radiology request not found'], 404);

    // A final verified report already means the study is done - do not let a
    // status edit walk that backwards.
    $hasFinal = $db->fetchOne(
        "SELECT id FROM radiology_results WHERE radiology_request_id = ? AND status IN ('final','amended')",
        [(int)$id]
    );
    if ($hasFinal && $status !== 'completed') {
        jsonResponse(['error' => 'This study already has a verified report and cannot be moved back to "' . $status . '"'], 409);
    }

    try {
        $fields = ['status' => $status];
        // Scheduling: an optional appointment slot for the study.
        $slot = trim($data['scheduled_at'] ?? '');
        if ($slot !== '') {
            $fields['scheduled_at'] = date('Y-m-d H:i:s', strtotime($slot));
        }
        if ($status === 'scheduled' && empty($fields['scheduled_at']) && empty($old['scheduled_at'])) {
            jsonResponse(['error' => 'Pick a schedule slot before marking this study as scheduled'], 400);
        }

        $db->update('radiology_requests', $fields, 'id = ?', [(int)$id]);
        logAudit('UPDATE', 'radiology_requests', (int)$id, $old, $fields);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Radiology status error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to update status'], 500);
    }
}
