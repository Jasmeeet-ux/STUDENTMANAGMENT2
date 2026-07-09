<?php

use Dompdf\Dompdf;
session_start();
require_once __DIR__ . '/../db.php';
requireLogin();

// Dompdf import (for PDF export)
if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
    require_once __DIR__ . '/../../vendor/autoload.php';
    // Only import if available
    if (!class_exists('Dompdf\\Dompdf')) {
        // fallback: do nothing
    }
}

$userId = getCurrentUserId();
$format = isset($_GET['format']) ? strtolower($_GET['format']) : 'pdf';
$single = isset($_GET['single']) ? (int)$_GET['single'] : 0;

$params = [$userId];
$sql = "
    SELECT n.id, n.lesson_id, l.title AS lesson_title, c.title AS course_title,
           n.content, n.updated_at, n.favorited
    FROM notes n
    JOIN lessons l ON n.lesson_id = l.id
    JOIN courses c ON l.course_id = c.id
    WHERE n.user_id = ?
";
if ($single) {
    $sql .= " AND n.id = ?";
    $params[] = $single;
}
$sql .= " ORDER BY n.updated_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$notes = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($format === 'txt') {
    $txt = "Notes export - " . date('Y-m-d H:i') . "\n\n";
    foreach ($notes as $n) {
        $txt .= "Course: {$n['course_title']}\n";
        $txt .= "Lesson: {$n['lesson_title']}\n";
        $txt .= "Updated: {$n['updated_at']}\n";
        $txt .= "Favorited: " . ($n['favorited'] ? 'Yes' : 'No') . "\n";
        $txt .= "-----\n";
        $txt .= strip_tags($n['content']) . "\n\n\n";
    }
    header('Content-Type: text/plain; charset=utf-8');
    header('Content-Disposition: attachment; filename="notes-' . date('Ymd-His') . '.txt"');
    echo $txt;
    exit;
}

// PDF path: use dompdf if installed
if ($format === 'pdf') {
    if (file_exists(__DIR__ . '/../../vendor/autoload.php')) {
        // Dompdf already imported at top
        $html = "<h1>Notes export</h1><p>Exported: " . date('Y-m-d H:i') . "</p>";
        foreach ($notes as $n) {
            $html .= "<hr>";
            $html .= "<h2>" . htmlspecialchars($n['course_title']) . " — " . htmlspecialchars($n['lesson_title']) . "</h2>";
            $html .= "<p><em>Updated: {$n['updated_at']} — Favorited: " . ($n['favorited'] ? 'Yes' : 'No') . "</em></p>";
            $html .= "<div style='white-space:pre-wrap;'>" . nl2br(htmlspecialchars($n['content'])) . "</div>";
        }

        $dompdf = new Dompdf();
        $dompdf->loadHtml($html);
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $filename = 'notes-' . date('Ymd-His') . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $dompdf->output();
        exit;
    } else {
        // fallback to txt if dompdf isn't available
        header('Location: download-notes.php?format=txt' . ($single ? '&single=' . $single : ''));
        exit;
    }
}

// default fallback
header('Location: my-notes.php');
exit;
