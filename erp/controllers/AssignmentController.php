<?php
require_once __DIR__ . '/../models/Assignment.php';

class AssignmentController {
    private $assignmentModel;

    public function __construct() {
        $this->assignmentModel = new Assignment();
    }

    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'class_id' => $_GET['class_id'] ?? '',
            'subject_id' => $_GET['subject_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $assignments = $this->assignmentModel->getAll($filters);
        $classes = $this->assignmentModel->getClasses();
        $subjects = $this->assignmentModel->getSubjects();
        $stats = $this->assignmentModel->getDashboardStats();
        
        require __DIR__ . '/../views/assignments/index.php';
    }

    public function create() {
        $classes = $this->assignmentModel->getClasses();
        $subjects = $this->assignmentModel->getSubjects();
        $teachers = $this->assignmentModel->getTeachers();
        require __DIR__ . '/../views/assignments/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($id = $this->assignmentModel->create($_POST)) {
                $_SESSION['success'] = 'Assignment created successfully.';
                header('Location: ?module=assignments&action=show&id=' . $id);
            } else {
                $_SESSION['error'] = 'Failed to create assignment.';
                header('Location: ?module=assignments&action=create');
            }
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=assignments');
            exit;
        }
        $assignment = $this->assignmentModel->getById($id);
        $classes = $this->assignmentModel->getClasses();
        $subjects = $this->assignmentModel->getSubjects();
        $teachers = $this->assignmentModel->getTeachers();
        require __DIR__ . '/../views/assignments/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if ($this->assignmentModel->update($id, $_POST)) {
                $_SESSION['success'] = 'Assignment updated successfully.';
                header('Location: ?module=assignments');
            } else {
                $_SESSION['error'] = 'Failed to update assignment.';
                header('Location: ?module=assignments&action=edit&id=' . $id);
            }
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->assignmentModel->softDelete($id);
            $_SESSION['success'] = 'Assignment deleted successfully.';
        }
        header('Location: ?module=assignments');
        exit;
    }

    // View Assignment Details & Submissions
    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=assignments');
            exit;
        }
        
        $assignment = $this->assignmentModel->getById($id);
        $submissions = $this->assignmentModel->getSubmissions($id);
        
        require __DIR__ . '/../views/assignments/show.php';
    }

    public function saveGrades() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assignment_id = $_POST['assignment_id'];
            $grades_data = $_POST['grades'] ?? [];
            
            if ($this->assignmentModel->saveGrades($assignment_id, $grades_data)) {
                $_SESSION['success'] = 'Grades & Feedback saved successfully.';
            } else {
                $_SESSION['error'] = 'Failed to save grades.';
            }
            header('Location: ?module=assignments&action=show&id=' . $assignment_id);
            exit;
        }
    }
}
