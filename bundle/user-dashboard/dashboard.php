<?php
require_once __DIR__ . '/../db.php';
requireLogin();

$user   = getCurrentUser();
$userId = getCurrentUserId();

// Fetch last login & affiliate info
$lastLogin = 'N/A';
try {
      $stmt = $pdo->prepare("SELECT created_at, ip_address FROM sessions WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
      $stmt->execute([$userId]);
      $session = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($session) {
        $lastLogin = date('M d, Y h:i A', strtotime($session['created_at'])) . ' (' . ($session['ip_address'] ?? 'unknown IP') . ')';
    }
} catch (Exception $e) { error_log($e->getMessage()); }

// Check if user has any completed purchases
  $stmt = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = ? AND status='completed'");
  $stmt->execute([$userId]);
  if ($stmt->fetchColumn() == 0) {
    header('Location: ../pricings.php');
    exit;
}

// Dashboard data defaults
$dashboardData = [
    'userName' => htmlspecialchars($user['name'] ?? 'User'),
    'userInitial' => strtoupper(substr($user['name'] ?? 'U', 0, 1)),
    'learningStreak' => rand(3, 15),
    'experiencePoints' => rand(800, 3200),
    'completedCourses' => 0,
    'achievements' => 5,
    'totalEarnings' => 0,
    'earningsThisMonth' => 0,
    'referralCode' => $user['referral_code'] ?? 'N/A',
    'currentCourses' => []
];

// Affiliate earnings
try {
    $stmt = $pdo->prepare("SELECT total_earnings, pending_payout FROM affiliates WHERE user_id = ?");
    $stmt->execute([$userId]);
    $aff = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($aff) {
        $dashboardData['totalEarnings'] = $aff['total_earnings'];
        $dashboardData['earningsThisMonth'] = $aff['pending_payout'];
    }
} catch (Exception $e) { error_log($e->getMessage()); }

// Purchased courses + progress
try {
    $stmt = $pdo->prepare("
        SELECT c.id, c.title, 
               COUNT(l.id) as total_lessons,
               COALESCE(SUM(p.completed), 0) as completed_lessons
        FROM courses c
        JOIN purchases pu ON pu.course_id = c.id AND pu.user_id = ? AND pu.status='completed'
        LEFT JOIN lessons l ON l.course_id = c.id
        LEFT JOIN progress p ON p.lesson_id = l.id AND p.user_id = ?
        GROUP BY c.id
        ORDER BY c.created_at DESC LIMIT 3
    ");
    $stmt->execute([$userId, $userId]);
    $dashboardData['currentCourses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { error_log($e->getMessage()); }

$stmt = $pdo->prepare("
    SELECT c.title 
    FROM courses c 
    JOIN purchases p ON p.course_id = c.id 
    WHERE p.user_id = ? AND p.status = 'completed' 
    ORDER BY p.purchased_at DESC 
    LIMIT 1
");
$stmt->execute([$userId]);
$bundleName = $stmt->fetchColumn() ?: 'Standard Plan';

// Random daily learning tip
$tips = [
    "Set small learning goals today — focus on one lesson at a time.",
    "Rewatch one key concept you learned yesterday.",
    "Share your learning journey with your peers for motivation.",
    "Try to study at the same hour daily to form a habit!",
    "Review your notes from last week before moving ahead."
];
$dailyTip = $tips[array_rand($tips)];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Dashboard - Culture of Internet</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
}
</script>
</head>
<body class="bg-gray-50 overflow-x-hidden">

<!-- Mobile Header -->
<div class="md:hidden flex justify-between items-center bg-white px-4 py-3 shadow-sm sticky top-0 z-50">
  <h1 class="text-lg font-bold text-primary">Dashboard</h1>
  <button id="mobileMenuBtn" class="text-gray-600"><i class="fas fa-bars text-xl"></i></button>
</div>

<!-- Sidebar -->
        <?php include "includes/sidebar.php"; ?>

<!-- Main Content -->
<div class="md:ml-64 transition-all duration-300">
  <!-- Header -->
  <header class="bg-white shadow-sm border-b border-gray-200 px-6 py-4 flex justify-between items-center">
    <div>
      <h2 class="text-2xl font-bold text-gray-800">Welcome back, <?= $dashboardData['userName'] ?> 👋</h2>
      <p class="text-gray-600 text-sm">Active Plan: <strong><?= htmlspecialchars($bundleName) ?></strong></p>
      <p class="text-gray-500 text-xs">Last login: <?= htmlspecialchars($lastLogin) ?></p>
    </div>
    <div class="flex items-center space-x-4">
      <div class="flex items-center bg-gradient-to-r from-primary to-secondary text-white px-4 py-2 rounded-full">
        <i class="fas fa-fire mr-2"></i>
        <span class="font-semibold"><?= $dashboardData['learningStreak'] ?> Day Streak!</span>
      </div>
      <div class="w-10 h-10 bg-primary rounded-full flex items-center justify-center text-white font-bold">
        <?= $dashboardData['userInitial'] ?>
      </div>
    </div>
  </header>

  <!-- Body -->
  <main class="p-6">
    <!-- Gamification Cards -->
    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
      <?php
      $cards = [
        ['XP', number_format($dashboardData['experiencePoints']), 'fa-star', 'from-blue-500 to-blue-600'],
        ['Courses Done', $dashboardData['completedCourses'], 'fa-graduation-cap', 'from-emerald-500 to-emerald-600'],
        ['Badges', $dashboardData['achievements'], 'fa-medal', 'from-purple-500 to-purple-600'],
        ['Earnings', '₹' . number_format($dashboardData['totalEarnings'], 2), 'fa-coins', 'from-amber-500 to-amber-600'],
        ['Referrals', $dashboardData['referralCode'], 'fa-share-alt', 'from-indigo-500 to-indigo-600'],
      ];
      foreach ($cards as $c): ?>
      <div class="bg-gradient-to-br <?= $c[3] ?> p-4 rounded-xl text-white shadow-sm">
        <div class="flex justify-between items-center">
          <div>
            <p class="text-xs opacity-80"><?= $c[0] ?></p>
            <p class="text-2xl font-bold"><?= $c[1] ?></p>
          </div>
          <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
            <i class="fas <?= $c[2] ?>"></i>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>

    <!-- Affiliate Snapshot -->
    <div class="bg-white rounded-xl shadow-sm p-5 mb-8">
      <h3 class="text-lg font-bold text-gray-800 mb-3">Affiliate Snapshot</h3>
      <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
        <div><p class="text-gray-500 text-sm">Total Earnings</p><p class="text-lg font-bold text-green-600">₹<?= number_format($dashboardData['totalEarnings'], 2) ?></p></div>
        <div><p class="text-gray-500 text-sm">Pending Payout</p><p class="text-lg font-bold text-amber-600">₹<?= number_format($dashboardData['earningsThisMonth'], 2) ?></p></div>
        <div><p class="text-gray-500 text-sm">Commission Rate</p><p class="text-lg font-bold text-blue-600">25%</p></div>
        <div><p class="text-gray-500 text-sm">Your Code</p><p class="text-lg font-bold text-indigo-600"><?= htmlspecialchars($dashboardData['referralCode']) ?></p></div>
      </div>
    </div>

    <!-- Continue Learning -->
    <div class="bg-white rounded-xl shadow-sm p-5 mb-8">
      <h3 class="text-lg font-bold text-gray-800 mb-4">Continue Learning</h3>
      <?php if (!empty($dashboardData['currentCourses'])): foreach ($dashboardData['currentCourses'] as $c):
        $progress = ($c['total_lessons'] > 0) ? round(($c['completed_lessons'] / $c['total_lessons']) * 100) : 0; ?>
      <div class="border border-gray-200 rounded-lg p-4 mb-3 hover:shadow-md transition">
        <div class="flex justify-between items-center">
          <div>
            <h4 class="font-semibold text-gray-800"><?= htmlspecialchars($c['title']) ?></h4>
            <p class="text-gray-500 text-sm"><?= $c['completed_lessons'] ?>/<?= $c['total_lessons'] ?> lessons completed</p>
          </div>
          <a href="bundle/course-player.php?id=<?= $c['id'] ?>" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-accent text-sm"><?= $progress ? 'Continue' : 'Start' ?></a>
        </div>
        <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
          <div class="bg-primary h-2 rounded-full" style="width: <?= $progress ?>%"></div>
        </div>
      </div>
      <?php endforeach; else: ?>
      <p class="text-gray-500 text-center py-3">You haven’t enrolled in any courses yet. <a href="../bundle/courses.php" class="text-primary hover:underline">Browse Courses</a>.</p>
      <?php endif; ?>
    </div>

    <!-- Quick Links -->
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
      <?php
      $links = [
        ['Start New Course', 'fa-book-open', 'bundle/courses.php'],
        ['Affiliate Dashboard', 'fa-users', 'affiliate.php'],
        ['My Wallet', 'fa-wallet', 'wallet.php'],
        ['Certificates', 'fa-certificate', 'certificates.php']
      ];
      foreach ($links as $l): ?>
      <a href="<?= $l[2] ?>" class="flex flex-col items-center justify-center bg-white shadow-sm rounded-xl p-4 hover:shadow-md transition text-center">
        <div class="w-12 h-12 flex items-center justify-center bg-primary text-white rounded-full mb-2"><i class="fas <?= $l[1] ?>"></i></div>
        <p class="font-medium text-gray-700"><?= $l[0] ?></p>
      </a>
      <?php endforeach; ?>
    </div>

    <!-- Daily Tip -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start gap-3">
      <i class="fas fa-lightbulb text-blue-600 mt-1"></i>
      <div>
        <p class="font-semibold text-blue-800">Today's Learning Tip</p>
        <p class="text-blue-700 text-sm mt-1"><?= htmlspecialchars($dailyTip) ?></p>
      </div>
    </div>
  </main>
</div>

<script>
const sidebar=document.getElementById('sidebar');
document.getElementById('mobileMenuBtn')?.addEventListener('click',()=>sidebar.classList.remove('-translate-x-full'));
document.getElementById('closeSidebar')?.addEventListener('click',()=>sidebar.classList.add('-translate-x-full'));
</script>

</body>
</html>
