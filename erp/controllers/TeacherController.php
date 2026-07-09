<?php
require_once __DIR__ . '/../models/Teacher.php';

class TeacherController {
    private $teacherModel;

    public function __construct() {
        $this->teacherModel = new Teacher();
    }

    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'department_id' => $_GET['department_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $teachers = $this->teacherModel->getAll($filters);
        $departments = $this->teacherModel->getDepartments();
        $stats = $this->teacherModel->getDashboardStats();
        
        require __DIR__ . '/../views/teachers/index.php';
    }

    public function create() {
        $departments = $this->teacherModel->getDepartments();
        $designations = $this->teacherModel->getDesignations();
        $employment_types = $this->teacherModel->getEmploymentTypes();
        require __DIR__ . '/../views/teachers/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            // Professional Validation
            if (strlen($data['password']) < 8) {
                $_SESSION['error'] = 'Password must be at least 8 characters long.';
                header('Location: ?module=teachers&action=create');
                exit;
            }

            if ($this->teacherModel->checkDuplicate('email', $data['email'])) {
                $_SESSION['error'] = 'Email already exists.';
                header('Location: ?module=teachers&action=create');
                exit;
            }

            if ($this->teacherModel->checkDuplicate('employee_id', $data['employee_id'])) {
                $_SESSION['error'] = 'Employee ID already exists.';
                header('Location: ?module=teachers&action=create');
                exit;
            }
            
            if ($this->teacherModel->checkDuplicate('username', $data['username'])) {
                $_SESSION['error'] = 'Username already exists.';
                header('Location: ?module=teachers&action=create');
                exit;
            }

            if ($this->teacherModel->create($data)) {
                $_SESSION['success'] = 'Teacher created successfully.';
                header('Location: ?module=teachers');
            } else {
                $_SESSION['error'] = 'Failed to create teacher.';
                header('Location: ?module=teachers&action=create');
            }
            exit;
        }
    }

    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=teachers');
            exit;
        }
        $teacher = $this->teacherModel->getById($id);
        $academicLoad = $this->teacherModel->getAcademicLoad($id);
        $classes = $this->teacherModel->getClasses($id);
        
        require __DIR__ . '/../views/teachers/show.php';
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=teachers');
            exit;
        }
        $teacher = $this->teacherModel->getById($id);
        $departments = $this->teacherModel->getDepartments();
        $designations = $this->teacherModel->getDesignations();
        $employment_types = $this->teacherModel->getEmploymentTypes();
        require __DIR__ . '/../views/teachers/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $data = $_POST;

            if ($this->teacherModel->checkDuplicate('email', $data['email'], $id)) {
                $_SESSION['error'] = 'Email already exists.';
                header('Location: ?module=teachers&action=edit&id=' . $id);
                exit;
            }

            if ($this->teacherModel->update($id, $data)) {
                $_SESSION['success'] = 'Teacher updated successfully.';
                header('Location: ?module=teachers&action=show&id=' . $id);
            } else {
                $_SESSION['error'] = 'Failed to update teacher.';
                header('Location: ?module=teachers&action=edit&id=' . $id);
            }
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            // Soft delete instead of hard delete
            if ($this->teacherModel->softDelete($id)) {
                $_SESSION['success'] = 'Teacher deleted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to delete teacher.';
            }
        }
        header('Location: ?module=teachers');
        exit;
    }

    public function exportCsv() {
        $teachers = $this->teacherModel->getAll();
        
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=teachers_export_' . date('Ymd_His') . '.csv');
        
        $output = fopen('php://output', 'w');
        fputcsv($output, ['ID', 'Employee ID', 'Name', 'Email', 'Phone', 'Department', 'Designation', 'Employment Type', 'Status', 'Joined Date']);
        
        foreach ($teachers as $t) {
            fputcsv($output, [
                $t['id'], 
                $t['employee_id'], 
                $t['name'], 
                $t['email'], 
                $t['phone'], 
                $t['department_name'], 
                $t['designation_name'], 
                $t['employment_type_name'], 
                $t['status'],
                $t['joining_date']
            ]);
        }
        fclose($output);
        exit;
    }
}
