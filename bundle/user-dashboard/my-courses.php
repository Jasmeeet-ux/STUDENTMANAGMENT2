<?php
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login-Sign-Up1.php');
    exit;
}

require_once __DIR__ . '/../db.php';
requireLogin();

$user = getCurrentUser();
$userId = getCurrentUserId();

// Redirect if user has no purchases
$stmt = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
if ($stmt->fetchColumn() == 0) {
    header('Location: ../pricings.php');
    exit;
}

$coursesData = [
    'courses' => [],
    'enrolled_count' => 0,
    'completed_count' => 0,
    'total_hours' => 0
];

try {
    $stmt = $pdo->prepare("
        SELECT 
            c.id,
            c.title,
            c.description,
            c.image_url AS thumbnail,
            COUNT(l.id) AS total_lessons,
            SUM(CASE WHEN p.completed = 1 THEN 1 ELSE 0 END) AS completed_lessons
        FROM purchases pur
        JOIN courses c ON pur.course_id = c.id
        LEFT JOIN lessons l ON l.course_id = c.id
        LEFT JOIN progress p ON p.lesson_id = l.id AND p.user_id = ?
        WHERE pur.user_id = ? AND pur.status = 'completed'
        GROUP BY c.id
        ORDER BY pur.purchased_at DESC
    ");
    $stmt->execute([$userId, $userId]);
    $courses = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $coursesData['courses'] = $courses;
    $coursesData['enrolled_count'] = count($courses);

    foreach ($courses as $c) {
        $progress = ($c['total_lessons'] > 0) ? ($c['completed_lessons'] / $c['total_lessons']) : 0;
        if ($progress >= 1) $coursesData['completed_count']++;
    }

} catch (PDOException $e) {
    error_log("My Courses Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>My Courses - Culture of Internet</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<script>
tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: '#2563eb',
                secondary: '#3b82f6',
                accent: '#1d4ed8',
                light: '#eff6ff',
                dark: '#1e3a8a'
            }
        }
    }
}
</script>
<style>
.course-card {
        transition: all 0.3s ease;
}
.course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
}
@media (max-width: 1024px) {
    .ml-64 {
        margin-left: 0 !important;
    }
}
@media (max-width: 640px) {
    .p-6 {
        padding: 1rem !important;
    }
    .course-card {
        margin-bottom: 1rem;
    }
    header.bg-white {
        padding: 1rem !important;
    }
}
</style>
</head>
<body class="bg-gray-50 text-gray-800">

<!-- Sidebar -->
<div class="md:hidden flex items-center justify-between bg-white px-4 py-3 border-b border-gray-200 sticky top-0 z-40">
    
    <span class="font-bold text-primary text-lg">Culture of Internet</span>
    <button id="openSidebar" class="text-gray-700 focus:outline-none">
        <i class="fas fa-bars fa-lg"></i>
    </button>
</div>
<?php include "includes/sidebar.php"; ?>

<!-- Main Content -->
<div class="md:ml-64 min-h-screen">
    <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4">
        <h2 class="text-2xl font-bold text-gray-800">My Courses</h2>
        <p class="text-gray-600 text-sm">Continue your learning journey</p>
    </header>

    <main class="p-6">
        <?php if (!empty($coursesData['courses'])): ?>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php foreach ($coursesData['courses'] as $course): 
                $progress = ($course['total_lessons'] > 0) ? round(($course['completed_lessons'] / $course['total_lessons']) * 100) : 0;
                $statusText = $progress == 100 ? 'Completed' : ($progress > 0 ? $progress.'% Complete' : 'Not Started');
                $statusColor = $progress == 100 ? 'text-green-600' : ($progress > 0 ? 'text-blue-600' : 'text-gray-600');
            ?>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden course-card border border-gray-100">
                <div class="h-44 bg-gray-100 relative">
                    <?php if (!empty($course['thumbnail'])): ?>
                        <img src="<?= htmlspecialchars($course['thumbnail']) ?>" alt="Course Thumbnail" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="h-full flex items-center justify-center bg-gradient-to-br from-primary to-secondary">
                            <i class="fas fa-book text-5xl text-white opacity-80"></i>
                        </div>
                    <?php endif; ?>
                    <div class="absolute bottom-2 left-2 bg-primary text-white text-xs px-3 py-1 rounded-full"><?= $statusText ?></div>
                </div>

                <div class="p-5">
                    <h3 class="text-lg font-semibold text-gray-800 truncate"><?= htmlspecialchars($course['title']) ?></h3>
                    <p class="text-gray-600 text-sm mt-1 line-clamp-2"><?= htmlspecialchars($course['description']) ?></p>

                    <div class="mt-4">
                        <div class="flex justify-between text-xs text-gray-500 mb-1">
                            <span>Progress</span>
                            <span><?= $course['completed_lessons'] ?>/<?= $course['total_lessons'] ?> lessons</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-primary h-2 rounded-full transition-all duration-500" style="width: <?= $progress ?>%"></div>
                        </div>
                    </div>

                    <a href="course-player.php?id=<?= $course['id'] ?>" 
                       class="mt-4 block text-center bg-primary hover:bg-accent text-white py-2 rounded-lg transition">
                       <i class="fas fa-play-circle mr-1"></i> 
                       <?= $progress == 100 ? 'Review Course' : ($progress > 0 ? 'Continue Learning' : 'Start Learning') ?>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
            <div class="bg-white rounded-xl shadow-sm p-8 sm:p-12 text-center">
                <p class="text-gray-500 mb-4">You haven’t enrolled in any courses yet.</p>
                <a href="../bundle/courses.php" class="bg-primary text-white px-5 py-2 rounded-lg hover:bg-accent transition">Explore Courses</a>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
<script>
// Sidebar toggle for mobile
const sidebar = document.getElementById('sidebar');
const openSidebar = document.getElementById('openSidebar');
const closeSidebar = document.getElementById('closeSidebar');
if (openSidebar && sidebar) {
    openSidebar.addEventListener('click', () => {
        sidebar.classList.remove('-translate-x-full');
        sidebar.classList.add('translate-x-0');
        document.body.style.overflow = 'hidden';
    });
}
if (closeSidebar && sidebar) {
    closeSidebar.addEventListener('click', () => {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        document.body.style.overflow = '';
    });
}
// Hide sidebar on click outside (mobile)
document.addEventListener('click', function(e) {
    if (window.innerWidth < 768 && sidebar && !sidebar.contains(e.target) && !openSidebar.contains(e.target)) {
        sidebar.classList.add('-translate-x-full');
        sidebar.classList.remove('translate-x-0');
        document.body.style.overflow = '';
    }
});
</script>
</html>
