<?php
// Prescriptions API.
//   GET  ?action=queue[&q=..]            -> prescriptions awaiting dispensing (the pharmacy worklist)
//   GET  ?patient_id=N | visit_id=N | id=N -> prescriptions for a patient / visit / one record
//   POST ?action=dispense                -> issue a prescription, decrementing stock
//
// Issue (dispense) is deliberately restricted to the pharmacy and admin roles: a
// doctor may prescribe, but handing the drug over is a pharmacy act, and it is the
// only thing in the system that reduces pharmacy_inventory.quantity_in_stock.
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

requireLogin();

if ($method === 'GET') {
    if ($action === 'queue') {
        requireRole(['nurse', 'doctor', 'pharmacy', 'admin']);
        listDispensingQueue();
    } elseif ($action === 'dispensed') {
        requireRole(['nurse', 'doctor', 'pharmacy', 'admin']);
        listDispensed();
    } else {
        requireRole(['nurse', 'doctor', 'admin']);
        listPrescriptions();
    }
} elseif ($method === 'POST' && $action === 'dispense') {
    requireRole(['pharmacy', 'admin']);
    dispensePrescription();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

/**
 * Prescriptions scoped to a patient, visit or single id.
 */
function listPrescriptions() {
    $db = Database::getInstance();

    $sql = prescriptionSelect() . "
        WHERE 1=1";
    $params = [];

    if (!empty($_GET['id'])) {
        $sql .= " AND pr.id = ?";
        $params[] = (int)$_GET['id'];
    } elseif (!empty($_GET['visit_id'])) {
        $sql .= " AND pr.visit_id = ?";
        $params[] = (int)$_GET['visit_id'];
    } elseif (!empty($_GET['patient_id'])) {
        $sql .= " AND v.patient_id = ?";
        $params[] = (int)$_GET['patient_id'];
    } else {
        jsonResponse(['error' => 'Provide patient_id, visit_id or id'], 400);
    }

    if (!empty($_GET['status']) && in_array($_GET['status'], ['pending', 'dispensed', 'cancelled'], true)) {
        $sql .= " AND pr.status = ?";
        $params[] = $_GET['status'];
    }

    $sql .= " ORDER BY pr.prescribed_at DESC";

    $rows = $db->fetchAll($sql, $params);
    jsonResponse(['success' => true, 'prescriptions' => $rows]);
}

/**
 * Shared projection. patient_name is built the same way admissions.php builds it so
 * the same patient reads identically across modules.
 */
function prescriptionSelect($extraColumns = '') {
    return "SELECT pr.*,
                   pi.drug_name,
                   pi.generic_name,
                   pi.unit,
                   pi.quantity_in_stock,
                   u.full_name AS doctor_name,
                   v.visit_number,
                   v.patient_id,
                   p.hospital_number,
                   CONCAT_WS(' ', p.title, p.first_name, p.middle_name, p.last_name) AS patient_name"
        . ($extraColumns !== '' ? ",\n                   " . $extraColumns : '')
        . "
            FROM prescriptions pr
            JOIN pharmacy_inventory pi ON pi.id = pr.drug_id
            JOIN users u ON u.id = pr.doctor_id
            JOIN patient_visits v ON v.id = pr.visit_id
            JOIN patient_registrations p ON p.id = v.patient_id";
}

/**
 * The pharmacy worklist: everything still to be handed over, newest first.
 * Search covers the patient, the hospital number and the drug.
 */
function listDispensingQueue() {
    $db = Database::getInstance();
    $q = trim((string)($_GET['q'] ?? ''));

    $sql = prescriptionSelect() . "
        WHERE pr.status = 'pending'";
    $params = [];

    if ($q !== '') {
        $sql .= " AND (p.first_name LIKE ? OR p.last_name LIKE ?
                      OR p.hospital_number LIKE ? OR pi.drug_name LIKE ?
                      OR pi.drug_code LIKE ?)";
        $like = '%' . $q . '%';
        array_push($params, $like, $like, $like, $like, $like);
    }

    $sql .= " ORDER BY pr.prescribed_at ASC, pr.id ASC";

    $rows = $db->fetchAll($sql, $params);

    // Age the queue so a prescription sitting for days is visible as such.
    foreach ($rows as &$row) {
        $row['waiting_days'] = waitingDays($row['prescribed_at']);
        $row['quantity_dispensed'] = 0;
    }
    unset($row);

    jsonResponse(['success' => true, 'prescriptions' => $rows]);
}

/**
 * Recently issued drugs - the audit trail a pharmacist reads to answer "was this
 * already given out?", and the reason a repeat issue is caught rather than doubled.
 */
function listDispensed() {
    $db = Database::getInstance();

    // The dispensing transaction is joined in so the pharmacist's own record of
    // what was issued, by whom, and the counselling note are readable back on
    // the audit trail - not just captured and forgotten.
    $sql = prescriptionSelect("md.quantity_dispensed AS dispensed_quantity,
                                   md.notes AS dispensing_notes,
                                   md.dispensed_at,
                                   IFNULL(pu.full_name, '') AS pharmacist_name") . "
            JOIN medication_dispensing md ON md.prescription_id = pr.id
            LEFT JOIN users pu ON pu.id = md.pharmacist_id
        WHERE pr.status = 'dispensed'
        ORDER BY md.dispensed_at DESC
        LIMIT 200";

    $rows = $db->fetchAll($sql, []);

    foreach ($rows as &$row) {
        $row['waiting_days'] = 0;
    }
    unset($row);

    jsonResponse(['success' => true, 'prescriptions' => $rows]);
}

function waitingDays($timestamp) {
    if (!$timestamp) return 0;
    $then = strtotime($timestamp);
    if ($then === false) return 0;
    return max(0, (int)floor((time() - $then) / 86400));
}

/**
 * Issue a prescription.
 *
 * Everything that must be true of the stock and the prescription is checked inside
 * one transaction with the inventory row locked, so two pharmacists dispensing the
 * same last tablet cannot both succeed.
 */
function dispensePrescription() {
    $data = getPostData();

    $prescriptionId = (int)($data['prescription_id'] ?? 0);
    $quantity = (int)($data['quantity'] ?? 0);
    $batchNumber = trim((string)($data['batch_number'] ?? ''));
    $expiry = trim((string)($data['expiry_date'] ?? ''));
    $notes = trim((string)($data['notes'] ?? ''));

    if (!$prescriptionId) jsonResponse(['error' => 'Prescription ID is required'], 400);
    if ($quantity < 1) jsonResponse(['error' => 'Quantity to dispense must be at least 1'], 400);
    if ($expiry !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $expiry)) {
        jsonResponse(['error' => 'Expiry date must be a valid date'], 400);
    }

    $db = Database::getInstance();

    try {
        $db->beginTransaction();

        // Lock the prescription row for the duration so a double submit cannot
        // dispense the same line twice.
        $rx = $db->fetchOne(
            "SELECT pr.*, pi.drug_name, pi.unit, pi.quantity_in_stock, pi.is_active,
                    CONCAT_WS(' ', p.title, p.first_name, p.middle_name, p.last_name) AS patient_name,
                    p.hospital_number
             FROM prescriptions pr
             JOIN pharmacy_inventory pi ON pi.id = pr.drug_id
             JOIN patient_visits v ON v.id = pr.visit_id
             JOIN patient_registrations p ON p.id = v.patient_id
             WHERE pr.id = ?
             FOR UPDATE",
            [$prescriptionId]
        );

        if (!$rx) {
            $db->rollback();
            jsonResponse(['error' => 'Prescription not found'], 404);
        }

        if ($rx['status'] !== 'pending') {
            $db->rollback();
            jsonResponse([
                'error' => 'This prescription is already ' . $rx['status'] . ' - it cannot be issued again'
            ], 409);
        }

        if (!$rx['is_active']) {
            $db->rollback();
            jsonResponse(['error' => $rx['drug_name'] . ' is no longer an active stocked item'], 409);
        }

        $prescribed = max((int)$rx['quantity'], 1);
        if ($quantity > $prescribed) {
            $db->rollback();
            jsonResponse([
                'error' => 'The doctor prescribed ' . $prescribed . ' ' . ($rx['unit'] ?: 'unit(s)')
                    . ' - ' . $quantity . ' cannot be issued against it'
            ], 400);
        }

        if ($quantity > (int)$rx['quantity_in_stock']) {
            $db->rollback();
            jsonResponse([
                'error' => 'Only ' . (int)$rx['quantity_in_stock'] . ' ' . ($rx['unit'] ?: 'unit(s)')
                    . ' of ' . $rx['drug_name'] . ' left in stock'
            ], 409);
        }

        $newQty = (int)$rx['quantity_in_stock'] - $quantity;

        // The stock the pharmacy hands over must be the batch they record, so an
        // omitted batch falls back to the stock on hand rather than staying blank.
        if ($batchNumber === '') $batchNumber = (string)($rx['batch_number'] ?? '');
        if ($expiry === '' && !empty($rx['expiry_date'])) $expiry = $rx['expiry_date'];

        $db->update('pharmacy_inventory', ['quantity_in_stock' => $newQty], 'id = ?', [$rx['drug_id']]);

        $dispenseId = $db->insert('medication_dispensing', [
            'prescription_id'  => $prescriptionId,
            'pharmacist_id'    => getCurrentUserId(),
            'quantity_dispensed' => $quantity,
            'batch_number'     => $batchNumber !== '' ? $batchNumber : null,
            'expiry_date'      => $expiry !== '' ? $expiry : null,
            'notes'            => $notes !== '' ? $notes : null
        ]);

        $db->update('prescriptions', ['status' => 'dispensed'], 'id = ?', [$prescriptionId]);

        logAudit(
            'DISPENSE',
            'medication_dispensing',
            $dispenseId,
            null,
            [
                'prescription_id'  => $prescriptionId,
                'drug_name'        => $rx['drug_name'],
                'patient_name'     => $rx['patient_name'],
                'quantity_dispensed' => $quantity,
                'quantity_prescribed' => $prescribed,
                'stock_after'      => $newQty,
                'batch_number'     => $batchNumber
            ]
        );

        $db->commit();
    } catch (Exception $e) {
        $db->rollback();
        jsonResponse(['error' => 'Failed to dispense - nothing was issued'], 500);
    }

    jsonResponse([
        'success' => true,
        'dispensing_id' => $dispenseId,
        'quantity_dispensed' => $quantity,
        'quantity_in_stock' => $newQty
    ]);
}