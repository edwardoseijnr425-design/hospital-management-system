<?php
require_once __DIR__ . '/../config/config.php';

class User {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function authenticate($username, $password) {
        $sql = "SELECT id, username, password, full_name, role, department_id, is_active 
                FROM users 
                WHERE username = ? AND is_active = 1";
        
        $user = $this->db->fetchOne($sql, [$username]);
        
        if ($user && verifyPassword($password, $user['password'])) {
            // Update last login
            $this->updateLastLogin($user['id']);
            return $user;
        }
        
        return false;
    }
    
    public function create($data) {
        $hashedPassword = hashPassword($data['password']);
        
        $userData = [
            'username' => $data['username'],
            'password' => $hashedPassword,
            'email' => $data['email'] ?? null,
            'full_name' => $data['full_name'],
            'role' => $data['role'],
            'department_id' => $data['department_id'] ?? null,
            'created_by' => getCurrentUserId()
        ];
        
        try {
            $userId = $this->db->insert('users', $userData);
            
            // Create profile if provided
            if (isset($data['profile'])) {
                $this->createProfile($userId, $data['profile']);
            }
            
            logAudit('CREATE', 'users', $userId, null, $userData);
            
            return $userId;
        } catch (Exception $e) {
            error_log("User creation error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function update($userId, $data) {
        $oldData = $this->getById($userId);
        
        $updateData = [];
        
        if (isset($data['full_name'])) {
            $updateData['full_name'] = $data['full_name'];
        }
        
        if (isset($data['email'])) {
            $updateData['email'] = $data['email'];
        }
        
        if (isset($data['role'])) {
            $updateData['role'] = $data['role'];
        }
        
        if (isset($data['department_id'])) {
            $updateData['department_id'] = $data['department_id'];
        }
        
        if (isset($data['is_active'])) {
            $updateData['is_active'] = $data['is_active'];
        }
        
        if (isset($data['password'])) {
            $updateData['password'] = hashPassword($data['password']);
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        try {
            $this->db->update('users', $updateData, 'id = ?', [$userId]);
            
            // Update profile if provided
            if (isset($data['profile'])) {
                $this->updateProfile($userId, $data['profile']);
            }
            
            logAudit('UPDATE', 'users', $userId, $oldData, $updateData);
            
            return true;
        } catch (Exception $e) {
            error_log("User update error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function delete($userId) {
        $oldData = $this->getById($userId);
        
        try {
            $this->db->delete('users', 'id = ?', [$userId]);
            logAudit('DELETE', 'users', $userId, $oldData, null);
            return true;
        } catch (Exception $e) {
            error_log("User deletion error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getById($userId) {
        $sql = "SELECT u.*, d.name as department_name, d.code as department_code
                FROM users u
                LEFT JOIN departments d ON u.department_id = d.id
                WHERE u.id = ?";
        
        return $this->db->fetchOne($sql, [$userId]);
    }
    
    public function getByUsername($username) {
        $sql = "SELECT u.*, d.name as department_name, d.code as department_code
                FROM users u
                LEFT JOIN departments d ON u.department_id = d.id
                WHERE u.username = ?";
        
        return $this->db->fetchOne($sql, [$username]);
    }
    
    public function getAll($filters = []) {
        $sql = "SELECT u.*, d.name as department_name, d.code as department_code
                FROM users u
                LEFT JOIN departments d ON u.department_id = d.id
                WHERE 1=1";
        
        $params = [];
        
        if (isset($filters['role'])) {
            $sql .= " AND u.role = ?";
            $params[] = $filters['role'];
        }
        
        if (isset($filters['department_id'])) {
            $sql .= " AND u.department_id = ?";
            $params[] = $filters['department_id'];
        }
        
        if (isset($filters['is_active'])) {
            $sql .= " AND u.is_active = ?";
            $params[] = $filters['is_active'];
        }
        
        if (isset($filters['search'])) {
            $sql .= " AND (u.full_name LIKE ? OR u.username LIKE ? OR u.email LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        
        $sql .= " ORDER BY u.created_at DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function getByRole($role) {
        $sql = "SELECT u.*, d.name as department_name
                FROM users u
                LEFT JOIN departments d ON u.department_id = d.id
                WHERE u.role = ? AND u.is_active = 1
                ORDER BY u.full_name";
        
        return $this->db->fetchAll($sql, [$role]);
    }
    
    public function getByDepartment($departmentId) {
        $sql = "SELECT * FROM users 
                WHERE department_id = ? AND is_active = 1
                ORDER BY full_name";
        
        return $this->db->fetchAll($sql, [$departmentId]);
    }
    
    private function updateLastLogin($userId) {
        $this->db->update('users', ['last_login' => date('Y-m-d H:i:s')], 'id = ?', [$userId]);
    }
    
    private function createProfile($userId, $profileData) {
        $profileData['user_id'] = $userId;
        $this->db->insert('user_profiles', $profileData);
    }
    
    private function updateProfile($userId, $profileData) {
        $existingProfile = $this->getProfile($userId);
        
        if ($existingProfile) {
            $this->db->update('user_profiles', $profileData, 'user_id = ?', [$userId]);
        } else {
            $profileData['user_id'] = $userId;
            $this->db->insert('user_profiles', $profileData);
        }
    }
    
    public function getProfile($userId) {
        $sql = "SELECT * FROM user_profiles WHERE user_id = ?";
        return $this->db->fetchOne($sql, [$userId]);
    }
    
    public function canCreateUser($creatorRole, $targetRole) {
        // Super admin can create any role
        if ($creatorRole === 'super_admin') {
            return true;
        }
        
        // Admin can create admin and below
        if ($creatorRole === 'admin') {
            $allowedRoles = ['admin', 'it', 'records', 'nurse', 'doctor', 'pharmacy', 'lab', 'radiology', 'account', 'revenue'];
            return in_array($targetRole, $allowedRoles);
        }
        
        // Other roles cannot create users
        return false;
    }
}
?>
