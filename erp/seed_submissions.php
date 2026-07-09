<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';
$conn = new mysqli($host, $user, $pass, $db);

// Get 200 random assignments
$assignments = $conn->query("SELECT id, class_id FROM erp_assignments WHERE deleted_at IS NULL ORDER BY RAND() LIMIT 200")->fetch_all(MYSQLI_ASSOC);

$added = 0;
$conn->begin_transaction();
try {
    foreach ($assignments as $a) {
        $aid = $a['id'];
        $cid = $a['class_id'];
        
        // Get students for this class
        $students = $conn->query("
            SELECT u.id 
            FROM users u
            JOIN section_students ss ON u.id = ss.student_id
            JOIN sections sec ON ss.section_id = sec.id
            WHERE sec.class_id = $cid AND u.role = 'student'
        ")->fetch_all(MYSQLI_ASSOC);
        
        if (empty($students)) continue;
        
        // Shuffle students and pick up to 20
        shuffle($students);
        $subset = array_slice($students, 0, rand(5, 20));
        
        $stmt = $conn->prepare("
            INSERT INTO erp_assignment_submissions 
            (assignment_id, student_id, submission_text, status, submitted_at) 
            VALUES (?, ?, ?, ?, ?)
            ON DUPLICATE KEY UPDATE status=VALUES(status)
        ");
        
        foreach ($subset as $st) {
            $sid = $st['id'];
            $text = "Here is my submission for the assignment. Please find the details attached or described here.";
            $statuses = ['Submitted', 'Submitted', 'Late'];
            $status = $statuses[array_rand($statuses)];
            $submitted_at = date('Y-m-d H:i:s', strtotime('-' . rand(1, 48) . ' hours'));
            
            $stmt->bind_param("iisss", $aid, $sid, $text, $status, $submitted_at);
            $stmt->execute();
            $added++;
        }
    }
    $conn->commit();
    echo "Successfully seeded $added pending submissions!";
} catch (Exception $e) {
    $conn->rollback();
    echo "Error: " . $e->getMessage();
}
