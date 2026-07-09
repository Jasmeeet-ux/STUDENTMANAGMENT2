<?php
require "db.php";
require "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid module ID.");
}

$module_id = intval($_GET['id']);

// Check if lessons exist inside the module
$lessonCount = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE module_id = ?");
$lessonCount->execute([$module_id]);
$count = $lessonCount->fetchColumn();

if ($count > 0) {
    die("Error: Cannot delete module because it contains lessons.");
}

// Delete module
$stmt = $pdo->prepare("DELETE FROM modules WHERE id = ?");
$stmt->execute([$module_id]);

header("Location: modules.php");
exit;
