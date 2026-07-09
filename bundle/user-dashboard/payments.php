<?php
// payments.php - Combined Payment History (purchases + transactions)

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

session_start();
require_once __DIR__ . '/../db.php';
requireLogin();

// current user
$user = getCurrentUser();
$userId = getCurrentUserId();

// basic safety: if no user, redirect to login
if (!$userId) {
    header('Location: bundle/login.php');
    exit;
}

try {
    // --- Summary stats (purchases only for "spent" stats) ---
      $stmt = $pdo->prepare("
        SELECT 
            COALESCE(SUM(p.amount),0) AS total_spent,
            COALESCE((SELECT SUM(amount) FROM purchases WHERE user_id = :uid AND status = 'completed' AND purchased_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)),0) AS monthly_spent,
            COALESCE(COUNT(*) ,0) AS courses_purchased
        FROM purchases p
        WHERE p.user_id = :uid AND p.status = 'completed'
    ");
    $stmt->execute([':uid' => $userId]);
    $summary = $stmt->fetch(PDO::FETCH_ASSOC) ?: ['total_spent' => 0, 'monthly_spent' => 0, 'courses_purchased' => 0];

    $paymentData = [
        'total_spent' => (float)$summary['total_spent'],
        'monthly_spent' => (float)$summary['monthly_spent'],
        'courses_purchased' => (int)$summary['courses_purchased'],
    ];
    $paymentData['avg_price'] = $paymentData['courses_purchased'] > 0 ? ($paymentData['total_spent'] / $paymentData['courses_purchased']) : 0.0;

    // --- Pagination / combined records count ---
    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 8;
    $offset = ($page - 1) * $limit;

    // Count combined records (purchases + transactions)
    $countSql = "
        SELECT COUNT(*) as cnt FROM (
            SELECT p.id, p.purchased_at AS dt FROM purchases p WHERE p.user_id = :uid
            UNION ALL
            SELECT t.id, t.created_at AS dt FROM transactions t WHERE t.user_id = :uid
        ) tmp
    ";
    $stmt = $pdo->prepare($countSql);
    $stmt->execute([':uid' => $userId]);
    $total_records = (int)($stmt->fetchColumn() ?: 0);
    $total_pages = $total_records > 0 ? (int)ceil($total_records / $limit) : 1;

    // --- Fetch combined page of records (merged + ordered by date desc) ---
    // We fetch a union with a type tag (purchase|transaction), common columns, then order and limit.
    $combinedSql = "
        SELECT * FROM (
            SELECT 
                'purchase' AS type,
                p.id,
                p.course_id,
                p.amount,
                p.status,
                p.purchased_at AS dt,
                c.title AS title,
                NULL AS gateway
            FROM purchases p
            LEFT JOIN courses c ON p.course_id = c.id
            WHERE p.user_id = :uid

            UNION ALL

            SELECT
                'transaction' AS type,
                t.id,
                NULL AS course_id,
                t.amount,
                t.status,
                t.created_at AS dt,
                NULL AS title,
                t.gateway
            FROM transactions t
            WHERE t.user_id = :uid
        ) q
        ORDER BY dt DESC
        LIMIT :limit OFFSET :offset
    ";
    $stmt = $pdo->prepare($combinedSql);
    $stmt->bindValue(':uid', $userId, PDO::PARAM_INT);
    $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
    $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
    $stmt->execute();
    $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Payments Page Error: " . $e->getMessage());
    $entries = [];
    $total_records = 0;
    $total_pages = 1;
    $paymentData = $paymentData ?? ['total_spent'=>0,'monthly_spent'=>0,'courses_purchased'=>0,'avg_price'=>0];
}

function h($s) { return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>Payment History - Culture of Internet</title>
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<script>
tailwind.config = { theme: { extend: { colors: { primary:'#2563eb', accent:'#1d4ed8' } } } };
</script>
<style>
@media (max-width: 1024px) {
  .md\:w-60 {
    width: 0 !important;
  }
  .md\:ml-64 {
    margin-left: 0 !important;
  }
}
@media (max-width: 640px) {
  .p-6 {
    padding: 1rem !important;
  }
  .mx-auto {
    margin-left: 0 !important;
    margin-right: 0 !important;
  }
  .rounded-xl {
    border-radius: 0.75rem !important;
  }
}
</style>
</head>
<body class="bg-gray-50 min-h-screen">

  <div class="min-h-screen flex flex-col md:flex-row">
    <!-- Mobile Sidebar Toggle -->
    <div class="md:hidden flex items-center justify-between bg-white px-4 py-3 border-b border-gray-200 sticky top-0 z-40">
      <button id="openSidebar" class="text-gray-700 focus:outline-none">
        <i class="fas fa-bars fa-lg"></i>
      </button>
      <span class="font-bold text-primary text-lg">Culture of Internet</span>
    </div>
    <!-- Sidebar -->
    <div class="md:w-60 w-full fixed md:static z-50 top-0 left-0 h-full md:h-auto">
      <?php include "includes/sidebar.php"; ?>
    </div>

    <div class="flex-1 w-full ">
      <!-- Header -->
      <header class="bg-white border-b sticky top-0 z-20">
        <div class=" mx-auto px-8 py-4 flex items-center justify-between">
          <div>
            <h2 class="text-2xl font-bold text-gray-800">Payment History</h2>
            <p class="text-sm text-gray-600 mt-1">All your purchases, transactions and payouts in one place</p>
          </div>
          <div class="flex items-center gap-3">
            <a href="export-payments.php" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-accent transition-colors">
              <i class="fas fa-download mr-2"></i>Export All
            </a>
          </div>
        </div>
      </header>

      <main class="mx-auto p-6">
        <!-- Summary stats -->
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-8">
          <div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl text-white">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-blue-100 text-sm">Total Spent</p>
                <p class="text-3xl font-bold">₹<?= number_format($paymentData['total_spent'], 2) ?></p>
              </div>
              <div class="w-12 h-12 bg-blue-400 rounded-full flex items-center justify-center">
                <i class="fas fa-rupee-sign text-xl"></i>
              </div>
            </div>
            <p class="text-blue-100 text-sm mt-4">Lifetime purchases (completed)</p>
          </div>

          <div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 rounded-xl text-white">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-emerald-100 text-sm">This Month</p>
                <p class="text-3xl font-bold">₹<?= number_format($paymentData['monthly_spent'], 2) ?></p>
              </div>
              <div class="w-12 h-12 bg-emerald-400 rounded-full flex items-center justify-center">
                <i class="fas fa-calendar text-xl"></i>
              </div>
            </div>
            <p class="text-emerald-100 text-sm mt-4"><?= date('F Y') ?></p>
          </div>

          <div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-xl text-white">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-purple-100 text-sm">Courses Purchased</p>
                <p class="text-3xl font-bold"><?= $paymentData['courses_purchased'] ?></p>
              </div>
              <div class="w-12 h-12 bg-purple-400 rounded-full flex items-center justify-center">
                <i class="fas fa-graduation-cap text-xl"></i>
              </div>
            </div>
            <p class="text-purple-100 text-sm mt-4">Completed purchases</p>
          </div>

          <div class="bg-gradient-to-br from-amber-500 to-amber-600 p-6 rounded-xl text-white">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-amber-100 text-sm">Avg. Course Price</p>
                <p class="text-3xl font-bold">₹<?= number_format($paymentData['avg_price'], 2) ?></p>
              </div>
              <div class="w-12 h-12 bg-amber-400 rounded-full flex items-center justify-center">
                <i class="fas fa-chart-bar text-xl"></i>
              </div>
            </div>
            <p class="text-amber-100 text-sm mt-4">Across completed purchases</p>
          </div>
        </div>

        <!-- Filters & search (non-functional placeholders but responsive) -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
          <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div class="flex flex-wrap gap-3">
              <select id="filterType" class="px-3 py-2 border rounded">
                <option value="">All types</option>
                <option value="purchase">Course purchases</option>
                <option value="transaction">Other transactions</option>
              </select>

              <select id="filterStatus" class="px-3 py-2 border rounded">
                <option value="">All statuses</option>
                <option>completed</option>
                <option>pending</option>
                <option>failed</option>
              </select>

              <select id="filterRange" class="px-3 py-2 border rounded">
                <option value="30">Last 30 days</option>
                <option value="90">Last 90 days</option>
                <option value="365">Last 12 months</option>
                <option value="all">All time</option>
              </select>
            </div>

            <div class="flex items-center gap-2">
              <div class="relative">
                <input id="searchBox" type="text" placeholder="Search..." class="pl-10 pr-3 py-2 border rounded w-72">
                <i class="fas fa-search absolute left-3 top-2.5 text-gray-400"></i>
              </div>
              <button id="applyFilters" class="px-3 py-2 bg-primary text-white rounded hover:bg-accent">Apply</button>
            </div>
          </div>
        </div>

        <!-- Combined transaction list -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
          <div class="px-6 py-4 border-b">
            <h3 class="text-lg font-semibold text-gray-800">Recent Activity</h3>
            <p class="text-sm text-gray-500 mt-1">Showing latest purchases & transactions</p>
          </div>

          <!-- Table for medium+ screens -->
          <div class="hidden md:block overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Activity</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Amount</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-100">
                <?php if (!empty($entries)): ?>
                  <?php foreach ($entries as $row): 
                      $isPurchase = ($row['type'] === 'purchase');
                      $title = $isPurchase ? ($row['title'] ?? 'Course') : ($row['gateway'] ?? 'Transaction');
                      $date = date('M d, Y', strtotime($row['dt'] ?? null));
                      $time = date('g:i A', strtotime($row['dt'] ?? null));
                      $amount = number_format((float)$row['amount'], 2);
                      $status = $row['status'] ?? 'unknown';
                      $statusClasses = [
                        'completed'=>'bg-green-100 text-green-800',
                        'pending'=>'bg-yellow-100 text-yellow-800',
                        'failed'=>'bg-red-100 text-red-800'
                      ];
                      $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
                  ?>
                    <tr class="hover:bg-gray-50">
                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center">
                          <div class="w-10 h-10 bg-primary rounded-lg flex items-center justify-center mr-3 text-white">
                            <i class="<?= $isPurchase ? 'fas fa-book-open' : 'fas fa-exchange-alt' ?>"></i>
                          </div>
                          <div>
                            <div class="text-sm font-medium text-gray-900"><?= h($title) ?></div>
                            <div class="text-xs text-gray-500"><?= $isPurchase ? 'Course Purchase' : 'Transaction' ?></div>
                          </div>
                        </div>
                      </td>

                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= h($date) ?><br><span class="text-xs text-gray-500"><?= h($time) ?></span>
                      </td>

                      <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm font-medium text-gray-900">₹<?= $amount ?></div>
                      </td>

                      <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        <?= $isPurchase ? 'Online (Course)' : h($row['gateway'] ?: '—') ?>
                      </td>

                      <td class="px-6 py-4 whitespace-nowrap">
                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full <?= $statusClass ?>"><?= h(ucfirst($status)) ?></span>
                      </td>

                      <td class="px-6 py-4 whitespace-nowrap text-sm">
                        <div class="flex items-center gap-2">
                          <?php if ($isPurchase): ?>
                            <a href="generate-invoice.php?purchase_id=<?= (int)$row['id'] ?>" class="text-primary hover:text-accent" title="Download invoice"><i class="fas fa-receipt"></i></a>
                          <?php else: ?>
                            <a href="generate-invoice.php?txn_id=<?= (int)$row['id'] ?>" class="text-primary hover:text-accent" title="Download receipt"><i class="fas fa-file-invoice"></i></a>
                          <?php endif; ?>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php else: ?>
                  <tr>
                    <td colspan="6" class="text-center py-12 text-gray-500">No activity found.</td>
                  </tr>
                <?php endif; ?>
              </tbody>
            </table>
          </div>

          <!-- Mobile cards -->
          <div class="md:hidden space-y-3 p-4">
            <?php if (!empty($entries)): ?>
              <?php foreach ($entries as $row):
                $isPurchase = ($row['type'] === 'purchase');
                $title = $isPurchase ? ($row['title'] ?? 'Course') : ($row['gateway'] ?? 'Transaction');
                $dateFull = date('M d, Y g:i A', strtotime($row['dt'] ?? null));
                $amount = number_format((float)$row['amount'], 2);
                $status = $row['status'] ?? 'unknown';
                $statusClasses = [
                  'completed'=>'bg-green-100 text-green-800',
                  'pending'=>'bg-yellow-100 text-yellow-800',
                  'failed'=>'bg-red-100 text-red-800'
                ];
                $statusClass = $statusClasses[$status] ?? 'bg-gray-100 text-gray-800';
              ?>
                <div class="bg-white rounded-lg p-4 shadow-sm">
                  <div class="flex items-start justify-between">
                    <div class="flex gap-3">
                      <div class="w-12 h-12 bg-primary rounded-lg flex items-center justify-center text-white">
                        <i class="<?= $isPurchase ? 'fas fa-book-open' : 'fas fa-exchange-alt' ?>"></i>
                      </div>
                      <div>
                        <div class="font-medium text-gray-900"><?= h($title) ?></div>
                        <div class="text-xs text-gray-500"><?= $isPurchase ? 'Course Purchase' : 'Transaction' ?></div>
                        <div class="text-xs text-gray-500 mt-1"><?= h($dateFull) ?></div>
                      </div>
                    </div>

                    <div class="text-right">
                      <div class="font-semibold text-gray-900">₹<?= $amount ?></div>
                      <div class="text-xs mt-2">
                        <span class="inline-flex px-2 py-1 rounded-full <?= $statusClass ?> text-xs font-semibold"><?= h(ucfirst($status)) ?></span>
                      </div>
                    </div>
                  </div>

                  <div class="mt-3 flex gap-2">
                    <?php if ($isPurchase): ?>
                      <a href="generate-invoice.php?purchase_id=<?= (int)$row['id'] ?>" class="px-3 py-2 border rounded text-sm text-primary"><i class="fas fa-receipt mr-2"></i>Invoice</a>
                    <?php else: ?>
                      <a href="generate-invoice.php?txn_id=<?= (int)$row['id'] ?>" class="px-3 py-2 border rounded text-sm text-primary"><i class="fas fa-file-invoice mr-2"></i>Receipt</a>
                    <?php endif; ?>
                  </div>
                </div>
              <?php endforeach; ?>
            <?php else: ?>
              <div class="text-center text-gray-500 py-8">No activity found.</div>
            <?php endif; ?>
          </div>

          <!-- Pagination -->
          <div class="px-6 py-4 border-t bg-gray-50">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Showing <span class="font-medium"><?= $total_records > 0 ? ($offset + 1) : 0 ?></span>
                to <span class="font-medium"><?= min($offset + $limit, $total_records) ?></span>
                of <span class="font-medium"><?= $total_records ?></span> transactions
              </div>

              <div class="flex items-center gap-2">
                <?php if ($page > 1): ?>
                  <a href="?page=<?= $page - 1 ?>" class="px-3 py-1 border rounded hover:bg-gray-100">Previous</a>
                <?php endif; ?>

                <?php
                // show at most 7 page links centered around current page
                $start = max(1, $page - 3);
                $end = min($total_pages, $page + 3);
                for ($i = $start; $i <= $end; $i++): ?>
                  <a href="?page=<?= $i ?>" class="px-3 py-1 text-sm <?= $i === $page ? 'bg-primary text-white rounded' : 'border rounded hover:bg-gray-100' ?>"><?= $i ?></a>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                  <a href="?page=<?= $page + 1 ?>" class="px-3 py-1 border rounded hover:bg-gray-100">Next</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
        </div>

        <!-- Billing snippet (show user email and placeholder address; editable in profile) -->
        <div class="mt-8 bg-white rounded-xl shadow-sm p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-800">Billing Information</h3>
            <a href="profile.php" class="text-primary hover:underline">Edit</a>
          </div>
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <p class="text-sm text-gray-600">Email</p>
              <p class="font-medium text-gray-800"><?= h($user['email'] ?? $user['email'] ?? '—') ?></p>
            </div>
            <div>
              <p class="text-sm text-gray-600">Default billing city</p>
              <p class="font-medium text-gray-800"><?= h($user['location'] ?? '—') ?></p>
            </div>
          </div>
        </div>

      </main>
    </div>
  </div>

  <script>
    // Basic client-side filter hooks (non-server)
    document.getElementById('applyFilters')?.addEventListener('click', function() {
      // currently a client-side placeholder (would need server integration to actually filter)
      alert('Filters are UI-only in this page. Server filtering can be implemented if you want.');
    });
  </script>
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
