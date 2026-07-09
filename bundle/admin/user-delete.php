<?php
// user-delete.php
require_once "db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) { header("Location: admin-login.php"); exit; }

// Only admin allowed
$as = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$as->execute([$admin_id]);
$adm = $as->fetch();
if (!$adm || $adm['role'] !== 'admin') { http_response_code(403); echo "Forbidden"; exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: users.php"); exit; }

// Soft delete: status = deleted
$pdo->prepare("UPDATE users SET status='deleted' WHERE id = ?")->execute([$id]);

// Remove sessions and optionally sensitive tokens to force logout and prevent re-login
$pdo->prepare("DELETE FROM sessions WHERE user_id = ?")->execute([$id]);
$pdo->prepare("UPDATE users SET reset_token = NULL, reset_token_expiry = NULL WHERE id = ?")->execute([$id]);

// log
$l = $pdo->prepare("INSERT INTO user_logs (user_id, activity, ip_address, device_info) VALUES (?,?,?,?)");
$l->execute([$id,'other',$_SERVER['REMOTE_ADDR'] ?? null,'deleted_by_admin:'.$admin_id]);

header("Location: users.php");
exit;
