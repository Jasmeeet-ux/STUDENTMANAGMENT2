<?php
// Start session safely
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "db.php";

// Clean expired sessions in DB
cleanupExpiredSessions($pdo);

// Validate admin session token
$admin_id = validateAdminSession($pdo);

// If session invalid → destroy & redirect
if (!$admin_id) {

    // Clear PHP session
    session_unset();
    session_destroy();

    // Redirect to correct login file
    header("Location: admin-login.php");
    exit;
}

// (Optional) fetch admin info for use in dashboard or topbar
$stmt = $pdo->prepare("SELECT id, name, email, role FROM users WHERE id = ? LIMIT 1");
$stmt->execute([$admin_id]);
$admin = $stmt->fetch();

// Safety: Ensure user is an admin
if (!$admin || strtolower($admin['role']) !== 'admin') {

    // Delete any existing DB session
    if (isset($_SESSION['admin_token'])) {
        $del = $pdo->prepare("DELETE FROM sessions WHERE session_token = ?");
        $del->execute([$_SESSION['admin_token']]);
    }

    session_unset();
    session_destroy();

    header("Location: admin-login.php");
    exit;
}
