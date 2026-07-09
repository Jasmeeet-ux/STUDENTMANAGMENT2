<?php
require_once __DIR__ . '/../Database.php';

class Attendance {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // --- Dropdown Data ---
    public function getSections() {
        $result = $this->db->query("
            SELECT s.id, s.name, cls.name as class_name, c.course_name 
            FROM sections s
            JOIN classes cls ON s.class_id = cls.id
            JOIN courses c ON cls.course_id = c.id
            WHERE s.status = 'active' AND s.deleted_at IS NULL
            ORDER BY c.course_name ASC, cls.name ASC, s.name ASC
        ");
        return $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getSectionSubjects($section_id) {
        $stmt = $this->db->prepare("
            SELECT sub.id, sub.name 
            FROM subjects sub
            JOIN section_subjects ss ON sub.id = ss.subject_id
            WHERE ss.section_id = ? AND sub.status = 'active' AND sub.deleted_at IS NULL
        ");
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    // --- Marking Attendance ---
    public function getStudentsForAttendance($section_id) {
        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.reg_no 
            FROM users u
            JOIN section_students ss ON u.id = ss.student_id
            WHERE ss.section_id = ? AND u.role = 'student'
            ORDER BY u.name ASC
        ");
        $stmt->bind_param("i", $section_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAttendanceRecord($section_id, $subject_id, $date) {
        $query = "SELECT student_id, status, remarks FROM erp_attendance WHERE section_id = ? AND date = ? AND deleted_at IS NULL";
        if ($subject_id) {
            $query .= " AND subject_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("isi", $section_id, $date, $subject_id);
        } else {
            $query .= " AND subject_id IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("is", $section_id, $date);
        }
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        $record = [];
        foreach ($result as $row) {
            $record[$row['student_id']] = [
                'status' => $row['status'],
                'remarks' => $row['remarks']
            ];
        }
        return $record;
    }

    public function saveAttendance($section_id, $subject_id, $date, $attendance_data) {
        $teacher_id = $_SESSION['teacher_id'] ?? null; // If a teacher is logged in
        $subject_val = $subject_id ? $subject_id : null;
        
        $this->db->begin_transaction();
        try {
            foreach ($attendance_data as $student_id => $data) {
                $status = $data['status'];
                $remarks = $data['remarks'] ?? '';
                
                // Use INSERT ON DUPLICATE KEY UPDATE
                $stmt = $this->db->prepare("
                    INSERT INTO erp_attendance (student_id, section_id, subject_id, teacher_id, date, status, remarks)
                    VALUES (?, ?, ?, ?, ?, ?, ?)
                    ON DUPLICATE KEY UPDATE status = ?, remarks = ?, updated_at = CURRENT_TIMESTAMP
                ");
                $stmt->bind_param("iiiisssss", 
                    $student_id, $section_id, $subject_val, $teacher_id, $date, $status, $remarks,
                    $status, $remarks
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

    // --- Reports and History ---
    public function getMonthlyReport($section_id, $subject_id, $month, $year) {
        $date_pattern = "$year-$month-%";
        
        $query = "
            SELECT a.date, a.status, u.id as student_id, u.name as student_name, u.reg_no
            FROM erp_attendance a
            JOIN users u ON a.student_id = u.id
            WHERE a.section_id = ? AND a.date LIKE ? AND a.deleted_at IS NULL
        ";
        
        if ($subject_id) {
            $query .= " AND a.subject_id = ?";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("isi", $section_id, $date_pattern, $subject_id);
        } else {
            $query .= " AND a.subject_id IS NULL";
            $stmt = $this->db->prepare($query);
            $stmt->bind_param("is", $section_id, $date_pattern);
        }
        
        $stmt->execute();
        $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        
        // Structure: [student_id => ['name' => ..., 'reg_no' => ..., 'attendance' => ['YYYY-MM-DD' => 'Present', ...], 'stats' => ['Present' => 0, ...]]]
        $report = [];
        foreach ($result as $row) {
            $sid = $row['student_id'];
            if (!isset($report[$sid])) {
                $report[$sid] = [
                    'name' => $row['student_name'],
                    'reg_no' => $row['reg_no'],
                    'attendance' => [],
                    'stats' => ['Present' => 0, 'Absent' => 0, 'Late' => 0, 'Leave' => 0]
                ];
            }
            $report[$sid]['attendance'][$row['date']] = $row['status'];
            $report[$sid]['stats'][$row['status']]++;
        }
        
        return $report;
    }

    public function getStudentHistory($student_id) {
        $stmt = $this->db->prepare("
            SELECT a.date, a.status, a.remarks, s.name as section_name, sub.name as subject_name
            FROM erp_attendance a
            LEFT JOIN sections s ON a.section_id = s.id
            LEFT JOIN subjects sub ON a.subject_id = sub.id
            WHERE a.student_id = ? AND a.deleted_at IS NULL
            ORDER BY a.date DESC
            LIMIT 100
        ");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getDashboardStats() {
        $stats = [];
        $today = date('Y-m-d');
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_attendance WHERE date = '$today' AND deleted_at IS NULL");
        $stats['total_marked_today'] = $res->fetch_assoc()['cnt'];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_attendance WHERE date = '$today' AND status = 'Present' AND deleted_at IS NULL");
        $stats['present_today'] = $res->fetch_assoc()['cnt'];
        
        $res = $this->db->query("SELECT COUNT(*) as cnt FROM erp_attendance WHERE date = '$today' AND status = 'Absent' AND deleted_at IS NULL");
        $stats['absent_today'] = $res->fetch_assoc()['cnt'];
        
        $stats['attendance_percentage'] = $stats['total_marked_today'] > 0 
            ? round(($stats['present_today'] / $stats['total_marked_today']) * 100, 1) 
            : 0;

        return $stats;
    }
}
