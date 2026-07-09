<?php
require_once __DIR__ . '/../Database.php';

class Course {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // --- Master Data Getters ---
    public function getDepartments() {
        $result = $this->db->query("SELECT * FROM departments WHERE status='active' AND deleted_at IS NULL");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    // --- CRUD ---
    public function getAll($filters = []) {
        $query = "
            SELECT c.id, c.course_code as code, c.course_name as name, c.department_id, c.description, c.credits, c.status, c.created_at, d.name as department_name
            FROM courses c
            LEFT JOIN departments d ON c.department_id = d.id
            WHERE c.deleted_at IS NULL
        ";

        if (!empty($filters['search'])) {
            $search = $this->db->real_escape_string($filters['search']);
            $query .= " AND (c.course_name LIKE '%$search%' OR c.course_code LIKE '%$search%')";
        }
        if (!empty($filters['department_id'])) {
            $dept_id = (int)$filters['department_id'];
            $query .= " AND c.department_id = $dept_id";
        }
        if (!empty($filters['status'])) {
            $status = $this->db->real_escape_string($filters['status']);
            $query .= " AND c.status = '$status'";
        }

        $query .= " ORDER BY c.created_at DESC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT c.id, c.course_code as code, c.course_name as name, c.department_id, c.description, c.credits, c.status, c.created_at, d.name as department_name
            FROM courses c
            LEFT JOIN departments d ON c.department_id = d.id
            WHERE c.id = ? AND c.deleted_at IS NULL
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function checkDuplicate($field, $value, $exclude_id = null) {
        // Map 'code' to 'course_code' and 'name' to 'course_name' for checking
        $db_field = ($field == 'code') ? 'course_code' : (($field == 'name') ? 'course_name' : $field);
        $query = "SELECT id FROM courses WHERE $db_field = ? AND deleted_at IS NULL";
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
            INSERT INTO courses (
                course_code, course_name, department_id, description, credits, status
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "ssisis", 
            $data['code'], $data['name'], $data['department_id'], 
            $data['description'], $data['credits'], $data['status']
        );
        
        $success = $stmt->execute();
        if ($success) {
            $id = $this->db->insert_id;
            $this->logAction('create', $id, null, json_encode($data));
            return $id;
        }
        return false;
    }

    public function update($id, $data) {
        $old_data = $this->getById($id);
        
        $stmt = $this->db->prepare("
            UPDATE courses SET 
                course_code = ?, course_name = ?, department_id = ?, 
                description = ?, credits = ?, status = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "ssisisi", 
            $data['code'], $data['name'], $data['department_id'], 
            $data['description'], $data['credits'], $data['status'], $id
        );
        
        $success = $stmt->execute();
        if ($success) {
            $this->logAction('update', $id, json_encode($old_data), json_encode($data));
        }
        return $success;
    }

    public function softDelete($id) {
        $old_data = $this->getById($id);
        $stmt = $this->db->prepare("UPDATE courses SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        if ($success) {
            $this->logAction('delete', $id, json_encode($old_data), null);
        }
        return $success;
    }

    // --- Analytics / Statistics ---
    public function getDashboardStats() {
        $stats = [];
        
        $result = $this->db->query("SELECT COUNT(*) as count FROM courses WHERE deleted_at IS NULL");
        $stats['total'] = $result->fetch_assoc()['count'];
        
        $result = $this->db->query("SELECT COUNT(*) as count FROM courses WHERE status = 'active' AND deleted_at IS NULL");
        $stats['active'] = $result->fetch_assoc()['count'];
        
        $result = $this->db->query("SELECT COUNT(*) as count FROM departments WHERE status = 'active' AND deleted_at IS NULL");
        $stats['departments'] = $result->fetch_assoc()['count'];
        
        $result = $this->db->query("SELECT SUM(credits) as total_credits FROM courses WHERE status = 'active' AND deleted_at IS NULL");
        $stats['total_credits'] = $result->fetch_assoc()['total_credits'];

        return $stats;
    }

    // --- Audit ---
    private function logAction($action, $record_id, $old_value, $new_value) {
        $user_id = $_SESSION['admin_id'] ?? ($_SESSION['sub_admin_id'] ?? 0);
        $user_type = 'admin';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        $stmt = $this->db->prepare("INSERT INTO audit_logs (user_id, user_type, action, module, record_id, old_value, new_value, ip_address) VALUES (?, ?, ?, 'Course Management', ?, ?, ?, ?)");
        $stmt->bind_param("ississs", $user_id, $user_type, $action, $record_id, $old_value, $new_value, $ip);
        $stmt->execute();
    }
}
