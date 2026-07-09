<?php
require_once __DIR__ . '/../models/Department.php';

class DepartmentController {
    private $deptModel;

    public function __construct() {
        $this->deptModel = new Department();
    }

    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $departments = $this->deptModel->getAll($filters);
        
        // Fetch counts for each department
        foreach ($departments as &$dept) {
            $dept['course_count'] = $this->deptModel->getCourseCount($dept['id']);
            $dept['teacher_count'] = $this->deptModel->getTeacherCount($dept['id']);
        }
        unset($dept);

        $stats = $this->deptModel->getDashboardStats();
        
        require __DIR__ . '/../views/departments/index.php';
    }

    public function create() {
        require __DIR__ . '/../views/departments/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            if ($this->deptModel->checkDuplicate('name', $data['name'])) {
                $_SESSION['error'] = 'Department Name already exists.';
                header('Location: ?module=departments&action=create');
                exit;
            }

            if ($this->deptModel->create($data)) {
                $_SESSION['success'] = 'Department created successfully.';
                header('Location: ?module=departments');
            } else {
                $_SESSION['error'] = 'Failed to create department.';
                header('Location: ?module=departments&action=create');
            }
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=departments');
            exit;
        }
        $department = $this->deptModel->getById($id);
        require __DIR__ . '/../views/departments/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $data = $_POST;

            if ($this->deptModel->checkDuplicate('name', $data['name'], $id)) {
                $_SESSION['error'] = 'Department Name already exists.';
                header('Location: ?module=departments&action=edit&id=' . $id);
                exit;
            }

            if ($this->deptModel->update($id, $data)) {
                $_SESSION['success'] = 'Department updated successfully.';
                header('Location: ?module=departments');
            } else {
                $_SESSION['error'] = 'Failed to update department.';
                header('Location: ?module=departments&action=edit&id=' . $id);
            }
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($this->deptModel->softDelete($id)) {
                $_SESSION['success'] = 'Department deleted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to delete department.';
            }
        }
        header('Location: ?module=departments');
        exit;
    }
}
