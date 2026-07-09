<?php
session_start();
require_once __DIR__ . '/../db.php';
requireLogin();

$userId = getCurrentUserId();
$courseId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$currentLessonId = isset($_GET['lesson']) ? (int)$_GET['lesson'] : 0;

// Validate course access
$stmt = $pdo->prepare("SELECT c.* 
                      FROM courses c
                      JOIN purchases p ON c.id = p.course_id
                      WHERE c.id = ? AND p.user_id = ? AND p.status = 'completed'");
$stmt->execute([$courseId, $userId]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) die("Access denied or course not found.");

// Lessons + progress
$stmt = $pdo->prepare("
    SELECT l.*, COALESCE(p.completed, 0) AS completed
    FROM lessons l
    LEFT JOIN progress p ON l.id = p.lesson_id AND p.user_id = ?
    WHERE l.course_id = ?
    ORDER BY l.id ASC
");
$stmt->execute([$userId, $courseId]);
$lessons = $stmt->fetchAll(PDO::FETCH_ASSOC);
if (empty($lessons)) die("No lessons found for this course.");

$currentLesson = null;
if ($currentLessonId) {
    foreach ($lessons as $l) if ($l['id'] == $currentLessonId) $currentLesson = $l;
}
if (!$currentLesson) $currentLesson = $lessons[0];

// Stats
$totalLessons = count($lessons);
$completedLessons = count(array_filter($lessons, fn($l) => $l['completed']));
$progressPercent = round(($completedLessons / $totalLessons) * 100);

// Notes
$stmt = $pdo->prepare("SELECT content FROM notes WHERE user_id = ? AND lesson_id = ?");
$stmt->execute([$userId, $currentLesson['id']]);
$note = $stmt->fetchColumn() ?: '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($course['title']) ?> - Course Player</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<script>
tailwind.config = {
  theme: { extend: { colors: { primary:'#2563eb', accent:'#1d4ed8', dark:'#1e3a8a' } } }
};
</script>
</head>
<style>
@media (max-width: 1024px) {
  .max-w-7xl {
    max-width: 100vw !important;
  }
}
@media (max-width: 768px) {
  .aspect-video {
    aspect-ratio: 16/9;
    min-width: 100vw;
    margin-left: -1.5rem;
    margin-right: -1.5rem;
    border-radius: 0 !important;
  }
  .px-6 {
    padding-left: 0.5rem !important;
    padding-right: 0.5rem !important;
  }
  .py-8 {
    padding-top: 1rem !important;
    padding-bottom: 1rem !important;
  }
}
@media (max-width: 640px) {
  .aspect-video {
    min-width: 100vw;
    margin-left: -1rem;
    margin-right: -1rem;
    border-radius: 0 !important;
  }
}
</style>
</head>
<body class="bg-gradient-to-br from-blue-50 via-white to-blue-50 min-h-screen">

<header class="bg-white/80 backdrop-blur-md border-b border-blue-100 sticky top-0 z-50 shadow-sm">
  <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
    <div class="flex items-center space-x-4">
      <a href="my-courses.php" class="text-gray-700 hover:text-blue-600 flex items-center">
        <i class="fa fa-arrow-left mr-2"></i> Back to Courses
      </a>
      <h1 class="text-lg font-semibold text-gray-900"><?= htmlspecialchars($course['title']) ?></h1>
    </div>
    <div class="text-sm text-gray-600"><?= $progressPercent ?>% Complete</div>
  </div>
</header>

<div class="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-4 gap-8 px-6 py-8">
  <!-- Video Section -->
  <div class="lg:col-span-3">
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden mb-6">
      <div class="aspect-video bg-black">
        <?php if (!empty($currentLesson['video_url'])): ?>
          <iframe src="<?= htmlspecialchars($currentLesson['video_url']) ?>" class="w-full h-full" frameborder="0" allowfullscreen></iframe>
        <?php else: ?>
          <div class="flex items-center justify-center text-white h-full">No video available</div>
        <?php endif; ?>
      </div>
    </div>

    <!-- Lesson Info -->
    <div class="bg-white rounded-2xl p-6 shadow-sm mb-8">
      <h2 class="text-2xl font-bold mb-3"><?= htmlspecialchars($currentLesson['title']) ?></h2>
      <p class="text-gray-600 mb-6"><?= htmlspecialchars($currentLesson['description'] ?? 'No description provided.') ?></p>

      <div class="flex justify-between items-center">
        <?php
        $index = array_search($currentLesson['id'], array_column($lessons, 'id'));
        $prevLesson = $lessons[$index - 1] ?? null;
        $nextLesson = $lessons[$index + 1] ?? null;
        ?>
        <a href="?id=<?= $courseId ?>&lesson=<?= $prevLesson['id'] ?? $currentLesson['id'] ?>"
           class="px-5 py-2 bg-gray-100 rounded-lg text-gray-700 hover:bg-gray-200 <?= !$prevLesson ? 'opacity-50 pointer-events-none' : '' ?>">← Previous</a>

        <div class="flex items-center gap-3">
          <button onclick="markLessonComplete(<?= $currentLesson['id'] ?>)" 
            class="px-5 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">Mark Complete</button>
          <a href="?id=<?= $courseId ?>&lesson=<?= $nextLesson['id'] ?? $currentLesson['id'] ?>"
             class="px-5 py-2 bg-primary text-white rounded-lg hover:bg-accent <?= !$nextLesson ? 'opacity-50 pointer-events-none' : '' ?>">Next →</a>
        </div>
      </div>
    </div>

    <!-- Notes -->
    <div class="bg-white rounded-2xl p-6 shadow-sm">
      <h3 class="text-lg font-bold mb-4">My Notes for this Lesson</h3>
      <textarea id="userNote" class="w-full h-40 border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-primary focus:outline-none"
        placeholder="Type your notes here..."><?= htmlspecialchars($note) ?></textarea>
      <div class="mt-3 flex justify-between items-center text-sm text-gray-500">
        <span id="saveStatus">Auto-saved</span>
        <div class="flex items-center gap-2">
          <button onclick="saveNote()" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-accent">Save Now</button>
          <a href="download-notes.php?format=txt&single=<?= $currentLesson['id'] ?>" class="text-primary hover:underline">Download</a>
        </div>
      </div>
    </div>
  </div>

  <!-- Sidebar -->
  <div class="lg:col-span-1">
    <div class="bg-white rounded-2xl shadow-sm p-6">
      <h3 class="text-lg font-bold mb-4">Course Content</h3>
      <div class="space-y-3 max-h-[70vh] overflow-y-auto">
        <?php foreach ($lessons as $i => $lesson): 
          $isCurrent = $lesson['id'] == $currentLesson['id'];
          $isCompleted = $lesson['completed'];
        ?>
          <a id="lesson-<?= $lesson['id'] ?>" href="?id=<?= $courseId ?>&lesson=<?= $lesson['id'] ?>"
             class="block p-3 rounded-xl border transition-all 
             <?= $isCurrent ? 'bg-blue-600 text-white' : 
                ($isCompleted ? 'bg-blue-50 border-blue-200 text-blue-800' : 
                'bg-white border-gray-200 hover:bg-gray-50') ?>">
            <div class="flex items-center justify-between">
              <span><?= $i + 1 ?>. <?= htmlspecialchars($lesson['title']) ?></span>
              <?php if ($isCompleted): ?>
                <i class="fa fa-check-circle"></i>
              <?php elseif ($isCurrent): ?>
                <i class="fa fa-play-circle text-white"></i>
              <?php endif; ?>
            </div>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="mt-6 text-center">
        <p class="text-sm text-gray-600"><?= $completedLessons ?> of <?= $totalLessons ?> lessons completed</p>
        <div class="w-full bg-gray-200 h-2 rounded-full mt-2">
          <div id="progressBar" class="bg-primary h-2 rounded-full" style="width: <?= $progressPercent ?>%"></div>
        </div>
      </div>
    </div>
  </div>
</div>

<script>
function markLessonComplete(lessonId) {
  fetch('update-progress.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'lesson_id=' + lessonId
  }).then(r => r.json()).then(data => {
    if (data.success) {
      document.getElementById('progressBar').style.width = data.progress + '%';
      setTimeout(() => location.reload(), 600);
    } else {
      alert(data.message);
    }
  });
}

// Auto-save notes every 5 seconds
let noteTimer;
document.getElementById('userNote').addEventListener('input', () => {
  clearTimeout(noteTimer);
  noteTimer = setTimeout(saveNote, 2000);
});

function saveNote() {
  const content = document.getElementById('userNote').value;
  const status = document.getElementById('saveStatus');
  status.textContent = 'Saving...';
  fetch('save-note', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'lesson_id=<?= $currentLesson['id'] ?>&content=' + encodeURIComponent(content)
  }).then(r => r.json()).then(data => {
    status.textContent = data.success ? 'Saved ✓' : 'Save failed';
  });
}
</script>
</body>
</html>
