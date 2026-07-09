<?php
session_start();
require_once __DIR__ . '/../db.php';
requireLogin();

header('Content-Type: application/json');

// DEBUG — check raw POST
if (!isset($_POST['lesson_id'])) {
    echo json_encode([
        'success' => false,
        'message' => 'POST lesson_id missing',
        'raw_post' => $_POST
    ]);
    exit;
}

$userId = getCurrentUserId();
$lessonId = (int)$_POST['lesson_id'];
$content = trim($_POST['content'] ?? '');

try {
    $stmt = $pdo->prepare("
        INSERT INTO notes (user_id, lesson_id, content, favorited)
        VALUES (?, ?, ?, 0)
        ON DUPLICATE KEY UPDATE 
            content = VALUES(content),
            updated_at = NOW()
    ");

    $stmt->execute([$userId, $lessonId, $content]);

    echo json_encode([
        'success' => true,
        'saved_lesson' => $lessonId,
        'content_length' => strlen($content)
    ]);

} catch (Exception $e) {

    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'sql_state' => $e->getCode()
    ]);
}
