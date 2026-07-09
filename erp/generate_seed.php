<?php
// generate_seed.php
$sql = "SET FOREIGN_KEY_CHECKS = 0;\n";

$tables = [
    'departments', 'courses', 'subjects', 'teachers', 'classes', 'sections', 'users', 'section_students',
    'erp_attendance', 'erp_exams', 'erp_exam_subjects', 'erp_exam_marks', 'erp_assignments', 'erp_assignment_submissions',
    'erp_fee_structures', 'erp_fee_invoices', 'erp_fee_payments', 'erp_leaves'
];
foreach ($tables as $table) {
    $sql .= "TRUNCATE TABLE `$table`;\n";
}
$sql .= "SET FOREIGN_KEY_CHECKS = 1;\n\n";

$sql .= "INSERT INTO departments (name, status) VALUES ('Computer Science', 'active'), ('Business Administration', 'active'), ('Engineering', 'active'), ('Arts & Humanities', 'active');\n";
$sql .= "INSERT INTO courses (course_name, course_code, department_id) VALUES ('B.Sc. Computer Science', 'BSCS', 1), ('B.B.A. Business Admin', 'BBA', 2), ('B.E. Mechanical Eng.', 'BEME', 3), ('B.A. Literature', 'BAL', 4);\n";

$sql .= "INSERT INTO teachers (name, username, password, email, department_id, employee_id, status) VALUES 
('Dr. John Smith', 'teacher0', 'password', 'drjohnsmith@edu.com', 1, 'EMP1000', 'active'),
('Prof. Jane Doe', 'teacher1', 'password', 'profjanedoe@edu.com', 2, 'EMP1001', 'active'),
('Dr. Emily Clark', 'teacher2', 'password', 'dremilyclark@edu.com', 3, 'EMP1002', 'active'),
('Mr. Robert Brown', 'teacher3', 'password', 'mrrobertbrown@edu.com', 4, 'EMP1003', 'active'),
('Ms. Sarah Connor', 'teacher4', 'password', 'mssarahconnor@edu.com', 1, 'EMP1004', 'active');\n";

$sql .= "INSERT INTO subjects (name, code, course_id, teacher_id) VALUES 
('Data Structures', 'CS101', 1, 1),
('Algorithms', 'CS102', 1, 1),
('Marketing 101', 'BA101', 2, 2),
('Finance', 'BA102', 2, 2),
('Thermodynamics', 'ME101', 3, 3),
('Modern Lit', 'AL101', 4, 4);\n";

$sql .= "INSERT INTO classes (name, course_id, teacher_id) VALUES 
('Freshman CS', 1, 1),
('Sophomore CS', 1, 2),
('Freshman BBA', 2, 3),
('Junior Eng', 3, 4);\n";

$sql .= "INSERT INTO sections (name, class_id, teacher_id) VALUES 
('Section A', 1, 1), ('Section B', 1, 2),
('Section A', 2, 1),
('Section A', 3, 3),
('Section A', 4, 4);\n";

$first_names = ['James', 'Mary', 'John', 'Patricia', 'Robert', 'Jennifer', 'Michael', 'Linda', 'William', 'Elizabeth', 'David', 'Barbara', 'Richard', 'Susan', 'Joseph', 'Jessica'];
$last_names = ['Smith', 'Johnson', 'Williams', 'Brown', 'Jones', 'Garcia', 'Miller', 'Davis', 'Rodriguez', 'Martinez', 'Hernandez', 'Lopez', 'Gonzalez', 'Wilson', 'Anderson', 'Thomas'];

$student_inserts = [];
for ($i = 1; $i <= 50; $i++) {
    $fname = $first_names[array_rand($first_names)];
    $lname = $last_names[array_rand($last_names)];
    $name = "$fname $lname";
    $reg_no = "STU2026" . str_pad($i, 3, '0', STR_PAD_LEFT);
    $email = strtolower($fname . "." . $lname . $i) . "@student.edu";
    $student_inserts[] = "('$name', '$reg_no', '$email', 'password', 'student', 1)";
}
$sql .= "INSERT INTO users (name, reg_no, email, password, role, terms_accepted) VALUES " . implode(", ", $student_inserts) . ";\n";

$sec_stu_inserts = [];
for ($sid = 1; $sid <= 50; $sid++) {
    $sec_id = rand(1, 5);
    $sec_stu_inserts[] = "($sec_id, $sid)";
}
$sql .= "INSERT INTO section_students (section_id, student_id) VALUES " . implode(", ", $sec_stu_inserts) . ";\n";

$att_inserts = [];
for ($d = 0; $d < 10; $d++) {
    $date = date('Y-m-d', strtotime("-$d days"));
    if (date('N', strtotime($date)) < 6) { 
        for ($sid = 1; $sid <= 50; $sid++) {
            $status = (rand(1, 100) > 85) ? 'Absent' : 'Present';
            $att_inserts[] = "($sid, 1, '$date', '$status')";
        }
    }
}
if (!empty($att_inserts)) {
    $sql .= "INSERT INTO erp_attendance (student_id, section_id, `date`, status) VALUES " . implode(", ", $att_inserts) . ";\n";
}

$sql .= "INSERT INTO erp_exams (name, exam_type, class_id, start_date, end_date, status) VALUES 
('Mid Term CS Freshman', 'Mid Semester', 1, '" . date('Y-m-d', strtotime('-1 month')) . "', '" . date('Y-m-d', strtotime('-25 days')) . "', 'Completed'),
('Final Term BBA', 'Final Semester', 3, '" . date('Y-m-d', strtotime('+10 days')) . "', '" . date('Y-m-d', strtotime('+15 days')) . "', 'Upcoming');\n";

$sql .= "INSERT INTO erp_exam_subjects (exam_id, subject_id, exam_date) VALUES 
(1, 1, '" . date('Y-m-d', strtotime('-28 days')) . "'),
(1, 2, '" . date('Y-m-d', strtotime('-26 days')) . "');\n";

$mark_inserts = [];
for ($sid = 1; $sid <= 20; $sid++) {
    $marks1 = rand(40, 95);
    $marks2 = rand(40, 95);
    $mark_inserts[] = "(1, $sid, $marks1)";
    $mark_inserts[] = "(2, $sid, $marks2)";
}
$sql .= "INSERT INTO erp_exam_marks (exam_subject_id, student_id, total_marks) VALUES " . implode(", ", $mark_inserts) . ";\n";

$sql .= "INSERT INTO erp_fee_structures (fee_type, academic_year, course_id, amount, due_date) VALUES 
('Tuition', '2026-2027', 1, 5000.00, '" . date('Y-m-d', strtotime('+20 days')) . "'),
('Tuition', '2026-2027', 2, 4500.00, '" . date('Y-m-d', strtotime('+20 days')) . "');\n";

$inv_inserts = [];
for ($sid = 1; $sid <= 50; $sid++) {
    $struct_id = ($sid % 2) + 1;
    $amount = ($struct_id == 1) ? 5000 : 4500;
    $status = (rand(1, 100) > 30) ? 'Paid' : 'Pending';
    $inv_inserts[] = "($sid, $struct_id, $amount, '$status')";
}
$sql .= "INSERT INTO erp_fee_invoices (student_id, fee_structure_id, total_amount, status) VALUES " . implode(", ", $inv_inserts) . ";\n";

// Generating Payments is trickier with auto_increment. I'll just generate payments for the first 10 invoices
$pay_inserts = [];
for ($inv = 1; $inv <= 10; $inv++) {
    $pay_inserts[] = "($inv, 4500, 'Card', '" . date('Y-m-d', strtotime('-' . rand(1, 15) . ' days')) . "')";
}
$sql .= "INSERT INTO erp_fee_payments (invoice_id, amount, payment_method, payment_date) VALUES " . implode(", ", $pay_inserts) . ";\n";

$sql .= "INSERT INTO erp_assignments (title, class_id, subject_id, teacher_id, due_date, status) VALUES 
('Linked Lists Implementation', 1, 1, 1, '" . date('Y-m-d', strtotime('+5 days')) . "', 'Active'),
('Marketing Case Study', 3, 3, 2, '" . date('Y-m-d', strtotime('-2 days')) . "', 'Active');\n";

$sql .= "INSERT INTO erp_leaves (user_type, teacher_id, leave_type, start_date, end_date, reason, status) VALUES 
('teacher', 1, 'Sick Leave', '" . date('Y-m-d', strtotime('-5 days')) . "', '" . date('Y-m-d', strtotime('-4 days')) . "', 'Fever', 'Approved');\n";
$sql .= "INSERT INTO erp_leaves (user_type, student_id, leave_type, start_date, end_date, reason, status) VALUES 
('student', 5, 'Casual Leave', '" . date('Y-m-d', strtotime('+1 days')) . "', '" . date('Y-m-d', strtotime('+2 days')) . "', 'Family function', 'Pending');\n";

file_put_contents('seed_data.sql', $sql);
echo "SQL File Generated.\n";
