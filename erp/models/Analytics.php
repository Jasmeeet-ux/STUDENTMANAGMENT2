<?php
require_once __DIR__ . '/../Database.php';

class Analytics {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function getSummaryStats() {
        $stats = [];
        // Core counts
        $stats['total_students'] = $this->db->query("SELECT COUNT(*) as c FROM users WHERE role='student'")->fetch_assoc()['c'];
        $stats['total_teachers'] = $this->db->query("SELECT COUNT(*) as c FROM teachers WHERE deleted_at IS NULL")->fetch_assoc()['c'];
        $stats['total_departments'] = $this->db->query("SELECT COUNT(*) as c FROM departments WHERE deleted_at IS NULL")->fetch_assoc()['c'];
        $stats['total_courses'] = $this->db->query("SELECT COUNT(*) as c FROM courses WHERE deleted_at IS NULL")->fetch_assoc()['c'];
        $stats['total_subjects'] = $this->db->query("SELECT COUNT(*) as c FROM subjects WHERE deleted_at IS NULL")->fetch_assoc()['c'];
        $stats['total_classes'] = $this->db->query("SELECT COUNT(*) as c FROM classes WHERE deleted_at IS NULL")->fetch_assoc()['c'];
        $stats['total_sections'] = $this->db->query("SELECT COUNT(*) as c FROM sections WHERE deleted_at IS NULL")->fetch_assoc()['c'];
        
        // Attendance Today
        $att = $this->db->query("SELECT COUNT(*) as total, SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present FROM erp_attendance WHERE `date`=CURDATE()")->fetch_assoc();
        $stats['attendance_today_percent'] = ($att['total'] > 0) ? round(($att['present'] / $att['total']) * 100, 1) : 0;
        
        // Exams & Assignments
        $stats['upcoming_exams'] = $this->db->query("SELECT COUNT(*) as c FROM erp_exams WHERE start_date >= CURDATE()")->fetch_assoc()['c'];
        $stats['pending_assignments'] = $this->db->query("SELECT COUNT(*) as c FROM erp_assignments WHERE deleted_at IS NULL")->fetch_assoc()['c'];
        
        // Leaves
        $stats['pending_leaves'] = $this->db->query("SELECT COUNT(*) as c FROM erp_leaves WHERE status='Pending'")->fetch_assoc()['c'];
        
        // Fees
        $fees = $this->db->query("SELECT SUM(amount) as collected FROM erp_fee_payments")->fetch_assoc();
        $inv = $this->db->query("SELECT SUM(total_amount + fine_amount) as total_due FROM erp_fee_invoices")->fetch_assoc();
        $stats['fee_collected'] = $fees['collected'] ?? 0;
        $stats['fee_pending'] = ($inv['total_due'] ?? 0) - ($fees['collected'] ?? 0);
        
        return $stats;
    }

    public function getRecentlyAddedStudents($limit = 5) {
        $res = $this->db->query("SELECT id, name, email, reg_no as id_number FROM users WHERE role='student' ORDER BY id DESC LIMIT " . (int)$limit);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getRecentlyAddedTeachers($limit = 5) {
        $res = $this->db->query("SELECT t.id, t.name, t.email, d.name as department FROM teachers t LEFT JOIN departments d ON t.department_id = d.id WHERE t.deleted_at IS NULL ORDER BY t.created_at DESC LIMIT " . (int)$limit);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getActiveCourses() {
        $res = $this->db->query("SELECT c.course_name, c.course_code, d.name as department FROM courses c LEFT JOIN departments d ON c.department_id = d.id WHERE c.status='active' AND c.deleted_at IS NULL ORDER BY c.created_at DESC LIMIT 5");
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getSubjectsByDepartment() {
        $query = "
            SELECT d.name as label, COUNT(s.id) as value
            FROM departments d
            LEFT JOIN courses c ON d.id = c.department_id
            LEFT JOIN subjects s ON c.id = s.course_id AND s.deleted_at IS NULL
            WHERE d.deleted_at IS NULL
            GROUP BY d.id
        ";
        $res = $this->db->query($query);
        $data = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
        
        // Shorten names so they don't get cut off in the pie chart legend
        $abbreviations = [
            'Computer Science and Engineering' => 'CSE',
            'Information Technology' => 'IT',
            'Mechanical Engineering' => 'Mech Eng',
            'Civil Engineering' => 'Civil Eng',
            'Electrical Engineering' => 'Elec Eng',
            'Electronics and Communication' => 'ECE',
            'Business Administration' => 'BBA',
            'Commerce and Finance' => 'Com & Fin',
            'Arts and Humanities' => 'Arts',
            'Applied Sciences' => 'Sciences',
            'Architecture' => 'Arch',
            'Chemical Engineering' => 'Chem Eng',
            'Aerospace Engineering' => 'Aero Eng',
            'Biotechnology' => 'BioTech'
        ];
        
        foreach ($data as &$row) {
            if (isset($abbreviations[$row['label']])) {
                $row['label'] = $abbreviations[$row['label']];
            } else if (strlen($row['label']) > 15) {
                $row['label'] = substr($row['label'], 0, 12) . '...';
            }
        }
        
        return $data;
    }

    public function getTodayAttendance() {
        $stats = ['Present' => 0, 'Absent' => 0, 'Late' => 0, 'Leave' => 0];
        $res = $this->db->query("SELECT status, COUNT(*) as count FROM erp_attendance WHERE `date`=CURDATE() GROUP BY status");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $stats[$row['status']] = $row['count'];
            }
        }
        return $stats;
    }

    public function getAttendanceTrend() {
        $query = "
            SELECT DATE_FORMAT(`date`, '%Y-%m') as month,
                   COUNT(*) as total,
                   SUM(CASE WHEN status='Present' THEN 1 ELSE 0 END) as present
            FROM erp_attendance
            WHERE `date` >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
            GROUP BY month
            ORDER BY month ASC
        ";
        $result = $this->db->query($query);
        $data = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
        $trend = [];
        foreach($data as $row) {
            $trend[] = [
                'label' => date('M y', strtotime($row['month'] . '-01')),
                'value' => ($row['total'] > 0) ? round(($row['present'] / $row['total']) * 100, 1) : 0
            ];
        }
        return $trend;
    }

    public function getRecentlyCompletedExams($limit = 5) {
        $res = $this->db->query("SELECT name, exam_type, start_date, end_date FROM erp_exams WHERE status='Completed' ORDER BY end_date DESC LIMIT " . (int)$limit);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }
    
    public function getUpcomingExams($limit = 5) {
        $res = $this->db->query("SELECT name, exam_type, start_date FROM erp_exams WHERE status='Upcoming' ORDER BY start_date ASC LIMIT " . (int)$limit);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getPendingAssignments($limit = 5) {
        $res = $this->db->query("SELECT a.title, a.due_date, s.name as subject FROM erp_assignments a JOIN subjects s ON a.subject_id = s.id WHERE a.status='Active' AND a.due_date >= CURDATE() ORDER BY a.due_date ASC LIMIT " . (int)$limit);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getRecentlySubmittedAssignments($limit = 5) {
        $res = $this->db->query("SELECT a.title, u.name as student, sub.submitted_at, sub.status FROM erp_assignment_submissions sub JOIN erp_assignments a ON sub.assignment_id = a.id JOIN users u ON sub.student_id = u.id ORDER BY sub.submitted_at DESC LIMIT " . (int)$limit);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getLeaveRequestsStats() {
        $stats = ['Pending' => 0, 'Approved' => 0, 'Rejected' => 0];
        $res = $this->db->query("SELECT status, COUNT(*) as count FROM erp_leaves GROUP BY status");
        if ($res) {
            while ($row = $res->fetch_assoc()) {
                $stats[$row['status']] = $row['count'];
            }
        }
        return $stats;
    }

    public function getRecentPayments($limit = 5) {
        $res = $this->db->query("SELECT p.amount, p.payment_date, p.payment_method, u.name as student FROM erp_fee_payments p JOIN erp_fee_invoices i ON p.invoice_id = i.id JOIN users u ON i.student_id = u.id ORDER BY p.payment_date DESC LIMIT " . (int)$limit);
        return $res ? $res->fetch_all(MYSQLI_ASSOC) : [];
    }

    public function getRecentActivities() {
        $activities = [];
        
        $res = $this->db->query("SELECT name as title, 'Exam Scheduled' as type, created_at FROM erp_exams ORDER BY created_at DESC LIMIT 3");
        while ($row = $res->fetch_assoc()) $activities[] = $row;
        
        $res = $this->db->query("SELECT title, 'Assignment Created' as type, created_at FROM erp_assignments ORDER BY created_at DESC LIMIT 3");
        while ($row = $res->fetch_assoc()) $activities[] = $row;
        
        $res = $this->db->query("SELECT leave_type as title, CONCAT('Leave ', status) as type, updated_at as created_at FROM erp_leaves ORDER BY updated_at DESC LIMIT 4");
        while ($row = $res->fetch_assoc()) $activities[] = $row;
        
        usort($activities, function($a, $b) {
            return strtotime($b['created_at']) - strtotime($a['created_at']);
        });
        
        return array_slice($activities, 0, 8);
    }
}
