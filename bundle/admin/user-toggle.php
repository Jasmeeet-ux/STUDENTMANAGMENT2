<?php
// user-toggle.php
require_once "db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) { header("Location: admin-login.php"); exit; }

$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: users.php"); exit; }

// only admin
$as = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$as->execute([$admin_id]);
$adm = $as->fetch();
if (!$adm || $adm['role'] !== 'admin') { http_response_code(403); echo "Forbidden"; exit; }

if ($action === 'toggle_session' || $action === 'force_logout') {
    $pdo->prepare("DELETE FROM sessions WHERE user_id = ?")->execute([$id]);
    $l = $pdo->prepare("INSERT INTO user_logs (user_id, activity, ip_address, device_info) VALUES (?,?,?,?)");
    $l->execute([$id,'other',$_SERVER['REMOTE_ADDR'] ?? null,'force_logout_by_admin:'.$admin_id]);
}

header("Location: users.php");
exit;
