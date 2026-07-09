<?php
// wallet.php - Updated responsive wallet page (collapsible sidebar, mobile-first)

// Prevent caching to ensure logout works properly
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start();
require_once __DIR__ . '/../db.php';
requireLogin();

// Get user / id
$user = getCurrentUser();
$userId = getCurrentUserId();

// Quick safety defaults
$walletData = [
    'available_balance' => 0.00,
    'total_earnings' => 0.00,
    'pending_payout' => 0.00,
    'recent_transactions' => [],
    'breakdown' => [
        'affiliate' => 0.00,
        'bonus' => 0.00,
        'incentives' => 0.00,
        'referral' => 0.00,
    ]
];

try {
    // Affiliate row (if any)
    $stmt = $pdo->prepare("SELECT total_earnings, pending_payout FROM affiliates WHERE user_id = ?");
    $stmt->execute([$userId]);
    $affiliate = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($affiliate) {
        $walletData['total_earnings'] = (float)($affiliate['total_earnings'] ?? 0.00);
        $walletData['pending_payout'] = (float)($affiliate['pending_payout'] ?? 0.00);
    }

    // Ensure numeric and available balance is not negative
    $walletData['available_balance'] = max(0.00, (float)$walletData['total_earnings'] - (float)$walletData['pending_payout']);

    // Recent transactions (limit 6 for mobile friendliness)
    $stmt = $pdo->prepare("SELECT amount, created_at, gateway, status FROM transactions WHERE user_id = ? ORDER BY created_at DESC LIMIT 6");
    $stmt->execute([$userId]);
    $walletData['recent_transactions'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Breakdown sums (safely using COALESCE)
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(commission),0) FROM referrals WHERE referrer_id = ?");
    $stmt->execute([$userId]);
    $walletData['breakdown']['affiliate'] = (float)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE user_id = ? AND gateway = 'bonus' AND status = 'completed'");
    $stmt->execute([$userId]);
    $walletData['breakdown']['bonus'] = (float)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE user_id = ? AND gateway = 'incentive' AND status = 'completed'");
    $stmt->execute([$userId]);
    $walletData['breakdown']['incentives'] = (float)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(amount),0) FROM transactions WHERE user_id = ? AND gateway = 'referral_bonus' AND status = 'completed'");
    $stmt->execute([$userId]);
    $walletData['breakdown']['referral'] = (float)$stmt->fetchColumn();

} catch (PDOException $e) {
    error_log("Wallet Dashboard Error: " . $e->getMessage());
    // keep defaults if DB queries fail
}

// Prepare chart data safely (JSON)
$chartData = [
    'labels' => ['Affiliate Commission','Completion Bonus','Review Incentives','Referral Bonuses'],
    'values' => array_values($walletData['breakdown'])
];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Wallet & Earnings - Culture of Internet</title>

<!-- Tailwind CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<!-- FontAwesome (for hamburger & icons) -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<script>
tailwind.config = {
  theme: {
    extend: {
      colors: {
        primary: '#2563eb',
        accent: '#1d4ed8',
        light: '#dbeafe'
      }
    }
  }
}
</script>

<style>
/* Minor style tweaks */
body { -webkit-font-smoothing:antialiased; -moz-osx-font-smoothing:grayscale; }
.sidebar-transition { transition: transform .25s ease-in-out; }
@media (max-width: 1024px) {
  /* reduce padding on small screens */
  .content-padding { padding-left: 1rem; padding-right: 1rem; }
}
</style>
</head>
<body class="bg-gray-50">

<!-- Mobile top bar -->
<header class="lg:hidden bg-white shadow sticky top-0 z-40">
  <div class="flex items-center justify-between px-4 py-3">
   
    <div class="text-center">
      <div class="text-base font-semibold text-gray-800">Wallet & Earnings</div>
      <div class="text-xs text-gray-500">Manage your balance & payouts</div>
    </div>
    <div class="w-8"></div>
     <button id="mobile-toggle" aria-label="Open menu" aria-expanded="false" class="text-gray-700 hover:text-primary focus:outline-none">
      <!-- Option 3: FontAwesome hamburger -->
      <i class="fas fa-bars text-xl"></i>
    </button>
  </div>
</header>

<!-- Layout -->
<div class="min-h-screen flex">

  <!-- Sidebar -->
        <?php include "includes/sidebar.php"; ?>

  <!-- Overlay for mobile when sidebar open -->
  <div id="overlay" class="fixed inset-0 bg-black/40 z-30 hidden lg:hidden"></div>

  <!-- Main content -->
  <div class="flex-1 lg:ml-64">
    <main class="content-padding p-6">

      <!-- Header area (desktop) -->
      <div class="hidden lg:flex items-center justify-between mb-6">
        <div>
          <h2 class="text-2xl font-bold text-gray-800">Wallet & Earnings</h2>
          <p class="text-sm text-gray-600">Manage your earnings, withdrawals, and payment methods</p>
        </div>
        <div class="flex items-center gap-4">
          <div class="text-right">
            <div class="text-xs text-gray-500">Available Balance</div>
            <div class="text-lg font-semibold text-gray-800">₹<?php echo number_format($walletData['available_balance'], 2); ?></div>
          </div>
          <div class="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-white">
            <i class="fas fa-wallet"></i>
          </div>
        </div>
      </div>

      <!-- Mobile summary card -->
      <div class="lg:hidden mb-4">
        <div class="bg-white rounded-xl shadow p-4 flex items-center justify-between">
          <div>
            <div class="text-xs text-gray-500">Available Balance</div>
            <div class="text-xl font-semibold text-gray-800">₹<?php echo number_format($walletData['available_balance'], 2); ?></div>
          </div>
          <div>
            <button id="withdraw-mobile" class="px-3 py-2 bg-primary text-white rounded-md text-sm">Withdraw</button>
          </div>
        </div>
      </div>

      <!-- Wallet Overview -->
      <section class="grid grid-cols-2 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 rounded-xl text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-emerald-100 text-sm">Available Balance</p>
              <p class="text-3xl font-bold">₹<?php echo number_format($walletData['available_balance'], 2); ?></p>
            </div>
            <div class="w-12 h-12 bg-emerald-400 rounded-full flex items-center justify-center">
              <i class="fas fa-wallet"></i>
            </div>
          </div>
          <div class="mt-4">
            <button class="bg-white text-emerald-600 font-semibold py-2 px-4 rounded-lg hover:bg-gray-100 transition-colors text-sm">Withdraw Funds</button>
          </div>
        </div>

        <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-blue-100 text-sm">Total Earnings</p>
              <p class="text-3xl font-bold">₹<?php echo number_format($walletData['total_earnings'], 2); ?></p>
            </div>
            <div class="w-12 h-12 bg-blue-400 rounded-full flex items-center justify-center">
              <i class="fas fa-chart-line"></i>
            </div>
          </div>
          <p class="text-blue-100 text-sm mt-4">All time earnings</p>
        </div>

        <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-xl text-white">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-purple-100 text-sm">Pending Payouts</p>
              <p class="text-3xl font-bold">₹<?php echo number_format($walletData['pending_payout'], 2); ?></p>
            </div>
            <div class="w-12 h-12 bg-purple-400 rounded-full flex items-center justify-center">
              <i class="fas fa-clock"></i>
            </div>
          </div>
          <p class="text-purple-100 text-sm mt-4">Processing...</p>
        </div>
      </section>

      <!-- Quick Actions + Payment Methods -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
          <h3 class="text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
          <div class="grid grid-cols-2 gap-4">
            <button class="bg-primary text-white p-4 rounded-lg text-center">
              <i class="fas fa-plus text-2xl mb-2"></i>
              <div class="font-semibold">Add Money</div>
            </button>
            <button class="bg-emerald-500 text-white p-4 rounded-lg text-center">
              <i class="fas fa-arrow-up text-2xl mb-2"></i>
              <div class="font-semibold">Withdraw</div>
            </button>
            <button class="bg-purple-500 text-white p-4 rounded-lg text-center">
              <i class="fas fa-exchange-alt text-2xl mb-2"></i>
              <div class="font-semibold">Transfer</div>
            </button>
            <button class="bg-amber-500 text-white p-4 rounded-lg text-center">
              <i class="fas fa-history text-2xl mb-2"></i>
              <div class="font-semibold">History</div>
            </button>
          </div>

          <div class="mt-6">
            <h4 class="font-semibold text-gray-800 mb-3">Recent Transactions</h4>
            <div class="space-y-3">
              <?php if (!empty($walletData['recent_transactions'])): ?>
                <?php foreach ($walletData['recent_transactions'] as $tx): 
                  $isCredit = ((float)$tx['amount']) >= 0;
                  $label = htmlspecialchars(ucfirst($tx['gateway'] ?: 'Transaction'));
                ?>
                <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                  <div class="flex items-center">
                    <div class="w-9 h-9 <?php echo $isCredit ? 'bg-green-100' : 'bg-red-100'; ?> rounded-full flex items-center justify-center mr-3">
                      <i class="fas <?php echo $isCredit ? 'fa-plus' : 'fa-minus'; ?> text-sm <?php echo $isCredit ? 'text-green-600' : 'text-red-600'; ?>"></i>
                    </div>
                    <div>
                      <div class="text-sm font-medium text-gray-800"><?php echo $label; ?></div>
                      <div class="text-xs text-gray-500"><?php echo date('M d, Y, g:i A', strtotime($tx['created_at'])); ?></div>
                    </div>
                  </div>
                  <div class="text-sm font-semibold <?php echo $isCredit ? 'text-green-600' : 'text-red-600'; ?>">
                    <?php echo $isCredit ? '+' : '-'; ?>₹<?php echo number_format(abs($tx['amount']), 2); ?>
                  </div>
                </div>
                <?php endforeach; ?>
              <?php else: ?>
                <p class="text-gray-500 text-center py-4">No recent transactions.</p>
              <?php endif; ?>
            </div>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-gray-800">Payment Methods</h3>
            <button class="text-primary hover:text-accent">
              <i class="fas fa-plus mr-1"></i> Add New
            </button>
          </div>

          <div class="space-y-4">
            <div class="p-4 border-2 border-primary bg-light rounded-lg">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center text-white">
                    <i class="fas fa-university"></i>
                  </div>
                  <div>
                    <div class="font-semibold text-gray-800">Bank Account</div>
                    <div class="text-xs text-gray-500">HDFC Bank ••••4521</div>
                  </div>
                </div>
                <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded-full">Primary</span>
              </div>
              <div class="mt-3 flex gap-2">
                <button class="flex-1 bg-primary text-white py-2 rounded-lg text-sm">Withdraw Here</button>
                <button class="px-3 py-2 border rounded-lg text-sm">Edit</button>
              </div>
            </div>

            <div class="p-4 border rounded-lg">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center text-purple-600">
                    <i class="fas fa-mobile"></i>
                  </div>
                  <div>
                    <div class="font-semibold text-gray-800">UPI</div>
                    <div class="text-xs text-gray-500">shivani@paytm</div>
                  </div>
                </div>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Available</span>
              </div>
              <div class="mt-3 flex gap-2">
                <button class="flex-1 border border-primary text-primary py-2 rounded-lg text-sm">Use UPI</button>
                <button class="px-3 py-2 border rounded-lg text-sm">Edit</button>
              </div>
            </div>

            <div class="p-4 border rounded-lg">
              <div class="flex items-center justify-between">
                <div class="flex items-center gap-3">
                  <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center text-blue-600">
                    <i class="fab fa-paypal"></i>
                  </div>
                  <div>
                    <div class="font-semibold text-gray-800">PayPal</div>
                    <div class="text-xs text-gray-500">shivani****@gmail.com</div>
                  </div>
                </div>
                <span class="bg-gray-100 text-gray-800 text-xs px-2 py-1 rounded-full">Available</span>
              </div>
              <div class="mt-3 flex gap-2">
                <button class="flex-1 border border-primary text-primary py-2 rounded-lg text-sm">Use PayPal</button>
                <button class="px-3 py-2 border rounded-lg text-sm">Edit</button>
              </div>
            </div>
          </div>
        </div>
      </section>

      <!-- Charts & Breakdown -->
      <section class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Earnings Breakdown</h3>
          <div class="relative" style="height:320px;">
            <canvas id="earningsChart"></canvas>
          </div>
        </div>

        <div class="bg-white rounded-xl shadow p-6">
          <h3 class="text-lg font-semibold mb-4">Breakdown Details</h3>

          <div class="space-y-4">
            <?php
              $breakdown = $walletData['breakdown'];
              $items = [
                ['title'=>'Affiliate Commission','key'=>'affiliate','color'=>'blue','icon'=>'fa-users'],
                ['title'=>'Course Completion Bonus','key'=>'bonus','color'=>'emerald','icon'=>'fa-trophy'],
                ['title'=>'Review Incentives','key'=>'incentives','color'=>'purple','icon'=>'fa-star'],
                ['title'=>'Referral Bonuses','key'=>'referral','color'=>'amber','icon'=>'fa-gift'],
              ];
              foreach ($items as $it):
            ?>
            <div class="flex items-center justify-between p-4 rounded-lg bg-<?php echo $it['color']; ?>-50">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-<?php echo $it['color']; ?>-500 text-white rounded-lg flex items-center justify-center">
                  <i class="fas <?php echo $it['icon']; ?>"></i>
                </div>
                <div>
                  <div class="font-semibold text-gray-800"><?php echo $it['title']; ?></div>
                  <div class="text-xs text-gray-500">Source: <?php echo $it['title']; ?></div>
                </div>
              </div>
              <div class="text-right">
                <div class="font-bold text-<?php echo $it['color']; ?>-600">₹<?php echo number_format($breakdown[$it['key']] ?? 0, 2); ?></div>
                <div class="text-xs text-gray-600">—</div>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
      </section>

      <!-- Withdrawal info -->
      <section class="bg-white rounded-xl shadow p-6 mb-8">
        <h3 class="text-lg font-semibold mb-4">Withdrawal Information</h3>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-6">
          <div class="text-center">
            <div class="w-16 h-16 bg-blue-100 rounded-full mx-auto flex items-center justify-center mb-3">
              <i class="fas fa-rupee-sign text-blue-600"></i>
            </div>
            <div class="font-semibold text-gray-800">Minimum Withdrawal</div>
            <div class="text-2xl font-bold text-blue-600">₹500</div>
            <div class="text-xs text-gray-500 mt-1">Per transaction</div>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 bg-emerald-100 rounded-full mx-auto flex items-center justify-center mb-3">
              <i class="fas fa-clock text-emerald-600"></i>
            </div>
            <div class="font-semibold text-gray-800">Processing Time</div>
            <div class="text-2xl font-bold text-emerald-600">1-3</div>
            <div class="text-xs text-gray-500 mt-1">Business days</div>
          </div>
          <div class="text-center">
            <div class="w-16 h-16 bg-purple-100 rounded-full mx-auto flex items-center justify-center mb-3">
              <i class="fas fa-percentage text-purple-600"></i>
            </div>
            <div class="font-semibold text-gray-800">Transaction Fee</div>
            <div class="text-2xl font-bold text-purple-600">₹0</div>
            <div class="text-xs text-gray-500 mt-1">No charges</div>
          </div>
        </div>

        <div class="mt-6 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
          <div class="flex items-start gap-3">
            <i class="fas fa-info-circle text-yellow-600 mt-1"></i>
            <div>
              <div class="font-semibold text-yellow-800">Important Notes</div>
              <ul class="text-sm text-yellow-700 list-disc ml-5 mt-2 space-y-1">
                <li>Withdrawals processed on business days only.</li>
                <li>Ensure your payment details are accurate to avoid delays.</li>
                <li>Contact support if you don't receive funds within 5 business days.</li>
                <li>Tax deductions may apply as per Indian regulations.</li>
              </ul>
            </div>
          </div>
        </div>
      </section>

    </main>
  </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
/* Sidebar toggle behavior (mobile) */
const sidebar = document.getElementById('sidebar');
const overlay = document.getElementById('overlay');
const mobileToggle = document.getElementById('mobile-toggle');

function openSidebar() {
  sidebar.classList.remove('-translate-x-full');
  overlay.classList.remove('hidden');
  mobileToggle.setAttribute('aria-expanded','true');
}
function closeSidebar() {
  sidebar.classList.add('-translate-x-full');
  overlay.classList.add('hidden');
  mobileToggle.setAttribute('aria-expanded','false');
}

mobileToggle.addEventListener('click', () => {
  if (sidebar.classList.contains('-translate-x-full')) openSidebar();
  else closeSidebar();
});
overlay.addEventListener('click', closeSidebar);

// Close sidebar on Escape
document.addEventListener('keydown', (e) => {
  if (e.key === 'Escape') closeSidebar();
});

/* Chart */
const chartLabels = <?php echo json_encode($chartData['labels'], JSON_HEX_TAG); ?>;
const chartValues = <?php echo json_encode($chartData['values'], JSON_HEX_TAG); ?>;

const ctx = document.getElementById('earningsChart').getContext('2d');
const earningsChart = new Chart(ctx, {
  type: 'doughnut',
  data: {
    labels: chartLabels,
    datasets: [{
      data: chartValues,
      backgroundColor: ['#3b82f6', '#10b981', '#8b5cf6', '#f59e0b'],
      borderWidth: 0
    }]
  },
  options: {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: { position: 'bottom', labels: { usePointStyle: true, padding: 16 } }
    }
  }
});
</script>
</body>
</html>
