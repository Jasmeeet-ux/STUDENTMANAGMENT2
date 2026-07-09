<?php
require_once __DIR__ . '/../models/Attendance.php';

class AttendanceController {
    private $attendanceModel;

    public function __construct() {
        $this->attendanceModel = new Attendance();
    }

    public function index() {
        $sections = $this->attendanceModel->getSections();
        $stats = $this->attendanceModel->getDashboardStats();
        
        $section_id = $_GET['section_id'] ?? '';
        $subject_id = $_GET['subject_id'] ?? '';
        $date = $_GET['date'] ?? date('Y-m-d');
        
        $subjects = [];
        $students = [];
        $attendance_record = [];
        
        if ($section_id) {
            $subjects = $this->attendanceModel->getSectionSubjects($section_id);
            $students = $this->attendanceModel->getStudentsForAttendance($section_id);
            $attendance_record = $this->attendanceModel->getAttendanceRecord($section_id, $subject_id, $date);
        }

        require __DIR__ . '/../views/attendance/index.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $section_id = $_POST['section_id'] ?? null;
            $subject_id = $_POST['subject_id'] ?? null;
            $date = $_POST['date'] ?? null;
            $attendance_data = $_POST['attendance'] ?? []; // format: [student_id => ['status' => '...', 'remarks' => '...']]

            if (!$section_id || !$date || empty($attendance_data)) {
                $_SESSION['error'] = 'Missing required attendance data.';
                header('Location: ?module=attendance');
                exit;
            }

            if ($this->attendanceModel->saveAttendance($section_id, $subject_id, $date, $attendance_data)) {
                $_SESSION['success'] = 'Attendance marked successfully.';
            } else {
                $_SESSION['error'] = 'Failed to mark attendance.';
            }
            
            $url = "?module=attendance&section_id=$section_id&date=$date";
            if ($subject_id) $url .= "&subject_id=$subject_id";
            header("Location: $url");
            exit;
        }
    }

    public function report() {
        $sections = $this->attendanceModel->getSections();
        
        $section_id = $_GET['section_id'] ?? '';
        $subject_id = $_GET['subject_id'] ?? '';
        $month = $_GET['month'] ?? date('m');
        $year = $_GET['year'] ?? date('Y');
        
        $subjects = [];
        $report_data = [];
        $days_in_month = cal_days_in_month(CAL_GREGORIAN, (int)$month, (int)$year);
        
        if ($section_id) {
            $subjects = $this->attendanceModel->getSectionSubjects($section_id);
            $report_data = $this->attendanceModel->getMonthlyReport($section_id, $subject_id, $month, $year);
        }

        require __DIR__ . '/../views/attendance/report.php';
    }

    public function history() {
        // Find a student
        $student_id = $_GET['student_id'] ?? null;
        $history = [];
        
        if ($student_id) {
            $history = $this->attendanceModel->getStudentHistory($student_id);
        }

        require __DIR__ . '/../views/attendance/history.php';
    }
}
