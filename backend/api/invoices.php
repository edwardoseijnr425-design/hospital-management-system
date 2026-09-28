<?php
// EHMS — Account Management (Invoices) API
//   GET  ?action=list                       -> invoices (filters: status, q)
//   GET  ?action=detail&id=N                -> invoice + line items
//   GET  ?action=stats                      -> totals / pending / paid / outstanding
//   POST ?action=create                     -> create invoice + items
//   PUT  ?action=status&id=N                -> update invoice status
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

requireLogin();

if ($method === 'GET' && $action === 'detail') {
    invoiceDetail();
} elseif ($method === 'GET' && $action === 'stats') {
    invoiceStats();
} elseif ($method === 'GET' && $action === 'transactions') {
    listTransactions();
} elseif ($method === 'GET' && $action === 'areas') {
    revenueAreas();
} elseif ($method === 'GET') {
    listInvoices();
} elseif ($method === 'POST' && $action === 'create') {
    createInvoice();
} elseif ($method === 'PUT' && $action === 'status') {
    updateInvoiceStatus();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

function listInvoices() {
    $db = Database::getInstance();
    $sql = "SELECT i.*,
                   CONCAT(TRIM(CONCAT(p.first_name, ' ', IFNULL(p.middle_name, ''))), ' ', p.last_name) AS patient_name,
                   p.hospital_number,
                   v.visit_number,
                   IFNULL(s.name, '') AS sponsor_name,
                   (SELECT COUNT(*) FROM billing_items bi WHERE bi.invoice_id = i.id) AS item_count
            FROM invoices i
            JOIN patient_registrations p ON p.id = i.patient_id
            LEFT JOIN patient_visits v ON v.id = i.visit_id
            LEFT JOIN sponsors s ON s.id = i.sponsor_id
            WHERE 1=1";
    $params = [];

    if (!empty($_GET['status'])) {
        $sql .= " AND i.status = ?";
        $params[] = $_GET['status'];
    }
    if (!empty($_GET['q'])) {
        $sql .= " AND (p.first_name LIKE ? OR p.last_name LIKE ? OR p.hospital_number LIKE ? OR i.invoice_number LIKE ?)";
        $like = '%' . $_GET['q'] . '%';
        $params = array_merge($params, [$like, $like, $like, $like]);
    }
    $sql .= " ORDER BY i.created_at DESC";
    $rows = $db->fetchAll($sql, $params);
    jsonResponse(['success' => true, 'invoices' => $rows]);
}

function invoiceDetail() {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'Invoice ID required'], 400);

    $db = Database::getInstance();
    $invoice = $db->fetchOne("SELECT i.*, p.first_name as patient_first, p.last_name as patient_last FROM invoices i JOIN patient_registrations p ON p.id=i.patient_id WHERE i.id = ?", [$id]);
    if (!$invoice) jsonResponse(['error' => 'Invoice not found'], 404);

    $items = $db->fetchAll("SELECT * FROM billing_items WHERE invoice_id = ?", [$id]);
    jsonResponse(['success' => true, 'invoice' => $invoice, 'items' => $items]);
}

function invoiceStats() {
    $db = Database::getInstance();
    $stats = [
        'total_revenue' => (float)($db->fetchOne("SELECT COALESCE(SUM(net_amount),0) t FROM invoices WHERE status = 'paid'")['t'] ?? 0),
        'pending'       => (int)($db->fetchOne("SELECT COUNT(*) c FROM invoices WHERE status IN ('pending','partial')")['c'] ?? 0),
        'outstanding'   => (float)($db->fetchOne("SELECT COALESCE(SUM(net_amount),0) t FROM invoices WHERE status IN ('pending','partial')")['t'] ?? 0),
        'paid_count'    => (int)($db->fetchOne("SELECT COUNT(*) c FROM invoices WHERE status = 'paid'")['c'] ?? 0),
        'total_count'   => (int)($db->fetchOne("SELECT COUNT(*) c FROM invoices")['c'] ?? 0),
    ];
    jsonResponse(['success' => true, 'stats' => $stats]);
}

function createInvoice() {
    $data = getPostData();

    $required = ['patient_id', 'items'];
    $errors = validateRequired($data, $required);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $items = is_array($data['items']) ? $data['items'] : [];
    if (empty($items)) {
        jsonResponse(['error' => 'At least one line item is required'], 400);
    }

    $db = Database::getInstance();

    $total = 0;
    foreach ($items as $item) {
        $qty = max((int)($item['quantity'] ?? 1), 1);
        $price = (float)($item['unit_price'] ?? 0);
        $total += $qty * $price;
    }
    $discount = (float)($data['discount_amount'] ?? 0);
    $tax = (float)($data['tax_amount'] ?? 0);
    $net = max($total - $discount + $tax, 0);

    $status = in_array($data['status'] ?? '', ['draft', 'pending', 'partial', 'paid', 'cancelled'], true) ? $data['status'] : 'pending';

    $db = Database::getInstance();

    // invoices.visit_id is NOT NULL — resolve from payload or the patient's latest visit
    $visitId = !empty($data['visit_id']) ? (int)$data['visit_id'] : null;
    if ($visitId === null) {
        $latest = $db->fetchOne(
            "SELECT id FROM patient_visits WHERE patient_id = ? ORDER BY visit_date DESC LIMIT 1",
            [(int)$data['patient_id']]
        );
        $visitId = $latest ? (int)$latest['id'] : null;
    }
    if ($visitId === null) {
        jsonResponse(['error' => 'This patient has no visit on record. Create a visit before invoicing.'], 400);
    }

    try {
        $db->beginTransaction();

        $invoiceNumber = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
        $invoiceId = $db->insert('invoices', [
            'visit_id'        => $visitId,
            'invoice_number'  => $invoiceNumber,
            'patient_id'      => (int)$data['patient_id'],
            'total_amount'    => $total,
            'discount_amount' => $discount,
            'tax_amount'      => $tax,
            'net_amount'      => $net,
            'sponsor_id'      => !empty($data['sponsor_id']) ? (int)$data['sponsor_id'] : null,
            'status'          => $status,
            'created_by'      => getCurrentUserId(),
        ]);

        foreach ($items as $item) {
            $qty = max((int)($item['quantity'] ?? 1), 1);
            $price = (float)($item['unit_price'] ?? 0);
            $type = in_array($item['item_type'] ?? '', ['consultation', 'procedure', 'drug', 'lab_test', 'radiology', 'bed', 'other'], true)
                ? $item['item_type'] : 'other';
            $db->insert('billing_items', [
                'invoice_id'   => $invoiceId,
                'item_type'    => $type,
                'item_id'      => !empty($item['item_id']) ? (int)$item['item_id'] : 0,
                'description'  => trim($item['description'] ?? ''),
                'quantity'     => $qty,
                'unit_price'   => $price,
                'total_price'  => $qty * $price,
            ]);
        }

        $db->commit();
        logAudit('CREATE', 'invoices', $invoiceId, null, ['number' => $invoiceNumber, 'net' => $net]);
        jsonResponse(['success' => true, 'invoice_id' => $invoiceId, 'invoice_number' => $invoiceNumber]);
    } catch (Exception $e) {
        $db->rollback();
        error_log("Invoice create error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to create invoice'], 500);
    }
}

function updateInvoiceStatus() {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'Invoice ID required'], 400);

    $data = getPostData();
    $status = $data['status'] ?? null;
    $valid = ['draft', 'pending', 'partial', 'paid', 'cancelled'];
    if (!in_array($status, $valid, true)) {
        jsonResponse(['error' => 'Invalid status'], 400);
    }

    $db = Database::getInstance();
    $old = $db->fetchOne("SELECT * FROM invoices WHERE id = ?", [$id]);
    if (!$old) jsonResponse(['error' => 'Invoice not found'], 404);

    try {
        $updateData = ['status' => $status];
        if (array_key_exists('payment_method', $data)) {
            $method = trim((string)$data['payment_method']);
            $updateData['payment_method'] = $method !== '' ? substr($method, 0, 40) : null;
        }
        $db->update('invoices', $updateData, 'id = ?', [$id]);
        logAudit('UPDATE', 'invoices', $id, $old, $updateData);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        jsonResponse(['error' => 'Failed to update invoice'], 500);
    }
}

// Money paid log: paid invoices with patient + revenue areas (billing item types)
function listTransactions() {
    $db = Database::getInstance();

    $sql = "SELECT i.id, i.invoice_number, i.net_amount, i.status, i.payment_method, i.created_at, i.updated_at,
                   CONCAT(TRIM(CONCAT(p.first_name, ' ', IFNULL(p.middle_name, ''))), ' ', p.last_name) AS patient_name,
                   p.hospital_number,
                   (SELECT COUNT(*) FROM billing_items bi WHERE bi.invoice_id = i.id) AS item_count,
                   (SELECT GROUP_CONCAT(DISTINCT bi.item_type ORDER BY bi.item_type SEPARATOR ', ')
                      FROM billing_items bi WHERE bi.invoice_id = i.id) AS areas
            FROM invoices i
            JOIN patient_registrations p ON p.id = i.patient_id
            WHERE i.status = 'paid'";
    $params = [];

    if (!empty($_GET['date_from'])) {
        $sql .= " AND DATE(i.updated_at) >= ?";
        $params[] = $_GET['date_from'];
    }
    if (!empty($_GET['date_to'])) {
        $sql .= " AND DATE(i.updated_at) <= ?";
        $params[] = $_GET['date_to'];
    }
    if (!empty($_GET['q'])) {
        $sql .= " AND (i.invoice_number LIKE ? OR p.hospital_number LIKE ?
                  OR p.first_name LIKE ? OR p.last_name LIKE ?)";
        $like = '%' . $_GET['q'] . '%';
        array_push($params, $like, $like, $like, $like);
    }

    $sql .= " ORDER BY i.updated_at DESC LIMIT 200";

    $rows = $db->fetchAll($sql, $params);

    $total = 0;
    foreach ($rows as &$r) {
        $total += (float)$r['net_amount'];
    }
    unset($r);

    jsonResponse([
        'success' => true,
        'transactions' => $rows,
        'total_paid' => round($total, 2)
    ]);
}

// Revenue by area (billing item type) aggregated from paid invoices
function revenueAreas() {
    $db = Database::getInstance();

    $sql = "SELECT bi.item_type,
                   COUNT(DISTINCT bi.invoice_id) AS transactions,
                   COUNT(*) AS items_sold,
                   COALESCE(SUM(bi.total_price), 0) AS revenue
            FROM billing_items bi
            JOIN invoices i ON i.id = bi.invoice_id
            WHERE i.status = 'paid'";
    $params = [];

    if (!empty($_GET['date_from'])) {
        $sql .= " AND DATE(i.updated_at) >= ?";
        $params[] = $_GET['date_from'];
    }
    if (!empty($_GET['date_to'])) {
        $sql .= " AND DATE(i.updated_at) <= ?";
        $params[] = $_GET['date_to'];
    }

    $sql .= " GROUP BY bi.item_type ORDER BY revenue DESC";

    $rows = $db->fetchAll($sql, $params);

    $labels = [
        'consultation' => 'Consultation',
        'procedure'    => 'Procedure / Theatre',
        'drug'         => 'Drug / Pharmacy',
        'lab_test'     => 'Laboratory',
        'radiology'    => 'Radiology',
        'bed'          => 'Bed / Admission',
        'other'        => 'Other Services'
    ];

    $out = [];
    $totalRevenue = 0.0;
    $totalTransactions = 0;
    foreach ($rows as $row) {
        $row['label'] = $labels[$row['item_type']] ?? ucfirst(str_replace('_', ' ', $row['item_type']));
        $row['revenue'] = round((float)$row['revenue'], 2);
        $out[] = $row;
        $totalRevenue += (float)$row['revenue'];
        $totalTransactions += (int)$row['transactions'];
    }

    jsonResponse([
        'success' => true,
        'areas' => $out,
        'totals' => ['revenue' => round($totalRevenue, 2), 'transactions' => $totalTransactions]
    ]);
}