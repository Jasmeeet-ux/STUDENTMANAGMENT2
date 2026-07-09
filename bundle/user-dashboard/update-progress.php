<?php
session_start();
require_once __DIR__ . '/../db.php';
requireLogin();

header('Content-Type: application/json');

$userId = getCurrentUserId();
$lessonId = isset($_POST['lesson_id']) ? (int)$_POST['lesson_id'] : 0;

if (!$lessonId) {
    echo json_encode(['success' => false, 'message' => 'Invalid lesson.']);
    exit;
}

// Verify lesson belongs to a purchased course
$stmt = $pdo->prepare("
    SELECT l.course_id 
    FROM lessons l
    JOIN courses c ON c.id = l.course_id
    JOIN purchases p ON p.course_id = c.id
    WHERE l.id = ? AND p.user_id = ? AND p.status = 'completed'
");
$stmt->execute([$lessonId, $userId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$course) {
    echo json_encode(['success' => false, 'message' => 'Access denied or course not purchased.']);
    exit;
}

$courseId = $course['course_id'];

// Mark this lesson as completed (or update existing)
$stmt = $pdo->prepare("
    INSERT INTO progress (user_id, lesson_id, completed, updated_at)
    VALUES (?, ?, 1, NOW())
    ON DUPLICATE KEY UPDATE completed=1, updated_at=NOW()
");
$stmt->execute([$userId, $lessonId]);

// Calculate overall progress
$stmt = $pdo->prepare("SELECT COUNT(*) FROM lessons WHERE course_id = ?");
$stmt->execute([$courseId]);
$total = (int)$stmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT COUNT(*) FROM progress p
    JOIN lessons l ON p.lesson_id = l.id
    WHERE p.user_id = ? AND l.course_id = ? AND p.completed = 1
");
$stmt->execute([$userId, $courseId]);
$completed = (int)$stmt->fetchColumn();

$progressPercent = $total ? round(($completed / $total) * 100) : 0;

echo json_encode([
    'success' => true,
    'message' => 'Lesson marked complete.',
    'progress' => $progressPercent,
    'completed_lessons' => $completed,
    'total_lessons' => $total
]);
