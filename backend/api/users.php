<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

requireLogin();

switch ($method) {
    case 'GET':
        if ($action === 'me') {
            getCurrentUser();
        } elseif ($action === 'list') {
            listUsers();
        } elseif ($action === 'departments') {
            getDepartments();
        } else {
            getUser();
        }
        break;
    case 'POST':
        if ($action === 'create') {
            createUser();
        } elseif ($action === 'create_department') {
            createDepartment();
        } elseif ($action === 'update_department') {
            updateDepartment();
        } else {
            jsonResponse(['error' => 'Invalid action'], 400);
        }
        break;
    case 'PUT':
        updateUser();
        break;
    case 'DELETE':
        deleteUser();
        break;
    default:
        jsonResponse(['error' => 'Method not allowed'], 405);
}

function getCurrentUser() {
    $userModel = new User();
    $user = $userModel->getById(getCurrentUserId());
    $profile = $userModel->getProfile(getCurrentUserId());
    
    jsonResponse([
        'success' => true,
        'user' => publicUserFields(array_merge($user, ['profile' => $profile]))
    ]);
}

function getUser() {
    $userId = $_GET['id'] ?? getCurrentUserId();
    
    // Reading your own record is fine; reading someone else's is an admin
    // action, same as editing them. Previously this took an id straight from
    // the query string, so any signed-in account could read any other
    // account's row.
    if ($userId != getCurrentUserId()) {
        requireRole(['super_admin', 'admin']);
    }
    
    $userModel = new User();
    $user = $userModel->getById($userId);
    $profile = $userModel->getProfile($userId);
    
    if (!$user) {
        jsonResponse(['error' => 'User not found'], 404);
    }
    
    jsonResponse([
        'success' => true,
        'user' => publicUserFields(array_merge($user, ['profile' => $profile]))
    ]);
}

function listUsers() {
    requireRole(['super_admin', 'admin']);
    
    $filters = [
        'role' => $_GET['role'] ?? null,
        'department_id' => $_GET['department_id'] ?? null,
        'is_active' => $_GET['is_active'] ?? null,
        'search' => $_GET['search'] ?? null
    ];
    
    $userModel = new User();
    $users = $userModel->getAll($filters);
    
    jsonResponse([
        'success' => true,
        'users' => array_map('publicUserFields', $users)
    ]);
}

function createUser() {
    requireRole(['super_admin', 'admin']);
    
    $data = getPostData();
    
    $errors = validateRequired($data, ['username', 'password', 'full_name', 'role']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }
    
    if (!isValidUserRole($data['role'])) {
        jsonResponse(['error' => 'Invalid role'], 400);
    }
    
    if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
        jsonResponse(['error' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'], 400);
    }
    
    $currentUserRole = getCurrentUserRole();
    
    if (!hasRole(['super_admin']) && !hasRole(['admin'])) {
        jsonResponse(['error' => 'You do not have permission to create users'], 403);
    }
    
    $userModel = new User();
    
    if (!$userModel->canCreateUser($currentUserRole, $data['role'])) {
        jsonResponse(['error' => 'You do not have permission to create users with this role'], 403);
    }
    
    try {
        $userId = $userModel->create($data);
        jsonResponse(['success' => true, 'user_id' => $userId]);
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            jsonResponse(['error' => 'Username or email already exists'], 409);
        }
        jsonResponse(['error' => 'User creation failed'], 500);
    }
}

function updateUser() {
    $userId = $_GET['id'] ?? null;
    
    if (!$userId) {
        jsonResponse(['error' => 'User ID is required'], 400);
    }
    
    // Users can update their own profile, admins can update others
    if ($userId != getCurrentUserId()) {
        requireRole(['super_admin', 'admin']);
    }
    
    $userModel = new User();
    
    if (!$userModel->getById($userId)) {
        jsonResponse(['error' => 'User not found'], 404);
    }
    
    $data = getPostData();
    
    // A self-update may only carry the fields listed in
    // SELF_EDITABLE_USER_FIELDS. Without this the privilege fields reach
    // User::update() unchecked and a signed-in user can promote themselves by
    // PUTting their own id with a role of their choosing.
    if (!hasRole(['super_admin', 'admin'])) {
        $data = array_intersect_key($data, array_flip(SELF_EDITABLE_USER_FIELDS));
    }
    
    // An admin may move other accounts around, but may not mint a
    // super_admin - the same limit createUser() applies via
    // canCreateUser(), otherwise "admin" becomes a stepping stone to full
    // control of the system.
    if (isset($data['role'])) {
        if (!isValidUserRole($data['role'])) {
            jsonResponse(['error' => 'Invalid role'], 400);
        }
        
        if (!hasRole(['super_admin']) && $data['role'] === 'super_admin') {
            jsonResponse(['error' => 'You do not have permission to assign this role'], 403);
        }
    }
    
    try {
        $userModel->update($userId, $data);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            jsonResponse(['error' => 'Username or email already exists'], 409);
        }
        jsonResponse(['error' => 'User update failed'], 500);
    }
}

function deleteUser() {
    requireRole(['super_admin']);
    
    $userId = $_GET['id'] ?? null;
    
    if (!$userId) {
        jsonResponse(['error' => 'User ID is required'], 400);
    }
    
    if ($userId == getCurrentUserId()) {
        jsonResponse(['error' => 'You cannot delete your own account'], 400);
    }
    
    $userModel = new User();
    
    try {
        $userModel->delete($userId);
        jsonResponse(['success' => true]);
    } catch (Exception $e) {
        jsonResponse(['error' => 'User deletion failed'], 500);
    }
}

function getDepartments() {
    $db = Database::getInstance();
    
    $departments = $db->fetchAll("SELECT * FROM departments ORDER BY name");
    
    jsonResponse([
        'success' => true,
        'departments' => $departments
    ]);
}

function createDepartment() {
    requireRole(['super_admin', 'admin']);
    
    $data = getPostData();
    
    $errors = validateRequired($data, ['name', 'code']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }
    
    $db = Database::getInstance();
    
    $deptData = [
        'name'                => trim($data['name']),
        'code'                => strtoupper(trim($data['code'])),
        'description'         => trim($data['description'] ?? ''),
        'head_of_department'  => !empty($data['head_of_department']) ? (int)$data['head_of_department'] : null,
    ];
    
    try {
        $deptId = $db->insert('departments', $deptData);
        logAudit('CREATE', 'departments', $deptId, null, ['name' => $deptData['name'], 'code' => $deptData['code']]);
        jsonResponse(['success' => true, 'department_id' => $deptId, 'message' => 'Department created successfully']);
    } catch (Exception $e) {
        error_log("Department create error: " . $e->getMessage());
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            jsonResponse(['error' => 'Department name or code already exists'], 409);
        }
        jsonResponse(['error' => 'Failed to create department'], 500);
    }
}

function updateDepartment() {
    requireRole(['super_admin', 'admin']);

    $deptId = $_GET['id'] ?? null;
    if (!$deptId) {
        jsonResponse(['error' => 'Department ID is required'], 400);
    }

    $data = getPostData();
    $db = Database::getInstance();

    $updates = [];
    if (isset($data['name'])) $updates['name'] = trim($data['name']);
    if (isset($data['code'])) $updates['code'] = strtoupper(trim($data['code']));
    if (isset($data['description'])) $updates['description'] = trim($data['description']);
    if (array_key_exists('head_of_department', $data)) {
        $updates['head_of_department'] = !empty($data['head_of_department']) ? (int)$data['head_of_department'] : null;
    }

    if (isset($data['status']) && in_array($data['status'], ['Active', 'Frozen', 'Deactivated'], true)) {
        $updates['status'] = $data['status'];
    }

    if (empty($updates)) {
        jsonResponse(['error' => 'Nothing to update'], 400);
    }

    try {
        $oldData = $db->fetchOne("SELECT * FROM departments WHERE id = ?", [$deptId]);
        if (!$oldData) {
            jsonResponse(['error' => 'Department not found'], 404);
        }
        $db->update('departments', $updates, 'id = ?', [$deptId]);
        logAudit('UPDATE', 'departments', $deptId, $oldData, $updates);
        jsonResponse(['success' => true, 'message' => 'Department updated successfully']);
    } catch (Exception $e) {
        error_log("Department update error: " . $e->getMessage());
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            jsonResponse(['error' => 'Department name or code already exists'], 409);
        }
        jsonResponse(['error' => 'Failed to update department'], 500);
    }
}
?>
