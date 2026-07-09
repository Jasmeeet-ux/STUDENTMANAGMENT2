<?php
session_start();
require_once __DIR__ . '/db.php';

if (isset($_SESSION['admin_token'])) {
    $token = $_SESSION['admin_token'];
    $pdo->prepare("DELETE FROM sessions WHERE session_token = ?")->execute([$token]);
}

session_destroy();
header("Location: login.php");
exit;
