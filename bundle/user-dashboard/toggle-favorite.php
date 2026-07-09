<?php
session_start();
require_once __DIR__ . '/../db.php';
requireLogin();

$userId = getCurrentUserId();
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if (!$id) {
    echo json_encode(['success' => false, 'error' => 'Invalid id']);
    exit;
}

// verify ownership
$stmt = $pdo->prepare("SELECT favorited FROM notes WHERE id = ? AND user_id = ?");
$stmt->execute([$id, $userId]);
$note = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$note) {
    echo json_encode(['success' => false, 'error' => 'Not found']);
    exit;
}

$new = $note['favorited'] ? 0 : 1;
$stmt = $pdo->prepare("UPDATE notes SET favorited = ?, " . ($new ? "favorited_at = NOW()" : "favorited_at = NULL") . " WHERE id = ? AND user_id = ?");
$stmt->execute([$new, $id, $userId]);

echo json_encode(['success' => true, 'favorited' => (bool)$new]);
