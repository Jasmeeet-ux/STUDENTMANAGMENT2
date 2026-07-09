<?php
// seed_teacher_extra.php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->query("TRUNCATE TABLE teacher_documents");

// We don't truncate audit_logs completely if it has other logs, but for clean state let's just delete teacher logs
$conn->query("DELETE FROM audit_logs WHERE user_type = 'teacher'");

$docs = ['resume', 'certificate', 'id_proof', 'degree'];
$actions = ['Logged In', 'Graded Assignment', 'Marked Attendance', 'Updated Profile', 'Uploaded Document'];

$res = $conn->query("SELECT id FROM teachers");
while ($row = $res->fetch_assoc()) {
    $tid = $row['id'];
    
    // Seed Documents (2-4 per teacher)
    $numDocs = rand(2, 4);
    for($i=0; $i<$numDocs; $i++) {
        $type = $docs[array_rand($docs)];
        $path = "uploads/teachers/{$tid}/{$type}_" . time() . ".pdf";
        $conn->query("INSERT INTO teacher_documents (teacher_id, document_type, file_path) VALUES ($tid, '$type', '$path')");
    }
    
    // Seed Activity Logs (5-10 per teacher)
    $numLogs = rand(5, 10);
    for($i=0; $i<$numLogs; $i++) {
        $action = $actions[array_rand($actions)];
        $module = strtolower(str_replace(' ', '_', $action));
        $date = date('Y-m-d H:i:s', time() - rand(0, 86400 * 30)); // past 30 days
        $conn->query("INSERT INTO audit_logs (user_id, user_type, action, module, created_at) VALUES ($tid, 'teacher', '$action', '$module', '$date')");
    }
}

echo "Extra teacher data seeded successfully!\n";
