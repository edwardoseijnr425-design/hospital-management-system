<?php
// EHMS — Clinical Notes API (Doctor Station)
//   GET  ?action=list&admission_id=N|patient_id=N|note_type=X           -> clinical notes
//   GET  ?action=inpatients                                               -> currently admitted patients
//                                                                          with ward/bed, for the station table
//   GET  ?action=detail&id=N                                             -> one note
//   POST ?action=create                                                  -> save an OPD / IPD / discharge entry
//   DELETE ?action=delete&id=N                                          -> remove an entry
//
// A DISCHARGE_SUMMARY is stored as a clinical note and mirrored onto the
// admission (discharge summary text) so the real discharge flow still owns the
// actual status change — this API never discharges a patient by itself.
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

requireLogin();
requireRole(['admin', 'doctor', 'nurse']);

// Declared before the dispatcher below: PHP executes top-level statements in
// order and does not hoist const, so a const placed after the dispatch block
// would be undefined by the time the handlers run.
const CLINICAL_NOTE_TYPES = ['OPD_NOTE', 'IPD_NOTE', 'DISCHARGE_SUMMARY'];

if ($method === 'GET' && $action === 'inpatients') {
    listInpatients();
} elseif ($method === 'GET' && $action === 'detail') {
    noteDetail();
} elseif ($method === 'GET') {
    listNotes();
} elseif ($method === 'POST' && $action === 'create') {
    createNote();
} elseif ($method === 'DELETE' && $action === 'delete') {
    deleteNote();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

// The Doctor Station table: every patient currently in a bed, with the notes
// already written for that stay.
function listInpatients() {
    $db = Database::getInstance();
    $q = trim($_GET['q'] ?? '');

    $sql = "SELECT a.id AS admission_id, a.admission_code, a.patient_id, a.admission_date,
                   a.admission_type, a.diagnosis, a.admitting_doctor,
                   CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) AS patient_name,
                   p.hospital_number, p.gender,
                   TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age,
                   w.ward_name, b.bed_number,
                   (SELECT COUNT(*) FROM clinical_notes n
                     WHERE n.admission_id = a.id AND n.note_type = 'IPD_NOTE') AS ipd_note_count,
                   (SELECT COUNT(*) FROM clinical_notes n
                     WHERE n.admission_id = a.id AND n.note_type = 'DISCHARGE_SUMMARY') AS summary_count
            FROM admissions a
            JOIN patient_registrations p ON p.id = a.patient_id
            JOIN wards w ON w.id = a.ward_id
            JOIN beds b ON b.id = a.bed_id
            WHERE a.status = 'Admitted'";
    $params = [];

    if ($q !== '') {
        $like = '%' . $q . '%';
        $sql .= " AND (p.hospital_number LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ? OR a.admission_code LIKE ? OR b.bed_number LIKE ? OR w.ward_name LIKE ?)";
        array_push($params, $like, $like, $like, $like, $like, $like);
    }
    $sql .= " ORDER BY a.admission_date DESC, a.id DESC";

    $rows = $db->fetchAll($sql, $params);
    jsonResponse(['success' => true, 'inpatients' => $rows]);
}

function listNotes() {
    $db = Database::getInstance();

    $admissionId = (int)($_GET['admission_id'] ?? 0);
    $patientId   = (int)($_GET['patient_id'] ?? 0);
    $noteType    = strtoupper(trim($_GET['note_type'] ?? ''));

    $sql = "SELECT n.*,
                   CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) AS patient_name,
                   p.hospital_number,
                   a.admission_code,
                   u.full_name AS doctor_name
            FROM clinical_notes n
            JOIN patient_registrations p ON p.id = n.patient_id
            LEFT JOIN admissions a ON a.id = n.admission_id
            JOIN users u ON u.id = n.doctor_id
            WHERE 1=1";
    $params = [];

    if ($admissionId) {
        $sql .= " AND n.admission_id = ?";
        $params[] = $admissionId;
    }
    if ($patientId) {
        $sql .= " AND n.patient_id = ?";
        $params[] = $patientId;
    }
    if (in_array($noteType, CLINICAL_NOTE_TYPES, true)) {
        $sql .= " AND n.note_type = ?";
        $params[] = $noteType;
    }
    $sql .= " ORDER BY n.created_at DESC, n.id DESC LIMIT 300";

    $rows = $db->fetchAll($sql, $params);
    jsonResponse(['success' => true, 'notes' => $rows]);
}

function noteDetail() {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'Note ID is required'], 400);

    $db = Database::getInstance();
    $note = $db->fetchOne(
        "SELECT n.*,
                CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) AS patient_name,
                p.hospital_number, a.admission_code, u.full_name AS doctor_name
         FROM clinical_notes n
         JOIN patient_registrations p ON p.id = n.patient_id
         LEFT JOIN admissions a ON a.id = n.admission_id
         JOIN users u ON u.id = n.doctor_id
         WHERE n.id = ?",
        [$id]
    );
    if (!$note) jsonResponse(['error' => 'Note not found'], 404);

    jsonResponse(['success' => true, 'note' => $note]);
}

function createNote() {
    $data = getPostData();
    $errors = validateRequired($data, ['patient_id', 'note_type', 'clinical_note']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $db = Database::getInstance();

    $patient = $db->fetchOne("SELECT id, hospital_number FROM patient_registrations WHERE id = ?", [(int)$data['patient_id']]);
    if (!$patient) jsonResponse(['error' => 'Patient not found'], 404);

    $noteType = strtoupper(trim($data['note_type']));
    if (!in_array($noteType, CLINICAL_NOTE_TYPES, true)) {
        jsonResponse(['error' => 'Select a valid entry type'], 400);
    }

    $text = trim($data['clinical_note']);
    if (strlen($text) > 20000) {
        jsonResponse(['error' => 'Clinical entry is too long (max 20000 characters)'], 400);
    }

    // An admission_id is optional: an OPD note may belong to a visit with no
    // admission behind it. When given it must be a real, live admission.
    $admissionId = !empty($data['admission_id']) ? (int)$data['admission_id'] : null;
    $admission = null;
    if ($admissionId) {
        $admission = $db->fetchOne("SELECT * FROM admissions WHERE id = ?", [$admissionId]);
        if (!$admission) jsonResponse(['error' => 'Admission not found'], 404);
        if ((int)$admission['patient_id'] !== (int)$data['patient_id']) {
            jsonResponse(['error' => 'That admission belongs to a different patient'], 400);
        }
        if (strtolower($admission['status']) !== 'admitted') {
            jsonResponse(['error' => 'This admission is already discharged — its record is closed'], 400);
        }
    }

    // A visit_id, when supplied, must belong to the same patient.
    $visitId = !empty($data['visit_id']) ? (int)$data['visit_id'] : null;
    if ($visitId) {
        $visit = $db->fetchOne("SELECT id FROM patient_visits WHERE id = ? AND patient_id = ?", [$visitId, (int)$data['patient_id']]);
        if (!$visit) jsonResponse(['error' => 'Visit not found for this patient'], 404);
    }

    // Only one discharge summary per stay: a second one would contradict the
    // first, so the existing summary has to be replaced deliberately instead.
    if ($noteType === 'DISCHARGE_SUMMARY' && $admissionId) {
        $existing = $db->fetchOne(
            "SELECT id FROM clinical_notes WHERE admission_id = ? AND note_type = 'DISCHARGE_SUMMARY' ORDER BY id DESC LIMIT 1",
            [$admissionId]
        );
        if ($existing) {
            jsonResponse(['error' => 'A discharge summary already exists for this stay — edit it instead of adding another'], 409);
        }
    }

    try {
        $db->beginTransaction();

        $noteId = $db->insert('clinical_notes', [
            'admission_id' => $admissionId,
            'visit_id'     => $visitId,
            'patient_id'   => (int)$data['patient_id'],
            'note_type'    => $noteType,
            'clinical_note'=> $text,
            'doctor_id'    => getCurrentUserId(),
        ]);

        // Mirror the discharge summary onto the admission so the real discharge
        // screen shows it alongside the discharge outcome. The status itself is
        // left alone — only the discharge flow changes that.
        if ($noteType === 'DISCHARGE_SUMMARY' && $admissionId) {
            $db->update('admissions', ['discharge_notes' => $text], 'id = ?', [$admissionId]);
        }

        $db->commit();

        logAudit('CREATE', 'clinical_notes', $noteId, null, [
            'patient_id'   => (int)$data['patient_id'],
            'admission_id' => $admissionId,
            'note_type'    => $noteType,
        ]);

        jsonResponse(['success' => true, 'note_id' => $noteId]);
    } catch (Exception $e) {
        $db->rollback();
        error_log("Clinical note create error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to save the clinical entry'], 500);
    }
}

function deleteNote() {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'Note ID is required'], 400);

    $db = Database::getInstance();
    $note = $db->fetchOne("SELECT * FROM clinical_notes WHERE id = ?", [$id]);
    if (!$note) jsonResponse(['error' => 'Note not found'], 404);

    // A clinical entry may only be removed by the clinician who wrote it, or by
    // an administrator. Without this the file-level role gate alone would let any
    // nurse delete another doctor's record - including a discharge summary.
    if (!hasRole(['admin']) && (int)$note['doctor_id'] !== (int)getCurrentUserId()) {
        jsonResponse(['error' => 'You can only delete your own clinical entries'], 403);
    }

    try {
        $db->beginTransaction();

        $db->delete('clinical_notes', 'id = ?', [$id]);

        // Clear the mirrored copy if this was the discharge summary.
        if ($note['note_type'] === 'DISCHARGE_SUMMARY' && $note['admission_id']) {
            $db->update('admissions', ['discharge_notes' => null], 'id = ?', [(int)$note['admission_id']]);
        }

        $db->commit();
        logAudit('DELETE', 'clinical_notes', $id, $note, null);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        $db->rollback();
        error_log("Clinical note delete error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to delete the clinical entry'], 500);
    }
}
