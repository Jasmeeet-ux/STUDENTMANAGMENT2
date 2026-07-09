<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';
$conn = new mysqli($host, $user, $pass, $db);
$tables = ['users WHERE role="student"', 'teachers', 'departments', 'courses', 'subjects', 'classes', 'sections', 'erp_attendance', 'erp_exams', 'erp_assignments', 'erp_leaves', 'erp_fee_invoices', 'erp_fee_payments'];
foreach($tables as $t) { 
    $res = $conn->query("SELECT COUNT(*) as c FROM $t");
    if($res) echo $t . ': ' . $res->fetch_assoc()['c'] . "\n";
}
