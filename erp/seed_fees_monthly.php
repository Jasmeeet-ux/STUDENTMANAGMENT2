<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 1. Truncate existing fee data
$conn->query("SET FOREIGN_KEY_CHECKS = 0;");
$conn->query("TRUNCATE TABLE erp_fee_payments");
$conn->query("TRUNCATE TABLE erp_fee_invoices");
$conn->query("TRUNCATE TABLE erp_fee_structures");
$conn->query("SET FOREIGN_KEY_CHECKS = 1;");

// 2. Create 12 fee structures per course
$months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

echo "Creating Fee Structures...\n";
$fee_structures = []; // course_id => [array of fee structure ids]
for ($c = 1; $c <= 15; $c++) {
    $fee_structures[$c] = [];
    foreach ($months as $i => $m) {
        $due_date = "2026-" . str_pad($i + 1, 2, '0', STR_PAD_LEFT) . "-05"; // 5th of each month
        $amount = 15000.00;
        $name = "Tuition Fee - $m";
        $conn->query("INSERT INTO erp_fee_structures (course_id, fee_type, academic_year, amount, due_date) VALUES ($c, '$name', '2025-2026', $amount, '$due_date')");
        $fee_structures[$c][] = $conn->insert_id;
    }
}

// 3. Map students to courses
echo "Mapping students to courses...\n";
$res = $conn->query("
    SELECT ss.student_id, c.course_id 
    FROM section_students ss
    JOIN sections s ON ss.section_id = s.id
    JOIN classes c ON s.class_id = c.id
");

$student_courses = [];
while ($row = $res->fetch_assoc()) {
    $student_courses[$row['student_id']] = $row['course_id'];
}

// 4. Batch Generate Invoices & Payments
echo "Generating Invoices and Payments...\n";

$invoiceValues = [];
$paymentValues = [];
$currentMonth = (int)date('m');

// In memory array to batch inserts
$invoice_id_counter = 1; 
$conn->query("ALTER TABLE erp_fee_invoices AUTO_INCREMENT = 1");
$conn->query("ALTER TABLE erp_fee_payments AUTO_INCREMENT = 1");

$batchCount = 0;
$conn->begin_transaction();

foreach ($student_courses as $student_id => $course_id) {
    if (!isset($fee_structures[$course_id])) continue;
    
    foreach ($fee_structures[$course_id] as $index => $fs_id) {
        $monthNum = $index + 1; // 1 to 12
        $amount = 15000.00;
        
        $paid_amount = 0;
        $status = 'Pending';
        
        if ($monthNum < $currentMonth) {
            // Past month - 90% paid, 10% pending
            if (rand(1, 100) <= 90) {
                $paid_amount = $amount;
                $status = 'Paid';
            }
        } else if ($monthNum == $currentMonth) {
            // Current month - 50% paid, 20% partial, 30% pending
            $r = rand(1, 100);
            if ($r <= 50) {
                $paid_amount = $amount;
                $status = 'Paid';
            } else if ($r <= 70) {
                $paid_amount = 5000.00;
                $status = 'Partial';
            }
        }
        
        // Push invoice
        $invoiceValues[] = "($student_id, $fs_id, $amount, 0.00, $paid_amount, '$status')";
        
        if ($paid_amount > 0) {
            $methods = ['Card', 'Online', 'Bank Transfer', 'Cash'];
            $method = $methods[array_rand($methods)];
            $pay_date = "2026-" . str_pad($monthNum, 2, '0', STR_PAD_LEFT) . "-" . str_pad(rand(1,28), 2, '0', STR_PAD_LEFT) . " 10:00:00";
            $paymentValues[] = "($invoice_id_counter, '$pay_date', $paid_amount, '$method', 'REF" . rand(10000, 99999) . "')";
        }
        
        $invoice_id_counter++;
        $batchCount++;
        
        if ($batchCount >= 5000) {
            $conn->query("INSERT INTO erp_fee_invoices (student_id, fee_structure_id, total_amount, fine_amount, paid_amount, status) VALUES " . implode(',', $invoiceValues));
            if (!empty($paymentValues)) {
                $conn->query("INSERT INTO erp_fee_payments (invoice_id, payment_date, amount, payment_method, reference_no) VALUES " . implode(',', $paymentValues));
            }
            $invoiceValues = [];
            $paymentValues = [];
            $batchCount = 0;
        }
    }
}

// Final batch
if (!empty($invoiceValues)) {
    $conn->query("INSERT INTO erp_fee_invoices (student_id, fee_structure_id, total_amount, fine_amount, paid_amount, status) VALUES " . implode(',', $invoiceValues));
}
if (!empty($paymentValues)) {
    $conn->query("INSERT INTO erp_fee_payments (invoice_id, payment_date, amount, payment_method, reference_no) VALUES " . implode(',', $paymentValues));
}

$conn->commit();

echo "Successfully generated full 1-year monthly fee records (over 42,000 invoices) for all students!\n";
