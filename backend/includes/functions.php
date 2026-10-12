<?php
// Common utility functions

// A role is only valid if it is one of the values the users.role ENUM accepts.
// Checked before any write so a bad value is a 400 rather than a MySQL enum
// failure reported as a server error.
function isValidUserRole($role) {
    return in_array($role, USER_ROLES, true);
}

// Fields a user is allowed to change on their own account. role,
// department_id and is_active are absent on purpose: they are privileges, and
// a user must not be able to grant themselves a different one. Changing
// another person's privileges goes through the admin path in users.php.
const SELF_EDITABLE_USER_FIELDS = ['full_name', 'email', 'password', 'profile'];

// Strip secrets before a user record is sent to a browser. The users table is
// read with SELECT *, so the bcrypt hash would otherwise ride along in every
// user payload and be harvestable by any logged-in account.
function publicUserFields($user) {
    if (!is_array($user)) {
        return $user;
    }
    unset($user['password']);
    return $user;
}

function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function generateHospitalNumber() {
    $prefix = 'HMS';
    $year = date('Y');
    $random = str_pad(rand(1, 99999), 5, '0', STR_PAD_LEFT);
    return $prefix . $year . $random;
}

function generateVisitNumber($patientId) {
    $prefix = 'VIS';
    $date = date('Ymd');
    $random = str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);
    return $prefix . $date . $random;
}

function generateInvoiceNumber() {
    $prefix = 'INV';
    $date = date('Ymd');
    $random = str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT);
    return $prefix . $date . $random;
}

function hashPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: /hms/frontend/index.php');
        exit;
    }
}

function hasRole($roles) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (!is_array($roles)) {
        $roles = [$roles];
    }
    
    // Super admin has access to all modules
    if ($_SESSION['user_role'] === 'super_admin') {
        return true;
    }
    
    return in_array($_SESSION['user_role'], $roles);
}

function requireRole($roles) {
    requireLogin();
    
    if (!hasRole($roles)) {
        http_response_code(403);
        die('Access denied. You do not have permission to access this resource.');
    }
}

function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

function getCurrentUserRole() {
    return $_SESSION['user_role'] ?? null;
}

function getCurrentUserName() {
    return $_SESSION['user_name'] ?? null;
}

function logAudit($action, $tableName, $recordId = null, $oldValues = null, $newValues = null) {
    $db = Database::getInstance();
    
    $data = [
        'user_id' => getCurrentUserId(),
        'action' => $action,
        'table_name' => $tableName,
        'record_id' => $recordId,
        'old_values' => $oldValues ? json_encode($oldValues) : null,
        'new_values' => $newValues ? json_encode($newValues) : null,
        'ip_address' => $_SERVER['REMOTE_ADDR'] ?? null,
        'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null
    ];
    
    try {
        $db->insert('audit_trails', $data);
    } catch (Exception $e) {
        error_log("Audit log error: " . $e->getMessage());
    }
}

function formatDate($date, $format = 'Y-m-d H:i:s') {
    return date($format, strtotime($date));
}

function calculateAge($dateOfBirth) {
    $today = new DateTime();
    $birthDate = new DateTime($dateOfBirth);
    $age = $today->diff($birthDate)->y;
    return $age;
}

function formatCurrency($amount, $currency = 'GHS') {
    return $currency . ' ' . number_format($amount, 2);
}

function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function redirect($url) {
    header('Location: ' . $url);
    exit;
}

function getPostData() {
    $json = file_get_contents('php://input');
    return json_decode($json, true) ?? $_POST;
}

function validateRequired($data, $requiredFields) {
    $errors = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field])) {
            $errors[] = "$field is required";
            continue;
        }
        $value = $data[$field];
        if (is_array($value)) {
            if (empty($value)) {
                $errors[] = "$field is required";
            }
        } elseif (trim((string)$value) === '') {
            $errors[] = "$field is required";
        }
    }
    
    return $errors;
}

function uploadFile($file, $uploadDir, $allowedTypes = null) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        throw new Exception('File upload error');
    }
    
    if ($allowedTypes && !in_array($file['type'], $allowedTypes)) {
        throw new Exception('Invalid file type');
    }
    
    if ($file['size'] > UPLOAD_MAX_SIZE) {
        throw new Exception('File size exceeds maximum limit');
    }
    
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . '.' . $extension;
    $filepath = $uploadDir . '/' . $filename;
    
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        throw new Exception('Failed to move uploaded file');
    }
    
    return $filename;
}

function sendEmail($to, $subject, $message, $headers = '') {
    $defaultHeaders = "From: noreply@hms.com\r\n";
    $defaultHeaders .= "MIME-Version: 1.0\r\n";
    $defaultHeaders .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers . $defaultHeaders);
}

function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ---------------------------------------------------------------------------
// Imaging catalogue + service charging
// ---------------------------------------------------------------------------

// Imaging modality catalogue: single source of truth shared by the Radiology
// API (backend/api/radiology.php), the Service Prices picker
// (backend/api/services.php) and the Radiology page fragment.
function radiologyCatalogueData() {
    return [
        'xray' => [
            ['Chest X-Ray (PA & Lateral)',    'Chest'],
            ['Chest X-Ray (Single View)',     'Chest'],
            ['Abdominal X-Ray (Plain)',       'Abdomen'],
            ['Pelvic X-Ray',                  'Pelvis'],
            ['X-Ray of the Skull',            'Head'],
            ['Spine X-Ray (Lumbar)',          'Lumbar Spine'],
            ['Extremity X-Ray',               'Extremity'],
        ],
        'ultrasound' => [
            ['Abdominal Ultrasound',          'Abdomen'],
            ['Obstetric Ultrasound',          'Abdomen / Pelvis'],
            ['Pelvic Ultrasound',             'Pelvis'],
            ['Thyroid Ultrasound',            'Neck'],
            ['Breast Ultrasound',             'Breast'],
            ['Renal Ultrasound (KUB)',        'Abdomen'],
        ],
        'ct' => [
            ['CT Head (Brain)',               'Head'],
            ['CT Chest (High Resolution)',    'Chest'],
            ['CT Abdomen & Pelvis (Contrast)','Abdomen / Pelvis'],
            ['CT Spine (Lumbar / Cervical)',  'Spine'],
        ],
        'mri' => [
            ['MRI Brain',                     'Head'],
            ['MRI Lumbar Spine',              'Lumbar Spine'],
            ['MRI Knee',                      'Knee'],
            ['MRI Abdomen',                   'Abdomen'],
        ],
        'mammography' => [
            ['Mammogram (Bilateral)',         'Breast'],
            ['Mammogram (Unilateral)',        'Breast'],
        ],
        'fluoroscopy' => [
            ['Barium Swallow',                'Chest / Oesophagus'],
            ['Barium Meal',                   'Abdomen'],
            ['IV Urography',                  'Abdomen / Pelvis'],
        ],
    ];
}

// Flatten the catalogue to [['modality'=>, 'exam_type'=>, 'body_part'=>], ...].
function radiologyCatalogueFlat() {
    $out = [];
    foreach (radiologyCatalogueData() as $mod => $studies) {
        foreach ($studies as $s) {
            $out[] = ['modality' => $mod, 'exam_type' => $s[0], 'body_part' => $s[1]];
        }
    }
    return $out;
}

// The active price for a name-keyed service (radiology exam / lab test), or
// null when no price has been set on the Service Prices page.
function resolveServicePrice($serviceType, $serviceRef) {
    if ($serviceRef === null || trim((string)$serviceRef) === '') return null;
    $db = Database::getInstance();
    return $db->fetchOne(
        "SELECT price, currency FROM service_prices
          WHERE service_type = ? AND service_ref = ? AND is_active = 1
          ORDER BY effective_date DESC, id DESC LIMIT 1",
        [$serviceType, $serviceRef]
    ) ?: null;
}

// The Doctor Station raises imaging from a short fixed list whose names differ
// from the radiology catalogue (e.g. 'Chest X-Ray' vs 'Chest X-Ray (PA &
// Lateral)'). Map those short names to the canonical study so a single price
// set on the Service Prices page covers both entry points. Adjust this map if
// the doctor list changes.
const RADIOLOGY_EXAM_ALIASES = [
    'Chest X-Ray'    => 'Chest X-Ray (PA & Lateral)',
    'Abdominal Scan' => 'Abdominal Ultrasound',
];

// Imaging price for a study: exact catalogue name first, then a known Doctor
// Station alias. Returns ['price'=>, 'currency'=>] or null.
function resolveRadiologyPrice($examName) {
    $p = resolveServicePrice('radiology', $examName);
    if ($p) return $p;
    if (isset(RADIOLOGY_EXAM_ALIASES[$examName])) {
        return resolveServicePrice('radiology', RADIOLOGY_EXAM_ALIASES[$examName]);
    }
    return null;
}

// Reuse a visit's open invoice, or open one. draft/pending/partial are still
// billable; paid and cancelled are not. Returns the invoice id, or null when
// the visit cannot be found.
function ensureOpenInvoiceForVisit($visitId) {
    $db = Database::getInstance();
    $visit = $db->fetchOne("SELECT id, patient_id FROM patient_visits WHERE id = ?", [(int)$visitId]);
    if (!$visit) return null;

    $open = $db->fetchOne(
        "SELECT id FROM invoices
          WHERE visit_id = ? AND status IN ('draft','pending','partial')
          ORDER BY id DESC LIMIT 1",
        [(int)$visitId]
    );
    if ($open) return (int)$open['id'];

    $invoiceId = $db->insert('invoices', [
        'visit_id'        => (int)$visitId,
        'invoice_number'  => generateInvoiceNumber() . '-' . strtoupper(substr(uniqid(), -4)),
        'patient_id'      => (int)$visit['patient_id'],
        'total_amount'    => 0,
        'discount_amount' => 0,
        'tax_amount'      => 0,
        'net_amount'      => 0,
        'status'          => 'pending',
        'created_by'      => getCurrentUserId(),
    ]);
    logAudit('CREATE', 'invoices', $invoiceId, null, ['visit_id' => (int)$visitId, 'source' => 'auto-charge']);
    return (int)$invoiceId;
}

// Post a charge line onto the visit's invoice and re-total it. No-ops (returns
// null) when there is no positive price. Returns ['invoice_id','line_total',
// 'net_amount'] on success.
function chargeServiceToVisit($visitId, $itemType, $itemId, $description, $unitPrice, $quantity = 1) {
    $unitPrice = (float)$unitPrice;
    if ($unitPrice <= 0) return null;

    $db = Database::getInstance();
    $invoiceId = ensureOpenInvoiceForVisit($visitId);
    if (!$invoiceId) return null;

    $qty = max((int)$quantity, 1);
    $lineTotal = round($qty * $unitPrice, 2);

    $db->insert('billing_items', [
        'invoice_id'  => $invoiceId,
        'item_type'   => $itemType,
        'item_id'     => (int)$itemId,
        'description' => $description,
        'quantity'    => $qty,
        'unit_price'  => $unitPrice,
        'total_price' => $lineTotal,
    ]);

    // Re-total from the line items so the invoice header can never drift from
    // what is actually billed.
    $sums = $db->fetchOne(
        "SELECT COALESCE(SUM(total_price), 0) AS gross FROM billing_items WHERE invoice_id = ?",
        [$invoiceId]
    );
    $invoice = $db->fetchOne("SELECT discount_amount, tax_amount FROM invoices WHERE id = ?", [$invoiceId]);
    $gross = (float)$sums['gross'];
    $net = max($gross - (float)$invoice['discount_amount'] + (float)$invoice['tax_amount'], 0);
    $db->update('invoices', ['total_amount' => $gross, 'net_amount' => $net], 'id = ?', [$invoiceId]);

    logAudit('UPDATE', 'invoices', $invoiceId, null, [
        'auto_charge' => $description,
        'item_type'   => $itemType,
        'line_total'  => $lineTotal,
    ]);

    return ['invoice_id' => $invoiceId, 'line_total' => $lineTotal, 'net_amount' => $net];
}
?>
