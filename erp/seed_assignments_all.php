<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';
$conn = new mysqli($host, $user, $pass, $db);

// Get all subjects
$subjects = $conn->query("SELECT id, name FROM subjects WHERE status='active' AND deleted_at IS NULL")->fetch_all(MYSQLI_ASSOC);

// Get a default teacher
$teacher = $conn->query("SELECT id FROM teachers WHERE status='active' LIMIT 1")->fetch_assoc()['id'] ?? 1;

// Find which classes are active
$classes = $conn->query("SELECT id FROM classes WHERE status='active' AND deleted_at IS NULL")->fetch_all(MYSQLI_ASSOC);
$class_ids = array_column($classes, 'id');

$added = 0;

foreach ($subjects as $s) {
    $sid = $s['id'];
    $sname = $s['name'];
    
    // Check if assignment already exists for this subject
    $check = $conn->query("SELECT id FROM erp_assignments WHERE subject_id = $sid AND deleted_at IS NULL LIMIT 1");
    if ($check->num_rows == 0) {
        // Create an assignment for this subject
        $cid = $class_ids[array_rand($class_ids)]; // Pick a random class
        
        $title = $sname . ' - Comprehensive Assignment';
        $desc = 'Please complete all exercises for ' . $sname . '. Submit your work before the deadline.';
        $due = date('Y-m-d H:i:s', strtotime('+' . rand(3, 14) . ' days'));
        
        $stmt = $conn->prepare("INSERT INTO erp_assignments (title, description, class_id, subject_id, teacher_id, due_date, max_marks, status) VALUES (?, ?, ?, ?, ?, ?, 100, 'Active')");
        $stmt->bind_param("ssiiis", $title, $desc, $cid, $sid, $teacher, $due);
        $stmt->execute();
        $added++;
    }
}

echo "Seeded $added new assignments to ensure 100% subject coverage!";
