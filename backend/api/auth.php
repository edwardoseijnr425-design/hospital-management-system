<?php
require_once __DIR__ . '/../config/config.php';

header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? '';

if ($method === 'POST' && $action === 'login') {
    handleLogin();
} elseif ($method === 'POST' && $action === 'logout') {
    handleLogout();
} elseif ($method === 'POST' && $action === 'register') {
    handleRegister();
} else {
    jsonResponse(['error' => 'Invalid action'], 400);
}

function handleLogin() {
    $data = getPostData();
    
    $errors = validateRequired($data, ['username', 'password']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }
    
    $userModel = new User();
    $user = $userModel->authenticate($data['username'], $data['password']);
    
    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['department_id'] = $user['department_id'];
        $_SESSION['login_time'] = time();
        
        logAudit('LOGIN', 'users', $user['id']);
        
        jsonResponse([
            'success' => true,
            'user' => [
                'id' => $user['id'],
                'username' => $user['username'],
                'full_name' => $user['full_name'],
                'role' => $user['role'],
                'department_id' => $user['department_id'],
                'department_name' => isset($user['department_name']) ? $user['department_name'] : null
            ]
        ]);
    } else {
        jsonResponse(['error' => 'Invalid username or password'], 401);
    }
}

function handleLogout() {
    if (isLoggedIn()) {
        logAudit('LOGOUT', 'users', getCurrentUserId());
    }
    
    session_destroy();
    jsonResponse(['success' => true]);
}

function handleRegister() {
    // Only super admin can create initial admin accounts
    // This endpoint is for initial setup only
    
    $data = getPostData();
    
    $errors = validateRequired($data, ['username', 'password', 'full_name', 'role']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }
    
    if (strlen($data['password']) < PASSWORD_MIN_LENGTH) {
        jsonResponse(['error' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'], 400);
    }
    
    $userModel = new User();
    
    try {
        $userId = $userModel->create($data);
        jsonResponse(['success' => true, 'user_id' => $userId]);
    } catch (Exception $e) {
        if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
            jsonResponse(['error' => 'Username or email already exists'], 409);
        }
        jsonResponse(['error' => 'Registration failed'], 500);
    }
}
?>
