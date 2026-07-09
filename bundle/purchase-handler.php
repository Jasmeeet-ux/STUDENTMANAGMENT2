<?php
// purchase-handler.php
// Responsible for accepting purchase requests from pricings.php and redirecting to payment.php
session_start();
require 'db.php';

// If you have helper wrappers like isDatabaseAvailable() or isLoggedIn(), they are used below.
// Otherwise this file uses direct session checks.

function sendPurchaseError($msg) {
    $_SESSION['purchase_error'] = $msg;
    header('Location: pricings.php?error=' . urlencode($msg));
    exit;
}

// Strictly expect POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    error_log('purchase-handler: invalid request method: ' . $_SERVER['REQUEST_METHOD']);
    sendPurchaseError('Invalid request method.');
}

// Get POSTed fields
$courseId = isset($_POST['course_id']) ? trim($_POST['course_id']) : null;
$priceRaw = isset($_POST['price']) ? trim($_POST['price']) : null; // kept for logging but NOT trusted

if (empty($courseId)) {
    error_log('purchase-handler: missing course_id');
    sendPurchaseError('Missing course information.');
}

// Basic numeric validation for course id
if (!ctype_digit((string)$courseId)) {
    error_log('purchase-handler: invalid course_id: ' . var_export($courseId, true));
    sendPurchaseError('Invalid course selection.');
}

// Save intended purchase for after auth (if needed)
$_SESSION['intended_purchase'] = [
    'course_id' => (int)$courseId,
    // Keep price for debugging only (final price is read from DB)
    'price_submitted' => $priceRaw
];

// If not logged in, redirect to unified login/signup page
$loggedIn = isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
if (!$loggedIn) {
    header('Location: login-Sign-Up1.php');
    exit;
}

// If logged in, redirect to payment page
header('Location: payment.php?course_id=' . urlencode($courseId));
exit;
