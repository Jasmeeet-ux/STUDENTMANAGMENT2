<?php
require_once __DIR__ . '/../models/AcademicClass.php';

class ClassController {
    private $classModel;

    public function __construct() {
        $this->classModel = new AcademicClass();
    }

    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'course_id' => $_GET['course_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $classes = $this->classModel->getAll($filters);
        $courses = $this->classModel->getCourses();
        $stats = $this->classModel->getDashboardStats();
        
        require __DIR__ . '/../views/classes/index.php';
    }

    public function create() {
        $courses = $this->classModel->getCourses();
        $teachers = $this->classModel->getTeachers();
        require __DIR__ . '/../views/classes/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            if ($this->classModel->checkDuplicate('name', $data['name'])) {
                $_SESSION['error'] = 'Class Name already exists.';
                header('Location: ?module=classes&action=create');
                exit;
            }

            if ($this->classModel->create($data)) {
                $_SESSION['success'] = 'Class created successfully.';
                header('Location: ?module=classes');
            } else {
                $_SESSION['error'] = 'Failed to create class.';
                header('Location: ?module=classes&action=create');
            }
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=classes');
            exit;
        }
        $class = $this->classModel->getById($id);
        $courses = $this->classModel->getCourses();
        $teachers = $this->classModel->getTeachers();
        require __DIR__ . '/../views/classes/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $data = $_POST;

            if ($this->classModel->checkDuplicate('name', $data['name'], $id)) {
                $_SESSION['error'] = 'Class Name already exists.';
                header('Location: ?module=classes&action=edit&id=' . $id);
                exit;
            }

            if ($this->classModel->update($id, $data)) {
                $_SESSION['success'] = 'Class updated successfully.';
                header('Location: ?module=classes');
            } else {
                $_SESSION['error'] = 'Failed to update class.';
                header('Location: ?module=classes&action=edit&id=' . $id);
            }
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($this->classModel->softDelete($id)) {
                $_SESSION['success'] = 'Class deleted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to delete class.';
            }
        }
        header('Location: ?module=classes');
        exit;
    }
}
