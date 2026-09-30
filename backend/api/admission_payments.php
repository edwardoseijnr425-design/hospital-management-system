<?php
// EHMS — Admission Payments API (in-patient deposits & final clearance)
//   GET  ?action=list&admission_id=N&payment_type=X   -> payments recorded against a stay
//   GET  ?action=ipd_billing                          -> per-admission bill summary:
//                                                          total billed, deposits paid, balance
//   GET  ?action=detail&id=N                         -> one payment
//   POST ?action=record                               -> record a DEPOSIT or CLEARANCE_PAYMENT
//
// Money received is recorded here; what is owed stays on invoices. A clearance
// payment settles the balance and marks the linked invoices paid, but it never
// discharges the patient — discharge stays the real discharge flow's job, so a
// bill can be settled while the patient is still an in-patient.
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'ipd_billing';

requireLogin();
// Money is taken by ward staff and settled by accounts, so both are allowed —
// same split as the invoices API, which is login-only.
requireRole(['admin', 'doctor', 'nurse', 'account', 'revenue']);

// Declared before the dispatcher below: PHP executes top-level statements in
// order and does not hoist const, so a const placed after the dispatch block
// would be undefined by the time the handlers run.
const PAYMENT_TYPES = ['DEPOSIT', 'CLEARANCE_PAYMENT'];

if ($method === 'GET' && $action === 'list') {
    listPayments();
} elseif ($method === 'GET' && $action === 'detail') {
    paymentDetail();
} elseif ($method === 'GET') {
    ipdBilling();
} elseif ($method === 'POST' && $action === 'record') {
    recordPayment();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

function listPayments() {
    $db = Database::getInstance();
    $admissionId = (int)($_GET['admission_id'] ?? 0);
    if (!$admissionId) jsonResponse(['error' => 'Admission ID is required'], 400);

    $type = strtoupper(trim($_GET['payment_type'] ?? ''));
    $sql = "SELECT pay.*, u.full_name AS recorded_by_name, i.invoice_number
            FROM admission_payments pay
            LEFT JOIN users u ON u.id = pay.recorded_by
            LEFT JOIN invoices i ON i.id = pay.invoice_id
            WHERE pay.admission_id = ?";
    $params = [$admissionId];
    if (in_array($type, PAYMENT_TYPES, true)) {
        $sql .= " AND pay.payment_type = ?";
        $params[] = $type;
    }
    $sql .= " ORDER BY pay.created_at DESC, pay.id DESC";

    $rows = $db->fetchAll($sql, $params);
    jsonResponse(['success' => true, 'payments' => $rows]);
}

function paymentDetail() {
    $id = (int)($_GET['id'] ?? 0);
    if (!$id) jsonResponse(['error' => 'Payment ID is required'], 400);

    $db = Database::getInstance();
    $row = $db->fetchOne(
        "SELECT pay.*, u.full_name AS recorded_by_name, i.invoice_number
         FROM admission_payments pay
         LEFT JOIN users u ON u.id = pay.recorded_by
         LEFT JOIN invoices i ON i.id = pay.invoice_id
         WHERE pay.id = ?",
        [$id]
    );
    if (!$row) jsonResponse(['error' => 'Payment not found'], 404);

    jsonResponse(['success' => true, 'payment' => $row]);
}

// The station table: one row per in-patient with the bill, what has been paid,
// and what is outstanding. Total billed comes from the patient's invoices
// raised against this stay's visit window; paid comes from admission_payments.
function ipdBilling() {
    $db = Database::getInstance();
    $q = trim($_GET['q'] ?? '');

    $sql = "SELECT a.id AS admission_id, a.admission_code, a.patient_id,
                   a.admission_date, a.admission_type, a.status,
                   a.discharged_at, a.final_diagnosis,
                   CONCAT_WS(' ', p.first_name, NULLIF(p.middle_name, ''), p.last_name) AS patient_name,
                   p.hospital_number, p.gender,
                   TIMESTAMPDIFF(YEAR, p.date_of_birth, CURDATE()) AS age,
                   w.ward_name, b.bed_number,
                   COALESCE((
                       SELECT SUM(i.net_amount) FROM invoices i
                       WHERE i.patient_id = a.patient_id
                         AND i.status <> 'cancelled'
                         AND i.visit_id IN (
                             SELECT v.id FROM patient_visits v
                             WHERE v.patient_id = a.patient_id
                               AND v.visit_date >= (
                                   SELECT MIN(v2.visit_date) FROM patient_visits v2 WHERE v2.patient_id = a.patient_id
                               )
                         )
                   ), 0) AS total_billed,
                   COALESCE((
                       SELECT SUM(pay.amount) FROM admission_payments pay
                       WHERE pay.admission_id = a.id
                   ), 0) AS total_paid
            FROM admissions a
            JOIN patient_registrations p ON p.id = a.patient_id
            JOIN wards w ON w.id = a.ward_id
            JOIN beds b ON b.id = a.bed_id
            WHERE a.status = 'Admitted'";
    $params = [];

    if ($q !== '') {
        $like = '%' . $q . '%';
        $sql .= " AND (p.hospital_number LIKE ? OR p.first_name LIKE ? OR p.last_name LIKE ?
                        OR a.admission_code LIKE ? OR b.bed_number LIKE ? OR w.ward_name LIKE ?)";
        array_push($params, $like, $like, $like, $like, $like, $like);
    }
    $sql .= " ORDER BY a.admission_date DESC, a.id DESC";

    $rows = $db->fetchAll($sql, $params);

    $totals = ['billed' => 0.0, 'paid' => 0.0, 'balance' => 0.0];
    foreach ($rows as &$r) {
        $r['total_billed'] = round((float)$r['total_billed'], 2);
        $r['total_paid']   = round((float)$r['total_paid'], 2);
        $r['balance']      = round(max($r['total_billed'] - $r['total_paid'], 0), 2);
        $r['settled']      = $r['total_billed'] > 0 && $r['balance'] <= 0.005;
        $r['no_bill']      = $r['total_billed'] <= 0;

        $totals['billed']  += $r['total_billed'];
        $totals['paid']    += $r['total_paid'];
        $totals['balance'] += $r['balance'];
    }
    unset($r);

    $totals['billed']  = round($totals['billed'], 2);
    $totals['paid']    = round($totals['paid'], 2);
    $totals['balance'] = round($totals['balance'], 2);

    jsonResponse(['success' => true, 'rows' => $rows, 'totals' => $totals]);
}

function recordPayment() {
    $data = getPostData();
    $errors = validateRequired($data, ['admission_id', 'amount', 'payment_type']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $db = Database::getInstance();

    $admission = $db->fetchOne("SELECT * FROM admissions WHERE id = ?", [(int)$data['admission_id']]);
    if (!$admission) jsonResponse(['error' => 'Admission not found'], 404);
    if (strtolower($admission['status']) !== 'admitted') {
        jsonResponse(['error' => 'This admission is already discharged — its bill is closed'], 400);
    }

    $type = strtoupper(trim($data['payment_type']));
    if (!in_array($type, PAYMENT_TYPES, true)) {
        jsonResponse(['error' => 'Select a valid payment type'], 400);
    }

    $amount = round((float)$data['amount'], 2);
    if ($amount <= 0 || $amount > 9999999.99) {
        jsonResponse(['error' => 'Enter a valid amount greater than zero'], 400);
    }

    // Optional link to the invoice this money settles. It must be one of this
    // patient's invoices, otherwise the payment would be recorded against an
    // unrelated bill.
    $invoiceId = !empty($data['invoice_id']) ? (int)$data['invoice_id'] : null;
    if ($invoiceId) {
        $invoice = $db->fetchOne("SELECT * FROM invoices WHERE id = ? AND patient_id = ?", [$invoiceId, (int)$admission['patient_id']]);
        if (!$invoice) jsonResponse(['error' => 'Invoice not found for this patient'], 404);
        if ($invoice['status'] === 'cancelled') {
            jsonResponse(['error' => 'That invoice is cancelled — cannot pay against it'], 400);
        }
        if ($invoice['status'] === 'paid') {
            jsonResponse(['error' => 'That invoice is already settled'], 409);
        }
    }

    $method = trim($data['payment_method'] ?? '');
    if (strlen($method) > 40) $method = substr($method, 0, 40);
    $notes = trim($data['notes'] ?? '');
    if (strlen($notes) > 255) $notes = substr($notes, 0, 255);

    try {
        $db->beginTransaction();

        $paymentId = $db->insert('admission_payments', [
            'admission_id'   => (int)$admission['id'],
            'invoice_id'     => $invoiceId,
            'amount'         => $amount,
            'currency'       => 'GHS',
            'payment_type'   => $type,
            'payment_method' => $method !== '' ? $method : null,
            'notes'          => $notes !== '' ? $notes : null,
            'recorded_by'    => getCurrentUserId(),
        ]);

        // Settle invoices this payment covers. A deposit only marks the target
        // invoice partial; a clearance payment marks every open invoice for the
        // stay paid once the money received has cleared the total owed.
        $paidTotal = (float)($db->fetchOne(
            "SELECT COALESCE(SUM(amount), 0) AS t FROM admission_payments WHERE admission_id = ?",
            [(int)$admission['id']]
        )['t'] ?? 0);

        $billedTotal = (float)($db->fetchOne(
            "SELECT COALESCE(SUM(i.net_amount), 0) AS t
             FROM invoices i
             WHERE i.patient_id = ? AND i.status <> 'cancelled'
               AND i.visit_id IN (
                   SELECT v.id FROM patient_visits v WHERE v.patient_id = ?
               )",
            [(int)$admission['patient_id'], (int)$admission['patient_id']]
        )['t'] ?? 0);

        if ($invoiceId) {
            $target = $db->fetchOne("SELECT * FROM invoices WHERE id = ?", [$invoiceId]);
            $targetStatus = $billedTotal > 0 && $paidTotal >= $billedTotal ? 'paid' : 'partial';
            $db->update('invoices', [
                'status'         => $targetStatus,
                'payment_method' => $method !== '' ? $method : $target['payment_method'],
            ], 'id = ?', [$invoiceId]);
        } elseif ($billedTotal > 0 && $paidTotal >= $billedTotal) {
            $db->update('invoices', ['status' => 'paid'],
                "patient_id = ? AND status IN ('pending','partial')", [(int)$admission['patient_id']]);
        }

        $db->commit();

        logAudit('CREATE', 'admission_payments', $paymentId, null, [
            'admission_id' => (int)$admission['id'],
            'amount'       => $amount,
            'payment_type' => $type,
            'currency'     => 'GHS',
        ]);

        jsonResponse([
            'success'      => true,
            'payment_id'   => $paymentId,
            'total_paid'   => round($paidTotal, 2),
            'total_billed' => round($billedTotal, 2),
            'balance'      => round(max($billedTotal - $paidTotal, 0), 2),
        ]);
    } catch (Exception $e) {
        $db->rollback();
        error_log("Admission payment error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to record the payment'], 500);
    }
}
