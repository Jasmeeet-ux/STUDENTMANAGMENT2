<?php
// Ensure session exists before anything
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "db.php"; // includes PDO + session functions

// Validate admin session using DB session token
$userId = validateAdminSession($pdo);

if (!$userId) {
    header("Location: admin-login.php");
    exit;
}

// Fetch logged-in admin user
$stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$userId]);
$admin = $stmt->fetch();

// If user not found OR not an admin → force logout
if (!$admin || strtolower($admin['role']) !== "admin") {

    // Remove any session that exists
    if (isset($_SESSION['admin_token'])) {
        $del = $pdo->prepare("DELETE FROM sessions WHERE session_token = ?");
        $del->execute([$_SESSION['admin_token']]);
    }

    session_unset();
    session_destroy();

    header("Location: admin-login.php");
    exit;
}
?>
