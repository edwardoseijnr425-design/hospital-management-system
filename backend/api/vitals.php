<?php
// EHMS — Vital Signs API
//   GET                          ?visit_id=N|nurse_id=N|date_filter=today|week|month -> readings
//   POST ?action=create                                                   -> record a reading
//   PUT  ?id=N                                                          -> amend a reading
//
// Every measurement is range-checked on the way in. DECIMAL(4,1) permits a
// temperature of 999.9 and this table has held one of 54.5 with an oxygen
// saturation of 2.0, so without this a mis-keyed decimal becomes a permanent
// clinical record that the next clinician reads as fact.
//
// The bounds below are the widest values a living patient can present with, NOT
// the normal adult range. A fever of 39.5 or a saturation of 88 is a real and
// important finding, so it is stored; only impossible readings are refused.
// The normal-range highlight on the form stays advisory and is deliberately not
// mirrored here.
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

requireLogin();
requireRole(['nurse', 'doctor', 'admin']);

// Declared before the dispatcher below: PHP executes top-level statements in
// order and does not hoist const, so a const placed after the dispatch block
// would be undefined by the time the handlers run.

// field => [min, max]. Chosen to admit any genuinely observed value and reject
// only transcription faults (misplaced decimal, wrong unit, sentinel zeros).
const VITAL_FIELDS = [
    'temperature'             => [25.0, 45.0],
    'blood_pressure_systolic' => [40, 320],
    'blood_pressure_diastolic'=> [20, 200],
    'heart_rate'              => [25, 250],
    'respiratory_rate'        => [4, 80],
    'oxygen_saturation'       => [30.0, 100.0],
    'weight'                  => [0.3, 350.0],
    'height'                  => [20.0, 260.0],
];

// Labels for error messages, so a nurse is told "Temperature" rather than
// "temperature".
const VITAL_LABELS = [
    'temperature'             => 'Temperature',
    'blood_pressure_systolic' => 'BP systolic',
    'blood_pressure_diastolic'=> 'BP diastolic',
    'heart_rate'              => 'Heart rate',
    'respiratory_rate'        => 'Respiratory rate',
    'oxygen_saturation'       => 'Oxygen saturation',
    'weight'                  => 'Weight',
    'height'                  => 'Height',
];

// At least one of these must be present, otherwise the row records nothing.
const VITAL_MEASUREMENTS = [
    'temperature', 'blood_pressure_systolic', 'blood_pressure_diastolic',
    'heart_rate', 'respiratory_rate', 'oxygen_saturation',
    'weight', 'height',
];

if ($method === 'GET') {
    getVitals();
} elseif ($method === 'POST' && $action === 'create') {
    createVitals();
} elseif ($method === 'PUT') {
    updateVitals();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

function getVitals() {
    $filters = [
        'visit_id' => $_GET['visit_id'] ?? null,
        'nurse_id' => $_GET['nurse_id'] ?? null,
        'date_filter' => $_GET['date_filter'] ?? null
    ];
    
    $db = Database::getInstance();
    
    $sql = "SELECT vs.*, v.visit_number, v.patient_id, p.hospital_number,
                   CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                   u.full_name as nurse_name
            FROM vital_signs vs
            JOIN patient_visits v ON vs.visit_id = v.id
            JOIN patient_registrations p ON v.patient_id = p.id
            JOIN users u ON vs.nurse_id = u.id
            WHERE 1=1";
    
    $params = [];
    
    if ($filters['visit_id']) {
        $sql .= " AND vs.visit_id = ?";
        $params[] = $filters['visit_id'];
    }
    
    if ($filters['nurse_id']) {
        $sql .= " AND vs.nurse_id = ?";
        $params[] = $filters['nurse_id'];
    }
    
    if ($filters['date_filter']) {
        switch ($filters['date_filter']) {
            case 'today':
                $sql .= " AND DATE(vs.recorded_at) = CURDATE()";
                break;
            case 'week':
                $sql .= " AND vs.recorded_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'month':
                $sql .= " AND vs.recorded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
        }
    }
    
    $sql .= " ORDER BY vs.recorded_at DESC";
    
    $vitals = $db->fetchAll($sql, $params);
    
    jsonResponse([
        'success' => true,
        'vitals' => $vitals
    ]);
}

/**
 * Coerce and range-check posted measurements.
 *
 * Blank and absent both mean NULL — every measurement is optional, but at least
 * one must actually carry a value. Non-numeric text is refused rather than
 * silently becoming 0, which is what produced the sentinel readings this guards
 * against.
 *
 * @param array $posted   Raw request body.
 * @param bool  $partial  True when amending an existing row, where an absent
 *                        field means "leave as is" and must not be counted
 *                        towards the at-least-one rule.
 * @return array{0: array<string,float|int|null>, 1: string[]} [values, errors]
 */
function validateVitalPayload(array $posted, $partial = false) {
    $values = [];
    $errors = [];
    $measured = 0;

    foreach (VITAL_FIELDS as $field => $range) {
        if (!array_key_exists($field, $posted)) {
            // Absent on an amend: leave the stored value alone.
            if ($partial) continue;
            $values[$field] = null;
            continue;
        }

        $raw = $posted[$field];
        if (is_string($raw)) $raw = trim($raw);

        if ($raw === null || $raw === '') {
            $values[$field] = null;
            continue;
        }

        if (!is_numeric($raw)) {
            $errors[] = VITAL_LABELS[$field] . ' must be a number';
            continue;
        }

        $number = (float)$raw;
        if ($number < $range[0] || $number > $range[1]) {
            $errors[] = VITAL_LABELS[$field] . ' of ' . rtrim(rtrim(number_format($number, 1, '.', ''), '0'), '.')
                . ' is not a possible reading (expected ' . $range[0] . ' to ' . $range[1] . ')';
            continue;
        }

        $values[$field] = $number;
        $measured++;
    }

    // Only nag about the empty form when nothing else is wrong. Otherwise a
    // single out-of-range field also reports "at least one measurement", which
    // reads as though the other fields were the problem.
    if (!$partial && $measured === 0 && !$errors) {
        $errors[] = 'Record at least one measurement before saving';
    }

    // Blood pressure is meaningful only as a pair, and the diastolic figure must
    // be below the systolic one. 100/100 as recorded on one row is a pair with a
    // pulse pressure of zero, which is not a reading.
    $sys = $values['blood_pressure_systolic'] ?? null;
    $dia = $values['blood_pressure_diastolic'] ?? null;
    if ($sys !== null xor $dia !== null) {
        $errors[] = 'Record both BP systolic and BP diastolic, or leave both blank';
    }
    if ($sys !== null && $dia !== null && $dia >= $sys) {
        $errors[] = 'BP diastolic (' . (int)$dia . ') must be lower than BP systolic (' . (int)$sys . ')';
    }

    return [$values, $errors];
}

/**
 * Derive BMI from the measurements present on the row. Returns null when either
 * input is absent, so a partial amend does not blank a previously stored BMI.
 */
function deriveBmi(array $values, ?float $existing = null): ?float {
    $weight = $values['weight'] ?? null;
    $height = $values['height'] ?? null;
    if ($weight === null || $height === null || $height <= 0) return $existing;
    $metres = $height / 100;
    return round($weight / ($metres * $metres), 1);
}

function createVitals() {
    $data = getPostData();

    $visitId = (int)trim((string)($data['visit_id'] ?? ''));
    if (!$visitId) {
        jsonResponse(['error' => 'Choose the visit these readings belong to'], 400);
    }

    [$values, $errors] = validateVitalPayload($data);
    if ($errors) {
        jsonResponse(['error' => implode('. ', $errors), 'errors' => $errors], 400);
    }

    $db = Database::getInstance();

    $visit = $db->fetchOne("SELECT id FROM patient_visits WHERE id = ?", [$visitId]);
    if (!$visit) {
        jsonResponse(['error' => 'Visit not found'], 404);
    }

    // Recorded against the signed-in clinician, never a posted nurse_id.
    $vitalsData = array_merge($values, [
        'visit_id' => $visitId,
        'nurse_id' => getCurrentUserId(),
        'bmi'      => deriveBmi($values),
        'notes'    => trim((string)($data['notes'] ?? '')) === '' ? null : trim((string)$data['notes']),
    ]);

    try {
        $vitalsId = $db->insert('vital_signs', $vitalsData);
        logAudit('CREATE', 'vital_signs', $vitalsId, null, $vitalsData);
        
        jsonResponse([
            'success' => true,
            'vitals_id' => $vitalsId
        ]);
    } catch (Exception $e) {
        error_log("Vitals creation error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to record vital signs'], 500);
    }
}

function updateVitals() {
    $vitalsId = (int)($_GET['id'] ?? 0);
    
    if (!$vitalsId) {
        jsonResponse(['error' => 'Vitals ID is required'], 400);
    }
    
    $data = getPostData();
    $db = Database::getInstance();

    $existing = $db->fetchOne("SELECT * FROM vital_signs WHERE id = ?", [$vitalsId]);
    if (!$existing) {
        jsonResponse(['error' => 'Vital record not found'], 404);
    }

    // Amending an existing row: absent fields keep their stored value, and the
    // new readings are checked in the context of what is already there.
    $merged = $existing;
    foreach (VITAL_FIELDS as $field => $range) {
        if (array_key_exists($field, $data)) $merged[$field] = $data[$field];
    }
    [$values, $errors] = validateVitalPayload($merged, true);
    if ($errors) {
        jsonResponse(['error' => implode('. ', $errors), 'errors' => $errors], 400);
    }

    // visit_id and nurse_id are deliberately not writable: a reading belongs to
    // the encounter and the clinician who took it, and accepting them here would
    // let a posted body reassign an observation to another patient or nurse.
    $updateData = array_merge($values, [
        'bmi'   => deriveBmi($values, $existing['bmi'] !== null ? (float)$existing['bmi'] : null),
        'notes' => array_key_exists('notes', $data)
            ? (trim((string)$data['notes']) === '' ? null : trim((string)$data['notes']))
            : $existing['notes'],
    ]);

    try {
        $db->update('vital_signs', $updateData, 'id = ?', [$vitalsId]);
        logAudit('UPDATE', 'vital_signs', $vitalsId, $existing, $updateData);
        
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Vitals update error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to update vital signs'], 500);
    }
}