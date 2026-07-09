<?php
// upload-avatar.php
// Handles avatar uploads (AJAX). Expects multipart/form-data with file 'avatar'.

session_start();
require_once __DIR__ . '/../db.php';
requireLogin();

header('Content-Type: application/json');

$userId = getCurrentUserId();
if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Not authenticated']);
    exit;
}

if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['success' => false, 'message' => 'No file uploaded or upload error']);
    exit;
}

$file = $_FILES['avatar'];
$maxSize = 2 * 1024 * 1024; // 2MB
$allowedMime = ['image/png', 'image/jpeg', 'image/jpg', 'image/webp'];

if ($file['size'] > $maxSize) {
    echo json_encode(['success' => false, 'message' => 'File too large (max 2MB)']);
    exit;
}

$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);

if (!in_array($mime, $allowedMime)) {
    echo json_encode(['success' => false, 'message' => 'Invalid file type']);
    exit;
}

// Prepare destination
$uploadDir = __DIR__ . '/uploads/avatars';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Determine extension
$ext = 'png';
switch ($mime) {
    case 'image/png': $ext = 'png'; break;
    case 'image/webp': $ext = 'webp'; break;
    default: $ext = 'jpg'; break;
}

$timestamp = time();
$filename = "avatar_{$userId}_{$timestamp}.{$ext}";
$destPath = $uploadDir . '/' . $filename;
$relativePath = 'uploads/avatars/' . $filename;

// Move file
if (!move_uploaded_file($file['tmp_name'], $destPath)) {
    echo json_encode(['success' => false, 'message' => 'Failed to save file']);
    exit;
}

// Optionally: remove older avatar files for this user to save space (safe attempt)
$pattern = $uploadDir . '/avatar_' . $userId . '_*.*';
$existing = glob($pattern);
foreach ($existing as $existingFile) {
    if (basename($existingFile) !== $filename) {
        @unlink($existingFile);
    }
}

// Update DB
try {
    $stmt = $pdo->prepare("UPDATE users SET avatar_path = ? WHERE id = ?");
    $stmt->execute([$relativePath, $userId]);

    // Refresh session user data if present
    if (isset($_SESSION['user_data'])) {
        $_SESSION['user_data']['avatar_path'] = $relativePath;
    }

    echo json_encode(['success' => true, 'message' => 'Uploaded', 'path' => $relativePath]);
} catch (PDOException $e) {
    error_log("Avatar update error: " . $e->getMessage());
    // Remove saved file on DB error
    @unlink($destPath);
    echo json_encode(['success' => false, 'message' => 'Database error']);
}
