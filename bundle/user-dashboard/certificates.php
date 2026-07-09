<?php
// Prevent caching to ensure logout works properly
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start();
require_once __DIR__ . '/../db.php';
requireLogin();

$user = getCurrentUser();
$userId = getCurrentUserId();

// Check if user has completed any purchases
$stmt = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$hasPurchases = $stmt->fetchColumn() > 0;

if (!$hasPurchases) {
    header('Location: ../../pricing.php');
    exit;
}

// Initialize all expected array keys safely
$certificateData = [
    'certificates'    => [],
    'total_count'     => 0,
    'monthly_count'   => 0,
    'enrolled_count'  => 0
];

try {
    // Fetch fully completed courses
    $stmt = $pdo->prepare("
        SELECT
            c.id,
            c.title,
            c.created_by,
            c.category_id,
            pur.purchased_at AS completion_date,
            COUNT(l.id) AS total_lessons,
            COUNT(CASE WHEN p.completed = 1 THEN 1 END) AS completed_lessons
        FROM courses c
        JOIN purchases pur ON c.id = pur.course_id
        LEFT JOIN lessons l ON c.id = l.course_id
        LEFT JOIN progress p ON l.id = p.lesson_id AND p.user_id = ?
        WHERE pur.user_id = ? AND pur.status = 'completed'
        GROUP BY c.id, c.title, c.created_by, c.category_id, pur.purchased_at
        HAVING COUNT(CASE WHEN p.completed = 1 THEN 1 END) = COUNT(l.id)
        ORDER BY pur.purchased_at DESC
    ");
    $stmt->execute([$userId, $userId]);
    $certificates = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch instructor names
    if (!empty($certificates)) {
        $instructorIds = array_column($certificates, 'created_by');
        if (!empty($instructorIds)) {
            $placeholders = implode(',', array_fill(0, count($instructorIds), '?'));
            $stmt = $pdo->prepare("SELECT id, name FROM users WHERE id IN ($placeholders)");
            $stmt->execute($instructorIds);
            $instructors = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);

            foreach ($certificates as &$cert) {
                $cert['instructor_name'] = $instructors[$cert['created_by']] ?? 'N/A';
            }
        }
    }

    $certificateData['certificates'] = $certificates;
    $certificateData['total_count']  = count($certificates);

    // Monthly completions
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM purchases
        WHERE user_id = ? AND status = 'completed'
        AND purchased_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$userId]);
    $certificateData['monthly_count'] = (int) $stmt->fetchColumn();

    // Enrolled count
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = ? AND status = 'completed'");
    $stmt->execute([$userId]);
    $certificateData['enrolled_count'] = (int) $stmt->fetchColumn();
} catch (PDOException $e) {
    error_log("Certificates Page Error: " . $e->getMessage());
}

// Prevent undefined key warnings even if queries fail
foreach (['certificates','total_count','monthly_count','enrolled_count'] as $key) {
    if (!isset($certificateData[$key])) $certificateData[$key] = 0;
}

$featuredCert = $certificateData['certificates'][0] ?? null;
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Certificates - Culture of Internet</title>
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
                light: '#dbeafe',
                dark: '#1e3a8a'
            }
        }
    }
};
</script>
<style>
.glass-effect {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(12px);
        border: 1px solid rgba(255, 255, 255, 0.3);
}
.certificate-glow {
        box-shadow: 0 0 30px rgba(37, 99, 235, 0.15);
}
.float-animation {
        animation: float 3s ease-in-out infinite;
}
@keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-5px); }
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
    .glass-effect {
        padding: 1.5rem !important;
    }
    header.bg-white {
        padding: 1rem !important;
    }
}
</style>
</head>
<body class="bg-gray-50">
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
    <header class="bg-white shadow-sm border-b border-gray-200">
        <div class="px-6 py-4">
            <h2 class="text-2xl font-bold text-gray-800">My Certificates</h2>
            <p class="text-gray-600 mt-1">Your achievements and completed course certificates</p>
        </div>
    </header>

    <div class="p-6">
        <!-- Stats -->
        <div class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
            <?php
            $stats = [
                ['label' => 'Total Certificates', 'color' => 'emerald', 'icon' => 'fa-certificate', 'value' => $certificateData['total_count']],
                ['label' => 'This Month', 'color' => 'blue', 'icon' => 'fa-trophy', 'value' => $certificateData['monthly_count']],
                ['label' => 'Skill Categories', 'color' => 'purple', 'icon' => 'fa-layer-group', 'value' => count(array_unique(array_column($certificateData['certificates'], 'category_id')))],
                ['label' => 'Completion Rate', 'color' => 'amber', 'icon' => 'fa-chart-line', 'value' => ($certificateData['enrolled_count'] > 0) ? round(($certificateData['total_count'] / $certificateData['enrolled_count']) * 100) . '%' : '0%']
            ];
            foreach ($stats as $stat): ?>
            <div class="bg-gradient-to-br from-<?= $stat['color'] ?>-500 to-<?= $stat['color'] ?>-600 p-6 rounded-xl text-white">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-<?= $stat['color'] ?>-100 text-sm"><?= $stat['label'] ?></p>
                        <p class="text-3xl font-bold"><?= $stat['value'] ?></p>
                    </div>
                    <div class="w-12 h-12 bg-<?= $stat['color'] ?>-400 rounded-full flex items-center justify-center">
                        <i class="fas <?= $stat['icon'] ?> text-xl"></i>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Featured Certificate -->
        <?php if ($featuredCert): ?>
        <div class="mb-12">
            <h2 class="text-2xl font-bold text-blue-800 mb-6 flex items-center">
                <div class="w-2 h-8 bg-gradient-to-b from-blue-400 to-blue-600 rounded-full mr-4"></div>
                Latest Achievement
            </h2>
            <div class="glass-effect rounded-3xl p-8 certificate-glow float-animation">
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 items-center">
                    <!-- Preview -->
                    <div class="relative">
                        <div class="bg-gradient-to-br from-white to-blue-50 rounded-2xl p-8 text-gray-900 shadow-2xl transform rotate-1">
                            <div class="text-center">
                                <div class="w-20 h-20 bg-gradient-to-br from-blue-500 to-blue-700 rounded-full flex items-center justify-center mx-auto mb-6">
                                    <i class="fa fa-award text-white text-3xl"></i>
                                </div>
                                <h3 class="text-2xl font-bold mb-2">Certificate of Completion</h3>
                                <p class="text-lg mb-6"><?= htmlspecialchars($featuredCert['title']) ?></p>
                                <p class="text-gray-600 mb-4">Awarded to <span class="font-bold"><?= htmlspecialchars($user['name'] ?? 'Student') ?></span></p>
                                <p class="text-gray-600 mb-6">for successfully completing this course</p>
                                <div class="flex justify-between items-center text-sm text-gray-500">
                                    <span><?= date('F d, Y', strtotime($featuredCert['completion_date'])) ?></span>
                                    <span>ID: COI-<?= $featuredCert['id'] ?>-<?= $userId ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Details -->
                    <div>
                        <h3 class="text-3xl font-bold text-blue-800 mb-4"><?= htmlspecialchars($featuredCert['title']) ?></h3>
                        <p class="text-gray-700 mb-6">Congratulations! You’ve completed this course successfully.</p>
                        <div class="flex space-x-4">
                            <a href="generate-certificate.php?course_id=<?= $featuredCert['id'] ?>" target="_blank" class="px-6 py-3 bg-gradient-to-r from-blue-500 to-blue-700 text-white rounded-xl hover:scale-105 transition-all duration-300">
                                <i class="fa fa-download mr-2"></i>Download PDF
                            </a>
                            <button class="px-6 py-3 glass-effect text-gray-700 rounded-xl hover:text-blue-800 hover:bg-blue-200 transition-all duration-300">
                                Share on LinkedIn
                            </button>
                            <a href="verify-certificate.php?id=COI-<?= $featuredCert['id'] ?>-<?= $userId ?>" class="px-6 py-3 glass-effect text-gray-700 rounded-xl hover:text-blue-800 transition-all duration-300">
                                Verify
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Certificates Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php if (!empty($certificateData['certificates'])): ?>
                <?php foreach ($certificateData['certificates'] as $cert): ?>
                <div class="bg-white rounded-xl shadow-lg border-4 border-primary overflow-hidden">
                    <div class="bg-gradient-to-br from-primary to-secondary p-6 text-center">
                        <div class="w-16 h-16 bg-white rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-award text-2xl text-primary"></i>
                        </div>
                        <h3 class="text-white font-bold text-lg">Certificate of Completion</h3>
                    </div>
                    <div class="p-6">
                        <h4 class="font-bold text-gray-800 text-lg mb-2"><?= htmlspecialchars($cert['title']) ?></h4>
                        <p class="text-gray-600 text-sm mb-4">Completed on <?= date('F d, Y', strtotime($cert['completion_date'])) ?></p>
                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Instructor:</span>
                                <span class="font-semibold"><?= htmlspecialchars($cert['instructor_name']) ?></span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600">Credential ID:</span>
                                <span class="font-semibold">COI-<?= $cert['id'] ?>-<?= $userId ?></span>
                            </div>
                        </div>
                        <div class="flex space-x-2">
                            <a href="generate-certificate.php?course_id=<?= $cert['id'] ?>" target="_blank" class="flex-1 bg-primary text-white py-2 px-4 rounded-lg hover:bg-accent transition-colors text-sm text-center">
                                <i class="fas fa-download mr-2"></i>Download
                            </a>
                            <button class="p-2 border border-gray-300 rounded-lg hover:bg-gray-50">
                                <i class="fas fa-share"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-span-full text-center py-8 sm:py-12 bg-white rounded-xl shadow-sm">
                    <p class="text-gray-500">You have not earned any certificates yet.</p>
                    <a href="my-courses.php" class="mt-4 inline-block bg-primary text-white py-2 px-4 rounded-lg hover:bg-accent transition-colors">Continue Learning</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
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
