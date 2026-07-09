<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "Starting 1-year attendance sync...\n";

// Get all active students with their sections
// We need student_id and section_id
$res = $conn->query("
    SELECT ss.student_id, ss.section_id
    FROM section_students ss
    JOIN users u ON ss.student_id = u.id
    WHERE u.role = 'student'
");
$students = [];
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
}
$total = count($students);
echo "Found $total students.\n";

// We will seed the past 12 months.
// To keep DB size manageable and fast, we'll only seed 1 day per month (or 10 days per month)
// Wait, the chart is monthly, but if they want "1 year", maybe 1 record per month per student is enough to show a trend,
// or we can seed the 1st of every month for the last 12 months.
// Actually, let's seed 1 random day per week for the last 52 weeks, so we have good data spread.

$weeks = 52;
$start_date = strtotime("-52 weeks");

$conn->query("TRUNCATE TABLE erp_attendance");

$values = [];
$batch_size = 5000;
$count = 0;

for ($w = 0; $w < $weeks; $w++) {
    $date = date('Y-m-d', strtotime("+$w weeks", $start_date));
    
    foreach ($students as $s) {
        $sid = $s['student_id'];
        $sec = $s['section_id'];
        
        $r = rand(1, 100);
        if ($r <= 85) {
            $status = 'Present';
        } elseif ($r <= 92) {
            $status = 'Absent';
        } elseif ($r <= 97) {
            $status = 'Late';
        } else {
            $status = 'Leave';
        }
        
        $values[] = "($sec, $sid, '$date', '$status')";
        
        if (count($values) >= $batch_size) {
            $sql = "INSERT INTO erp_attendance (section_id, student_id, date, status) VALUES " . implode(',', $values);
            $conn->query($sql);
            $count += count($values);
            $values = [];
        }
    }
}

// Insert remaining
if (count($values) > 0) {
    $sql = "INSERT INTO erp_attendance (section_id, student_id, date, status) VALUES " . implode(',', $values);
    $conn->query($sql);
    $count += count($values);
}

// Ensure today is seeded for the top metric
$today = date('Y-m-d');
$values = [];
foreach ($students as $s) {
    $sid = $s['student_id'];
    $sec = $s['section_id'];
    $r = rand(1, 100);
    $status = ($r <= 88) ? 'Present' : 'Absent';
    $values[] = "($sec, $sid, '$today', '$status')";
    if (count($values) >= $batch_size) {
        $sql = "INSERT IGNORE INTO erp_attendance (section_id, student_id, date, status) VALUES " . implode(',', $values);
        $conn->query($sql);
        $values = [];
    }
}
if (count($values) > 0) {
    $sql = "INSERT IGNORE INTO erp_attendance (section_id, student_id, date, status) VALUES " . implode(',', $values);
    $conn->query($sql);
}

echo "Successfully seeded over $count attendance records across 52 weeks!\n";
