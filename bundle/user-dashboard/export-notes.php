<?php
session_start();
require_once __DIR__ . '/../db.php';
requireLogin();

$userId = getCurrentUserId();

$stmt = $pdo->prepare("
    SELECT n.id, n.lesson_id, l.title AS lesson_title, c.title AS course_title,
           n.content, n.updated_at, n.favorited
    FROM notes n
    JOIN lessons l ON n.lesson_id = l.id
    JOIN courses c ON l.course_id = c.id
    WHERE n.user_id = ?
    ORDER BY n.updated_at DESC
");
$stmt->execute([$userId]);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Output JSON for sync scripts / external APIs
header('Content-Type: application/json');
echo json_encode(['user_id' => $userId, 'exported_at' => date('c'), 'notes' => $notes], JSON_PRETTY_PRINT);
