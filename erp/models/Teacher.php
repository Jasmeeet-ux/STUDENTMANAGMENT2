<?php
require_once __DIR__ . '/../Database.php';

class Teacher {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // --- Master Data Getters ---
    public function getDepartments() {
        $result = $this->db->query("SELECT * FROM departments WHERE status='active'");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getDesignations() {
        $result = $this->db->query("SELECT * FROM designations WHERE status='active'");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getEmploymentTypes() {
        $result = $this->db->query("SELECT * FROM employment_types WHERE status='active'");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // --- Teacher CRUD ---
    public function getAll($filters = []) {
        $query = "
            SELECT t.*, 
                   d.name as department_name, 
                   des.name as designation_name,
                   et.name as employment_type_name
            FROM teachers t
            LEFT JOIN departments d ON t.department_id = d.id
            LEFT JOIN designations des ON t.designation_id = des.id
            LEFT JOIN employment_types et ON t.employment_type_id = et.id
            WHERE t.deleted_at IS NULL
        ";

        if (!empty($filters['search'])) {
            $search = $this->db->real_escape_string($filters['search']);
            $query .= " AND (t.name LIKE '%$search%' OR t.employee_id LIKE '%$search%' OR t.email LIKE '%$search%')";
        }
        if (!empty($filters['department_id'])) {
            $dept_id = (int)$filters['department_id'];
            $query .= " AND t.department_id = $dept_id";
        }
        if (!empty($filters['status'])) {
            $status = $this->db->real_escape_string($filters['status']);
            $query .= " AND t.status = '$status'";
        }

        $query .= " ORDER BY t.created_at DESC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT t.*, 
                   d.name as department_name, 
                   des.name as designation_name,
                   et.name as employment_type_name
            FROM teachers t
            LEFT JOIN departments d ON t.department_id = d.id
            LEFT JOIN designations des ON t.designation_id = des.id
            LEFT JOIN employment_types et ON t.employment_type_id = et.id
            WHERE t.id = ? AND t.deleted_at IS NULL
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    // Checks for duplicate unique fields (email, employee_id, phone, username)
    public function checkDuplicate($field, $value, $exclude_id = null) {
        $query = "SELECT id FROM teachers WHERE $field = ? AND deleted_at IS NULL";
        if ($exclude_id) {
            $query .= " AND id != ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("si", $value, $exclude_id);
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("s", $value);
        }
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO teachers (
                name, employee_id, email, phone, username, password, 
                department_id, designation_id, employment_type_id, 
                gender, blood_group, dob, joining_date, qualification, 
                experience_years, address, emergency_contact_name, 
                emergency_contact_phone, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        $stmt->bind_param(
            "ssssssiiisssssissss", 
            $data['name'], $data['employee_id'], $data['email'], $data['phone'], 
            $data['username'], $passwordHash, 
            $data['department_id'], $data['designation_id'], $data['employment_type_id'],
            $data['gender'], $data['blood_group'], $data['dob'], $data['joining_date'], 
            $data['qualification'], $data['experience_years'], $data['address'], 
            $data['emergency_contact_name'], $data['emergency_contact_phone'], $data['status']
        );
        
        $success = $stmt->execute();
        if ($success) {
            $id = $this->db->insert_id;
            $this->logAction('create', $id, null, json_encode($data));
            $this->createNotification('Teacher Added', "New teacher {$data['name']} has been added.");
            return $id;
        }
        return false;
    }

    public function update($id, $data) {
        $old_data = $this->getById($id);
        
        $fields = [
            'name' => 's', 'employee_id' => 's', 'email' => 's', 'phone' => 's',
            'department_id' => 'i', 'designation_id' => 'i', 'employment_type_id' => 'i',
            'gender' => 's', 'blood_group' => 's', 'dob' => 's', 'joining_date' => 's',
            'qualification' => 's', 'experience_years' => 'i', 'address' => 's',
            'emergency_contact_name' => 's', 'emergency_contact_phone' => 's', 'status' => 's'
        ];

        $set_parts = [];
        $params = [];
        $types = "";

        foreach ($fields as $field => $type) {
            if (isset($data[$field])) {
                $set_parts[] = "$field = ?";
                $params[] = $data[$field];
                $types .= $type;
            }
        }

        if (!empty($data['password'])) {
            $set_parts[] = "password = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
            $types .= "s";
        }

        $params[] = $id;
        $types .= "i";

        $query = "UPDATE teachers SET " . implode(', ', $set_parts) . " WHERE id = ?";
        $stmt = $this->db->prepare($query);
        $stmt->bind_param($types, ...$params);
        
        $success = $stmt->execute();
        if ($success) {
            $this->logAction('update', $id, json_encode($old_data), json_encode($data));
            $this->createNotification('Teacher Updated', "Teacher details for {$data['name']} have been updated.");
        }
        return $success;
    }

    public function softDelete($id) {
        $old_data = $this->getById($id);
        $stmt = $this->db->prepare("UPDATE teachers SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        if ($success) {
            $this->logAction('delete', $id, json_encode($old_data), null);
            $this->createNotification('Teacher Deleted', "Teacher {$old_data['name']} has been removed.");
        }
        return $success;
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE teachers SET status = ? WHERE id = ?");
        $stmt->bind_param("si", $status, $id);
        $success = $stmt->execute();
        if ($success) {
            $this->logAction('status_change', $id, null, $status);
        }
        return $success;
    }

    // --- Analytics / Statistics ---
    public function getDashboardStats() {
        $stats = [];
        
        $result = $this->db->query("SELECT COUNT(*) as count FROM teachers WHERE deleted_at IS NULL");
        $stats['total'] = $result->fetch_assoc()['count'];
        
        $result = $this->db->query("SELECT COUNT(*) as count FROM teachers WHERE status = 'active' AND deleted_at IS NULL");
        $stats['active'] = $result->fetch_assoc()['count'];
        
        $result = $this->db->query("SELECT COUNT(*) as count FROM teachers WHERE status = 'on_leave' AND deleted_at IS NULL");
        $stats['on_leave'] = $result->fetch_assoc()['count'];
        
        $result = $this->db->query("SELECT COUNT(*) as count FROM teachers WHERE MONTH(created_at) = MONTH(CURRENT_DATE()) AND YEAR(created_at) = YEAR(CURRENT_DATE()) AND deleted_at IS NULL");
        $stats['new_this_month'] = $result->fetch_assoc()['count'];

        return $stats;
    }

    public function getDepartmentDistribution() {
        $query = "
            SELECT d.name, COUNT(t.id) as count 
            FROM departments d
            LEFT JOIN teachers t ON d.id = t.department_id AND t.deleted_at IS NULL
            GROUP BY d.id
        ";
        return $this->db->query($query)->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getGenderDistribution() {
        $query = "SELECT gender, COUNT(id) as count FROM teachers WHERE deleted_at IS NULL GROUP BY gender";
        return $this->db->query($query)->fetch_all(MYSQLI_ASSOC);
    }

    // --- Audit & Notifications ---
    private function logAction($action, $record_id, $old_value, $new_value) {
        $user_id = $_SESSION['admin_id'] ?? ($_SESSION['sub_admin_id'] ?? 0);
        $user_type = 'admin'; // simplification
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        $stmt = $this->db->prepare("INSERT INTO audit_logs (user_id, user_type, action, module, record_id, old_value, new_value, ip_address) VALUES (?, ?, ?, 'Teacher Management', ?, ?, ?, ?)");
        $stmt->bind_param("ississs", $user_id, $user_type, $action, $record_id, $old_value, $new_value, $ip);
        $stmt->execute();
    }

    private function createNotification($title, $message) {
        $user_id = $_SESSION['admin_id'] ?? ($_SESSION['sub_admin_id'] ?? 0);
        $user_type = 'admin';
        
        $stmt = $this->db->prepare("INSERT INTO notifications (user_id, user_type, title, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("isss", $user_id, $user_type, $title, $message);
        $stmt->execute();
    }
    
    public function getAcademicLoad($teacher_id) {
        // Fetch subjects assigned to teacher, along with their course details
        $stmt = $this->db->prepare("
            SELECT s.name as subject_name, s.code as subject_code, c.course_name, c.course_code 
            FROM subjects s
            JOIN courses c ON s.course_id = c.id
            WHERE s.teacher_id = ?
        ");
        $stmt->bind_param("i", $teacher_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getClasses($teacher_id) {
        $stmt = $this->db->prepare("
            SELECT name FROM classes WHERE teacher_id = ?
            UNION 
            SELECT name FROM sections WHERE teacher_id = ?
        ");
        $stmt->bind_param("ii", $teacher_id, $teacher_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
