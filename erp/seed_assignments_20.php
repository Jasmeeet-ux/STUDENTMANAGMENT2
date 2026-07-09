<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';
$conn = new mysqli($host, $user, $pass, $db);

// Fetch all subjects, classes, teachers
$subjects = $conn->query("SELECT id, name FROM subjects WHERE status='active' AND deleted_at IS NULL")->fetch_all(MYSQLI_ASSOC);
$classes = $conn->query("SELECT id FROM classes WHERE status='active' AND deleted_at IS NULL")->fetch_all(MYSQLI_ASSOC);
$teachers = $conn->query("SELECT id FROM teachers WHERE status='active' AND deleted_at IS NULL")->fetch_all(MYSQLI_ASSOC);

$class_ids = array_column($classes, 'id');
$teacher_ids = array_column($teachers, 'id');

$added = 0;

$types = ['Lab Report', 'Case Study', 'Mid-Term Essay', 'Weekly Worksheet', 'Project Proposal', 'Literature Review', 'Problem Set', 'Group Presentation', 'Final Paper', 'Quiz Preparation'];

$conn->begin_transaction();

try {
    foreach ($subjects as $s) {
        $sid = $s['id'];
        $sname = $s['name'];
        
        $res = $conn->query("SELECT COUNT(*) as cnt FROM erp_assignments WHERE subject_id = $sid AND deleted_at IS NULL");
        $count = $res->fetch_assoc()['cnt'];
        
        $needed = 20 - $count;
        
        if ($needed > 0) {
            $stmt = $conn->prepare("INSERT INTO erp_assignments (title, description, class_id, subject_id, teacher_id, due_date, max_marks, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            
            for ($i = 0; $i < $needed; $i++) {
                $type = $types[array_rand($types)];
                $title = $sname . ' - ' . $type . ' ' . ($i + 1);
                $desc = "Please complete this $type for $sname.";
                $cid = $class_ids[array_rand($class_ids)];
                $tid = $teacher_ids[array_rand($teacher_ids)];
                
                // Random due date between -30 days and +30 days
                $days = rand(-30, 30);
                $due = date('Y-m-d H:i:s', strtotime(($days >= 0 ? '+' : '') . $days . ' days'));
                $max_marks = rand(5, 10) * 10; // 50, 60, ..., 100
                $status = ($days >= 0) ? 'Active' : 'Closed';
                
                $stmt->bind_param("ssiiisds", $title, $desc, $cid, $sid, $tid, $due, $max_marks, $status);
                $stmt->execute();
                $added++;
            }
        }
    }
    $conn->commit();
    echo "Successfully seeded $added assignments so every subject has at least 20!";
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
