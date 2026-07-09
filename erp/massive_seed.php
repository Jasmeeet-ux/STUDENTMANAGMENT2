<?php
// massive_seed.php

set_time_limit(0);
ini_set('memory_limit', '512M');

$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Truncate tables
echo "Truncating tables...\n";
$conn->query("SET FOREIGN_KEY_CHECKS = 0");
$tables = [
    'departments', 'courses', 'subjects', 'teachers', 'classes', 'sections', 'users', 'section_students',
    'erp_attendance', 'erp_exams', 'erp_exam_subjects', 'erp_exam_marks', 'erp_assignments', 'erp_assignment_submissions',
    'erp_fee_structures', 'erp_fee_invoices', 'erp_fee_payments', 'erp_leaves'
];
foreach ($tables as $table) {
    $conn->query("TRUNCATE TABLE `$table`");
}
$conn->query("SET FOREIGN_KEY_CHECKS = 1");

// 2. Data Arrays
$firstNames = ['Aarav', 'Vihaan', 'Aditya', 'Arjun', 'Sai', 'Krishna', 'Ishaan', 'Shaurya', 'Atharv', 'Kabir', 'Ananya', 'Diya', 'Aditi', 'Riya', 'Myra', 'Priya', 'Sneha', 'Pooja', 'Rahul', 'Amit', 'Rohit', 'Suresh', 'Ramesh', 'Mahesh', 'Vikas', 'Manish', 'Kiran', 'Rajesh', 'Sanjay', 'Neha', 'Kavita', 'Smriti', 'Akshay', 'Vikram', 'Varun', 'Tarun', 'Pratik', 'Suraj', 'Arun'];
$lastNames = ['Sharma', 'Singh', 'Kumar', 'Patel', 'Gupta', 'Verma', 'Reddy', 'Rao', 'Yadav', 'Joshi', 'Iyer', 'Chatterjee', 'Banerjee', 'Bose', 'Das', 'Mishra', 'Pandey', 'Deshmukh', 'Patil', 'Nair', 'Menon', 'Chauhan', 'Thakur', 'Bhat', 'Gowda'];

$departments = [
    'Computer Science and Engineering', 'Information Technology', 'Mechanical Engineering', 
    'Civil Engineering', 'Electrical Engineering', 'Electronics and Communication', 
    'Biotechnology', 'Chemical Engineering', 'Aerospace Engineering', 
    'Business Administration', 'Commerce and Finance', 'Arts and Humanities', 
    'Applied Sciences', 'Law', 'Architecture'
];

$courses = [
    ['B.Tech CSE', 'CSE'], ['B.Tech IT', 'IT'], ['B.Tech ME', 'ME'], 
    ['B.Tech CE', 'CE'], ['B.Tech EE', 'EE'], ['B.Tech ECE', 'ECE'], 
    ['B.Tech Bio', 'BIO'], ['B.Tech Chem', 'CHEM'], ['B.Tech Aero', 'AERO'], 
    ['BBA', 'BBA'], ['B.Com', 'BCOM'], ['B.A. Arts', 'BA'], 
    ['B.Sc Science', 'BSC'], ['L.L.B.', 'LLB'], ['B.Arch', 'BARCH']
];

// Helper for generating random dates
function randomDate($startDate, $endDate) {
    $min = strtotime($startDate);
    $max = strtotime($endDate);
    $val = rand($min, $max);
    return date('Y-m-d H:i:s', $val);
}

// 3. Departments
echo "Inserting 15 Departments...\n";
foreach ($departments as $dept) {
    $conn->query("INSERT INTO departments (name, status) VALUES ('$dept', 'active')");
}

// 4. Courses
echo "Inserting 15 Courses...\n";
foreach ($courses as $i => $c) {
    $dept_id = $i + 1;
    $conn->query("INSERT INTO courses (course_name, course_code, department_id, status) VALUES ('{$c[0]}', '{$c[1]}', $dept_id, 'active')");
}

// 5. Teachers (100)
echo "Inserting 100 Teachers...\n";
for ($i = 1; $i <= 100; $i++) {
    $fname = $firstNames[array_rand($firstNames)];
    $lname = $lastNames[array_rand($lastNames)];
    $name = "Prof. $fname $lname";
    $username = strtolower($fname . $lname . $i);
    $email = $username . "@edu.in";
    $dept_id = rand(1, 15);
    $emp_id = "EMP2026" . str_pad($i, 3, '0', STR_PAD_LEFT);
    $conn->query("INSERT INTO teachers (name, username, password, email, department_id, employee_id, status) VALUES 
        ('$name', '$username', 'password', '$email', $dept_id, '$emp_id', 'active')");
}

// 6. Subjects (3-5 per course)
echo "Inserting Subjects...\n";
$subject_id_map = []; // course_id => [subject_ids]
for ($cid = 1; $cid <= 15; $cid++) {
    for ($s = 1; $s <= 4; $s++) {
        $subj_name = "Subject $s for Course $cid";
        $code = "SUB" . $cid . $s;
        $teacher_id = rand(1, 100);
        $conn->query("INSERT INTO subjects (name, code, course_id, teacher_id) VALUES ('$subj_name', '$code', $cid, $teacher_id)");
        $subject_id_map[$cid][] = $conn->insert_id;
    }
}

// 7. Classes and Sections
echo "Inserting Classes and Sections...\n";
$class_id_map = []; // course_id => [class_ids]
$section_id_map = []; // class_id => [section_ids]
$all_sections = [];
for ($cid = 1; $cid <= 15; $cid++) {
    $classes = ['First Year', 'Second Year'];
    foreach ($classes as $cl) {
        $tid = rand(1, 100);
        $conn->query("INSERT INTO classes (name, course_id, teacher_id) VALUES ('$cl', $cid, $tid)");
        $class_id = $conn->insert_id;
        $class_id_map[$cid][] = $class_id;
        
        foreach (['A', 'B'] as $sec) {
            $stid = rand(1, 100);
            $conn->query("INSERT INTO sections (name, class_id, teacher_id) VALUES ('Section $sec', $class_id, $stid)");
            $sec_id = $conn->insert_id;
            $section_id_map[$class_id][] = $sec_id;
            $all_sections[] = $sec_id;
        }
    }
}

// 8. Students (200)
echo "Inserting 200 Students...\n";
$student_ids = [];
$student_section_map = []; // student_id => section_id
$student_course_map = []; // student_id => course_id
for ($i = 1; $i <= 200; $i++) {
    $fname = $firstNames[array_rand($firstNames)];
    $lname = $lastNames[array_rand($lastNames)];
    $name = "$fname $lname";
    $reg_no = "STU2026" . str_pad($i, 3, '0', STR_PAD_LEFT);
    $email = strtolower($fname . "." . $lname . $i) . "@student.in";
    
    $conn->query("INSERT INTO users (name, reg_no, email, password, role, terms_accepted) VALUES ('$name', '$reg_no', '$email', 'password', 'student', 1)");
    $sid = $conn->insert_id;
    $student_ids[] = $sid;
    
    $sec_id = $all_sections[array_rand($all_sections)];
    $conn->query("INSERT INTO section_students (section_id, student_id) VALUES ($sec_id, $sid)");
    $student_section_map[$sid] = $sec_id;
    
    // Find course of this section
    $res = $conn->query("SELECT c.course_id FROM sections s JOIN classes c ON s.class_id = c.id WHERE s.id = $sec_id");
    if($res) {
        $row = $res->fetch_assoc();
        $student_course_map[$sid] = $row['course_id'];
    }
}

// 9. Attendance (09-07-2025 to 09-07-2026)
echo "Inserting Attendance (This might take a minute)...\n";
$startDate = strtotime('2025-07-09');
$endDate = strtotime('2026-07-09');
$totalDays = ($endDate - $startDate) / (60 * 60 * 24);

$batch = [];
$batch_size = 5000;
for ($day = 0; $day <= $totalDays; $day++) {
    $currentDate = $startDate + ($day * 86400);
    // skip weekends
    if (date('N', $currentDate) >= 6) continue;
    
    $dateStr = date('Y-m-d', $currentDate);
    foreach ($student_ids as $sid) {
        $sec_id = $student_section_map[$sid];
        $rand = rand(1, 100);
        $status = 'Present';
        if ($rand > 95) $status = 'Absent';
        elseif ($rand > 90) $status = 'Late';
        elseif ($rand > 88) $status = 'Leave';
        
        $batch[] = "($sid, $sec_id, '$dateStr', '$status')";
        
        if (count($batch) >= $batch_size) {
            $conn->query("INSERT INTO erp_attendance (student_id, section_id, `date`, status) VALUES " . implode(',', $batch));
            $batch = [];
        }
    }
}
if (count($batch) > 0) {
    $conn->query("INSERT INTO erp_attendance (student_id, section_id, `date`, status) VALUES " . implode(',', $batch));
}

// 10. Exams
echo "Inserting Exams and Marks...\n";
$exam_types = [
    ['name' => 'Mid Sem 1', 'type' => 'Mid Semester', 'start' => '2025-09-15', 'end' => '2025-09-25'],
    ['name' => 'Mid Sem 2', 'type' => 'Mid Semester', 'start' => '2026-02-15', 'end' => '2026-02-25'],
    ['name' => 'Final Sem', 'type' => 'Final Semester', 'start' => '2026-05-15', 'end' => '2026-05-25']
];

foreach ($courses as $cid => $cdata) {
    $course_id = $cid + 1;
    if (!isset($class_id_map[$course_id])) continue;
    
    foreach ($class_id_map[$course_id] as $class_id) {
        foreach ($exam_types as $et) {
            $status = (strtotime($et['start']) < time()) ? 'Completed' : 'Upcoming';
            $conn->query("INSERT INTO erp_exams (name, exam_type, class_id, start_date, end_date, status) VALUES 
                ('{$et['name']} - {$cdata[1]}', '{$et['type']}', $class_id, '{$et['start']}', '{$et['end']}', '$status')");
            $exam_id = $conn->insert_id;
            
            if (isset($subject_id_map[$course_id])) {
                foreach ($subject_id_map[$course_id] as $subj_id) {
                    $conn->query("INSERT INTO erp_exam_subjects (exam_id, subject_id, exam_date) VALUES ($exam_id, $subj_id, '{$et['start']}')");
                    $exam_subj_id = $conn->insert_id;
                    
                    // Generate marks for students in this class
                    $res = $conn->query("SELECT ss.student_id FROM sections s JOIN section_students ss ON s.id = ss.section_id WHERE s.class_id = $class_id");
                    $mbatch = [];
                    while($row = $res->fetch_assoc()) {
                        $sid = $row['student_id'];
                        $marks = rand(35, 100);
                        $mbatch[] = "($exam_subj_id, $sid, $marks)";
                    }
                    if(!empty($mbatch)) {
                        $conn->query("INSERT INTO erp_exam_marks (exam_subject_id, student_id, total_marks) VALUES " . implode(',', $mbatch));
                    }
                }
            }
        }
    }
}

// 11. Assignments
echo "Inserting Assignments...\n";
foreach ($subject_id_map as $cid => $subjects) {
    foreach ($subjects as $subj_id) {
        for ($a=1; $a<=3; $a++) {
            // Find a class for this course
            $class_id = $class_id_map[$cid][0]; 
            $due = date('Y-m-d H:i:s', strtotime("2026-03-01") + rand(0, 86400*30)); // random due in Mar 2026
            
            $tid = rand(1, 100);
            $conn->query("INSERT INTO erp_assignments (title, class_id, subject_id, teacher_id, due_date, status) VALUES 
                ('Assignment $a', $class_id, $subj_id, $tid, '$due', 'Active')");
            $assign_id = $conn->insert_id;
            
            // Submissions
            $res = $conn->query("SELECT ss.student_id FROM sections s JOIN section_students ss ON s.id = ss.section_id WHERE s.class_id = $class_id LIMIT 5");
            while($row = $res->fetch_assoc()) {
                $sid = $row['student_id'];
                $sub_date = date('Y-m-d H:i:s', strtotime($due) - rand(0, 86400*2));
                $conn->query("INSERT INTO erp_assignment_submissions (assignment_id, student_id, status, submitted_at, marks_obtained) VALUES 
                    ($assign_id, $sid, 'Graded', '$sub_date', " . rand(5, 10) . ")");
            }
        }
    }
}

// 12. Fees
echo "Inserting Fees...\n";
foreach ($courses as $cid => $cdata) {
    $course_id = $cid + 1;
    $conn->query("INSERT INTO erp_fee_structures (fee_type, academic_year, course_id, amount, due_date) VALUES 
        ('Tuition Fee', '2025-2026', $course_id, 15000.00, '2025-08-01')");
    $fs_id = $conn->insert_id;
    
    // Assign to students in this course
    foreach ($student_ids as $sid) {
        if ($student_course_map[$sid] == $course_id) {
            $status = (rand(1, 100) > 20) ? 'Paid' : 'Pending'; // 80% paid
            $conn->query("INSERT INTO erp_fee_invoices (student_id, fee_structure_id, total_amount, paid_amount, status) VALUES ($sid, $fs_id, 15000.00, ".($status=='Paid'?15000.00:0).", '$status')");
            $inv_id = $conn->insert_id;
            
            if ($status == 'Paid') {
                $conn->query("INSERT INTO erp_fee_payments (invoice_id, amount, payment_method, payment_date) VALUES ($inv_id, 15000.00, 'Online', '2025-07-15')");
            }
        }
    }
}

// 13. Leaves
echo "Inserting Leaves...\n";
for($i=1; $i<=20; $i++) {
    $sid = rand(1, 200);
    $conn->query("INSERT INTO erp_leaves (user_type, student_id, leave_type, start_date, end_date, reason, status) VALUES 
        ('student', $sid, 'Sick Leave', '2026-07-05', '2026-07-07', 'Viral Fever', 'Approved')");
        
    $tid = rand(1, 100);
    $conn->query("INSERT INTO erp_leaves (user_type, teacher_id, leave_type, start_date, end_date, reason, status) VALUES 
        ('teacher', $tid, 'Casual Leave', '2026-07-10', '2026-07-11', 'Family Function', 'Pending')");
}

echo "Database seeding completed successfully!\n";
