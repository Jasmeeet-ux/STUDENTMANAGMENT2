<?php
// user-suspend.php
require_once "db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) { header("Location: admin-login.php"); exit; }

// only admin can suspend
$as = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$as->execute([$admin_id]);
$adm = $as->fetch();
if (!$adm || $adm['role'] !== 'admin') { http_response_code(403); echo "Forbidden"; exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$action = $_GET['action'] ?? '';

if ($id <= 0 || !in_array($action, ['suspend','activate'])) {
    header("Location: users.php"); exit;
}

if ($action === 'suspend') {
    $pdo->prepare("UPDATE users SET status='suspended' WHERE id = ?")->execute([$id]);
    // remove sessions to force logout
    $pdo->prepare("DELETE FROM sessions WHERE user_id = ?")->execute([$id]);
    // log
    $l = $pdo->prepare("INSERT INTO user_logs (user_id, activity, ip_address, device_info) VALUES (?,?,?,?)");
    $l->execute([$id,'other',$_SERVER['REMOTE_ADDR'] ?? null,'suspended_by_admin:'.$admin_id]);
} else {
    $pdo->prepare("UPDATE users SET status='active' WHERE id = ?")->execute([$id]);
    $l = $pdo->prepare("INSERT INTO user_logs (user_id, activity, ip_address, device_info) VALUES (?,?,?,?)");
    $l->execute([$id,'other',$_SERVER['REMOTE_ADDR'] ?? null,'activated_by_admin:'.$admin_id]);
}

header("Location: users.php");
exit;
