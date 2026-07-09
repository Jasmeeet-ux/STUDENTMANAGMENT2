<?php
session_start();
require_once __DIR__ . '/../db.php';
requireLogin();

$userId = getCurrentUserId();
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'invalid id']);
    exit;
}

$stmt = $pdo->prepare("DELETE FROM notes WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $userId]);

echo json_encode(['success' => true]);
