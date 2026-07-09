<?php
session_start();

// Load DB + session functions
require_once __DIR__ . '/sessions.php'; 

// Check missing fields
if (empty($_POST['email']) || empty($_POST['password'])) {
    $_SESSION['login_error'] = "Please enter email and password.";
    header("Location: login.php");
    exit;
}

$email = trim($_POST['email']);
$password = $_POST['password'];

$stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? LIMIT 1");
$stmt->execute([$email]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['login_error'] = "User not found.";
    header("Location: login.php");
    exit;
}

// Only admin or instructor
if ($user['role'] !== 'admin' && $user['role'] !== 'instructor') {
    $_SESSION['login_error'] = "Access denied.";
    header("Location: login.php");
    exit;
}

// Password verify
if (!password_verify($password, $user['password'])) {
    $_SESSION['login_error'] = "Incorrect password.";
    header("Location: login.php");
    exit;
}

// Create session token
createAdminSession($pdo, $user['id']);

header("Location: index.php");
exit;
