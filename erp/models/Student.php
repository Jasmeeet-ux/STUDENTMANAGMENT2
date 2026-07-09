<?php
require_once __DIR__ . '/../Database.php';

class Student {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getAll($filters = [], $limit = 50, $offset = 0) {
        $query = "
            SELECT u.id, u.name, u.reg_no, u.email, 
                   s.name as section_name, c.name as class_name
            FROM users u
            LEFT JOIN section_students ss ON u.id = ss.student_id
            LEFT JOIN sections s ON ss.section_id = s.id
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE u.role = 'student'
        ";

        if (!empty($filters['search'])) {
            $search = $this->db->real_escape_string($filters['search']);
            $query .= " AND (u.name LIKE '%$search%' OR u.reg_no LIKE '%$search%' OR u.email LIKE '%$search%')";
        }

        $query .= " ORDER BY u.id DESC LIMIT $limit OFFSET $offset";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getTotalCount($filters = []) {
        $query = "SELECT COUNT(u.id) as count FROM users u WHERE u.role = 'student'";
        if (!empty($filters['search'])) {
            $search = $this->db->real_escape_string($filters['search']);
            $query .= " AND (u.name LIKE '%$search%' OR u.reg_no LIKE '%$search%' OR u.email LIKE '%$search%')";
        }
        $result = $this->db->query($query);
        return $result ? (int)$result->fetch_assoc()['count'] : 0;
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT u.*, 
                   s.name as section_name, c.name as class_name, c.id as class_id
            FROM users u
            LEFT JOIN section_students ss ON u.id = ss.student_id
            LEFT JOIN sections s ON ss.section_id = s.id
            LEFT JOIN classes c ON s.class_id = c.id
            WHERE u.id = ? AND u.role = 'student'
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }
}
