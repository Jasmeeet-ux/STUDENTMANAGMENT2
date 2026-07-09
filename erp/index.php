<?php
session_start();

// Ensure the user is an admin or super admin
// Integrating with existing authentication
// If not logged in as admin, redirect to existing login
if (!isset($_SESSION['admin_id']) && !isset($_SESSION['sub_admin_id'])) {
    // You could redirect to the existing login
    // header('Location: ../config/auth/login.php');
    // exit;
}

$module = $_GET['module'] ?? 'dashboard';
$action = $_GET['action'] ?? 'index';

// Simple Router
switch ($module) {
    case 'departments':
        require_once 'controllers/DepartmentController.php';
        $controller = new DepartmentController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;

    case 'subjects':
        require_once 'controllers/SubjectController.php';
        $controller = new SubjectController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;

    case 'classes':
        require_once 'controllers/ClassController.php';
        $controller = new ClassController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;

    case 'sections':
        require_once 'controllers/SectionController.php';
        $controller = new SectionController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;

    case 'attendance':
        require_once 'controllers/AttendanceController.php';
        $controller = new AttendanceController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;

    case 'exams':
    case 'examinations':
        require_once 'controllers/ExamController.php';
        $controller = new ExamController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;

    case 'assignments':
        require_once 'controllers/AssignmentController.php';
        $controller = new AssignmentController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;

    case 'fees':
        require_once 'controllers/FeeController.php';
        $controller = new FeeController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;

    case 'leaves':
        require_once 'controllers/LeaveController.php';
        $controller = new LeaveController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;

    case 'courses':
        require_once 'controllers/CourseController.php';
        $controller = new CourseController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;

    case 'teachers':
        require_once 'controllers/TeacherController.php';
        $controller = new TeacherController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;

    case 'students':
        require_once 'controllers/StudentController.php';
        $controller = new StudentController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;
        
    case 'analytics':
    case 'dashboard':
    default:
        require_once 'controllers/AnalyticsController.php';
        $controller = new AnalyticsController();
        if (method_exists($controller, $action)) {
            $controller->$action();
        } else {
            $controller->index();
        }
        break;
}
