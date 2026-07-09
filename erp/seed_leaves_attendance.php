<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';

$conn = new mysqli($host, $user, $pass, $db);

// 1. Seed some Leave Requests
$res = $conn->query("SELECT id FROM users WHERE role='student' LIMIT 20");
$students = [];
while ($row = $res->fetch_assoc()) $students[] = $row['id'];

foreach ($students as $i => $sid) {
    $types = ['Sick Leave', 'Casual Leave', 'Emergency Leave'];
    $status = ['Pending', 'Approved', 'Rejected'];
    
    $type = $types[array_rand($types)];
    $stat = $status[array_rand($status)];
    if ($i < 10) $stat = 'Pending'; // Ensure plenty of pending ones
    
    $start = date('Y-m-d', strtotime("+" . rand(1, 10) . " days"));
    $end = date('Y-m-d', strtotime($start . " +" . rand(1, 5) . " days"));
    
    $conn->query("INSERT INTO erp_leaves (student_id, user_type, leave_type, start_date, end_date, reason, status) 
                  VALUES ($sid, 'student', '$type', '$start', '$end', 'Need some time off for personal reasons.', '$stat')");
}

// 2. Fix the Attendance Flat Line (Add seasonal noise and ensure Today/Tomorrow is covered)
$res = $conn->query("SELECT DISTINCT date FROM erp_attendance");
$dates = [];
while ($row = $res->fetch_assoc()) $dates[] = $row['date'];

foreach ($dates as $d) {
    $month = (int)date('m', strtotime($d));
    
    // Base present rate varies by month (e.g. lower in summer/winter)
    // Jan=85, Feb=90, Mar=92, Apr=88, May=75, Jun=70, Jul=82, Aug=88, Sep=95, Oct=92, Nov=85, Dec=80
    $base_rates = [1=>85, 2=>90, 3=>92, 4=>88, 5=>75, 6=>70, 7=>82, 8=>88, 9=>95, 10=>92, 11=>85, 12=>80];
    $base = $base_rates[$month] ?? 85;
    
    // Add random noise +/- 3%
    $rate = $base + rand(-3, 3);
    
    // We will randomly update some 'Present' to 'Absent' or vice versa to hit the target rate
    // Actually, it's easier to just run an update query. If rate is lower than 85, set some Present to Absent.
    // Let's just update all records for that date to randomize it properly.
    // Too slow for all rows. Just update a percentage.
    if ($rate < 85) {
        $limit = (int)((85 - $rate) / 100 * 3360);
        $conn->query("UPDATE erp_attendance SET status='Absent' WHERE date='$d' AND status='Present' LIMIT $limit");
    } elseif ($rate > 85) {
        $limit = (int)(($rate - 85) / 100 * 3360);
        $conn->query("UPDATE erp_attendance SET status='Present' WHERE date='$d' AND status!='Present' LIMIT $limit");
    }
}

// 3. Ensure we have data for the exact CURRENT date taking into account timezones
$dates_to_seed = [date('Y-m-d'), date('Y-m-d', strtotime('+1 day')), date('Y-m-d', strtotime('-1 day'))];

$res = $conn->query("SELECT ss.student_id, ss.section_id FROM section_students ss JOIN users u ON ss.student_id = u.id WHERE u.role = 'student'");
$students = [];
while ($row = $res->fetch_assoc()) $students[] = $row;

foreach ($dates_to_seed as $d) {
    $values = [];
    foreach ($students as $s) {
        $sid = $s['student_id'];
        $sec = $s['section_id'];
        $r = rand(1, 100);
        $status = ($r <= 92) ? 'Present' : 'Absent';
        $values[] = "($sec, $sid, '$d', '$status')";
        if (count($values) >= 3000) {
            $conn->query("INSERT IGNORE INTO erp_attendance (section_id, student_id, date, status) VALUES " . implode(',', $values));
            $values = [];
        }
    }
    if (count($values) > 0) {
        $conn->query("INSERT IGNORE INTO erp_attendance (section_id, student_id, date, status) VALUES " . implode(',', $values));
    }
}

echo "Leaves seeded and attendance randomized & fixed!\n";
