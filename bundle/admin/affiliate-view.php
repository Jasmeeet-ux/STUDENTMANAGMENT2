<?php
// admin/affiliate-view.php
require_once "../db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

// get affiliate user id
$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$user_id) {
    header("Location: affiliates.php");
    exit;
}

// fetch user & affiliate row
$stmt = $pdo->prepare("SELECT u.id,u.name,u.email,u.created_at,u.status, a.id AS affiliate_id, a.total_earnings, a.pending_payout
                       FROM users u
                       LEFT JOIN affiliates a ON a.user_id = u.id
                       WHERE u.id = ?");
$stmt->execute([$user_id]);
$affiliate = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$affiliate) {
    // no such user
    header("Location: affiliates.php");
    exit;
}

/* ---------------------------
   Handle payout POST action
   --------------------------- */
$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'pay_pending') {
    // re-fetch pending to avoid race
    $stmt = $pdo->prepare("SELECT pending_payout FROM affiliates WHERE user_id = ? FOR UPDATE");
    $pdo->beginTransaction();
    $stmt->execute([$user_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $pending = $row ? (float)$row['pending_payout'] : 0.0;

    if ($pending > 0) {
        // create a transaction record for the payout (negative amount to show payout)
        $txStmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, currency, gateway, status) VALUES (?, ?, 'INR', 'Affiliate Payout', 'completed')");
        $txStmt->execute([$user_id, -1 * $pending]);

        // update affiliates table: move pending to total_earnings (already included) and set pending to 0
        $upd = $pdo->prepare("UPDATE affiliates SET pending_payout = 0 WHERE user_id = ?");
        $upd->execute([$user_id]);

        $pdo->commit();
        $flash = "Pending payout of ₹" . number_format($pending,2) . " marked as paid.";
    } else {
        $pdo->rollBack();
        $flash = "No pending payout to process.";
    }

    // refresh affiliate row
    $stmt = $pdo->prepare("SELECT u.id,u.name,u.email,u.created_at,u.status, a.id AS affiliate_id, a.total_earnings, a.pending_payout
                           FROM users u
                           LEFT JOIN affiliates a ON a.user_id = u.id
                           WHERE u.id = ?");
    $stmt->execute([$user_id]);
    $affiliate = $stmt->fetch(PDO::FETCH_ASSOC);
}

/* ---------------------------
   Compute totals and lists
   --------------------------- */

// total sales generated via referrals (sum purchases.amount where purchaser is referred by this affiliate)
$salesStmt = $pdo->prepare("
    SELECT IFNULL(SUM(p.amount),0) AS total_sales
    FROM purchases p
    JOIN referrals r ON r.referred_user_id = p.user_id
    WHERE r.referrer_id = ? AND p.status = 'completed'
");
$salesStmt->execute([$user_id]);
$totalSales = (float)$salesStmt->fetchColumn();

// list referred users + their purchases (grouped)
$referredStmt = $pdo->prepare("
    SELECT u.id, u.name, u.email, u.created_at,
           IFNULL((
               SELECT SUM(p2.amount) FROM purchases p2 WHERE p2.user_id = u.id AND p2.status='completed'
           ),0) AS purchases_total,
           IFNULL((
               SELECT SUM(r2.commission) FROM referrals r2 WHERE r2.referred_user_id = u.id AND r2.referrer_id = ?
           ),0) AS commission_amount
    FROM referrals r
    JOIN users u ON u.id = r.referred_user_id
    WHERE r.referrer_id = ?
    ORDER BY r.created_at DESC
");
$referredStmt->execute([$user_id, $user_id]);
$referredUsers = $referredStmt->fetchAll(PDO::FETCH_ASSOC);

// referrals list (detailed)
$refListStmt = $pdo->prepare("
    SELECT r.id, r.referred_user_id, r.commission, r.created_at, u.name AS referred_name, u.email AS referred_email
    FROM referrals r
    JOIN users u ON u.id = r.referred_user_id
    WHERE r.referrer_id = ?
    ORDER BY r.created_at DESC
");
$refListStmt->execute([$user_id]);
$referrals = $refListStmt->fetchAll(PDO::FETCH_ASSOC);

// affiliate transactions (from transactions table)
$txStmt = $pdo->prepare("
    SELECT id, amount, currency, gateway, status, created_at
    FROM transactions
    WHERE user_id = ?
    ORDER BY created_at DESC
    LIMIT 50
");
$txStmt->execute([$user_id]);
$transactions = $txStmt->fetchAll(PDO::FETCH_ASSOC);

// small helper
function h($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Affiliate — <?= h($affiliate['name']) ?> — Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
   <script>
    function toggleSidebar() {
        document.getElementById("mobileSidebar").classList.toggle("hidden");
    }

    // Close sidebar when clicking outside (Mobile Only)
    document.addEventListener("click", function (event) {
        const sidebar = document.getElementById("mobileSidebar");
        const toggleButton = event.target.closest("button");

        // If sidebar is hidden, do nothing
        if (sidebar.classList.contains("hidden")) return;

        // If clicked inside sidebar, ignore
        if (sidebar.contains(event.target)) return;

        // If clicked on toggle button, ignore
        if (toggleButton && toggleButton.getAttribute("onclick") === "toggleSidebar()") return;

        // ELSE → Close sidebar
        sidebar.classList.add("hidden");
    });
</script>

    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#3B82F6',
                        'primary-dark': '#1E40AF'
                    }
                }
            }
        }
    </script>
</head>

<body class="bg-gray-50">
   
<!-- Mobile Top Bar -->
    <div class="lg:hidden flex justify-between items-center px-4 py-3 bg-white shadow-sm border-b">
        <h1 class="text-lg font-semibold">Admin Panel</h1>
        <button onclick="toggleSidebar()" class="text-2xl">☰</button>
    </div>

    <div class="flex h-screen">

        <!-- SIDEBAR (Mobile + Desktop) -->
        <div id="mobileSidebar" class="hidden lg:block fixed lg:static top-0 left-0 w-64 h-full bg-white shadow-lg z-40">
            <?php include "includes/sidebar.php"; ?>
        </div>
    <div class="flex-1 overflow-auto">
      <!-- topbar -->
      <header class="bg-white shadow-sm border-b">
        <div class="px-6 py-4 flex justify-between items-center">
          <div>
            <a href="affiliates.php" class="text-sm text-gray-600 hover:underline">← Back to Affiliates</a>
            <h1 class="text-2xl font-semibold text-gray-800 mt-1">Affiliate: <?= h($affiliate['name']) ?></h1>
            <div class="text-sm text-gray-500 mt-1">Email: <?= h($affiliate['email']) ?> • Joined <?= date("M d, Y", strtotime($affiliate['created_at'])) ?></div>
          </div>

          <div class="flex items-center space-x-4">
            <div class="text-right">
              <div class="text-xs text-gray-500">Total Sales</div>
              <div class="font-semibold text-lg">₹<?= number_format($totalSales,2) ?></div>
            </div>

            <div class="text-right">
              <div class="text-xs text-gray-500">Pending Payout</div>
              <div class="font-semibold text-lg text-yellow-600">₹<?= number_format((float)$affiliate['pending_payout'],2) ?></div>
            </div>

            <div class="text-right">
              <div class="text-xs text-gray-500">Total Earnings</div>
              <div class="font-semibold text-lg text-green-700">₹<?= number_format((float)$affiliate['total_earnings'],2) ?></div>
            </div>

            <div>
              <form method="post" onsubmit="return confirm('Mark pending payout as paid?');">
                <input type="hidden" name="action" value="pay_pending" />
                <button type="submit" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                  Mark Payout Completed
                </button>
              </form>
            </div>
          </div>
        </div>
      </header>

      <main class="p-6 space-y-6">
        <?php if($flash): ?>
          <div class="bg-green-100 text-green-800 px-4 py-2 rounded"><?= h($flash) ?></div>
        <?php endif; ?>

        <!-- Referred users & summary -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          <div class="lg:col-span-2 bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-3">Referred Users</h3>
            <?php if(count($referredUsers) === 0): ?>
              <div class="text-sm text-gray-500">No referred users yet.</div>
            <?php else: ?>
              <div class="divide-y">
                <?php foreach($referredUsers as $ru): ?>
                  <div class="py-3 flex justify-between items-center">
                    <div>
                      <div class="font-medium"><?= h($ru['name']) ?> <span class="text-xs text-gray-400">#<?= h($ru['id']) ?></span></div>
                      <div class="text-sm text-gray-500"><?= h($ru['email']) ?></div>
                      <div class="text-xs text-gray-400 mt-1">Joined <?= date("M d, Y", strtotime($ru['created_at'])) ?></div>
                    </div>
                    <div class="text-right">
                      <div class="text-sm">Purchases: <span class="font-semibold">₹<?= number_format((float)$ru['purchases_total'],2) ?></span></div>
                      <div class="text-sm text-gray-600">Commission: ₹<?= number_format((float)$ru['commission_amount'],2) ?></div>
                    </div>
                  </div>
                <?php endforeach; ?>
              </div>
            <?php endif; ?>
          </div>

          <aside class="bg-white rounded-lg shadow p-4">
            <h3 class="text-lg font-semibold mb-3">Affiliate Snapshot</h3>
            <div class="space-y-3 text-sm text-gray-700">
              <div><span class="font-medium">Name:</span> <?= h($affiliate['name']) ?></div>
              <div><span class="font-medium">Email:</span> <?= h($affiliate['email']) ?></div>
              <div><span class="font-medium">Status:</span> <?= h($affiliate['status']) ?></div>
              <div><span class="font-medium">Total Sales:</span> ₹<?= number_format($totalSales,2) ?></div>
              <div><span class="font-medium">Pending Payout:</span> ₹<?= number_format((float)$affiliate['pending_payout'],2) ?></div>
              <div><span class="font-medium">Total Earnings:</span> ₹<?= number_format((float)$affiliate['total_earnings'],2) ?></div>
            </div>
          </aside>
        </div>

        <!-- Referrals table -->
        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex justify-between items-center mb-3">
            <h3 class="text-lg font-semibold">Referrals</h3>
            <div class="text-sm text-gray-500">Showing recent referrals</div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Date</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Referred User</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Commission</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($referrals)): ?>
                  <tr><td class="p-4 text-sm text-gray-500" colspan="4">No referrals recorded.</td></tr>
                <?php else: ?>
                  <?php foreach($referrals as $r): ?>
                    <tr class="border-t">
                      <td class="px-4 py-3 text-sm text-gray-700"><?= date("M d, Y", strtotime($r['created_at'])) ?></td>
                      <td class="px-4 py-3 text-sm">
                        <div class="font-medium"><?= h($r['referred_name']) ?></div>
                        <div class="text-xs text-gray-500"><?= h($r['referred_email']) ?></div>
                      </td>
                      <td class="px-4 py-3 text-sm">₹<?= number_format((float)$r['commission'],2) ?></td>
                      <td class="px-4 py-3 text-sm">
                        <a href="../user-view.php?id=<?= (int)$r['referred_user_id'] ?>" class="text-primary mr-3">View User</a>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Transactions -->
        <div class="bg-white rounded-lg shadow p-4">
          <div class="flex justify-between items-center mb-3">
            <h3 class="text-lg font-semibold">Transactions (Last 50)</h3>
            <div class="text-sm text-gray-500">Includes payouts & other transactions</div>
          </div>

          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Date</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Amount</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Gateway</th>
                  <th class="px-4 py-2 text-left text-xs font-semibold text-gray-600">Status</th>
                </tr>
              </thead>
              <tbody>
                <?php if(empty($transactions)): ?>
                  <tr><td class="p-4 text-sm text-gray-500" colspan="4">No transactions found.</td></tr>
                <?php else: ?>
                  <?php foreach($transactions as $t): ?>
                    <tr class="border-t">
                      <td class="px-4 py-3 text-sm"><?= date("M d, Y H:i", strtotime($t['created_at'])) ?></td>
                      <td class="px-4 py-3 text-sm"><?= number_format($t['amount'],2) ?> <?= h($t['currency']) ?></td>
                      <td class="px-4 py-3 text-sm"><?= h($t['gateway']) ?></td>
                      <td class="px-4 py-3 text-sm"><?= h($t['status']) ?></td>
                    </tr>
                  <?php endforeach; ?>
                <?php endif; ?>
              </tbody>
            </table>
          </div>
        </div>

      </main>
    </div>
  </div>
</body>
</html>
