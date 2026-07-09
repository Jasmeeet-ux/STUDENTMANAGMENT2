<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get some valid classes, subjects, teachers
$classes = [];
$res = $conn->query("SELECT id FROM classes WHERE deleted_at IS NULL LIMIT 20");
while ($row = $res->fetch_assoc()) $classes[] = $row['id'];

$subjects = [];
$res = $conn->query("SELECT id, course_id FROM subjects WHERE deleted_at IS NULL LIMIT 20");
while ($row = $res->fetch_assoc()) $subjects[] = $row['id'];

$teachers = [];
$res = $conn->query("SELECT id FROM teachers WHERE deleted_at IS NULL LIMIT 20");
while ($row = $res->fetch_assoc()) $teachers[] = $row['id'];

if (empty($classes) || empty($subjects) || empty($teachers)) {
    die("Need classes, subjects, and teachers to seed.");
}

// Seed Exams
$exam_types = ['Mid Semester', 'Final Semester', 'Practical Exam', 'Other'];
$exam_names = ['Data Structures Midterm', 'Physics Final Lab', 'Management Principles', 'Computer Networks Exam', 'Calculus II Midterm', 'Literature Review'];

for ($i = 0; $i < 15; $i++) {
    $name = $exam_names[array_rand($exam_names)] . " - " . date('Y');
    $type = $exam_types[array_rand($exam_types)];
    $class_id = $classes[array_rand($classes)];
    
    // Future dates for upcoming exams
    $days = rand(1, 30);
    $start = date('Y-m-d', strtotime("+$days days"));
    $end = date('Y-m-d', strtotime("$start +" . rand(0, 2) . " days"));
    
    $status = 'Upcoming';
    
    $sql = "INSERT INTO erp_exams (name, exam_type, class_id, start_date, end_date, status) 
            VALUES ('$name', '$type', $class_id, '$start', '$end', '$status')";
    $conn->query($sql);
}

// Seed Assignments
$assignment_titles = ['React Project', 'Essay on Modern History', 'Calculus Worksheet 5', 'Business Case Study', 'Physics Lab Report', 'Database Schema Design'];

for ($i = 0; $i < 25; $i++) {
    $title = $assignment_titles[array_rand($assignment_titles)];
    $desc = "Please complete the attached requirements and submit before the deadline.";
    $class_id = $classes[array_rand($classes)];
    $subject_id = $subjects[array_rand($subjects)];
    $teacher_id = $teachers[array_rand($teachers)];
    
    // Future due dates for pending assignments
    $days = rand(1, 14);
    $due_date = date('Y-m-d H:i:s', strtotime("+$days days"));
    
    $max_marks = rand(10, 100);
    
    $sql = "INSERT INTO erp_assignments (title, description, class_id, subject_id, teacher_id, due_date, max_marks, status) 
            VALUES ('$title', '$desc', $class_id, $subject_id, $teacher_id, '$due_date', $max_marks, 'Active')";
    $conn->query($sql);
}

echo "Seeded upcoming exams and active assignments successfully!\n";
