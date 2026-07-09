<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';

$conn = new mysqli($host, $user, $pass, $db);

// 1. Clear all existing payments
$conn->query("TRUNCATE TABLE erp_fee_payments");

// 2. Fetch all invoices
$res = $conn->query("SELECT id, total_amount FROM erp_fee_invoices");
$invoices = $res->fetch_all(MYSQLI_ASSOC);

$total_invoices = count($invoices);
$paid_count = (int)($total_invoices * 0.85); // 85% fully paid
$partial_count = (int)($total_invoices * 0.05); // 5% partially paid
// remaining 10% pending

// Shuffle to randomize
shuffle($invoices);

$stmt = $conn->prepare("INSERT INTO erp_fee_payments (invoice_id, payment_date, amount, payment_method) VALUES (?, NOW(), ?, 'Bank Transfer')");

// Insert Paid
for ($i = 0; $i < $paid_count; $i++) {
    $inv = $invoices[$i];
    if ($inv['total_amount'] > 0) {
        $stmt->bind_param("id", $inv['id'], $inv['total_amount']);
        $stmt->execute();
    }
}

// Insert Partial
for ($i = $paid_count; $i < $paid_count + $partial_count; $i++) {
    $inv = $invoices[$i];
    if ($inv['total_amount'] > 0) {
        $amount = $inv['total_amount'] / 2;
        $stmt->bind_param("id", $inv['id'], $amount);
        $stmt->execute();
    }
}

// 3. Sync back to invoices
$conn->query("
    UPDATE erp_fee_invoices i
    SET i.paid_amount = (SELECT COALESCE(SUM(amount), 0) FROM erp_fee_payments p WHERE p.invoice_id = i.id)
");

$conn->query("
    UPDATE erp_fee_invoices
    SET status = CASE 
        WHEN paid_amount >= (total_amount + fine_amount) AND (total_amount + fine_amount) > 0 THEN 'Paid'
        WHEN paid_amount > 0 AND paid_amount < (total_amount + fine_amount) THEN 'Partial'
        ELSE 'Pending'
    END
");

echo "Fees re-synced! Collected is now much higher.";
