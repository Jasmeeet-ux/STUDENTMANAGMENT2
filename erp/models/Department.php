<?php
require_once __DIR__ . '/../Database.php';

class Department {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($filters = []) {
        $query = "SELECT * FROM departments WHERE deleted_at IS NULL";

        if (!empty($filters['search'])) {
            $search = $this->db->real_escape_string($filters['search']);
            $query .= " AND name LIKE '%$search%'";
        }
        if (!empty($filters['status'])) {
            $status = $this->db->real_escape_string($filters['status']);
            $query .= " AND status = '$status'";
        }

        $query .= " ORDER BY name ASC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id) {
        $stmt = $this->db->prepare("SELECT * FROM departments WHERE id = ? AND deleted_at IS NULL");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getCourseCount($department_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM courses WHERE department_id = ? AND deleted_at IS NULL");
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res['cnt'];
    }

    public function getTeacherCount($department_id) {
        $stmt = $this->db->prepare("SELECT COUNT(*) as cnt FROM teachers WHERE department_id = ? AND deleted_at IS NULL");
        $stmt->bind_param("i", $department_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        return $res['cnt'];
    }

    public function create($data) {
        $stmt = $this->db->prepare("INSERT INTO departments (name, description, status) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $data['name'], $data['description'], $data['status']);
        $success = $stmt->execute();
        if ($success) {
            $this->logAction('create', $this->db->insert_id, null, json_encode($data));
            return $this->db->insert_id;
        }
        return false;
    }

    public function update($id, $data) {
        $old_data = $this->getById($id);
        $stmt = $this->db->prepare("UPDATE departments SET name = ?, description = ?, status = ? WHERE id = ?");
        $stmt->bind_param("sssi", $data['name'], $data['description'], $data['status'], $id);
        $success = $stmt->execute();
        if ($success) {
            $this->logAction('update', $id, json_encode($old_data), json_encode($data));
        }
        return $success;
    }

    public function softDelete($id) {
        $old_data = $this->getById($id);
        $stmt = $this->db->prepare("UPDATE departments SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        if ($success) {
            $this->logAction('delete', $id, json_encode($old_data), null);
        }
        return $success;
    }

    public function getDashboardStats() {
        $stats = [];
        $res = $this->db->query("SELECT COUNT(*) as total FROM departments WHERE deleted_at IS NULL");
        $stats['total'] = $res->fetch_assoc()['total'];

        $res = $this->db->query("SELECT COUNT(*) as active FROM departments WHERE status='active' AND deleted_at IS NULL");
        $stats['active'] = $res->fetch_assoc()['active'];
        
        $stats['courses'] = $this->db->query("SELECT COUNT(*) as total FROM courses WHERE deleted_at IS NULL")->fetch_assoc()['total'];
        
        return $stats;
    }

    public function checkDuplicate($field, $value, $exclude_id = null) {
        $query = "SELECT id FROM departments WHERE $field = ? AND deleted_at IS NULL";
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

    private function logAction($action, $record_id, $old_value, $new_value) {
        $user_id = $_SESSION['admin_id'] ?? ($_SESSION['sub_admin_id'] ?? 0);
        $stmt = $this->db->prepare("INSERT INTO audit_logs (user_id, user_type, action, module, record_id, old_value, new_value, ip_address) VALUES (?, 'admin', ?, 'Department Management', ?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt->bind_param("isisss", $user_id, $action, $record_id, $old_value, $new_value, $ip);
        $stmt->execute();
    }
}
