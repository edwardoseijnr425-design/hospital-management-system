<?php
require_once __DIR__ . '/../config/config.php';

class Patient {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function register($data) {
        $hospitalNumber = $this->generateUniqueHospitalNumber();
        
        $patientData = [
            'hospital_number' => $hospitalNumber,
            'first_name' => $data['first_name'],
            'middle_name' => $data['middle_name'] ?? null,
            'last_name' => $data['last_name'],
            'date_of_birth' => $data['date_of_birth'],
            'gender' => $data['gender'],
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'emergency_contact_name' => $data['emergency_contact_name'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'blood_group' => $data['blood_group'] ?? null,
            'sponsor_id' => $data['sponsor_id'] ?? null,
            'nhia_number' => $data['nhia_number'] ?? null,
            'registration_date' => date('Y-m-d'),
            'registered_by' => getCurrentUserId()
        ];
        
        try {
            $patientId = $this->db->insert('patient_registrations', $patientData);
            logAudit('CREATE', 'patient_registrations', $patientId, null, $patientData);
            return $patientId;
        } catch (Exception $e) {
            error_log("Patient registration error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function update($patientId, $data) {
        $oldData = $this->getById($patientId);
        
        $updateData = [];
        
        $allowedFields = ['first_name', 'middle_name', 'last_name', 'date_of_birth', 'gender', 
                         'phone', 'email', 'address', 'emergency_contact_name', 
                         'emergency_contact_phone', 'blood_group', 'sponsor_id', 'nhia_number'];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $updateData[$field] = $data[$field];
            }
        }
        
        if (empty($updateData)) {
            return false;
        }
        
        try {
            $this->db->update('patient_registrations', $updateData, 'id = ?', [$patientId]);
            logAudit('UPDATE', 'patient_registrations', $patientId, $oldData, $updateData);
            return true;
        } catch (Exception $e) {
            error_log("Patient update error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getById($patientId) {
        $sql = "SELECT p.*, s.name as sponsor_name, s.code as sponsor_code, s.nhia_status
                FROM patient_registrations p
                LEFT JOIN sponsors s ON p.sponsor_id = s.id
                WHERE p.id = ?";
        
        return $this->db->fetchOne($sql, [$patientId]);
    }
    
    public function getByHospitalNumber($hospitalNumber) {
        $sql = "SELECT p.*, s.name as sponsor_name, s.code as sponsor_code, s.nhia_status
                FROM patient_registrations p
                LEFT JOIN sponsors s ON p.sponsor_id = s.id
                WHERE p.hospital_number = ?";
        
        return $this->db->fetchOne($sql, [$hospitalNumber]);
    }
    
    public function search($query, $limit = 20) {
        $sql = "SELECT p.*, s.name as sponsor_name
                FROM patient_registrations p
                LEFT JOIN sponsors s ON p.sponsor_id = s.id
                WHERE p.is_active = 1
                AND (p.hospital_number LIKE ? 
                     OR CONCAT(p.first_name, ' ', p.last_name) LIKE ?
                     OR p.phone LIKE ?)
                ORDER BY p.registration_date DESC
                LIMIT ?";
        
        $searchTerm = '%' . $query . '%';
        return $this->db->fetchAll($sql, [$searchTerm, $searchTerm, $searchTerm, $limit]);
    }
    
    public function getAll($filters = [], $page = 1, $perPage = ITEMS_PER_PAGE) {
        $offset = ($page - 1) * $perPage;
        
        $sql = "SELECT p.*, s.name as sponsor_name, s.nhia_status
                FROM patient_registrations p
                LEFT JOIN sponsors s ON p.sponsor_id = s.id
                WHERE p.is_active = 1";
        
        $params = [];
        
        if (isset($filters['sponsor_id'])) {
            $sql .= " AND p.sponsor_id = ?";
            $params[] = $filters['sponsor_id'];
        }
        
        if (isset($filters['gender'])) {
            $sql .= " AND p.gender = ?";
            $params[] = $filters['gender'];
        }
        
        if (isset($filters['date_from'])) {
            $sql .= " AND p.registration_date >= ?";
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $sql .= " AND p.registration_date <= ?";
            $params[] = $filters['date_to'];
        }
        
        $sql .= " ORDER BY p.registration_date DESC LIMIT ? OFFSET ?";
        $params[] = $perPage;
        $params[] = $offset;
        
        return $this->db->fetchAll($sql, $params);
    }
    
    public function createVisit($patientId, $data) {
        $visitNumber = generateVisitNumber($patientId);
        
        $visitData = [
            'patient_id' => $patientId,
            'visit_number' => $visitNumber,
            'visit_type' => $data['visit_type'],
            'visit_date' => date('Y-m-d H:i:s'),
            'department_id' => $data['department_id'],
            'chief_complaint' => $data['chief_complaint'] ?? null,
            'status' => 'pending',
            'created_by' => getCurrentUserId()
        ];
        
        try {
            $visitId = $this->db->insert('patient_visits', $visitData);
            logAudit('CREATE', 'patient_visits', $visitId, null, $visitData);
            return $visitId;
        } catch (Exception $e) {
            error_log("Visit creation error: " . $e->getMessage());
            throw $e;
        }
    }
    
    public function getVisitById($visitId) {
        $sql = "SELECT v.*, p.hospital_number, p.first_name, p.last_name, p.date_of_birth, p.gender,
                       d.name as department_name, u.full_name as created_by_name
                FROM patient_visits v
                JOIN patient_registrations p ON v.patient_id = p.id
                JOIN departments d ON v.department_id = d.id
                JOIN users u ON v.created_by = u.id
                WHERE v.id = ?";
        
        return $this->db->fetchOne($sql, [$visitId]);
    }
    
    public function getPatientVisits($patientId, $limit = 10) {
        $sql = "SELECT v.*, d.name as department_name
                FROM patient_visits v
                JOIN departments d ON v.department_id = d.id
                WHERE v.patient_id = ?
                ORDER BY v.visit_date DESC
                LIMIT ?";
        
        return $this->db->fetchAll($sql, [$patientId, $limit]);
    }
    
    public function getActiveVisits($departmentId = null) {
        $sql = "SELECT v.*, p.hospital_number, CONCAT(p.first_name, ' ', p.last_name) as patient_name,
                       d.name as department_name
                FROM patient_visits v
                JOIN patient_registrations p ON v.patient_id = p.id
                JOIN departments d ON v.department_id = d.id
                WHERE v.status IN ('pending', 'in_progress')";
        
        $params = [];
        
        if ($departmentId) {
            $sql .= " AND v.department_id = ?";
            $params[] = $departmentId;
        }
        
        $sql .= " ORDER BY v.visit_date DESC";
        
        return $this->db->fetchAll($sql, $params);
    }
    
    private function generateUniqueHospitalNumber() {
        do {
            $hospitalNumber = generateHospitalNumber();
            $existing = $this->getByHospitalNumber($hospitalNumber);
        } while ($existing);
        
        return $hospitalNumber;
    }
    
    public function getStats($dateFrom = null, $dateTo = null) {
        $sql = "SELECT 
                    COUNT(*) as total_patients,
                    COUNT(CASE WHEN DATE(registration_date) = CURDATE() THEN 1 END) as today_registrations,
                    COUNT(CASE WHEN gender = 'male' THEN 1 END) as male_patients,
                    COUNT(CASE WHEN gender = 'female' THEN 1 END) as female_patients
                FROM patient_registrations
                WHERE is_active = 1";
        
        if ($dateFrom && $dateTo) {
            $sql .= " AND registration_date BETWEEN ? AND ?";
        }
        
        $params = [];
        if ($dateFrom && $dateTo) {
            $params = [$dateFrom, $dateTo];
        }
        
        return $this->db->fetchOne($sql, $params);
    }
}
?>
