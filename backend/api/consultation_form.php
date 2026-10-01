<?php
// EHMS — Consultation Form API (Doctor Station)
//   GET  ?action=context&patient_id=N    -> everything the form renders from:
//                                          patient header, latest vitals, past
//                                          records, open admission, investigation
//                                          catalogue and pending prescriptions
//   GET  ?action=drugs&q=text            -> drug picker, searched in pharmacy_inventory
//   POST ?action=save                    -> write the encounter: consultation,
//                                          allergies, lab + radiology requests,
//                                          prescription rows, follow-up booking
//
// Two deliberate boundaries:
//
//  * Admission is NOT done here. Occupying a bed is admissions.php's job and it
//    is the single owner of bed state; the form posts to admissions.php
//    ?action=admit as a separate call after this save succeeds. That keeps bed
//    occupancy in one place instead of duplicating it here.
//
//  * The Doctor Station form is patient-scoped, not admission-scoped, because it
//    can be opened for a patient who is not yet in a bed. But consultations and
//    vital_signs both hang off patient_visits.visit_id and admissions carries no
//    visit_id, so the encounter is attached to a visit resolved per patient: the
//    patient's open visit of the matching type is reused, otherwise one is
//    created. Reusing rather than always creating means repeated consultations
//    during one attendance share a single visit.
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'context';

requireLogin();
requireRole(['super_admin', 'admin', 'doctor']);

// Declared before the dispatcher below: PHP executes top-level statements in
// order and does not hoist const, so a const placed after the dispatch block
// would be undefined by the time the handlers run.

// The investigation checkboxes on the form. Each entry is
// [kind, name written to the request, body_part for radiology].
// Lab work goes to lab_requests; imaging goes to radiology_requests. Both
// tables already exist, so each checkbox raises a real order that the lab and
// radiology desks pick up, rather than a line of free text.
const CONSULTATION_INVESTIGATIONS = [
    'lab:Malaria Parasite / RDT' => ['lab', 'Malaria Parasite / RDT', ''],
    'lab:Full Blood Count (FBC)' => ['lab', 'Full Blood Count (FBC)', ''],
    'lab:Urinalysis'             => ['lab', 'Urinalysis', ''],
    'lab:Widal Test (Typhoid)'   => ['lab', 'Widal Test (Typhoid)', ''],
    'radiology:Chest X-Ray'      => ['radiology', 'Chest X-Ray', 'Chest'],
    'radiology:Abdominal Scan'   => ['radiology', 'Abdominal Scan', 'Abdomen'],
];

// Separator used to round-trip the free-text medication notes inside
// consultations.notes alongside the doctor's clinical notes.
const MED_NOTES_MARKER = 'MEDICATION NOTES (free text):';

// consultation_type is an ENUM('new','follow_up','emergency'). The form asks
// whether this is a new or an old/chronic diagnosis, which is not the same
// question, but follow_up is the value that already means "seen before, ongoing
// condition" — so it is reused rather than adding a column that would duplicate
// it. Nothing is lost: a chronic problem reviewed again is a follow-up.
const DIAGNOSIS_STATUS_TO_TYPE = [
    'New' => 'new',
    'Old' => 'follow_up',
];

// Vitals are read-only here — the form shows what the nurses recorded. These
// bounds decide what is believable enough to put in front of a clinician.
// DECIMAL(4,1) happily stores a temperature of 54.5 or an SpO2 of 2.0, and
// vital_signs in this database already holds such rows; displaying those raw
// would read as a real reading. Anything outside these bounds is shown as not
// recorded rather than printed.
const VITAL_RANGES = [
    'temperature'             => [25.0, 45.0],
    'blood_pressure_systolic' => [50, 260],
    'blood_pressure_diastolic'=> [30, 160],
    'heart_rate'              => [30, 220],
    'respiratory_rate'        => [5, 60],
    'oxygen_saturation'       => [50.0, 100.0],
];

if ($method === 'GET' && $action === 'drugs') {
    searchDrugs();
} elseif ($method === 'GET' && $action === 'context') {
    formContext();
} elseif ($method === 'POST' && $action === 'save') {
    saveEncounter();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

// Everything the form needs to open for one patient.
function formContext() {
    $patientId = (int)($_GET['patient_id'] ?? 0);
    if (!$patientId) jsonResponse(['error' => 'Patient ID is required'], 400);

    $db = Database::getInstance();

    $patient = $db->fetchOne(
        "SELECT p.*, TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age
         FROM patient_registrations p
         WHERE p.id = ?",
        [$patientId]
    );
    if (!$patient) jsonResponse(['error' => 'Patient not found'], 404);

    // An open admission, if any: the form pre-ticks the admit toggle and shows
    // the ward/bed rather than letting a doctor admit a patient who is in a bed.
    $admission = $db->fetchOne(
        "SELECT a.*, w.ward_name, b.bed_number
         FROM admissions a
         JOIN wards w ON w.id = a.ward_id
         JOIN beds b ON b.id = a.bed_id
         WHERE a.patient_id = ? AND a.status = 'Admitted'
         ORDER BY a.admission_date DESC LIMIT 1",
        [$patientId]
    );

    // Past records: the clinical note history plus the diagnoses already given.
    $notes = $db->fetchAll(
        "SELECT n.id, n.note_type, n.clinical_note, n.created_at,
                u.full_name AS doctor_name
         FROM clinical_notes n
         JOIN users u ON u.id = n.doctor_id
         WHERE n.patient_id = ?
         ORDER BY n.created_at DESC, n.id DESC LIMIT 20",
        [$patientId]
    );

    $consultations = $db->fetchAll(
        "SELECT c.id, c.consultation_type, c.diagnosis, c.status, c.consultation_date,
                v.visit_number, v.chief_complaint, u.full_name AS doctor_name
         FROM consultations c
         JOIN patient_visits v ON v.id = c.visit_id
         JOIN users u ON u.id = c.doctor_id
         WHERE v.patient_id = ?
         ORDER BY c.consultation_date DESC, c.id DESC LIMIT 20",
        [$patientId]
    );

    // Latest vitals for any visit this patient has.
    $vitals = $db->fetchOne(
        "SELECT v.temperature, v.blood_pressure_systolic, v.blood_pressure_diastolic,
                v.heart_rate, v.respiratory_rate, v.oxygen_saturation, v.recorded_at,
                u.full_name AS nurse_name
         FROM vital_signs v
         JOIN users u ON u.id = v.nurse_id
         WHERE v.visit_id IN (SELECT id FROM patient_visits WHERE patient_id = ?)
         ORDER BY v.recorded_at DESC, v.id DESC LIMIT 1",
        [$patientId]
    );

    // Prescriptions still waiting to be dispensed, so the doctor can see what is
    // already on the patient's medication rather than ordering it twice.
    $pendingPrescriptions = $db->fetchAll(
        "SELECT pr.id, pr.dosage, pr.frequency, pr.duration, pr.status, pr.prescribed_at,
                d.drug_name, d.generic_name, d.drug_code
         FROM prescriptions pr
         JOIN pharmacy_inventory d ON d.id = pr.drug_id
         JOIN patient_visits v ON v.id = pr.visit_id
         WHERE v.patient_id = ? AND pr.status = 'pending'
         ORDER BY pr.prescribed_at DESC LIMIT 20",
        [$patientId]
    );

    // Free-text medication notes from the most recent consultation, so reopening
    // the form does not silently drop what was typed last time.
    $lastMedicationNotes = '';
    if ($consultations) {
        $stored = $db->fetchOne(
            "SELECT c.notes FROM consultations c
             JOIN patient_visits v ON v.id = c.visit_id
             WHERE v.patient_id = ? AND c.notes LIKE ?
             ORDER BY c.consultation_date DESC, c.id DESC LIMIT 1",
            [$patientId, '%' . MED_NOTES_MARKER . '%']
        );
        if ($stored && $stored['notes']) {
            $pos = strpos($stored['notes'], MED_NOTES_MARKER);
            $lastMedicationNotes = trim(substr($stored['notes'], $pos + strlen(MED_NOTES_MARKER)));
        }
    }

    $catalogue = [];
    foreach (CONSULTATION_INVESTIGATIONS as $key => $spec) {
        $catalogue[] = [
            'key'   => $key,
            'label' => $spec[1],
            'kind'  => $spec[0],
        ];
    }

    // Clinics offered for the follow-up booking. appointments.department_id is
    // optional, so the form can leave it blank for a general review.
    $departments = $db->fetchAll(
        "SELECT id, name FROM departments WHERE id IN (
             SELECT department_id FROM users WHERE department_id IS NOT NULL
         ) OR id IN (SELECT department_id FROM patient_visits WHERE department_id IS NOT NULL)
         ORDER BY name"
    );

    jsonResponse([
        'success' => true,
        'patient' => [
            'id'              => (int)$patient['id'],
            'hospital_number' => $patient['hospital_number'],
            'name'            => trim($patient['first_name'] . ' '
                                   . ($patient['middle_name'] ?: '') . ' '
                                   . $patient['last_name']),
            'gender'          => $patient['gender'],
            'age'             => $patient['age'],
            'blood_group'     => $patient['blood_group'],
            'nhia_number'     => $patient['nhia_number'],
            'allergies'       => $patient['allergies'],
        ],
        'admission' => $admission ? [
            'id'          => (int)$admission['id'],
            'admission_code' => $admission['admission_code'],
            'ward_name'   => $admission['ward_name'],
            'bed_number'  => $admission['bed_number'],
            'diagnosis'   => $admission['diagnosis'],
        ] : null,
        'vitals'              => normaliseVitals($vitals),
        'notes'               => $notes,
        'consultations'       => $consultations,
        'pending_prescriptions' => $pendingPrescriptions,
        'last_medication_notes' => $lastMedicationNotes,
        'investigations'      => $catalogue,
        'departments'         => $departments,
        // Carried so the admitting-doctor field on the admission is attributed
        // correctly when this form raises an admission.
        'doctor_name'         => getCurrentUserName(),
    ]);
}

// Blank out any vital that falls outside a believable range, so a bad row reads
// as "not recorded" rather than as a real measurement.
function normaliseVitals($vitals) {
    $out = [
        'recorded_at' => $vitals['recorded_at'] ?? null,
        'nurse_name'  => $vitals['nurse_name'] ?? null,
        'discarded'   => [],
    ];
    foreach (VITAL_RANGES as $field => $range) {
        $value = $vitals[$field] ?? null;
        $numeric = ($value === null || $value === '') ? null : (float)$value;
        if ($numeric !== null && ($numeric < $range[0] || $numeric > $range[1])) {
            $out['discarded'][] = $field;
            $out[$field] = null;
        } else {
            $out[$field] = $numeric;
        }
    }
    return $out;
}

function searchDrugs() {
    $db = Database::getInstance();
    $q = trim($_GET['q'] ?? '');

    // Only dispensable stock is offered. A drug with nothing left would produce a
    // prescription the pharmacy cannot fill, so it is filtered out rather than
    // left for the doctor to discover at the counter.
    $sql = "SELECT id, drug_name, generic_name, drug_code, unit, quantity_in_stock, category
            FROM pharmacy_inventory
            WHERE is_active = 1 AND quantity_in_stock > 0";
    $params = [];

    if ($q !== '') {
        $like = '%' . $q . '%';
        $sql .= " AND (drug_name LIKE ? OR generic_name LIKE ? OR drug_code LIKE ?)";
        array_push($params, $like, $like, $like);
    }
    $sql .= " ORDER BY drug_name LIMIT 40";

    jsonResponse(['success' => true, 'drugs' => $db->fetchAll($sql, $params)]);
}

function saveEncounter() {
    $data = getPostData();
    $errors = validateRequired($data, ['patient_id', 'diagnosis_status']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $db = Database::getInstance();
    $patientId = (int)$data['patient_id'];

    $patient = $db->fetchOne("SELECT id, hospital_number FROM patient_registrations WHERE id = ?", [$patientId]);
    if (!$patient) jsonResponse(['error' => 'Patient not found'], 404);

    $statusKey = trim($data['diagnosis_status']);
    if (!isset(DIAGNOSIS_STATUS_TO_TYPE[$statusKey])) {
        jsonResponse(['error' => 'Choose whether this is a new or an old diagnosis'], 400);
    }
    $consultationType = DIAGNOSIS_STATUS_TO_TYPE[$statusKey];

    // An open admission decides the visit type and lets an already-admitted
    // patient be reviewed without a second admission being suggested.
    $admission = $db->fetchOne(
        "SELECT * FROM admissions WHERE patient_id = ? AND status = 'Admitted' ORDER BY admission_date DESC LIMIT 1",
        [$patientId]
    );
    $visitType = $admission ? 'ipd' : 'opd';

    $doctorNotes  = trim($data['doctor_notes'] ?? '');
    $complaints   = trim($data['presenting_complaints'] ?? '');
    $diagnosis    = trim($data['provisional_diagnosis'] ?? '');
    $medNotes     = trim($data['medication_notes'] ?? '');
    $allergies    = trim($data['allergies'] ?? '');
    $draft        = !empty($data['save_as_draft']);

    // A consultation with nothing in it is not a consultation. Complaints are
    // accepted as the whole content because a nurse-taken history with no doctor
    // note yet is a legitimate draft.
    if ($diagnosis === '' && $complaints === '' && $doctorNotes === '' && $medNotes === '') {
        jsonResponse(['error' => 'Add at least a complaint, diagnosis or note before saving'], 400);
    }

    // Free-text medication notes ride inside consultations.notes behind a
    // marker so the clinical notes stay intact and both can be read back apart.
    $notes = $doctorNotes;
    if ($medNotes !== '') {
        $notes = ($notes === '' ? '' : $notes . "\n\n") . MED_NOTES_MARKER . "\n" . $medNotes;
    }

    $investigationKeys = is_array($data['investigations'] ?? null)
        ? $data['investigations']
        : array_filter(array_map('trim', explode(',', (string)($data['investigations'] ?? ''))));
    $unknown = array_diff($investigationKeys, array_keys(CONSULTATION_INVESTIGATIONS));
    if ($unknown) {
        jsonResponse(['error' => 'Unknown investigation requested: ' . implode(', ', $unknown)], 400);
    }

    // Prescription lines from the drug picker.
    $rxLines = is_array($data['prescriptions'] ?? null) ? $data['prescriptions'] : [];
    $rxDrugIds = [];
    foreach ($rxLines as $line) {
        $drugId = (int)($line['drug_id'] ?? 0);
        if ($drugId) $rxDrugIds[] = $drugId;
    }

    // Follow-up booking.
    $followUpAt = trim($data['follow_up_datetime'] ?? '');
    $followUpDept = (int)($data['follow_up_department_id'] ?? 0);
    if ($followUpAt !== '') {
        $ts = strtotime($followUpAt);
        if ($ts === false) {
            jsonResponse(['error' => 'The follow-up date and time could not be read'], 400);
        }
        if ($ts < time()) {
            jsonResponse(['error' => 'The follow-up date is in the past — pick a future slot'], 400);
        }
        if ($followUpDept) {
            $deptExists = $db->fetchOne("SELECT id FROM departments WHERE id = ?", [$followUpDept]);
            if (!$deptExists) jsonResponse(['error' => 'The selected follow-up clinic does not exist'], 400);
        }
    }

    // Validated here, before the transaction opens: jsonResponse() exits, so a
    // refusal raised inside the try block would leave the transaction open.
    $consultationId = !empty($data['consultation_id']) ? (int)$data['consultation_id'] : null;
    if ($consultationId) {
        $owned = $db->fetchOne(
            "SELECT c.id FROM consultations c
             JOIN patient_visits v ON v.id = c.visit_id
             WHERE c.id = ? AND v.patient_id = ?",
            [$consultationId, $patientId]
        );
        if (!$owned) {
            jsonResponse(['error' => 'That consultation belongs to a different patient'], 400);
        }
    }

    try {
        $db->beginTransaction();

        $visitId = resolveVisit($db, $patientId, $visitType, $complaints, $admission);
        if (!$visitId) {
            $db->rollback();
            jsonResponse(['error' => 'No department is configured, so a visit cannot be opened for this patient'], 409);
        }

        $consultationId = $consultationId
            ? updateExistingConsultation($db, $consultationId, $consultationType, $diagnosis, $notes)
            : insertConsultation($db, $visitId, $consultationType, $diagnosis, $notes, $draft);

        // Allergies live on the patient, so they are updated whether or not the
        // encounter itself was a draft — an allergy is a standing fact.
        $db->update('patient_registrations', ['allergies' => $allergies === '' ? null : $allergies], 'id = ?', [$patientId]);

        $counts = [
            'lab' => 0, 'radiology' => 0, 'prescriptions' => 0, 'skipped' => 0,
        ];
        foreach ($investigationKeys as $key) {
            raiseInvestigation($db, $visitId, $key, $counts);
        }

        foreach ($rxLines as $line) {
            writePrescription($db, $visitId, $line, $counts);
        }

        $appointmentId = null;
        if ($followUpAt !== '') {
            $appointmentId = $db->insert('appointments', [
                'patient_id'         => $patientId,
                'appointment_date'   => date('Y-m-d H:i:s', strtotime($followUpAt)),
                'department_id'      => $followUpDept ?: null,
                'doctor_id'          => getCurrentUserId(),
                'reason'             => trim($data['follow_up_reason'] ?? '') ?: 'Follow-up booked from the Doctor Station',
                'consultation_type'  => 'FOLLOWUP',
                'visit_type'         => 'Review',
                'status'             => 'scheduled',
                'created_by'         => getCurrentUserId(),
            ]);
        }

        $db->commit();

        logAudit($draft ? 'CREATE_DRAFT' : 'CREATE', 'consultations', $consultationId, null, [
            'patient_id'    => $patientId,
            'visit_id'      => $visitId,
            'lab_requests'  => $counts['lab'],
            'radiology_requests' => $counts['radiology'],
            'prescriptions' => $counts['prescriptions'],
            'appointment_id'=> $appointmentId,
            'draft'         => $draft,
        ]);

        jsonResponse([
            'success' => true,
            'consultation_id' => $consultationId,
            'visit_id'         => $visitId,
            'appointment_id'   => $appointmentId,
            'created'          => [
                'lab'          => $counts['lab'],
                'radiology'    => $counts['radiology'],
                'prescriptions'=> $counts['prescriptions'],
            ],
            'skipped_duplicates' => $counts['skipped'],
        ]);
    } catch (Exception $e) {
        $db->rollback();
        error_log("Consultation form save error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to save the consultation — nothing was changed'], 500);
    }
}

// Reuse the patient's open visit of this type, or open one. Sharing a visit
// keeps repeat consultations during one attendance on one record.
function resolveVisit($db, $patientId, $visitType, $complaints, $admission) {
    $existing = $db->fetchOne(
        "SELECT id FROM patient_visits
         WHERE patient_id = ? AND visit_type = ?
           AND status IN ('pending', 'in_progress')
         ORDER BY visit_date DESC, id DESC LIMIT 1",
        [$patientId, $visitType]
    );
    if ($existing) {
        if ($complaints !== '') {
            // Keep the first recorded complaint; overwrite only while it is blank.
            $row = $db->fetchOne("SELECT chief_complaint FROM patient_visits WHERE id = ?", [(int)$existing['id']]);
            if ($row && trim((string)$row['chief_complaint']) === '') {
                $db->update('patient_visits', ['chief_complaint' => $complaints], 'id = ?', [(int)$existing['id']]);
            }
        }
        return (int)$existing['id'];
    }

    // patient_visits.department_id is NOT NULL, so a department has to be found.
    // The doctor's own department is the sensible default; failing that, the
    // department the patient was admitted under.
    $departmentId = null;
    $dept = $db->fetchOne("SELECT department_id FROM users WHERE id = ?", [getCurrentUserId()]);
    if ($dept && $dept['department_id']) $departmentId = (int)$dept['department_id'];
    if (!$departmentId && $admission && $admission['department_id']) {
        $departmentId = (int)$admission['department_id'];
    }
    if (!$departmentId) {
        $fallback = $db->fetchOne("SELECT id FROM departments ORDER BY id LIMIT 1");
        if ($fallback) $departmentId = (int)$fallback['id'];
    }
    if (!$departmentId) return null;

    return (int)$db->insert('patient_visits', [
        'patient_id'      => $patientId,
        'visit_number'    => generateVisitNumber($patientId),
        'visit_type'      => $visitType,
        'visit_date'      => date('Y-m-d H:i:s'),
        'department_id'   => $departmentId,
        'chief_complaint' => $complaints === '' ? null : $complaints,
        'status'          => 'in_progress',
        'created_by'      => getCurrentUserId(),
    ]);
}

function insertConsultation($db, $visitId, $type, $diagnosis, $notes, $draft) {
    return (int)$db->insert('consultations', [
        'visit_id'          => $visitId,
        'doctor_id'         => getCurrentUserId(),
        'consultation_type' => $type,
        'diagnosis'         => $diagnosis === '' ? null : $diagnosis,
        'notes'             => $notes === '' ? null : $notes,
        'consultation_date' => date('Y-m-d H:i:s'),
        'status'            => $draft ? 'pending' : 'in_progress',
    ]);
}

// Editing the consultation already validated as this patient's.
function updateExistingConsultation($db, $consultationId, $type, $diagnosis, $notes) {
    $db->update('consultations', [
        'consultation_type' => $type,
        'diagnosis'         => $diagnosis === '' ? null : $diagnosis,
        'notes'             => $notes === '' ? null : $notes,
    ], 'id = ?', [$consultationId]);

    return $consultationId;
}

// Raise one investigation order. An identical order already pending on the same
// visit is left alone, so re-saving a form does not stack duplicates.
function raiseInvestigation($db, $visitId, $key, &$counts) {
    if (!isset(CONSULTATION_INVESTIGATIONS[$key])) return false;
    list($kind, $name, $bodyPart) = CONSULTATION_INVESTIGATIONS[$key];
    $doctorId = getCurrentUserId();

    if ($kind === 'lab') {
        $dupe = $db->fetchOne(
            "SELECT id FROM lab_requests
             WHERE visit_id = ? AND test_type = ? AND status = 'pending' LIMIT 1",
            [$visitId, $name]
        );
        if ($dupe) { $counts['skipped']++; return false; }
        $db->insert('lab_requests', [
            'visit_id'         => $visitId,
            'doctor_id'        => $doctorId,
            'test_type'        => $name,
            'urgency'          => 'routine',
            'status'           => 'pending',
        ]);
        $counts['lab']++;
        return true;
    }

    $dupe = $db->fetchOne(
        "SELECT id FROM radiology_requests
         WHERE visit_id = ? AND exam_type = ? AND status IN ('pending','scheduled') LIMIT 1",
        [$visitId, $name]
    );
    if ($dupe) { $counts['skipped']++; return false; }
    $db->insert('radiology_requests', [
        'visit_id'    => $visitId,
        'doctor_id'   => $doctorId,
        'exam_type'   => $name,
        'body_part'   => $bodyPart === '' ? null : $bodyPart,
        'urgency'     => 'routine',
        'status'      => 'pending',
    ]);
    $counts['radiology']++;
    return true;
}

// One drug per row, because prescriptions.drug_id is a required foreign key —
// free text could never reach the pharmacy dispensing queue.
function writePrescription($db, $visitId, $line, &$counts) {
    $drugId = (int)($line['drug_id'] ?? 0);
    if (!$drugId) return false;

    $drug = $db->fetchOne(
        "SELECT id, drug_name, quantity_in_stock, is_active FROM pharmacy_inventory WHERE id = ?",
        [$drugId]
    );
    if (!$drug) return false;
    if (!$drug['is_active'] || (int)$drug['quantity_in_stock'] <= 0) {
        $counts['skipped']++;
        return false;
    }

    $dosage     = trim((string)($line['dosage'] ?? ''));
    $frequency  = trim((string)($line['frequency'] ?? ''));
    $duration   = trim((string)($line['duration'] ?? ''));
    $instructions = trim((string)($line['instructions'] ?? ''));
    // How much the doctor intends to issue. The pharmacy caps its dispense at this
    // figure, so it has to be recorded now rather than guessed at issue time.
    $quantity   = max((int)($line['quantity'] ?? 1), 1);

    $db->insert('prescriptions', [
        'visit_id'     => $visitId,
        'doctor_id'    => getCurrentUserId(),
        'drug_id'      => $drugId,
        'dosage'       => $dosage === '' ? null : $dosage,
        'frequency'    => $frequency === '' ? null : $frequency,
        'duration'     => $duration === '' ? null : $duration,
        'quantity'     => $quantity,
        'instructions' => $instructions === '' ? null : $instructions,
        'status'       => 'pending',
    ]);
    $counts['prescriptions']++;
    return true;
}