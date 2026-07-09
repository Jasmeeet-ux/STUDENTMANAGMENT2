<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get 25 students to seed leave requests
$res = $conn->query("SELECT student_id FROM erp_attendance WHERE status='Leave' AND date=CURDATE() LIMIT 25");
$students = [];
while ($row = $res->fetch_assoc()) {
    $students[] = $row['student_id'];
}

// If we don't have enough, just get random students
if (count($students) < 25) {
    $res = $conn->query("SELECT id FROM users WHERE role='student' ORDER BY RAND() LIMIT 25");
    while ($row = $res->fetch_assoc()) {
        $students[] = $row['id'];
    }
}

// Also get some teachers
$res = $conn->query("SELECT id FROM teachers ORDER BY RAND() LIMIT 10");
$teachers = [];
while ($row = $res->fetch_assoc()) {
    $teachers[] = $row['id'];
}

$conn->query("TRUNCATE TABLE erp_leaves");

$types = ['Sick Leave', 'Casual Leave', 'Emergency Leave', 'Vacation', 'Bereavement Leave'];
$statuses = ['Pending', 'Approved', 'Rejected'];

// Seed Student Leaves
foreach ($students as $index => $s_id) {
    $type = $types[array_rand($types)];
    $status = $statuses[array_rand($statuses)];
    // Ensure we have some of each status
    if ($index < 5) $status = 'Pending';
    if ($index >= 5 && $index < 15) $status = 'Approved';
    if ($index >= 15 && $index < 20) $status = 'Rejected';
    
    $reason = "This is a dummy reason for $type.";
    $start = date('Y-m-d', strtotime('-' . rand(0, 5) . ' days'));
    $end = date('Y-m-d', strtotime($start . ' +' . rand(1, 3) . ' days'));
    
    $stmt = $conn->prepare("INSERT INTO erp_leaves (user_type, student_id, leave_type, start_date, end_date, reason, status) VALUES ('student', ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $s_id, $type, $start, $end, $reason, $status);
    $stmt->execute();
}

// Seed Teacher Leaves
foreach ($teachers as $index => $t_id) {
    $type = $types[array_rand($types)];
    $status = $statuses[array_rand($statuses)];
    
    $reason = "Teacher dummy reason for $type.";
    $start = date('Y-m-d', strtotime('-' . rand(0, 5) . ' days'));
    $end = date('Y-m-d', strtotime($start . ' +' . rand(1, 3) . ' days'));
    
    $stmt = $conn->prepare("INSERT INTO erp_leaves (user_type, teacher_id, leave_type, start_date, end_date, reason, status) VALUES ('teacher', ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $t_id, $type, $start, $end, $reason, $status);
    $stmt->execute();
}

echo "Successfully seeded " . (count($students) + count($teachers)) . " leave requests into Leave Management!\n";
