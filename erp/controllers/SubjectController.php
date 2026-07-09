<?php
require_once __DIR__ . '/../models/Subject.php';

class SubjectController {
    private $subjectModel;

    public function __construct() {
        $this->subjectModel = new Subject();
    }

    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'course_id' => $_GET['course_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $subjects = $this->subjectModel->getAll($filters);
        $courses = $this->subjectModel->getCourses();
        $stats = $this->subjectModel->getDashboardStats();
        
        require __DIR__ . '/../views/subjects/index.php';
    }

    public function create() {
        $courses = $this->subjectModel->getCourses();
        $teachers = $this->subjectModel->getTeachers();
        require __DIR__ . '/../views/subjects/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            if ($this->subjectModel->checkDuplicate('code', $data['code'])) {
                $_SESSION['error'] = 'Subject Code already exists.';
                header('Location: ?module=subjects&action=create');
                exit;
            }

            if ($this->subjectModel->create($data)) {
                $_SESSION['success'] = 'Subject created successfully.';
                header('Location: ?module=subjects');
            } else {
                $_SESSION['error'] = 'Failed to create subject.';
                header('Location: ?module=subjects&action=create');
            }
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=subjects');
            exit;
        }
        $subject = $this->subjectModel->getById($id);
        $courses = $this->subjectModel->getCourses();
        $teachers = $this->subjectModel->getTeachers();
        require __DIR__ . '/../views/subjects/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $data = $_POST;

            if ($this->subjectModel->checkDuplicate('code', $data['code'], $id)) {
                $_SESSION['error'] = 'Subject Code already exists.';
                header('Location: ?module=subjects&action=edit&id=' . $id);
                exit;
            }

            if ($this->subjectModel->update($id, $data)) {
                $_SESSION['success'] = 'Subject updated successfully.';
                header('Location: ?module=subjects');
            } else {
                $_SESSION['error'] = 'Failed to update subject.';
                header('Location: ?module=subjects&action=edit&id=' . $id);
            }
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($this->subjectModel->softDelete($id)) {
                $_SESSION['success'] = 'Subject deleted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to delete subject.';
            }
        }
        header('Location: ?module=subjects');
        exit;
    }
}
