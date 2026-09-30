<?php
// EHMS — Draft Admissions API
//   GET                           ?status=DRAFT|FINALIZED|CANCELLED|ALL&q=text  -> draft admissions
//   GET  ?action=detail&id=N                                           -> one draft with its patient/ward/bed
//   POST ?action=create                                                -> save a draft (bed stays free)
//   POST ?action=update&id=N                                           -> edit a DRAFT
//   POST ?action=finalize&id=N                                        -> promote a draft into a real admission
//   POST ?action=cancel&id=N                                          -> discard a draft
//
// A draft is an admission still being filled in. The bed is NOT occupied while
// the draft exists — finalizing is what creates the real admissions row and
// occupies the bed, in one transaction.
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

requireLogin();
requireRole(['admin', 'doctor', 'nurse']);

// Declared before the dispatcher below: PHP executes top-level statements in
// order and does not hoist const, so a const placed after the dispatch block
// would be undefined by the time the handlers run.
const DRAFT_ADMISSION_TYPES = ['Emergency', 'Routine', 'Elective', 'Transfer', 'Maternity'];

if ($method === 'GET' && $action === 'detail') {
    draftDetail();
} elseif ($method === 'GET') {
    listDrafts();
} elseif ($method === 'POST' && $action === 'create') {
    createDraft();
} elseif ($method === 'POST' && $action === 'update') {
    updateDraft();
} elseif ($method === 'POST' && $action === 'finalize') {
    finalizeDraft();
} elseif ($method === 'POST' && $action === 'cancel') {
    cancelDraft();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

function listDrafts() {
    $db = Database::getInstance();
    $status = strtoupper(trim($_GET['status'] ?? 'DRAFT'));
    $q = trim($_GET['q'] ?? '');

    $sql = "SELECT d.*,
                   CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) AS patient_name,
                   p.hospital_number,
                   p.gender,
                   TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age,
                   w.ward_name,
                   b.bed_number,
                   dp.full_name AS created_by_name,
                   a.admission_code AS finalized_admission_code
            FROM draft_admissions d
            JOIN patient_registrations p ON p.id = d.patient_id
            LEFT JOIN wards w ON w.id = d.ward_id
            LEFT JOIN beds b ON b.id = d.bed_id
            LEFT JOIN users dp ON dp.id = d.created_by
            LEFT JOIN admissions a ON a.id = d.finalized_admission_id
            WHERE 1=1";
    $params = [];

    if (in_array($status, ['DRAFT', 'FINALIZED', 'CANCELLED'], true)) {
        $sql .= " AND d.status = ?";
        $params[] = $status;
    }
    if ($q !== '') {
        $like = '%' . $q . '%';
        $sql .= " AND (p.hospital_number LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR d.draft_number LIKE ?)";
        array_push($params, $like, $like, $like, $like);
    }
    $sql .= " ORDER BY d.created_at DESC, d.id DESC";

    $rows = $db->fetchAll($sql, $params);
    jsonResponse(['success' => true, 'drafts' => $rows]);
}

function draftDetail() {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'Draft ID is required'], 400);

    $db = Database::getInstance();
    $draft = $db->fetchOne(
        "SELECT d.*,
                CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) AS patient_name,
                p.hospital_number, p.gender, p.date_of_birth,
                w.ward_name, b.bed_number, b.status AS bed_status
         FROM draft_admissions d
         JOIN patient_registrations p ON p.id = d.patient_id
         LEFT JOIN wards w ON w.id = d.ward_id
         LEFT JOIN beds b ON b.id = d.bed_id
         WHERE d.id = ?",
        [$id]
    );
    if (!$draft) jsonResponse(['error' => 'Draft not found'], 404);

    jsonResponse(['success' => true, 'draft' => $draft]);
}

// Shared validation for the ward/bed pair. A draft may be saved without a bed
// (the paste's "pending finalization" state), but if a bed is given it must
// belong to the ward and still be free.
function validateDraftBed($db, $wardId, $bedId, $excludeDraftId = 0) {
    if (empty($wardId) || empty($bedId)) return true;

    $bed = $db->fetchOne("SELECT * FROM beds WHERE id = ?", [(int)$bedId]);
    if (!$bed) {
        jsonResponse(['error' => 'Bed not found'], 404);
    }
    if ((int)$bed['ward_id'] !== (int)$wardId) {
        jsonResponse(['error' => 'Bed does not belong to the selected ward'], 400);
    }

    // A bed occupied by an admitted patient is genuinely unavailable. A bed held
    // by another *draft* is only a soft conflict — that draft can still be
    // edited or cancelled, so the first one to finalize wins.
    $taken = $db->fetchOne(
        "SELECT id FROM admissions WHERE bed_id = ? AND status = 'Admitted' LIMIT 1",
        [(int)$bedId]
    );
    if ($taken) {
        jsonResponse(['error' => 'Selected bed is already occupied by an admitted patient'], 409);
    }
    return true;
}

function createDraft() {
    $data = getPostData();
    $errors = validateRequired($data, ['patient_id']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $db = Database::getInstance();
    $patient = $db->fetchOne("SELECT id, hospital_number FROM patient_registrations WHERE id = ?", [(int)$data['patient_id']]);
    if (!$patient) jsonResponse(['error' => 'Patient not found'], 404);

    $wardId = !empty($data['ward_id']) ? (int)$data['ward_id'] : null;
    $bedId  = !empty($data['bed_id'])  ? (int)$data['bed_id']  : null;
    if ($bedId && !$wardId) {
        jsonResponse(['error' => 'Select a ward for the chosen bed'], 400);
    }
    validateDraftBed($db, $wardId, $bedId);

    $admissionDate = trim($data['admission_date'] ?? '');
    if ($admissionDate !== '') {
        $admissionDate = str_replace('T', ' ', $admissionDate);
    } else {
        $admissionDate = date('Y-m-d H:i:s');
    }

    $type = trim($data['admission_type'] ?? 'Routine');
    if (!in_array($type, DRAFT_ADMISSION_TYPES, true)) $type = 'Routine';

    $codeSuffix = $patient['hospital_number'] ? substr(trim($patient['hospital_number']), -4) : 'P';
    $draftNumber = 'DRF-' . date('YmdHis') . '-' . strtoupper(trim($codeSuffix));

    try {
        $db->beginTransaction();
        $draftId = $db->insert('draft_admissions', [
            'draft_number'     => $draftNumber,
            'patient_id'       => (int)$data['patient_id'],
            'ward_id'          => $wardId,
            'bed_id'           => $bedId,
            'admission_date'   => $admissionDate,
            'admission_type'   => $type,
            'admitting_doctor' => trim($data['admitting_doctor'] ?? ''),
            'department_id'    => !empty($data['department_id']) ? (int)$data['department_id'] : null,
            'diagnosis'        => trim($data['diagnosis'] ?? ''),
            'notes'            => trim($data['notes'] ?? ''),
            'status'           => 'DRAFT',
            'created_by'       => getCurrentUserId(),
        ]);
        $db->commit();

        logAudit('CREATE', 'draft_admissions', $draftId, null, [
            'draft_number' => $draftNumber,
            'patient_id'   => (int)$data['patient_id'],
            'ward_id'      => $wardId,
            'bed_id'       => $bedId,
        ]);
        jsonResponse(['success' => true, 'draft_id' => $draftId, 'draft_number' => $draftNumber]);
    } catch (Exception $e) {
        $db->rollback();
        error_log("Draft create error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to save the draft admission'], 500);
    }
}

function updateDraft() {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'Draft ID is required'], 400);

    $data = getPostData();
    $db = Database::getInstance();
    $draft = $db->fetchOne("SELECT * FROM draft_admissions WHERE id = ?", [$id]);
    if (!$draft) jsonResponse(['error' => 'Draft not found'], 404);
    if ($draft['status'] !== 'DRAFT') {
        jsonResponse(['error' => 'Only a pending draft can be edited'], 400);
    }

    $wardId = array_key_exists('ward_id', $data) ? (!empty($data['ward_id']) ? (int)$data['ward_id'] : null) : $draft['ward_id'];
    $bedId  = array_key_exists('bed_id', $data)  ? (!empty($data['bed_id'])  ? (int)$data['bed_id']  : null) : $draft['bed_id'];
    if ($bedId && !$wardId) jsonResponse(['error' => 'Select a ward for the chosen bed'], 400);
    validateDraftBed($db, $wardId, $bedId);

    $update = [
        'ward_id'          => $wardId,
        'bed_id'           => $bedId,
        'admitting_doctor' => trim($data['admitting_doctor'] ?? $draft['admitting_doctor']),
        'department_id'    => array_key_exists('department_id', $data)
            ? (!empty($data['department_id']) ? (int)$data['department_id'] : null)
            : $draft['department_id'],
        'diagnosis'        => trim($data['diagnosis'] ?? $draft['diagnosis']),
        'notes'            => array_key_exists('notes', $data) ? trim($data['notes']) : $draft['notes'],
    ];

    if (array_key_exists('admission_type', $data)) {
        $type = trim($data['admission_type']);
        $update['admission_type'] = in_array($type, DRAFT_ADMISSION_TYPES, true) ? $type : $draft['admission_type'];
    }
    if (array_key_exists('admission_date', $data) && trim($data['admission_date']) !== '') {
        $update['admission_date'] = str_replace('T', ' ', trim($data['admission_date']));
    }

    try {
        $db->update('draft_admissions', $update, 'id = ?', [$id]);
        logAudit('UPDATE', 'draft_admissions', $id, $draft, $update);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Draft update error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to update the draft admission'], 500);
    }
}

// Promote a draft into a real admission: create the admissions row, occupy the
// bed, and mark the draft FINALIZED — all in one transaction so the draft and
// the admission can never disagree.
function finalizeDraft() {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'Draft ID is required'], 400);

    $db = Database::getInstance();
    $draft = $db->fetchOne("SELECT * FROM draft_admissions WHERE id = ?", [$id]);
    if (!$draft) jsonResponse(['error' => 'Draft not found'], 404);
    if ($draft['status'] !== 'DRAFT') {
        jsonResponse(['error' => 'This draft has already been ' . strtolower($draft['status'])], 400);
    }

    $wardId = $draft['ward_id'];
    $bedId  = $draft['bed_id'];
    if (empty($wardId) || empty($bedId)) {
        jsonResponse(['error' => 'Choose a ward and bed on this draft before finalizing'], 400);
    }

    $bed = $db->fetchOne("SELECT * FROM beds WHERE id = ?", [$bedId]);
    if (!$bed) jsonResponse(['error' => 'Bed not found'], 404);
    if (strtolower($bed['status']) !== 'available') {
        jsonResponse(['error' => 'Selected bed is no longer available — pick another bed on the draft'], 409);
    }

    $patient = $db->fetchOne("SELECT id, hospital_number FROM patient_registrations WHERE id = ?", [(int)$draft['patient_id']]);
    if (!$patient) jsonResponse(['error' => 'Patient not found'], 404);

    $admissionCodeSuffix = $patient['hospital_number'] ? substr(trim($patient['hospital_number']), -4) : 'P';
    $admissionCode = 'ADM-' . date('YmdHis') . '-' . strtoupper(trim($admissionCodeSuffix));

    try {
        $db->beginTransaction();

        $db->update('beds', ['current_patient_id' => null, 'status' => 'Available'],
            "current_patient_id = ? AND status = 'Occupied'", [(int)$draft['patient_id']]);

        $admissionId = $db->insert('admissions', [
            'admission_code'   => $admissionCode,
            'patient_id'       => (int)$draft['patient_id'],
            'ward_id'          => (int)$wardId,
            'bed_id'           => (int)$bedId,
            'admission_date'   => $draft['admission_date'] ?: date('Y-m-d H:i:s'),
            'admission_type'   => $draft['admission_type'],
            'admitting_doctor' => $draft['admitting_doctor'],
            'department_id'    => $draft['department_id'],
            'diagnosis'        => $draft['diagnosis'],
            'notes'            => $draft['notes'],
            'admitted_by'      => getCurrentUserId(),
            'status'           => 'Admitted',
        ]);

        $db->update('beds', ['current_patient_id' => (int)$draft['patient_id'], 'status' => 'Occupied'],
            'id = ?', [(int)$bedId]);

        $db->update('draft_admissions', [
            'status'                   => 'FINALIZED',
            'finalized_admission_id'   => $admissionId,
        ], 'id = ?', [$id]);

        $db->commit();

        logAudit('UPDATE', 'draft_admissions', $id, $draft, [
            'status' => 'FINALIZED',
            'admission_id' => $admissionId,
        ]);
        logAudit('CREATE', 'admissions', $admissionId, null, [
            'admission_code' => $admissionCode,
            'from_draft'     => $draft['draft_number'],
        ]);

        jsonResponse([
            'success'        => true,
            'admission_id'   => $admissionId,
            'admission_code' => $admissionCode,
        ]);
    } catch (Exception $e) {
        $db->rollback();
        error_log("Draft finalize error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to finalize the draft admission'], 500);
    }
}

function cancelDraft() {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'Draft ID is required'], 400);

    $db = Database::getInstance();
    $draft = $db->fetchOne("SELECT * FROM draft_admissions WHERE id = ?", [$id]);
    if (!$draft) jsonResponse(['error' => 'Draft not found'], 404);
    if ($draft['status'] !== 'DRAFT') {
        jsonResponse(['error' => 'This draft has already been ' . strtolower($draft['status'])], 400);
    }

    try {
        $db->update('draft_admissions', ['status' => 'CANCELLED'], 'id = ?', [$id]);
        logAudit('UPDATE', 'draft_admissions', $id, $draft, ['status' => 'CANCELLED']);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Draft cancel error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to discard the draft admission'], 500);
    }
}
