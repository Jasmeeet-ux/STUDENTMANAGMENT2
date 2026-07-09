<?php
require_once __DIR__ . '/../models/Student.php';

class StudentController {
    private $studentModel;

    public function __construct() {
        $this->studentModel = new Student();
    }

    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? ''
        ];
        
        $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        $limit = 50;
        $offset = ($page - 1) * $limit;

        $students = $this->studentModel->getAll($filters, $limit, $offset);
        $totalStudents = $this->studentModel->getTotalCount($filters);
        $totalPages = ceil($totalStudents / $limit);
        
        require __DIR__ . '/../views/students/index.php';
    }

    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=students');
            exit;
        }
        $student = $this->studentModel->getById($id);
        if (!$student) {
            header('Location: ?module=students');
            exit;
        }
        
        require __DIR__ . '/../views/students/show.php';
    }
}
