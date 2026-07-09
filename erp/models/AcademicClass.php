<?php
require_once __DIR__ . '/../Database.php';

class AcademicClass {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getCourses() {
        $result = $this->db->query("SELECT id, course_code as code, course_name as name FROM courses WHERE status='active' AND deleted_at IS NULL");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getTeachers() {
        $result = $this->db->query("SELECT id, employee_id, name FROM teachers WHERE status='active' AND deleted_at IS NULL");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAll($filters = []) {
        $query = "
            SELECT cls.*, c.course_name as course_name, c.course_code as course_code, t.name as teacher_name 
            FROM classes cls
            LEFT JOIN courses c ON cls.course_id = c.id
            LEFT JOIN teachers t ON cls.teacher_id = t.id
            WHERE cls.deleted_at IS NULL
        ";

        if (!empty($filters['search'])) {
            $search = $this->db->real_escape_string($filters['search']);
            $query .= " AND (cls.name LIKE '%$search%' OR cls.room_number LIKE '%$search%')";
        }
        if (!empty($filters['course_id'])) {
            $course_id = (int)$filters['course_id'];
            $query .= " AND cls.course_id = $course_id";
        }
        if (!empty($filters['status'])) {
            $status = $this->db->real_escape_string($filters['status']);
            $query .= " AND cls.status = '$status'";
        }

        $query .= " ORDER BY cls.created_at DESC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT cls.*, c.course_name as course_name, c.course_code as course_code, t.name as teacher_name 
            FROM classes cls
            LEFT JOIN courses c ON cls.course_id = c.id
            LEFT JOIN teachers t ON cls.teacher_id = t.id
            WHERE cls.id = ? AND cls.deleted_at IS NULL
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function checkDuplicate($field, $value, $exclude_id = null) {
        $query = "SELECT id FROM classes WHERE $field = ? AND deleted_at IS NULL";
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
            INSERT INTO classes (
                name, course_id, teacher_id, room_number, capacity, status
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "siisis", 
            $data['name'], $data['course_id'], $data['teacher_id'], 
            $data['room_number'], $data['capacity'], $data['status']
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
            UPDATE classes SET 
                name = ?, course_id = ?, teacher_id = ?, 
                room_number = ?, capacity = ?, status = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "siisisi", 
            $data['name'], $data['course_id'], $data['teacher_id'], 
            $data['room_number'], $data['capacity'], $data['status'], $id
        );
        
        $success = $stmt->execute();
        if ($success) {
            $this->logAction('update', $id, json_encode($old_data), json_encode($data));
        }
        return $success;
    }

    public function softDelete($id) {
        $old_data = $this->getById($id);
        $stmt = $this->db->prepare("UPDATE classes SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        if ($success) {
            $this->logAction('delete', $id, json_encode($old_data), null);
        }
        return $success;
    }

    public function getDashboardStats() {
        $stats = [];
        
        $result = $this->db->query("SELECT COUNT(*) as count FROM classes WHERE deleted_at IS NULL");
        $stats['total'] = $result->fetch_assoc()['count'];
        
        $result = $this->db->query("SELECT COUNT(*) as count FROM classes WHERE status = 'active' AND deleted_at IS NULL");
        $stats['active'] = $result->fetch_assoc()['count'];
        
        $result = $this->db->query("SELECT SUM(capacity) as total_capacity FROM classes WHERE status = 'active' AND deleted_at IS NULL");
        $stats['total_capacity'] = $result->fetch_assoc()['total_capacity'];

        return $stats;
    }

    private function logAction($action, $record_id, $old_value, $new_value) {
        $user_id = $_SESSION['admin_id'] ?? ($_SESSION['sub_admin_id'] ?? 0);
        $user_type = 'admin';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        
        $stmt = $this->db->prepare("INSERT INTO audit_logs (user_id, user_type, action, module, record_id, old_value, new_value, ip_address) VALUES (?, ?, ?, 'Class Management', ?, ?, ?, ?)");
        $stmt->bind_param("ississs", $user_id, $user_type, $action, $record_id, $old_value, $new_value, $ip);
        $stmt->execute();
    }
}
