<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Clear old fees
$conn->query("SET FOREIGN_KEY_CHECKS = 0;");
$conn->query("TRUNCATE TABLE erp_fee_structures");
$conn->query("TRUNCATE TABLE erp_fee_invoices");
$conn->query("TRUNCATE TABLE erp_fee_payments");
$conn->query("SET FOREIGN_KEY_CHECKS = 1;");

$academic_year = '2026-2027';

// Define the Indian fee tiers
$tiers = [
    'B.Tech CSE' => ['Tuition' => 100000, 'Lab/Exam' => 15000, 'Library' => 10000],
    'B.Tech IT' => ['Tuition' => 100000, 'Lab/Exam' => 15000, 'Library' => 10000],
    'B.Tech ME' => ['Tuition' => 100000, 'Lab/Exam' => 15000, 'Library' => 10000],
    'B.Tech CE' => ['Tuition' => 100000, 'Lab/Exam' => 15000, 'Library' => 10000],
    'B.Tech EE' => ['Tuition' => 100000, 'Lab/Exam' => 15000, 'Library' => 10000],
    'B.Tech ECE' => ['Tuition' => 100000, 'Lab/Exam' => 15000, 'Library' => 10000],
    'B.Tech Bio' => ['Tuition' => 100000, 'Lab/Exam' => 15000, 'Library' => 10000],
    'B.Tech Chem' => ['Tuition' => 100000, 'Lab/Exam' => 15000, 'Library' => 10000],
    'B.Tech Aero' => ['Tuition' => 100000, 'Lab/Exam' => 15000, 'Library' => 10000],
    'B.Arch' => ['Tuition' => 105000, 'Lab/Exam' => 15000, 'Library' => 10000],
    'BBA' => ['Tuition' => 65000, 'Exam' => 10000, 'Library' => 5000],
    'L.L.B.' => ['Tuition' => 65000, 'Exam' => 10000, 'Library' => 5000],
    'B.Com' => ['Tuition' => 40000, 'Exam' => 6000, 'Library' => 4000],
    'B.Sc Science' => ['Tuition' => 40000, 'Lab/Exam' => 6000, 'Library' => 4000],
    'B.A. Arts' => ['Tuition' => 25000, 'Exam' => 7000, 'Library' => 3000],
];

// Fallback tier
$fallback = ['Tuition' => 50000, 'Exam' => 5000, 'Library' => 5000];

// Fetch all courses
$courses = $conn->query("SELECT id, course_name FROM courses");
while ($c = $courses->fetch_assoc()) {
    $cid = $c['id'];
    $cname = $c['course_name'];
    
    $tier = $tiers[$cname] ?? $fallback;
    
    foreach ($tier as $fee_name => $amount) {
        $type = $fee_name . " Fee ($academic_year)";
        $due = date('Y-m-d', strtotime('+30 days'));
        
        $stmt = $conn->prepare("INSERT INTO erp_fee_structures (course_id, fee_type, academic_year, amount, due_date) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $cid, $type, $academic_year, $amount, $due);
        $stmt->execute();
        
        $structure_id = $conn->insert_id;
        
        // Assign to all active students in this course
        // A student is in a course via class -> course
        $student_q = $conn->prepare("
            SELECT u.id 
            FROM users u
            JOIN section_students ss ON u.id = ss.student_id
            JOIN sections s ON ss.section_id = s.id
            JOIN classes cl ON s.class_id = cl.id
            WHERE u.role='student' AND cl.course_id = ?
            GROUP BY u.id
        ");
        $student_q->bind_param("i", $cid);
        $student_q->execute();
        $students = $student_q->get_result();
        
        while ($s = $students->fetch_assoc()) {
            $sid = $s['id'];
            $inv_stmt = $conn->prepare("INSERT INTO erp_fee_invoices (student_id, fee_structure_id, status) VALUES (?, ?, 'Pending')");
            $inv_stmt->bind_param("ii", $sid, $structure_id);
            $inv_stmt->execute();
            $inv_id = $conn->insert_id;
            
            // Randomly mark some as Paid (30% chance)
            if (rand(1, 100) <= 30) {
                $pay_stmt = $conn->prepare("INSERT INTO erp_fee_payments (invoice_id, amount, payment_method) VALUES (?, ?, 'Bank Transfer')");
                $pay_stmt->bind_param("id", $inv_id, $amount);
                $pay_stmt->execute();
                
                $conn->query("UPDATE erp_fee_invoices SET status='Paid' WHERE id=$inv_id");
            }
        }
    }
}

echo "Successfully seeded realistic Indian fee structures and generated invoices!\n";
