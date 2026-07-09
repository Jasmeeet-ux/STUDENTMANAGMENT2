<?php
require_once __DIR__ . '/../Database.php';

class Assignment {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getClasses() {
        $result = $this->db->query("
            SELECT cls.id, cls.name as class_name, c.course_name 
            FROM classes cls 
            LEFT JOIN courses c ON cls.course_id = c.id 
            WHERE cls.status='active' AND cls.deleted_at IS NULL
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getSubjects() {
        $result = $this->db->query("SELECT id, name, code FROM subjects WHERE status='active' AND deleted_at IS NULL");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getTeachers() {
        $result = $this->db->query("SELECT id, name FROM teachers WHERE status='active' AND deleted_at IS NULL");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getAll($filters = []) {
        $query = "
            SELECT a.*, cls.name as class_name, c.course_name, s.name as subject_name, t.name as teacher_name,
                   (SELECT COUNT(*) FROM erp_assignment_submissions WHERE assignment_id = a.id) as submission_count,
                   (SELECT COUNT(*) FROM section_students ss JOIN sections sec ON ss.section_id = sec.id WHERE sec.class_id = a.class_id) as total_students
            FROM erp_assignments a
            LEFT JOIN classes cls ON a.class_id = cls.id
            LEFT JOIN courses c ON cls.course_id = c.id
            LEFT JOIN subjects s ON a.subject_id = s.id
            LEFT JOIN teachers t ON a.teacher_id = t.id
            WHERE a.deleted_at IS NULL
        ";

        if (!empty($filters['search'])) {
            $search = $this->db->real_escape_string($filters['search']);
            $query .= " AND (a.title LIKE '%$search%' OR cls.name LIKE '%$search%' OR s.name LIKE '%$search%')";
        }
        if (!empty($filters['class_id'])) {
            $class_id = (int)$filters['class_id'];
            $query .= " AND a.class_id = $class_id";
        }
        if (!empty($filters['subject_id'])) {
            $subject_id = (int)$filters['subject_id'];
            $query .= " AND a.subject_id = $subject_id";
        }
        if (!empty($filters['status'])) {
            $status = $this->db->real_escape_string($filters['status']);
            $query .= " AND a.status = '$status'";
        }

        $query .= " ORDER BY a.due_date DESC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT a.*, cls.name as class_name, c.course_name, s.name as subject_name, t.name as teacher_name
            FROM erp_assignments a
            LEFT JOIN classes cls ON a.class_id = cls.id
            LEFT JOIN courses c ON cls.course_id = c.id
            LEFT JOIN subjects s ON a.subject_id = s.id
            LEFT JOIN teachers t ON a.teacher_id = t.id
            WHERE a.id = ? AND a.deleted_at IS NULL
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO erp_assignments (
                title, description, class_id, subject_id, teacher_id, due_date, max_marks, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $due_date = !empty($data['due_date']) ? $data['due_date'] : date('Y-m-d H:i:s', strtotime('+7 days'));
        
        $stmt->bind_param(
            "ssiiisds", 
            $data['title'], $data['description'], $data['class_id'], 
            $data['subject_id'], $data['teacher_id'], $due_date, 
            $data['max_marks'], $data['status']
        );
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE erp_assignments SET 
                title = ?, description = ?, class_id = ?, 
                subject_id = ?, teacher_id = ?, due_date = ?, 
                max_marks = ?, status = ?
            WHERE id = ?
        ");
        
        $stmt->bind_param(
            "ssiiisdsi", 
            $data['title'], $data['description'], $data['class_id'], 
            $data['subject_id'], $data['teacher_id'], $data['due_date'], 
            $data['max_marks'], $data['status'], $id
        );
        
        return $stmt->execute();
    }

    public function softDelete($id) {
        $stmt = $this->db->prepare("UPDATE erp_assignments SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
    
    // --- Submissions ---
    public function getSubmissions($assignment_id) {
        // Get all students enrolled in the class of this assignment
        $stmt = $this->db->prepare("
            SELECT u.id as student_id, u.name as student_name, u.reg_no, sec.name as section_name,
                   sub.id as submission_id, sub.submission_text, sub.file_path, sub.marks_obtained, 
                   sub.feedback, sub.status as sub_status, sub.submitted_at
            FROM erp_assignments a
            JOIN sections sec ON a.class_id = sec.class_id
            JOIN section_students ss ON sec.id = ss.section_id
            JOIN users u ON ss.student_id = u.id
            LEFT JOIN erp_assignment_submissions sub ON a.id = sub.assignment_id AND u.id = sub.student_id
            WHERE a.id = ? AND u.role = 'student'
            ORDER BY sec.name ASC, u.name ASC
        ");
        $stmt->bind_param("i", $assignment_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function saveGrades($assignment_id, $grades_data) {
        $this->db->begin_transaction();
        try {
            foreach ($grades_data as $student_id => $data) {
                $marks = isset($data['marks_obtained']) && $data['marks_obtained'] !== '' ? (float)$data['marks_obtained'] : null;
                $feedback = $data['feedback'] ?? null;
                $status = !empty($data['status']) ? $data['status'] : 'Pending';
                
                // Usually grading implies 'Graded' status if marks are provided
                if ($marks !== null && $status != 'Graded') {
                    $status = 'Graded';
                }

                $stmt = $this->db->prepare("
                    INSERT INTO erp_assignment_submissions (assignment_id, student_id, marks_obtained, feedback, status)
                    VALUES (?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        marks_obtained = ?, feedback = ?, status = ?, updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->bind_param("iidssids", 
                    $assignment_id, $student_id, $marks, $feedback, $status,
                    $marks, $feedback, $status
                );
                $stmt->execute();
            }
            $this->db->commit();
            return true;
        } catch (Exception $e) {
            $this->db->rollback();
            return false;
        }
    }

    public function getDashboardStats() {
        $stats = [];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_assignments WHERE deleted_at IS NULL");
        $stats['total'] = $res->fetch_assoc()['cnt'];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_assignments WHERE status = 'Active' AND deleted_at IS NULL");
        $stats['active'] = $res->fetch_assoc()['cnt'];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_assignment_submissions WHERE status = 'Submitted' OR status = 'Late'");
        $stats['pending_grading'] = $res->fetch_assoc()['cnt'];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_assignment_submissions WHERE status = 'Graded'");
        $stats['graded'] = $res->fetch_assoc()['cnt'];

        return $stats;
    }
}
