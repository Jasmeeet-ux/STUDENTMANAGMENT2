<?php
require_once __DIR__ . '/../models/Course.php';

class CourseController {
    private $courseModel;

    public function __construct() {
        $this->courseModel = new Course();
    }

    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'department_id' => $_GET['department_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $courses = $this->courseModel->getAll($filters);
        $departments = $this->courseModel->getDepartments();
        $stats = $this->courseModel->getDashboardStats();
        
        require __DIR__ . '/../views/courses/index.php';
    }

    public function create() {
        $departments = $this->courseModel->getDepartments();
        require __DIR__ . '/../views/courses/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            if ($this->courseModel->checkDuplicate('code', $data['code'])) {
                $_SESSION['error'] = 'Course Code already exists.';
                header('Location: ?module=courses&action=create');
                exit;
            }

            if ($this->courseModel->create($data)) {
                $_SESSION['success'] = 'Course created successfully.';
                header('Location: ?module=courses');
            } else {
                $_SESSION['error'] = 'Failed to create course.';
                header('Location: ?module=courses&action=create');
            }
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=courses');
            exit;
        }
        $course = $this->courseModel->getById($id);
        $departments = $this->courseModel->getDepartments();
        require __DIR__ . '/../views/courses/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $data = $_POST;

            if ($this->courseModel->checkDuplicate('code', $data['code'], $id)) {
                $_SESSION['error'] = 'Course Code already exists.';
                header('Location: ?module=courses&action=edit&id=' . $id);
                exit;
            }

            if ($this->courseModel->update($id, $data)) {
                $_SESSION['success'] = 'Course updated successfully.';
                header('Location: ?module=courses');
            } else {
                $_SESSION['error'] = 'Failed to update course.';
                header('Location: ?module=courses&action=edit&id=' . $id);
            }
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($this->courseModel->softDelete($id)) {
                $_SESSION['success'] = 'Course deleted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to delete course.';
            }
        }
        header('Location: ?module=courses');
        exit;
    }
}
