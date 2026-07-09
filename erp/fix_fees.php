<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$db = 'new_project';

$conn = new mysqli($host, $user, $pass, $db);

// Fix total_amount from fee structures
$conn->query("
    UPDATE erp_fee_invoices i
    JOIN erp_fee_structures s ON i.fee_structure_id = s.id
    SET i.total_amount = s.amount
");

// Fix payments that exceed total_amount
$conn->query("
    UPDATE erp_fee_payments p
    JOIN erp_fee_invoices i ON p.invoice_id = i.id
    SET p.amount = i.total_amount
    WHERE p.amount > i.total_amount
");

// Also add a little randomization so some are Partial
// Get some invoices to make partial
$conn->query("
    UPDATE erp_fee_payments p
    JOIN erp_fee_invoices i ON p.invoice_id = i.id
    SET p.amount = i.total_amount / 2
    WHERE p.id % 5 = 0
");

// Update paid_amount in invoices based on payments
$conn->query("
    UPDATE erp_fee_invoices i
    SET i.paid_amount = (SELECT COALESCE(SUM(amount), 0) FROM erp_fee_payments p WHERE p.invoice_id = i.id)
");

// Update invoice status based on paid_amount vs total_amount
$conn->query("
    UPDATE erp_fee_invoices
    SET status = CASE 
        WHEN paid_amount >= total_amount AND total_amount > 0 THEN 'Paid'
        WHEN paid_amount > 0 AND paid_amount < total_amount THEN 'Partial'
        ELSE 'Pending'
    END
");

echo "Fees fixed!\n";
