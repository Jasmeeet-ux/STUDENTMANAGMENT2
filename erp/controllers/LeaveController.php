<?php
require_once __DIR__ . '/../models/Leave.php';

class LeaveController {
    private $leaveModel;

    public function __construct() {
        $this->leaveModel = new Leave();
    }

    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? '',
            'user_type' => $_GET['user_type'] ?? ''
        ];
        
        $leaves = $this->leaveModel->getAll($filters);
        $stats = $this->leaveModel->getDashboardStats();
        
        require __DIR__ . '/../views/leaves/index.php';
    }

    public function apply() {
        $students = $this->leaveModel->getStudents();
        $teachers = $this->leaveModel->getTeachers();
        require __DIR__ . '/../views/leaves/apply.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($this->leaveModel->create($_POST)) {
                $_SESSION['success'] = 'Leave application submitted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to submit leave application.';
            }
            header('Location: ?module=leaves');
            exit;
        }
    }

    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=leaves');
            exit;
        }
        $leave = $this->leaveModel->getById($id);
        require __DIR__ . '/../views/leaves/show.php';
    }

    public function process() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'];
            $status = $_POST['status'];
            $remarks = $_POST['admin_remarks'] ?? '';
            
            if ($this->leaveModel->updateStatus($id, $status, $remarks)) {
                $_SESSION['success'] = 'Leave status updated successfully.';
            } else {
                $_SESSION['error'] = 'Failed to update leave status.';
            }
            header('Location: ?module=leaves&action=show&id=' . $id);
            exit;
        }
    }
}
