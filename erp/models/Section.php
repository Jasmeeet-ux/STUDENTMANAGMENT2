<?php
require_once __DIR__ . '/../Database.php';

class Section {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getClasses() {
        $result = $this->db->query("
            SELECT cls.id, cls.name as class_name, c.course_code 
            FROM classes cls 
            LEFT JOIN courses c ON cls.course_id = c.id 
            WHERE cls.status='active' AND cls.deleted_at IS NULL
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getTeachers() {
        $result = $this->db->query("SELECT id, name FROM teachers WHERE status='active' AND deleted_at IS NULL");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAll($filters = []) {
        $query = "
            SELECT s.*, cls.name as class_name, c.course_code, c.course_name, t.name as teacher_name,
                   (SELECT COUNT(*) FROM section_students WHERE section_id = s.id) as student_count
            FROM sections s
            LEFT JOIN classes cls ON s.class_id = cls.id
            LEFT JOIN courses c ON cls.course_id = c.id
            LEFT JOIN teachers t ON s.teacher_id = t.id
            WHERE s.deleted_at IS NULL
        ";

        if (!empty($filters['search'])) {
            $search = $this->db->real_escape_string($filters['search']);
            $query .= " AND (s.name LIKE '%$search%' OR cls.name LIKE '%$search%' OR s.room_number LIKE '%$search%')";
        }
        if (!empty($filters['class_id'])) {
            $class_id = (int)$filters['class_id'];
            $query .= " AND s.class_id = $class_id";
        }
        if (!empty($filters['status'])) {
            $status = $this->db->real_escape_string($filters['status']);
            $query .= " AND s.status = '$status'";
        }

        $query .= " ORDER BY c.course_name ASC, cls.name ASC, s.name ASC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT s.*, cls.name as class_name, c.course_code, c.course_name, t.name as teacher_name 
            FROM sections s
            LEFT JOIN classes cls ON s.class_id = cls.id
            LEFT JOIN courses c ON cls.course_id = c.id
            LEFT JOIN teachers t ON s.teacher_id = t.id
            WHERE s.id = ? AND s.deleted_at IS NULL
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO sections (
                class_id, name, teacher_id, room_number, capacity, status
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $stmt->bind_param(
            "isisis", 
            $data['class_id'], $data['name'], $data['teacher_id'], 
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
            UPDATE sections SET 
                class_id = ?, name = ?, teacher_id = ?, 
                room_number = ?, capacity = ?, status = ?
            WHERE id = ?
        ");
        $stmt->bind_param(
            "isisisi", 
            $data['class_id'], $data['name'], $data['teacher_id'], 
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
        $stmt = $this->db->prepare("UPDATE sections SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("i", $id);
        $success = $stmt->execute();
        if ($success) {
            $this->logAction('delete', $id, json_encode($old_data), null);
        }
        return $success;
    }

    public function checkDuplicate($class_id, $name, $exclude_id = null) {
        $query = "SELECT id FROM sections WHERE class_id = ? AND name = ? AND deleted_at IS NULL";
        if ($exclude_id) {
            $query .= " AND id != ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("isi", $class_id, $name, $exclude_id);
        } else {
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("is", $class_id, $name);
        }
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    // Assign Students
    public function getAvailableStudents() {
        // Students with role 'student' not yet assigned to any section
        $result = $this->db->query("
            SELECT u.id, u.name, u.email, u.reg_no 
            FROM users u 
            WHERE u.role = 'student' 
            AND u.id NOT IN (SELECT student_id FROM section_students)
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getSectionStudents($section_id) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.email, u.reg_no 
            FROM users u 
            JOIN section_students ss ON u.id = ss.student_id 
            WHERE ss.section_id = ?
        ");
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function addStudentToSection($section_id, $student_id) {
        $stmt = $this->db->prepare("INSERT IGNORE INTO section_students (section_id, student_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $section_id, $student_id);
        return $stmt->execute();
    }
    
    public function removeStudentFromSection($section_id, $student_id) {
        $stmt = $this->db->prepare("DELETE FROM section_students WHERE section_id = ? AND student_id = ?");
        $stmt->bind_param("ii", $section_id, $student_id);
        return $stmt->execute();
    }

    // Assign Subjects
    public function getAvailableSubjects($class_id) {
        // Get subjects for the course that this class belongs to
        $stmt = $this->db->prepare("
            SELECT s.id, s.name, s.code 
            FROM subjects s 
            JOIN classes c ON s.course_id = c.course_id 
            WHERE c.id = ? AND s.deleted_at IS NULL AND s.status = 'active'
        ");
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getSectionSubjects($section_id) {
        $stmt = $this->db->prepare("
            SELECT s.id, s.name, s.code, ss.teacher_id, t.name as teacher_name 
            FROM subjects s 
            JOIN section_subjects ss ON s.id = ss.subject_id 
            LEFT JOIN teachers t ON ss.teacher_id = t.id
            WHERE ss.section_id = ?
        ");
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function addSubjectToSection($section_id, $subject_id, $teacher_id) {
        // Teacher ID can be null
        $stmt = $this->db->prepare("INSERT INTO section_subjects (section_id, subject_id, teacher_id) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE teacher_id = ?");
        $stmt->bind_param("iiii", $section_id, $subject_id, $teacher_id, $teacher_id);
        return $stmt->execute();
    }
    
    public function removeSubjectFromSection($section_id, $subject_id) {
        $stmt = $this->db->prepare("DELETE FROM section_subjects WHERE section_id = ? AND subject_id = ?");
        $stmt->bind_param("ii", $section_id, $subject_id);
        return $stmt->execute();
    }

    public function getDashboardStats() {
        $stats = [];
        
        $result = $this->db->query("SELECT COUNT(*) as count FROM sections WHERE deleted_at IS NULL");
        $stats['total'] = $result->fetch_assoc()['count'];
        
        $result = $this->db->query("SELECT COUNT(*) as count FROM sections WHERE status = 'active' AND deleted_at IS NULL");
        $stats['active'] = $result->fetch_assoc()['count'];
        
        $result = $this->db->query("SELECT SUM(capacity) as total_capacity FROM sections WHERE status = 'active' AND deleted_at IS NULL");
        $stats['total_capacity'] = $result->fetch_assoc()['total_capacity'];

        return $stats;
    }

    private function logAction($action, $record_id, $old_value, $new_value) {
        $user_id = $_SESSION['admin_id'] ?? ($_SESSION['sub_admin_id'] ?? 0);
        $stmt = $this->db->prepare("INSERT INTO audit_logs (user_id, user_type, action, module, record_id, old_value, new_value, ip_address) VALUES (?, 'admin', ?, 'Section Management', ?, ?, ?, ?)");
        $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
        $stmt->bind_param("isisss", $user_id, $action, $record_id, $old_value, $new_value, $ip);
        $stmt->execute();
    }
}
