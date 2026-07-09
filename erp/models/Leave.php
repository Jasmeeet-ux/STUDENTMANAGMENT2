<?php
require_once __DIR__ . '/../Database.php';

class Leave {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getStudents() {
        $result = $this->db->query("SELECT id, name, reg_no FROM users WHERE role='student' ORDER BY name ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getTeachers() {
        $result = $this->db->query("SELECT id, name FROM teachers WHERE status='active' AND deleted_at IS NULL ORDER BY name ASC");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAll($filters = []) {
        $query = "
            SELECT l.*, 
                   COALESCE(u.name, t.name) as applicant_name,
                   COALESCE(u.reg_no, 'Teacher') as applicant_identifier
            FROM erp_leaves l
            LEFT JOIN users u ON l.student_id = u.id
            LEFT JOIN teachers t ON l.teacher_id = t.id
            WHERE 1=1
        ";

        if (!empty($filters['user_type'])) {
            $type = $this->db->real_escape_string($filters['user_type']);
            $query .= " AND l.user_type = '$type'";
        }
        
        if (!empty($filters['status'])) {
            $status = $this->db->real_escape_string($filters['status']);
            $query .= " AND l.status = '$status'";
        }
        
        if (!empty($filters['search'])) {
            $search = $this->db->real_escape_string($filters['search']);
            $query .= " AND (u.name LIKE '%$search%' OR t.name LIKE '%$search%' OR l.leave_type LIKE '%$search%')";
        }

        $query .= " ORDER BY l.created_at DESC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT l.*, 
                   COALESCE(u.name, t.name) as applicant_name,
                   COALESCE(u.reg_no, 'Teacher') as applicant_identifier
            FROM erp_leaves l
            LEFT JOIN users u ON l.student_id = u.id
            LEFT JOIN teachers t ON l.teacher_id = t.id
            WHERE l.id = ?
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO erp_leaves (
                user_type, student_id, teacher_id, leave_type, start_date, end_date, reason, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $student_id = $data['user_type'] == 'student' ? $data['applicant_id'] : null;
        $teacher_id = $data['user_type'] == 'teacher' ? $data['applicant_id'] : null;
        $status = $data['status'] ?? 'Pending';
        
        $stmt->bind_param(
            "siisssss", 
            $data['user_type'], $student_id, $teacher_id, 
            $data['leave_type'], $data['start_date'], $data['end_date'], 
            $data['reason'], $status
        );
        
        return $stmt->execute();
    }

    public function updateStatus($id, $status, $remarks = '') {
        $stmt = $this->db->prepare("
            UPDATE erp_leaves SET status = ?, admin_remarks = ? WHERE id = ?
        ");
        $stmt->bind_param("ssi", $status, $remarks, $id);
        return $stmt->execute();
    }

    public function getDashboardStats() {
        $stats = [
            'total' => 0,
            'pending' => 0,
            'approved' => 0,
            'rejected' => 0,
            'today_leaves' => 0
        ];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_leaves");
        $stats['total'] = $res->fetch_assoc()['cnt'];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_leaves WHERE status = 'Pending'");
        $stats['pending'] = $res->fetch_assoc()['cnt'];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_leaves WHERE status = 'Approved'");
        $stats['approved'] = $res->fetch_assoc()['cnt'];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_leaves WHERE status = 'Rejected'");
        $stats['rejected'] = $res->fetch_assoc()['cnt'];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_leaves WHERE status = 'Approved' AND CURDATE() BETWEEN start_date AND end_date");
        $stats['today_leaves'] = $res->fetch_assoc()['cnt'];

        return $stats;
    }
}
