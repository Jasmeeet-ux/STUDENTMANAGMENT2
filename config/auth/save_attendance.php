<?php
session_start();
require_once '../db.php';

if (!isset($_SESSION['admin_username'])) {
    http_response_code(403);
    echo "unauthorized";
    exit;
}

$reg_no   = $_POST['reg_no']   ?? '';
$batch_no = $_POST['batch_no'] ?? '';
$date     = $_POST['date']     ?? '';
$status   = $_POST['status']   ?? '';

if ($reg_no === '' || $date === '') {
    echo "error";
    exit;
}

// DELETE
if ($status === 'DEL') {
    $pdo->prepare("DELETE FROM attendance WHERE reg_no=? AND date=?")->execute([$reg_no, $date]);
    echo "deleted";
    exit;
}

// SAVE / UPDATE
if (!in_array($status, ['P','A','L'])) {
    echo "error";
    exit;
}

$check = $pdo->prepare("SELECT id FROM attendance WHERE reg_no=? AND date=?");
$check->execute([$reg_no, $date]);

if ($check->rowCount() > 0) {
    $pdo->prepare("UPDATE attendance SET status=? WHERE reg_no=? AND date=?")->execute([$status, $reg_no, $date]);
} else {
    $pdo->prepare("INSERT INTO attendance (reg_no, batch_no, date, status) VALUES (?,?,?,?)")->execute([$reg_no, $batch_no, $date, $status]);
}

echo "saved";