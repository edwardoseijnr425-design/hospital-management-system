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
?>
