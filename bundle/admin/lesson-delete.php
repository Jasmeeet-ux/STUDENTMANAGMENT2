<?php
require_once "db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: lessons.php");
    exit;
}

$id = intval($_GET['id']);

// Fallback function for PHP 7+
function startsWith($haystack, $needle) {
    return substr($haystack, 0, strlen($needle)) === $needle;
}

// Fetch lesson files
$stmt = $pdo->prepare("SELECT video_url, resource_url FROM lessons WHERE id = ? LIMIT 1");
$stmt->execute([$id]);
$lesson = $stmt->fetch();

if ($lesson) {

    // Delete row from database
    $del = $pdo->prepare("DELETE FROM lessons WHERE id = ?");
    $del->execute([$id]);

    // Delete uploaded lesson files
    foreach (['video_url', 'resource_url'] as $f) {
        if (!empty($lesson[$f]) && startsWith($lesson[$f], 'uploads/lessons/')) {

            $path = __DIR__ . '/' . $lesson[$f];

            // EXTRA SAFETY: prevent deleting parent folders
            if (is_file($path) && strpos(realpath($path), realpath(__DIR__ . '/uploads/lessons/')) === 0) {
                @unlink($path);
            }
        }
    }
}

header("Location: lessons.php");
exit;
