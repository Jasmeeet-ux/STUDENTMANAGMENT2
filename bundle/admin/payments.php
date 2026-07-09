<?php
require_once "db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

// Earnings
$total_revenue = $pdo->query("
    SELECT SUM(amount) FROM purchases WHERE status='completed'
")->fetchColumn() ?: 0;

// Pending payouts (affiliates)
$pending_payouts = $pdo->query("
    SELECT SUM(pending_payout) FROM affiliates
")->fetchColumn() ?: 0;

// Refunds
$total_refunds = $pdo->query("
    SELECT SUM(amount) FROM transactions WHERE amount < 0
")->fetchColumn() ?: 0;

// Recent transactions
$transactions = $pdo->query("
    SELECT t.*, u.name, u.email, p.course_id, c.title AS course_title
    FROM transactions t
    LEFT JOIN users u ON u.id = t.user_id
    LEFT JOIN purchases p ON p.payment_id = t.gateway
    LEFT JOIN courses c ON c.id = p.course_id
    ORDER BY t.id DESC LIMIT 10
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Management - Learning Platform</title>
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

    <!-- Main Content -->
    <div class="flex-1 overflow-auto">

        <!-- Header / TOP BAR -->
        <header class="bg-white shadow-sm border-b">
            <div class="px-6 py-4 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Payment Management</h2>
                <div class="flex space-x-3">
                    <button class="px-4 py-2 text-primary border border-primary rounded-lg hover:bg-primary hover:text-white">
                        Export Report
                    </button>
                    <a href="process-payout.php" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                        Process Refund
    </a>
                </div>
            </div>
        </header>

        <!-- Main Content Section -->
        <main class="p-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <!-- Total Revenue -->
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">Total Revenue</p>
                    <p class="text-2xl font-bold text-gray-900">₹<?= number_format($total_revenue) ?></p>
                    <div class="mt-2 text-green-600 text-sm">+12% from last month</div>
                </div>

                <!-- Pending Payouts -->
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">Pending Payouts</p>
                    <p class="text-2xl font-bold text-gray-900">₹<?= number_format($pending_payouts) ?></p>
                    <div class="mt-2 text-yellow-600 text-sm">Affiliates & instructors</div>
                </div>

                <!-- Refunds -->
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">Refunds Processed</p>
                    <p class="text-2xl font-bold text-gray-900">₹<?= number_format(abs($total_refunds)) ?></p>
                </div>

                <!-- Fees -->
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-sm text-gray-500">Transaction Fees</p>
                    <p class="text-2xl font-bold text-gray-900">₹1,450</p>
                </div>
            </div>

            <!-- Payment Gateway Settings -->
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">Payment Gateway Settings</h3>
                </div>
                <div class="p-6 grid grid-cols-1 md:grid-cols-3 gap-6">

                    <!-- Stripe -->
                    <div class="border p-4 rounded-lg">
                        <div class="flex justify-between mb-3">
                            <h4 class="font-medium">Stripe</h4>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Active</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Primary payment processor</p>
                        <button class="w-full border border-primary text-primary px-4 py-2 rounded hover:bg-primary hover:text-white">Configure</button>
                    </div>

                    <!-- PayPal -->
                    <div class="border p-4 rounded-lg">
                        <div class="flex justify-between mb-3">
                            <h4 class="font-medium">PayPal</h4>
                            <span class="px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">Active</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Alternative payment method</p>
                        <button class="w-full border border-primary text-primary px-4 py-2 rounded hover:bg-primary hover:text-white">Configure</button>
                    </div>

                    <!-- Bank Transfer -->
                    <div class="border p-4 rounded-lg">
                        <div class="flex justify-between mb-3">
                            <h4 class="font-medium">Bank Transfer</h4>
                            <span class="px-2 py-1 bg-gray-100 text-gray-800 text-xs rounded-full">Inactive</span>
                        </div>
                        <p class="text-sm text-gray-600 mb-4">Manual settlements</p>
                        <button class="w-full border text-gray-600 px-4 py-2 rounded hover:bg-gray-50">Enable</button>
                    </div>
                </div>
            </div>

            <!-- Recent Transactions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold">Recent Transactions</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                                <th class="px-6 py-3 text-left text-xs text-gray-500 uppercase">Type</th>
                                <th class="px-6 py-3 text-left text-xs text-gray-500 uppercase">Amount</th>
                                <th class="px-6 py-3 text-left text-xs text-gray-500 uppercase">Method</th>
                                <th class="px-6 py-3 text-left text-xs text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y">

                            <?php foreach ($transactions as $t): ?>
                            <tr>
                                <td class="px-6 py-3">
                                    <div class="font-medium"><?= htmlspecialchars($t['name']) ?></div>
                                    <div class="text-sm text-gray-500"><?= $t['email'] ?></div>
                                </td>

                                <td class="px-6 py-3">
                                    <?php if ($t['amount'] > 0): ?>
                                        <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Purchase</span>
                                    <?php else: ?>
                                        <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Payout</span>
                                    <?php endif; ?>
                                </td>

                                <td class="px-6 py-3 font-semibold 
                                    <?= ($t['amount'] >= 0 ? 'text-green-600' : 'text-red-600') ?>">
                                    <?= $t['amount'] >= 0 ? "+₹".$t['amount'] : "-₹".abs($t['amount']) ?>
                                </td>

                                <td class="px-6 py-3"><?= $t['gateway'] ?></td>

                                <td class="px-6 py-3"><?= date("M d, Y", strtotime($t['created_at'])) ?></td>

                                <td class="px-6 py-3">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        <?= $t['status']=='completed'?'bg-green-100 text-green-800':'' ?>
                                        <?= $t['status']=='pending'?'bg-yellow-100 text-yellow-800':'' ?>
                                        <?= $t['status']=='failed'?'bg-red-100 text-red-800':'' ?>">
                                        <?= ucfirst($t['status']) ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>

                        </tbody>
                    </table>
                </div>

            </div>

        </main>
    </div>
</div>
</body>
</html>
