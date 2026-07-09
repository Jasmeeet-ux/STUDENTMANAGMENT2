<?php
require_once __DIR__ . '/../models/Exam.php';

class ExamController {
    private $examModel;

    public function __construct() {
        $this->examModel = new Exam();
    }

    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'class_id' => $_GET['class_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $exams = $this->examModel->getAll($filters);
        $classes = $this->examModel->getClasses();
        $stats = $this->examModel->getDashboardStats();
        
        require __DIR__ . '/../views/exams/index.php';
    }

    public function create() {
        $classes = $this->examModel->getClasses();
        require __DIR__ . '/../views/exams/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($id = $this->examModel->create($_POST)) {
                $_SESSION['success'] = 'Exam created successfully.';
                header('Location: ?module=examinations&action=show&id=' . $id);
            } else {
                $_SESSION['error'] = 'Failed to create exam.';
                header('Location: ?module=examinations&action=create');
            }
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=examinations');
            exit;
        }
        $exam = $this->examModel->getById($id);
        $classes = $this->examModel->getClasses();
        require __DIR__ . '/../views/exams/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            if ($this->examModel->update($id, $_POST)) {
                $_SESSION['success'] = 'Exam updated successfully.';
                header('Location: ?module=examinations');
            } else {
                $_SESSION['error'] = 'Failed to update exam.';
                header('Location: ?module=examinations&action=edit&id=' . $id);
            }
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->examModel->softDelete($id);
            $_SESSION['success'] = 'Exam deleted successfully.';
        }
        header('Location: ?module=examinations');
        exit;
    }

    // View Exam Details & Assign Subjects
    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=examinations');
            exit;
        }
        
        $exam = $this->examModel->getById($id);
        $exam_subjects = $this->examModel->getExamSubjects($id);
        $available_subjects = $this->examModel->getAvailableSubjectsForExam($exam['class_id']);
        
        require __DIR__ . '/../views/exams/show.php';
    }

    public function addSubject() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $exam_id = $_POST['exam_id'];
            if ($this->examModel->addExamSubject($_POST)) {
                $_SESSION['success'] = 'Subject added to exam.';
            } else {
                $_SESSION['error'] = 'Subject might already be added.';
            }
            header('Location: ?module=examinations&action=show&id=' . $exam_id);
            exit;
        }
    }

    public function removeSubject() {
        $id = $_GET['id'] ?? null;
        $exam_id = $_GET['exam_id'] ?? null;
        if ($id) {
            $this->examModel->removeExamSubject($id);
            $_SESSION['success'] = 'Subject removed from exam.';
        }
        header('Location: ?module=examinations&action=show&id=' . $exam_id);
        exit;
    }

    // Enter Marks
    public function marks() {
        $exam_subject_id = $_GET['exam_subject_id'] ?? null;
        if (!$exam_subject_id) {
            header('Location: ?module=examinations');
            exit;
        }
        
        $exam_subject = $this->examModel->getExamSubjectById($exam_subject_id);
        $students = $this->examModel->getStudentsForExam($exam_subject['class_id']);
        $existing_marks = $this->examModel->getExamMarks($exam_subject_id);
        
        require __DIR__ . '/../views/exams/marks.php';
    }

    public function saveMarks() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $exam_subject_id = $_POST['exam_subject_id'];
            $marks_data = $_POST['marks'] ?? [];
            
            if ($this->examModel->saveMarks($exam_subject_id, $marks_data)) {
                $_SESSION['success'] = 'Marks saved successfully.';
            } else {
                $_SESSION['error'] = 'Failed to save marks.';
            }
            header('Location: ?module=examinations&action=marks&exam_subject_id=' . $exam_subject_id);
            exit;
        }
    }

    // Result Page
    public function result() {
        $exam_id = $_GET['exam_id'] ?? null;
        $student_id = $_GET['student_id'] ?? null;
        
        if (!$exam_id || !$student_id) {
            die("Invalid Request");
        }
        
        $exam = $this->examModel->getById($exam_id);
        
        // Fetch student details from users table manually for now
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT name, reg_no FROM users WHERE id = ?");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $student = $stmt->get_result()->fetch_assoc();
        
        $results = $this->examModel->getStudentResult($exam_id, $student_id);
        
        require __DIR__ . '/../views/exams/result.php';
    }
}
