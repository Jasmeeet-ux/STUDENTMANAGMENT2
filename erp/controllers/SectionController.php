<?php
require_once __DIR__ . '/../models/Section.php';

class SectionController {
    private $sectionModel;

    public function __construct() {
        $this->sectionModel = new Section();
    }

    public function index() {
        $filters = [
            'search' => $_GET['search'] ?? '',
            'class_id' => $_GET['class_id'] ?? '',
            'status' => $_GET['status'] ?? ''
        ];
        
        $sections = $this->sectionModel->getAll($filters);
        $classes = $this->sectionModel->getClasses();
        $stats = $this->sectionModel->getDashboardStats();
        
        require __DIR__ . '/../views/sections/index.php';
    }

    public function create() {
        $classes = $this->sectionModel->getClasses();
        $teachers = $this->sectionModel->getTeachers();
        require __DIR__ . '/../views/sections/create.php';
    }

    public function store() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = $_POST;
            
            if ($this->sectionModel->checkDuplicate($data['class_id'], $data['name'])) {
                $_SESSION['error'] = 'Section name already exists for this class.';
                header('Location: ?module=sections&action=create');
                exit;
            }

            if ($this->sectionModel->create($data)) {
                $_SESSION['success'] = 'Section created successfully.';
                header('Location: ?module=sections');
            } else {
                $_SESSION['error'] = 'Failed to create section.';
                header('Location: ?module=sections&action=create');
            }
            exit;
        }
    }

    public function edit() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=sections');
            exit;
        }
        $section = $this->sectionModel->getById($id);
        $classes = $this->sectionModel->getClasses();
        $teachers = $this->sectionModel->getTeachers();
        require __DIR__ . '/../views/sections/edit.php';
    }

    public function update() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? null;
            $data = $_POST;

            if ($this->sectionModel->checkDuplicate($data['class_id'], $data['name'], $id)) {
                $_SESSION['error'] = 'Section name already exists for this class.';
                header('Location: ?module=sections&action=edit&id=' . $id);
                exit;
            }

            if ($this->sectionModel->update($id, $data)) {
                $_SESSION['success'] = 'Section updated successfully.';
                header('Location: ?module=sections');
            } else {
                $_SESSION['error'] = 'Failed to update section.';
                header('Location: ?module=sections&action=edit&id=' . $id);
            }
            exit;
        }
    }

    public function delete() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            if ($this->sectionModel->softDelete($id)) {
                $_SESSION['success'] = 'Section deleted successfully.';
            } else {
                $_SESSION['error'] = 'Failed to delete section.';
            }
        }
        header('Location: ?module=sections');
        exit;
    }

    // View Details / Assigns
    public function show() {
        $id = $_GET['id'] ?? null;
        if (!$id) {
            header('Location: ?module=sections');
            exit;
        }
        $section = $this->sectionModel->getById($id);
        
        $assigned_students = $this->sectionModel->getSectionStudents($id);
        $available_students = $this->sectionModel->getAvailableStudents();
        
        $assigned_subjects = $this->sectionModel->getSectionSubjects($id);
        $available_subjects = $this->sectionModel->getAvailableSubjects($section['class_id']);
        $teachers = $this->sectionModel->getTeachers();
        
        require __DIR__ . '/../views/sections/show.php';
    }

    public function assignStudent() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $section_id = $_POST['section_id'] ?? null;
            $student_id = $_POST['student_id'] ?? null;
            if ($section_id && $student_id) {
                $this->sectionModel->addStudentToSection($section_id, $student_id);
                $_SESSION['success'] = 'Student assigned successfully.';
            }
            header('Location: ?module=sections&action=show&id=' . $section_id);
            exit;
        }
    }

    public function removeStudent() {
        $section_id = $_GET['section_id'] ?? null;
        $student_id = $_GET['student_id'] ?? null;
        if ($section_id && $student_id) {
            $this->sectionModel->removeStudentFromSection($section_id, $student_id);
            $_SESSION['success'] = 'Student removed from section.';
        }
        header('Location: ?module=sections&action=show&id=' . $section_id);
        exit;
    }

    public function assignSubject() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $section_id = $_POST['section_id'] ?? null;
            $subject_id = $_POST['subject_id'] ?? null;
            $teacher_id = !empty($_POST['teacher_id']) ? $_POST['teacher_id'] : null;
            
            if ($section_id && $subject_id) {
                $this->sectionModel->addSubjectToSection($section_id, $subject_id, $teacher_id);
                $_SESSION['success'] = 'Subject assigned successfully.';
            }
            header('Location: ?module=sections&action=show&id=' . $section_id);
            exit;
        }
    }

    public function removeSubject() {
        $section_id = $_GET['section_id'] ?? null;
        $subject_id = $_GET['subject_id'] ?? null;
        if ($section_id && $subject_id) {
            $this->sectionModel->removeSubjectFromSection($section_id, $subject_id);
            $_SESSION['success'] = 'Subject removed from section.';
        }
        header('Location: ?module=sections&action=show&id=' . $section_id);
        exit;
    }
}
