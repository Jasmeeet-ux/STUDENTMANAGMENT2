<?php
session_start();
require_once __DIR__ . '/../db.php';
requireLogin();

$userId = getCurrentUserId();
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
$content = isset($_POST['content']) ? trim($_POST['content']) : '';

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'invalid id']);
    exit;
}

// verify ownership
$stmt = $pdo->prepare("SELECT id FROM notes WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $userId]);
if (!$stmt->fetch()) {
    echo json_encode(['success' => false, 'error' => 'not found']);
    exit;
}

$stmt = $pdo->prepare("UPDATE notes SET content = ?, updated_at = NOW() WHERE id = ? AND user_id = ?");
$stmt->execute([$content, $id, $userId]);

echo json_encode(['success' => true]);
