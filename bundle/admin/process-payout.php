<?php
require_once "../db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: ../admin-login.php");
    exit;
}

// Validate affiliate ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: affiliate-payout.php?error=invalid_id");
    exit;
}

$affiliate_id = (int)$_GET['id'];

// Fetch affiliate data
$stmt = $pdo->prepare("
    SELECT a.*, u.name, u.email 
    FROM affiliates a
    JOIN users u ON u.id = a.user_id
    WHERE a.id = ?
");
$stmt->execute([$affiliate_id]);
$affiliate = $stmt->fetch(PDO::FETCH_ASSOC);

// No affiliate found
if (!$affiliate) {
    header("Location: affiliate-payout.php?error=not_found");
    exit;
}

$pending = floatval($affiliate["pending_payout"]);

// No payout available
if ($pending <= 0) {
    header("Location: affiliate-payout.php?error=nothing_to_payout");
    exit;
}

try {
    $pdo->beginTransaction();

    // 1️⃣ Add payout transaction
    $t = $pdo->prepare("
        INSERT INTO transactions (user_id, amount, currency, gateway, status)
        VALUES (?, ?, 'INR', 'Affiliate Payout', 'completed')
    ");
    $t->execute([
        $affiliate["user_id"],
        -1 * $pending, // negative indicates payout
    ]);

    // 2️⃣ Reset pending payout
    $upd = $pdo->prepare("
        UPDATE affiliates SET pending_payout = 0
        WHERE id = ?
    ");
    $upd->execute([$affiliate_id]);

    // 3️⃣ Log admin action (optional but recommended)
    $log = $pdo->prepare("
        INSERT INTO user_logs (user_id, activity, ip_address, device_info, meta)
        VALUES (?, 'other', ?, ?, ?)
    ");
    $meta = json_encode([
        "admin" => $admin_id,
        "action" => "affiliate_payout",
        "amount" => $pending
    ]);
    $log->execute([
        $affiliate["user_id"],
        $_SERVER['REMOTE_ADDR'] ?? null,
        $_SERVER['HTTP_USER_AGENT'] ?? null,
        $meta
    ]);

    $pdo->commit();

    header("Location: affiliate-payout.php?success=payout_done");

} catch (Exception $e) {
    $pdo->rollBack();
    header("Location: affiliate-payout.php?error=server_error");
}
exit;
?>
