<?php
session_start();
require_once 'db.php';

// Clean up any database sessions for this user (if they exist)
if (isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    try {
        // Clean up any sessions in the database (for consistency)
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE user_id = ?");
        $stmt->execute([$userId]);
    } catch (PDOException $e) {
        // Ignore errors if sessions table operations fail
        error_log("Session cleanup error: " . $e->getMessage());
    }
}

// Destroy all session data
$_SESSION = [];
session_unset();
session_destroy();

// Clear any session cookies
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Prevent caching (so back button won't show dashboard)
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");

// Redirect to login page
header("Location: index");
exit;