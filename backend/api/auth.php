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
} elseif ($method === 'POST' && $action === 'change_password') {
    handleChangePassword();
} elseif ($method === 'POST' && $action === 'reset_password') {
    handleResetPassword();
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
    // Only super admin can create initial admin accounts.
    // This endpoint is for initial setup only.
    //
    // The gate is the point of the function: without it, anyone able to reach
    // the server posts a role of their choosing and mints themselves a
    // super_admin account with no login at all. setup.php seeds the first
    // admin with a direct INSERT and never calls this endpoint, and no
    // frontend page calls it, so requiring a signed-in super admin here
    // removes the hole without closing off any real flow.
    requireLogin();
    requireRole(['super_admin']);

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

function handleChangePassword() {
    // Self-service password change: verifies the CURRENT password before updating.
    requireLogin();
    
    $data = getPostData();
    
    $errors = validateRequired($data, ['current_password', 'new_password', 'confirm_password']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }
    
    $currentPassword = $data['current_password'];
    $newPassword = $data['new_password'];
    $confirmPassword = $data['confirm_password'];
    
    if ($newPassword !== $confirmPassword) {
        jsonResponse(['error' => 'New password and confirmation password do not match'], 400);
    }
    
    if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        jsonResponse(['error' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'], 400);
    }
    
    $userModel = new User();
    $user = $userModel->getById(getCurrentUserId());
    
    if (!$user) {
        jsonResponse(['error' => 'User not found'], 404);
    }
    
    if (!verifyPassword($currentPassword, $user['password'])) {
        jsonResponse(['error' => 'Current password is incorrect'], 400);
    }
    
    $updated = $userModel->update($user['id'], ['password' => $newPassword]);
    if (!$updated) {
        jsonResponse(['error' => 'Password update failed'], 500);
    }
    
    logAudit('CHANGE_PASSWORD', 'users', $user['id']);
    
    jsonResponse(['success' => true]);
}

function handleResetPassword() {
    // Admin username-based password reset (no current-password check).
    requireLogin();
    requireRole(['super_admin', 'admin']);
    
    $data = getPostData();
    
    $errors = validateRequired($data, ['username', 'new_password', 'confirm_password']);
    if (!empty($errors)) {
        jsonResponse(['errors' => $errors], 400);
    }
    
    $username = trim($data['username']);
    $newPassword = $data['new_password'];
    $confirmPassword = $data['confirm_password'];
    
    if ($newPassword !== $confirmPassword) {
        jsonResponse(['error' => 'New password and confirmation password do not match'], 400);
    }
    
    if (strlen($newPassword) < PASSWORD_MIN_LENGTH) {
        jsonResponse(['error' => 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters'], 400);
    }
    
    $userModel = new User();
    $target = $userModel->getByUsername($username);
    
    if (!$target) {
        jsonResponse(['error' => 'User not found'], 404);
    }
    
    $updated = $userModel->update($target['id'], ['password' => $newPassword]);
    if (!$updated) {
        jsonResponse(['error' => 'Password reset failed'], 500);
    }
    
    logAudit('RESET_PASSWORD', 'users', $target['id']);
    
    jsonResponse(['success' => true, 'username' => $target['username']]);
}
?>
