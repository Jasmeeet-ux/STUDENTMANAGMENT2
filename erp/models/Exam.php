<?php
require_once __DIR__ . '/../Database.php';

class Exam {
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

    public function getAll($filters = []) {
        $query = "
            SELECT e.*, cls.name as class_name, c.course_name,
                   (SELECT COUNT(*) FROM erp_exam_subjects WHERE exam_id = e.id) as subject_count
            FROM erp_exams e
            LEFT JOIN classes cls ON e.class_id = cls.id
            LEFT JOIN courses c ON cls.course_id = c.id
            WHERE e.deleted_at IS NULL
        ";

        if (!empty($filters['search'])) {
            $search = $this->db->real_escape_string($filters['search']);
            $query .= " AND (e.name LIKE '%$search%' OR cls.name LIKE '%$search%')";
        }
        if (!empty($filters['class_id'])) {
            $class_id = (int)$filters['class_id'];
            $query .= " AND e.class_id = $class_id";
        }
        if (!empty($filters['status'])) {
            $status = $this->db->real_escape_string($filters['status']);
            $query .= " AND e.status = '$status'";
        }

        $query .= " ORDER BY e.created_at DESC";
        $result = $this->db->query($query);
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getById($id) {
        $stmt = $this->db->prepare("
            SELECT e.*, cls.name as class_name, c.course_name 
            FROM erp_exams e
            LEFT JOIN classes cls ON e.class_id = cls.id
            LEFT JOIN courses c ON cls.course_id = c.id
            WHERE e.id = ? AND e.deleted_at IS NULL
        ");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function create($data) {
        $stmt = $this->db->prepare("
            INSERT INTO erp_exams (
                name, exam_type, class_id, start_date, end_date, status
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $start = !empty($data['start_date']) ? $data['start_date'] : null;
        $end = !empty($data['end_date']) ? $data['end_date'] : null;
        
        $stmt->bind_param(
            "ssisss", 
            $data['name'], $data['exam_type'], $data['class_id'], 
            $start, $end, $data['status']
        );
        
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    public function update($id, $data) {
        $stmt = $this->db->prepare("
            UPDATE erp_exams SET 
                name = ?, exam_type = ?, class_id = ?, 
                start_date = ?, end_date = ?, status = ?
            WHERE id = ?
        ");
        
        $start = !empty($data['start_date']) ? $data['start_date'] : null;
        $end = !empty($data['end_date']) ? $data['end_date'] : null;
        
        $stmt->bind_param(
            "ssisssi", 
            $data['name'], $data['exam_type'], $data['class_id'], 
            $start, $end, $data['status'], $id
        );
        
        return $stmt->execute();
    }

    public function softDelete($id) {
        $stmt = $this->db->prepare("UPDATE erp_exams SET deleted_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    // --- Exam Subjects ---
    
    public function getExamSubjects($exam_id) {
        $stmt = $this->db->prepare("
            SELECT es.*, s.name as subject_name, s.code as subject_code
            FROM erp_exam_subjects es
            JOIN subjects s ON es.subject_id = s.id
            WHERE es.exam_id = ?
        ");
        $stmt->bind_param("i", $exam_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getExamSubjectById($exam_subject_id) {
        $stmt = $this->db->prepare("
            SELECT es.*, s.name as subject_name, s.code as subject_code, e.class_id, e.name as exam_name
            FROM erp_exam_subjects es
            JOIN subjects s ON es.subject_id = s.id
            JOIN erp_exams e ON es.exam_id = e.id
            WHERE es.id = ?
        ");
        $stmt->bind_param("i", $exam_subject_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getAvailableSubjectsForExam($class_id) {
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

    public function addExamSubject($data) {
        $stmt = $this->db->prepare("
            INSERT INTO erp_exam_subjects (
                exam_id, subject_id, exam_date, internal_max_marks, external_max_marks, passing_marks
            ) VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param("iisddd", 
            $data['exam_id'], $data['subject_id'], $data['exam_date'], 
            $data['internal_max_marks'], $data['external_max_marks'], $data['passing_marks']
        );
        return $stmt->execute();
    }
    
    public function removeExamSubject($exam_subject_id) {
        $stmt = $this->db->prepare("DELETE FROM erp_exam_subjects WHERE id = ?");
        $stmt->bind_param("i", $exam_subject_id);
        return $stmt->execute();
    }

    // --- Exam Marks ---
    
    public function getStudentsForExam($class_id) {
        // Get all students enrolled in sections of this class
        $stmt = $this->db->prepare("
            SELECT DISTINCT u.id, u.name, u.reg_no, sec.name as section_name
            FROM users u
            JOIN section_students ss ON u.id = ss.student_id
            JOIN sections sec ON ss.section_id = sec.id
            WHERE sec.class_id = ? AND u.role = 'student'
            ORDER BY sec.name ASC, u.name ASC
        ");
        $stmt->bind_param("i", $class_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getExamMarks($exam_subject_id) {
        $stmt = $this->db->prepare("
            SELECT * FROM erp_exam_marks WHERE exam_subject_id = ?
        ");
        $stmt->bind_param("i", $exam_subject_id);
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $marks = [];
        foreach($result as $row) {
            $marks[$row['student_id']] = $row;
        }
        return $marks;
    }

    public function calculateGrade($total_marks, $max_total) {
        if ($max_total <= 0) return 'F';
        $percent = ($total_marks / $max_total) * 100;
        
        if ($percent >= 90) return 'A+';
        if ($percent >= 80) return 'A';
        if ($percent >= 70) return 'B+';
        if ($percent >= 60) return 'B';
        if ($percent >= 50) return 'C';
        if ($percent >= 40) return 'D';
        return 'F';
    }

    public function saveMarks($exam_subject_id, $marks_data) {
        // Get max marks to calculate grades
        $es = $this->getExamSubjectById($exam_subject_id);
        $max_total = $es['internal_max_marks'] + $es['external_max_marks'];
        
        $this->db->begin_transaction();
        try {
            foreach($marks_data as $student_id => $m) {
                $is_absent = isset($m['is_absent']) ? 1 : 0;
                $internal = isset($m['internal_marks']) && $m['internal_marks'] !== '' ? (float)$m['internal_marks'] : null;
                $external = isset($m['external_marks']) && $m['external_marks'] !== '' ? (float)$m['external_marks'] : null;
                
                $total = 0;
                if ($internal !== null) $total += $internal;
                if ($external !== null) $total += $external;
                
                $grade = $is_absent ? 'F' : $this->calculateGrade($total, $max_total);
                if ($is_absent) {
                    $internal = 0; $external = 0; $total = 0;
                }
                
                $stmt = $this->db->prepare("
                    INSERT INTO erp_exam_marks (exam_subject_id, student_id, internal_marks, external_marks, total_marks, grade, is_absent)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE 
                        internal_marks = ?, external_marks = ?, total_marks = ?, grade = ?, is_absent = ?, updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->bind_param("iidddsiddsii", 
                    $exam_subject_id, $student_id, $internal, $external, $total, $grade, $is_absent,
                    $internal, $external, $total, $grade, $is_absent
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

    public function getStudentResult($exam_id, $student_id) {
        $stmt = $this->db->prepare("
            SELECT m.*, es.internal_max_marks, es.external_max_marks, es.passing_marks,
                   s.name as subject_name, s.code as subject_code
            FROM erp_exam_marks m
            JOIN erp_exam_subjects es ON m.exam_subject_id = es.id
            JOIN subjects s ON es.subject_id = s.id
            WHERE es.exam_id = ? AND m.student_id = ?
        ");
        $stmt->bind_param("ii", $exam_id, $student_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
    
    public function getDashboardStats() {
        $stats = [];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_exams WHERE deleted_at IS NULL");
        $stats['total'] = $res->fetch_assoc()['cnt'];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_exams WHERE status = 'Upcoming' AND deleted_at IS NULL");
        $stats['upcoming'] = $res->fetch_assoc()['cnt'];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_exams WHERE status = 'Ongoing' AND deleted_at IS NULL");
        $stats['ongoing'] = $res->fetch_assoc()['cnt'];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_exams WHERE status = 'Completed' AND deleted_at IS NULL");
        $stats['completed'] = $res->fetch_assoc()['cnt'];

        return $stats;
    }
}
