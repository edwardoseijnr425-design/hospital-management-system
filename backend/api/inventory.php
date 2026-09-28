<?php
// EHMS — Inventory Management API (Medical/General Store Stock + Requisitions)
//   GET  ?action=list&status=all|low|out    -> stock list (includes in_stock + store_type)
//   GET  ?action=stats                      -> totals / low stock / out of stock
//   GET  ?action=requisitions&status=ALL|PENDING|APPROVED|REJECTED -> requisition list
//   POST ?action=create                     -> add stock item
//   POST ?action=request                    -> request stock (creates requisition)
//   PUT  ?action=reqstatus&id=N             -> approve / reject requisition
//   POST ?action=restock&id=N               -> increase quantity on hand
//   PUT  ?action=update&id=N                -> update item fields
//   DELETE ?action=delete&id=N              -> remove item
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? 'list';

requireLogin();

if ($method === 'GET' && $action === 'stats') {
    inventoryStats();
} elseif ($method === 'GET' && $action === 'requisitions') {
    listRequisitions();
} elseif ($method === 'GET') {
    listInventory();
} elseif ($method === 'POST' && $action === 'create') {
    createItem();
} elseif ($method === 'POST' && $action === 'request') {
    createRequisition();
} elseif ($method === 'POST' && $action === 'restock') {
    restockItem();
} elseif ($method === 'PUT' && $action === 'reqstatus') {
    updateRequisitionStatus();
} elseif ($method === 'PUT' && $action === 'update') {
    updateItem();
} elseif ($method === 'DELETE' && $action === 'delete') {
    deleteItem();
} else {
    jsonResponse(['error' => 'Invalid request'], 400);
}

function listInventory() {
    $db = Database::getInstance();
    $sql = "SELECT * FROM pharmacy_inventory WHERE 1=1";
    $params = [];
    $filter = $_GET['status'] ?? 'all';

    if ($filter === 'low') {
        $sql .= " AND quantity_in_stock > 0 AND quantity_in_stock <= reorder_level";
    } elseif ($filter === 'out') {
        $sql .= " AND quantity_in_stock = 0";
    }

    if (!empty($_GET['store_type'])) {
        $storeType = (strtoupper($_GET['store_type']) === 'GENERAL') ? 'GENERAL' : 'MEDICAL';
        $sql .= " AND store_type = ?";
        $params[] = $storeType;
    }

    if (!empty($_GET['q'])) {
        $sql .= " AND (drug_name LIKE ? OR generic_name LIKE ? OR drug_code LIKE ? OR category LIKE ?)";
        $like = '%' . $_GET['q'] . '%';
        array_push($params, $like, $like, $like, $like);
    }
    $sql .= " ORDER BY drug_name";
    $rows = $db->fetchAll($sql, $params);

    // Legacy-friendly alias: in_stock = quantity_in_stock
    foreach ($rows as &$row) {
        $row['in_stock'] = (int)$row['quantity_in_stock'];
    }
    jsonResponse(['success' => true, 'items' => $rows]);
}

function inventoryStats() {
    $db = Database::getInstance();
    $stats = [
        'total_items'   => (int)($db->fetchOne("SELECT COUNT(*) c FROM pharmacy_inventory WHERE is_active = 1")['c'] ?? 0),
        'total_units'   => (int)($db->fetchOne("SELECT COALESCE(SUM(quantity_in_stock),0) s FROM pharmacy_inventory WHERE is_active = 1")['s'] ?? 0),
        'low_stock'     => (int)($db->fetchOne("SELECT COUNT(*) c FROM pharmacy_inventory WHERE is_active = 1 AND quantity_in_stock > 0 AND quantity_in_stock <= reorder_level")['c'] ?? 0),
        'out_of_stock'  => (int)($db->fetchOne("SELECT COUNT(*) c FROM pharmacy_inventory WHERE is_active = 1 AND quantity_in_stock = 0")['c'] ?? 0),
        'total_categories' => (int)($db->fetchOne("SELECT COUNT(DISTINCT category) c FROM pharmacy_inventory WHERE is_active = 1 AND category <> ''")['c'] ?? 0),
    ];
    jsonResponse(['success' => true, 'stats' => $stats]);
}

function listRequisitions() {
    $db = Database::getInstance();
    $status = strtoupper($_GET['status'] ?? 'ALL');

    $sql = "SELECT r.*, u.full_name AS requested_by_name, IFNULL(d.name, '') AS department, IFNULL(w.ward_name, '') AS ward_name
            FROM inventory_requisitions r
            LEFT JOIN users u ON u.id = r.requested_by
            LEFT JOIN departments d ON d.id = r.department_id
            LEFT JOIN wards w ON w.id = r.ward_id
            WHERE 1=1";
    $params = [];

    if ($status !== 'ALL') {
        $sql .= " AND r.status = ?";
        $params[] = ucfirst(strtolower($status));
    }
    $sql .= " ORDER BY r.created_at DESC";
    $rows = $db->fetchAll($sql, $params);

    // Attach line items
    foreach ($rows as &$row) {
        $row['items'] = $db->fetchAll(
            "SELECT ri.id, ri.item_id, ri.requested_qty, i.drug_name, i.store_type
             FROM requisition_items ri
             JOIN pharmacy_inventory i ON i.id = ri.item_id
             WHERE ri.requisition_id = ?", [$row['id']]);
    }
    jsonResponse(['success' => true, 'requisitions' => $rows]);
}

function createItem() {
    $data = getPostData();
    $required = ['drug_name', 'drug_code'];
    $errors = validateRequired($data, $required);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }

    $storeType = (strtoupper($data['store_type'] ?? '') === 'GENERAL') ? 'GENERAL' : 'MEDICAL';

    $db = Database::getInstance();
    try {
        $id = $db->insert('pharmacy_inventory', [
            'drug_name'      => trim($data['drug_name']),
            'generic_name'   => trim($data['generic_name'] ?? ''),
            'drug_code'      => trim($data['drug_code']),
            'store_type'     => $storeType,
            'category'       => trim($data['category'] ?? ''),
            'manufacturer'   => trim($data['manufacturer'] ?? ''),
            'batch_number'   => trim($data['batch_number'] ?? ''),
            'expiry_date'    => !empty($data['expiry_date']) ? $data['expiry_date'] : null,
            'quantity_in_stock' => max((int)($data['quantity_in_stock'] ?? 0), 0),
            'reorder_level'  => max((int)($data['reorder_level'] ?? 10), 0),
            'unit'           => trim($data['unit'] ?? ''),
            'storage_location' => trim($data['storage_location'] ?? ''),
            'is_active'      => true,
        ]);
        logAudit('CREATE', 'pharmacy_inventory', $id, null, ['drug_name' => $data['drug_name'], 'store_type' => $storeType]);
        jsonResponse(['success' => true, 'item_id' => $id]);
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            jsonResponse(['error' => 'Drug code already exists'], 409);
        }
        error_log("Inventory create error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to add item'], 500);
    }
}

function createRequisition() {
    $data = getPostData();

    $storeType = (strtoupper($data['store_type'] ?? '') === 'GENERAL') ? 'GENERAL' : 'MEDICAL';

    $itemIds = isset($data['item_id']) ? (array)$data['item_id'] : [];
    if (isset($data['requested_qty'])) {
        $qtys = (array)$data['requested_qty'];
    } elseif (isset($data['quantity'])) {
        $qtys = (array)$data['quantity'];
    } else {
        $qtys = [];
    }

    if (empty($itemIds)) {
        jsonResponse(['error' => 'At least one stock item is required'], 400);
    }

    $db = Database::getInstance();

    $requester = $db->fetchOne("SELECT id, department_id FROM users WHERE id = ?", [getCurrentUserId()]);
    $departmentId = !empty($data['department_id']) ? (int)$data['department_id'] : ($requester['department_id'] ?? null);
    $wardId = !empty($data['ward_id']) ? (int)$data['ward_id'] : null;

    // Old EHMS req code format: REQ-M-YYYYMMDDHHMMSS / REQ-G-...
    $reqCode = 'REQ-' . strtoupper(substr($storeType, 0, 1)) . '-' . date('YmdHis');

    try {
        $reqId = $db->insert('inventory_requisitions', [
            'req_code'      => $reqCode,
            'store_type'    => $storeType,
            'department_id' => $departmentId,
            'ward_id'       => $wardId,
            'requested_by'  => getCurrentUserId(),
            'remarks'       => trim($data['remarks'] ?? ''),
            'status'        => 'Pending',
        ]);

        foreach ($itemIds as $i => $itemId) {
            $item = $db->fetchOne("SELECT id, drug_name FROM pharmacy_inventory WHERE id = ? AND is_active = 1", [(int)$itemId]);
            if (!$item) continue;
            $qty = max((int)($qtys[$i] ?? 1), 1);
            $db->insert('requisition_items', [
                'requisition_id' => $reqId,
                'item_id'        => (int)$itemId,
                'requested_qty'  => $qty,
            ]);
        }

        logAudit('CREATE', 'inventory_requisitions', $reqId, null, ['req_code' => $reqCode, 'store_type' => $storeType]);
        jsonResponse(['success' => true, 'requisition_id' => $reqId, 'req_code' => $reqCode]);
    } catch (Exception $e) {
        error_log("Requisition create error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to submit requisition'], 500);
    }
}

function updateRequisitionStatus() {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'Requisition ID required'], 400);

    $data = getPostData();
    $status = ucfirst(strtolower($data['status'] ?? ''));
    if (!in_array($status, ['Approved', 'Rejected'], true)) {
        jsonResponse(['error' => 'Invalid status'], 400);
    }

    $db = Database::getInstance();
    $old = $db->fetchOne("SELECT * FROM inventory_requisitions WHERE id = ?", [$id]);
    if (!$old) jsonResponse(['error' => 'Requisition not found'], 404);

    try {
        $db->update('inventory_requisitions', [
            'status'  => $status,
            'remarks' => trim($data['remarks'] ?? ($old['remarks'] ?? '')),
        ], 'id = ?', [$id]);
        logAudit('UPDATE', 'inventory_requisitions', $id, $old, ['status' => $status]);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        error_log("Requisition update error: " . $e->getMessage());
        jsonResponse(['error' => 'Failed to update requisition'], 500);
    }
}

function restockItem() {
    $id = $_GET['id'] ?? null;
    $data = getPostData();
    $qty = max((int)($data['quantity'] ?? 0), 1);

    $db = Database::getInstance();
    $old = $db->fetchOne("SELECT * FROM pharmacy_inventory WHERE id = ?", [$id]);
    if (!$old) jsonResponse(['error' => 'Item not found'], 404);

    try {
        $newQty = (int)$old['quantity_in_stock'] + $qty;
        $db->update('pharmacy_inventory', ['quantity_in_stock' => $newQty], 'id = ?', [$id]);
        logAudit('UPDATE', 'pharmacy_inventory', $id, $old, ['quantity_in_stock' => $newQty, 'restock' => $qty]);
        jsonResponse(['success' => true, 'quantity_in_stock' => $newQty]);
    } catch (Exception $e) {
        jsonResponse(['error' => 'Failed to restock'], 500);
    }
}

function updateItem() {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'Item ID required'], 400);
    $data = getPostData();

    $db = Database::getInstance();
    $old = $db->fetchOne("SELECT * FROM pharmacy_inventory WHERE id = ?", [$id]);
    if (!$old) jsonResponse(['error' => 'Item not found'], 404);

    $fields = ['drug_name', 'generic_name', 'drug_code', 'store_type', 'category', 'manufacturer', 'batch_number',
               'expiry_date', 'quantity_in_stock', 'reorder_level', 'unit', 'storage_location', 'is_active'];
    $toUpdate = [];
    foreach ($fields as $f) {
        if (array_key_exists($f, $data)) {
            if ($f === 'store_type') {
                $toUpdate[$f] = (strtoupper($data[$f]) === 'GENERAL') ? 'GENERAL' : 'MEDICAL';
            } else {
                $toUpdate[$f] = $data[$f];
            }
        }
    }
    if (empty($toUpdate)) jsonResponse(['success' => true]);

    try {
        $db->update('pharmacy_inventory', $toUpdate, 'id = ?', [$id]);
        logAudit('UPDATE', 'pharmacy_inventory', $id, $old, $toUpdate);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        jsonResponse(['error' => 'Failed to update item'], 500);
    }
}

function deleteItem() {
    $id = $_GET['id'] ?? null;
    if (!$id) jsonResponse(['error' => 'Item ID required'], 400);

    $db = Database::getInstance();
    $old = $db->fetchOne("SELECT * FROM pharmacy_inventory WHERE id = ?", [$id]);
    if (!$old) jsonResponse(['error' => 'Item not found'], 404);

    try {
        $db->delete('pharmacy_inventory', 'id = ?', [$id]);
        logAudit('DELETE', 'pharmacy_inventory', $id, $old, null);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        jsonResponse(['error' => 'Failed to delete item'], 500);
    }
}