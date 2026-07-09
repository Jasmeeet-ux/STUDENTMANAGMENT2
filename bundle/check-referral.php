<?php
require 'db.php';

header('Content-Type: application/json');

$referralCode = trim($_GET['referral_code'] ?? '');

if (empty($referralCode)) {
    echo json_encode(['valid' => false, 'message' => 'Referral code is required.']);
    exit;
}

$stmt = $db->prepare("SELECT id, name FROM users WHERE referral_code = ?");
$stmt->execute([$referralCode]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($user) {
    echo json_encode(['valid' => true, 'referrer_name' => $user['name'], 'referrer_id' => $user['id']]);
} else {
    echo json_encode(['valid' => false, 'message' => 'Invalid referral code.']);
}
?>
