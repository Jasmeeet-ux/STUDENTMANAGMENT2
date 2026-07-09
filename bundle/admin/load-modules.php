<?php
require "db.php";

header('Content-Type: application/json');

$course_id = intval($_GET['course_id'] ?? 0);

if ($course_id <= 0) {
    echo json_encode([]);
    exit;
}

// Fetch modules ordered by module_number (correct column)
$stmt = $pdo->prepare("
    SELECT id, title, module_number 
    FROM modules 
    WHERE course_id = ? 
    ORDER BY module_number ASC
");
$stmt->execute([$course_id]);

$modules = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($modules);
