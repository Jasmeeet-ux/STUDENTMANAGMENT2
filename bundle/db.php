<?php
// DO NOT ADD ANY WHITESPACE ABOVE THIS LINE!!

// ====================== DATABASE CONNECTION =========================

$pdo = null;
$dbAvailable = false;

try {
    $pdo = new PDO(
        "mysql:host=localhost;port=3307;dbname=course_platform;charset=utf8mb4",
        "root",
        ""
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $dbAvailable = true;
} catch (PDOException $e) {
    $dbAvailable = false;
    error_log("DATABASE CONNECTION ERROR: " . $e->getMessage());
}

// ====================== SESSION FUNCTIONS ==========================

function startSession() {
    if (session_status() === PHP_SESSION_NONE) {
        @session_start();
    }
}

// ====================== AUTH HELPERS ================================

function isLoggedIn() {
    startSession();
    return isset($_SESSION['user_id']);
}

function loginUser($userId, $user = null) {
    startSession();
    $_SESSION['user_id'] = $userId;
    if ($user) {
        $_SESSION['user_name']  = $user['name'] ?? '';
        $_SESSION['user_email'] = $user['email'] ?? '';
        $_SESSION['user_role']  = $user['role'] ?? 'user';
    }
}

function logoutUser() {
    startSession();
    session_unset();
    session_destroy();
}

// ====================== USER HELPERS ================================

function getCurrentUser() {
    global $pdo;
    startSession();
    if (!isset($_SESSION['user_id'])) return null;
    if (!$pdo) return null;
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) logoutUser();
        return $user;
    } catch (Exception $e) {
        error_log("USER FETCH ERROR: " . $e->getMessage());
        return null;
    }
}

function getCurrentUserId() {
    startSession();
    return $_SESSION['user_id'] ?? null;
}

function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: login.php");
        exit();
    }
}

// ====================== DATABASE STATUS CHECK ======================

function isDatabaseAvailable() {
    global $dbAvailable;
    return $dbAvailable;
}

function requireDatabaseOrMaintenance() {
    global $dbAvailable;
    if (!$dbAvailable) {
        header('Location: maintenance.php');
        exit();
    }
}