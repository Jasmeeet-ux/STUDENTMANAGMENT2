<?php
// seed.php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Disable foreign key checks for truncation
$conn->query("SET FOREIGN_KEY_CHECKS = 0");

// Truncate tables
$tables = [
    'departments', 'courses', 'subjects', 'teachers', 'classes', 'sections', 'users', 'section_students',
    'erp_attendance', 'erp_exams', 'erp_exam_subjects', 'erp_exam_marks', 'erp_assignments', 'erp_assignment_submissions',
    'erp_fee_structures', 'erp_fee_invoices', 'erp_fee_payments', 'erp_leaves'
];
foreach ($tables as $table) {
    $conn->query("TRUNCATE TABLE `$table`");
}

$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// 1. Insert Departments
$depts = ['Computer Science', 'Business Administration', 'Engineering', 'Arts & Humanities'];
foreach ($depts as $d) {
    $conn->query("INSERT INTO departments (name, status) VALUES ('$d', 'active')");
}

// 2. Insert Courses
$conn->query("INSERT INTO courses (course_name, course_code, department_id) VALUES 
('B.Sc. Computer Science', 'BSCS', 1),
('B.B.A. Business Admin', 'BBA', 2),
('B.E. Mechanical Eng.', 'BEME', 3),
('B.A. Literature', 'BAL', 4)");

// 3. Insert Teachers
$teacher_names = ['Dr. John Smith', 'Prof. Jane Doe', 'Dr. Emily Clark', 'Mr. Robert Brown', 'Ms. Sarah Connor'];
foreach ($teacher_names as $i => $name) {
    $email = strtolower(str_replace([' ', '.'], '', $name)) . '@edu.com';
    $dept = ($i % 4) + 1;
    $conn->query("INSERT INTO teachers (name, username, password, email, department_id, employee_id, status) VALUES 
    ('$name', 'teacher$i', 'password', '$email', $dept, 'EMP100$i', 'active')");
}

// 4. Insert Subjects
$conn->query("INSERT INTO subjects (name, code, course_id, teacher_id) VALUES 
('Data Structures', 'CS101', 1, 1),
('Algorithms', 'CS102', 1, 1),
('Marketing 101', 'BA101', 2, 2),
('Finance', 'BA102', 2, 2),
('Thermodynamics', 'ME101', 3, 3),
('Modern Lit', 'AL101', 4, 4)");

// 5. Insert Classes
$conn->query("INSERT INTO classes (name, course_id, teacher_id) VALUES 
('Freshman CS', 1, 1),
('Sophomore CS', 1, 2),
('Freshman BBA', 2, 3),
('Junior Eng', 3, 4)");

// 6. Insert Sections
$conn->query("INSERT INTO sections (name, class_id, teacher_id) VALUES 
('Section A', 1, 1), ('Section B', 1, 2),
('Section A', 2, 1),
('Section A', 3, 3),
('Section A', 4, 4)");

// 7. Insert Students (Users table)
$first_names = ['James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda', 'William', 'Elizabeth', 'David', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Jessica'];
$last_names = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas'];

$student_ids = [];
for ($i = 1; $i <= 50; $i++) {
    $fname = $first_names[array_rand($first_names)];
    $lname = $last_names[array_rand($last_names)];
    $name = "$fname $lname";
    $reg_no = "STU2026" . str_pad($i, 3, '0', STR_PAD_LEFT);
    $email = strtolower($fname . "." . $lname . $i) . "@student.edu";
    
    $conn->query("INSERT INTO users (name, reg_no, email, password, role, terms_accepted) VALUES ('$name', '$reg_no', '$email', 'password', 'student', 1)");
    $student_ids[] = $conn->insert_id;
}

// 8. Assign Students to Sections
foreach ($student_ids as $sid) {
    $sec_id = rand(1, 5);
    $conn->query("INSERT INTO section_students (section_id, student_id) VALUES ($sec_id, $sid)");
}

// 9. Generate Attendance (last 10 days)
for ($d = 0; $d < 10; $d++) {
    $date = date('Y-m-d', strtotime("-$d days"));
    foreach ($student_ids as $sid) {
        if (date('N', strtotime($date)) < 6) { // Weekdays only
            $status = (rand(1, 100) > 85) ? 'Absent' : 'Present';
            // Randomly pick a section/subject for the student, but let's just make it simple
            $conn->query("INSERT INTO erp_attendance (student_id, section_id, `date`, status) VALUES ($sid, 1, '$date', '$status')");
        }
    }
}

// 10. Generate Exams
$conn->query("INSERT INTO erp_exams (name, exam_type, class_id, start_date, end_date, status) VALUES 
('Mid Term CS Freshman', 'Mid Semester', 1, '" . date('Y-m-d', strtotime('-1 month')) . "', '" . date('Y-m-d', strtotime('-25 days')) . "', 'Completed'),
('Final Term BBA', 'Final Semester', 3, '" . date('Y-m-d', strtotime('+10 days')) . "', '" . date('Y-m-d', strtotime('+15 days')) . "', 'Upcoming')");

// Link Exams to Subjects
$conn->query("INSERT INTO erp_exam_subjects (exam_id, subject_id, exam_date) VALUES 
(1, 1, '" . date('Y-m-d', strtotime('-28 days')) . "'),
(1, 2, '" . date('Y-m-d', strtotime('-26 days')) . "')");

// Generate Exam Marks for Mid Term
foreach ($student_ids as $i => $sid) {
    if ($i < 20) { // First 20 students in CS
        $marks = rand(40, 95);
        $conn->query("INSERT INTO erp_exam_marks (exam_subject_id, student_id, total_marks) VALUES (1, $sid, $marks)");
        $marks = rand(40, 95);
        $conn->query("INSERT INTO erp_exam_marks (exam_subject_id, student_id, total_marks) VALUES (2, $sid, $marks)");
    }
}

// 11. Generate Fee Structures and Invoices
$conn->query("INSERT INTO erp_fee_structures (title, course_id, amount, due_date) VALUES 
('Fall 2026 Tuition', 1, 5000.00, '" . date('Y-m-d', strtotime('+20 days')) . "'),
('Fall 2026 Tuition', 2, 4500.00, '" . date('Y-m-d', strtotime('+20 days')) . "')");

foreach ($student_ids as $i => $sid) {
    $struct_id = ($i % 2) + 1; // 1 or 2
    $amount = ($struct_id == 1) ? 5000 : 4500;
    $status = (rand(1, 100) > 30) ? 'Paid' : 'Pending';
    
    $conn->query("INSERT INTO erp_fee_invoices (student_id, fee_structure_id, total_amount, status) VALUES ($sid, $struct_id, $amount, '$status')");
    $inv_id = $conn->insert_id;
    
    if ($status == 'Paid') {
        $conn->query("INSERT INTO erp_fee_payments (invoice_id, amount_paid, payment_method, payment_date) VALUES ($inv_id, $amount, 'Credit Card', '" . date('Y-m-d', strtotime('-' . rand(1, 15) . ' days')) . "')");
    }
}

// 12. Assignments
$conn->query("INSERT INTO erp_assignments (title, subject_id, teacher_id, due_date, status) VALUES 
('Linked Lists Implementation', 1, 1, '" . date('Y-m-d', strtotime('+5 days')) . "', 'Active'),
('Marketing Case Study', 3, 2, '" . date('Y-m-d', strtotime('-2 days')) . "', 'Active')");

// 13. Leaves
$conn->query("INSERT INTO erp_leaves (user_type, user_id, leave_type, start_date, end_date, status) VALUES 
('teacher', 1, 'Sick Leave', '" . date('Y-m-d', strtotime('-5 days')) . "', '" . date('Y-m-d', strtotime('-4 days')) . "', 'Approved'),
('student', 5, 'Casual Leave', '" . date('Y-m-d', strtotime('+1 days')) . "', '" . date('Y-m-d', strtotime('+2 days')) . "', 'Pending')");

echo "Dummy data seeded successfully!\n";
