<?php
// Prevent caching to ensure logout works properly
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: bundle/login.php');
    exit;
}

require_once __DIR__ . '/../db.php';
requireLogin();

$user = getCurrentUser();
$userId = getCurrentUserId();

// Check if user has completed purchases
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM purchases WHERE user_id = ? AND status = 'completed'");
$stmt->execute([$userId]);
$hasPurchases = $stmt->fetchColumn() > 0;

if (!$hasPurchases) {
    header('Location: ../../pricing.php');
    exit;
}

$affiliateData = [
    'total_earnings' => '0.00',
    'pending_payout' => '0.00',
    'total_referrals' => 0,
    'monthly_earnings' => '0.00',
    'referral_code' => $user['referral_code'] ?? '',
    'conversion_rate' => 0,
    'commission_rate' => 25,
    'courses' => [],
    'recent_referrals' => [],
];

function create_slug($string) {
    return preg_replace('/[^A-Za-z0-9-]+/', '-', strtolower($string));
}

try {
    $stmt = $pdo->prepare("
        SELECT
            COALESCE(a.total_earnings, 0.00) as total_earnings,
            COALESCE(a.pending_payout, 0.00) as pending_payout,
            (SELECT COUNT(*) FROM referrals WHERE referrer_id = ?) as total_referrals,
            (SELECT COALESCE(SUM(commission), 0.00) FROM referrals WHERE referrer_id = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)) as monthly_earnings
        FROM affiliates a
        WHERE a.user_id = ?
    ");
    $stmt->execute([$userId, $userId, $userId]);
    $stats = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($stats) {
        $affiliateData['total_earnings'] = $stats['total_earnings'];
        $affiliateData['pending_payout'] = $stats['pending_payout'];
        $affiliateData['total_referrals'] = $stats['total_referrals'] ?: 0;
        $affiliateData['monthly_earnings'] = $stats['monthly_earnings'];
    }

    $stmt = $pdo->prepare("SELECT id, title FROM courses WHERE is_bundle = 0 ORDER BY created_at DESC LIMIT 3");
    $stmt->execute();
    $affiliateData['courses'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT u.name, r.commission, r.created_at 
        FROM referrals r
        JOIN users u ON r.referred_user_id = u.id
        WHERE r.referrer_id = ?
        ORDER BY r.created_at DESC
        LIMIT 4
    ");
    $stmt->execute([$userId]);
    $affiliateData['recent_referrals'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Affiliate Dashboard Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Affiliate Dashboard - Culture of Internet</title>
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
</head>
<body class="bg-gray-50 overflow-x-hidden">

<!-- Mobile Header -->
<div class="md:hidden flex justify-between items-center bg-white px-4 py-3 shadow-sm sticky top-0 z-50">
    <h1 class="text-lg font-bold text-primary">Affiliate Dashboard</h1>
    <button id="mobileMenuBtn" class="text-gray-600 focus:outline-none">
        <i class="fas fa-bars text-xl"></i>
    </button>
</div>

<!-- Sidebar -->
        <?php include "includes/sidebar.php"; ?>

<!-- Main Content -->
<div class="md:ml-60 transition-all duration-300">
    <header class="hidden md:block bg-white shadow-sm border-b border-gray-200 px-6 py-4">
        <h2 class="text-xl font-semibold text-gray-800">Affiliate Dashboard</h2>
        <p class="text-gray-600 text-sm">Track your referrals and commissions</p>
    </header>

    <main class="p-4 sm:p-6">
        <!-- Stats -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 sm:gap-6 mb-8">
            <?php
            $cards = [
                ['Total Earnings', '₹' . number_format($affiliateData['total_earnings'], 2), 'fa-rupee-sign', 'from-emerald-500 to-emerald-600'],
                ['Referrals', $affiliateData['total_referrals'], 'fa-users', 'from-blue-500 to-blue-600'],
                ['Conversion', $affiliateData['conversion_rate'] . '%', 'fa-chart-pie', 'from-purple-500 to-purple-600'],
                ['Commission', $affiliateData['commission_rate'] . '%', 'fa-percent', 'from-amber-500 to-amber-600'],
            ];
            foreach ($cards as $c): ?>
                <div class="bg-gradient-to-br <?= $c[3] ?> rounded-lg p-4 sm:p-5 text-white shadow-sm hover:shadow-md transition">
                    <div class="flex justify-between items-center">
                        <div>
                            <p class="text-xs opacity-80"><?= $c[0] ?></p>
                            <p class="text-xl sm:text-2xl font-bold"><?= $c[1] ?></p>
                        </div>
                        <div class="w-10 h-10 bg-white/20 rounded-full flex items-center justify-center">
                            <i class="fas <?= $c[2] ?> text-base sm:text-lg"></i>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Affiliate Links & Marketing -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
            <!-- Affiliate Links -->
            <div class="bg-white rounded-lg shadow-sm p-5">
                <h3 class="text-lg font-semibold mb-3 text-gray-800">Your Affiliate Links</h3>
                <div class="space-y-3">
                    <?php if (!empty($affiliateData['courses'])): 
                        foreach ($affiliateData['courses'] as $course): 
                            $link = "https://cultureofinternet.com/courses-bundles/courses.php?ref=" . htmlspecialchars($affiliateData['referral_code']) . "&course=" . $course['id']; ?>
                        <div class="border border-gray-200 rounded-md p-3 hover:shadow-sm transition">
                            <div class="flex justify-between mb-2">
                                <h4 class="font-medium text-gray-700 text-sm"><?= htmlspecialchars($course['title']) ?></h4>
                                <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded"><?= $affiliateData['commission_rate'] ?>%</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <input type="text" value="<?= $link ?>" class="flex-1 text-xs border border-gray-300 bg-gray-50 rounded px-2 py-1" readonly>
                                <button class="bg-primary text-white px-3 py-1 rounded hover:bg-accent text-xs"><i class="fa fa-copy"></i></button>
                            </div>
                        </div>
                    <?php endforeach; else: ?>
                        <p class="text-gray-500 text-sm text-center py-4">No courses available.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Marketing Materials -->
            <div class="bg-white rounded-lg shadow-sm p-5">
                <h3 class="text-lg font-semibold mb-3 text-gray-800">Marketing Materials</h3>
                <div class="space-y-4">
                    <?php
                    $materials = [
                        ['Social Media Posts', 'Pre-designed posts for Instagram, Facebook, and LinkedIn', 'fa-download', 'Download Assets'],
                        ['Email Templates', 'Ready-to-use email templates for your referrals', 'fa-envelope', 'View Templates'],
                        ['Banner Ads', 'Various sizes and formats for website integration', 'fa-image', 'Get Banners'],
                        ['Course Reviews', 'Authentic testimonials and course highlights', 'fa-star', 'View Reviews']
                    ];
                    foreach ($materials as $m): ?>
                    <div class="border border-gray-200 rounded-md p-3 hover:shadow-sm transition">
                        <h4 class="font-semibold text-gray-800 text-sm mb-1"><?= $m[0] ?></h4>
                        <p class="text-gray-600 text-xs mb-2"><?= $m[1] ?></p>
                        <button class="w-full bg-primary text-white py-1.5 text-sm rounded hover:bg-accent transition"><i class="fas <?= $m[2] ?> mr-1"></i><?= $m[3] ?></button>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Recent Referrals -->
        <div class="bg-white rounded-lg shadow-sm p-5 mb-8">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">Recent Referrals</h3>
            <div class="space-y-3">
                <?php if (!empty($affiliateData['recent_referrals'])):
                    foreach ($affiliateData['recent_referrals'] as $ref): ?>
                    <div class="flex justify-between items-center border border-gray-100 p-3 rounded-md hover:bg-gray-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 bg-primary text-white flex items-center justify-center rounded-full text-sm font-semibold">
                                <?= strtoupper(substr($ref['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800"><?= htmlspecialchars($ref['name']) ?></p>
                                <p class="text-xs text-gray-500"><?= date('M d, Y', strtotime($ref['created_at'])) ?></p>
                            </div>
                        </div>
                        <span class="text-green-600 font-semibold text-sm">+₹<?= number_format($ref['commission'], 2) ?></span>
                    </div>
                <?php endforeach; else: ?>
                    <p class="text-gray-500 text-sm text-center py-4">No recent referrals yet.</p>
                <?php endif; ?>
            </div>
            <button class="w-full mt-4 text-primary border border-primary py-2 rounded hover:bg-light text-sm">View All Referrals</button>
        </div>

        <!-- Commission Tiers -->
        <div class="bg-white rounded-lg shadow-sm p-5">
            <h3 class="text-lg font-semibold mb-4 text-gray-800">Commission Tiers</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                <div class="border border-gray-200 rounded-md p-4 text-center">
                    <div class="w-14 h-14 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-user text-lg text-gray-500"></i>
                    </div>
                    <h4 class="font-bold text-gray-800 text-sm mb-1">Bronze</h4>
                    <p class="text-2xl font-bold text-gray-700 mb-1">15%</p>
                    <p class="text-xs text-gray-600 mb-3">0–10 referrals/month</p>
                    <p class="text-xs text-gray-500">Basic materials, monthly payouts</p>
                </div>
                <div class="border-2 border-primary bg-light rounded-md p-4 text-center">
                    <div class="w-14 h-14 bg-primary rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-star text-white text-lg"></i>
                    </div>
                    <h4 class="font-bold text-primary text-sm mb-1">Silver (Current)</h4>
                    <p class="text-2xl font-bold text-primary mb-1">25%</p>
                    <p class="text-xs text-gray-600 mb-3">11–25 referrals/month</p>
                    <p class="text-xs text-gray-600">Premium materials, bi-weekly payouts, priority support</p>
                </div>
                <div class="border border-amber-400 rounded-md p-4 text-center">
                    <div class="w-14 h-14 bg-amber-100 rounded-full flex items-center justify-center mx-auto mb-3">
                        <i class="fas fa-crown text-amber-600 text-lg"></i>
                    </div>
                    <h4 class="font-bold text-amber-600 text-sm mb-1">Gold</h4>
                    <p class="text-2xl font-bold text-amber-600 mb-1">35%</p>
                    <p class="text-xs text-gray-600 mb-3">25+ referrals/month</p>
                    <p class="text-xs text-gray-500">Exclusive materials, weekly payouts, manager & landing pages</p>
                </div>
            </div>

            <div class="mt-6 p-4 bg-blue-50 rounded-md">
                <div class="flex items-center">
                    <i class="fas fa-info-circle text-primary mr-2"></i>
                    <div>
                        <p class="font-semibold text-gray-800 text-sm">Next Tier Progress</p>
                        <p class="text-xs text-gray-600">You need 8 more referrals this month to reach Gold tier</p>
                        <div class="w-full bg-gray-200 rounded-full h-2 mt-2">
                            <div class="bg-primary h-2 rounded-full" style="width: 68%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
const mobileMenuBtn = document.getElementById('mobileMenuBtn');
const sidebar = document.getElementById('sidebar');
const closeSidebar = document.getElementById('closeSidebar');
mobileMenuBtn.addEventListener('click', () => sidebar.classList.remove('-translate-x-full'));
closeSidebar.addEventListener('click', () => sidebar.classList.add('-translate-x-full'));
</script>

</body>
</html>
