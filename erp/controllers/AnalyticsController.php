<?php
require_once __DIR__ . '/../models/Analytics.php';

class AnalyticsController {
    private $model;

    public function __construct() {
        $this->model = new Analytics();
    }

    public function index() {
        $stats = $this->model->getSummaryStats();
        
        // Data for charts
        $subjectsByDept = json_encode($this->model->getSubjectsByDepartment());
        $attendanceTrend = json_encode($this->model->getAttendanceTrend());
        
        // Other Data
        $recentStudents = $this->model->getRecentlyAddedStudents(5);
        $recentTeachers = $this->model->getRecentlyAddedTeachers(5);
        $activeCourses = $this->model->getActiveCourses();
        $todayAttendance = $this->model->getTodayAttendance();
        $upcomingExams = $this->model->getUpcomingExams();
        $completedExams = $this->model->getRecentlyCompletedExams(3);
        $pendingAssignments = $this->model->getPendingAssignments();
        $submittedAssignments = $this->model->getRecentlySubmittedAssignments(3);
        $leaveStats = $this->model->getLeaveRequestsStats();
        $recentPayments = $this->model->getRecentPayments(5);
        $activities = $this->model->getRecentActivities();
        
        require __DIR__ . '/../views/dashboard/index.php';
    }
}
