<?php
require_once "../db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

/* -------------------------
   FETCH AFFILIATE STATS
---------------------------- */

// Total affiliates
$totalAffiliates = $pdo->query("SELECT COUNT(*) FROM affiliates")->fetchColumn();

// Total commissions earned
$totalCommission = $pdo->query("SELECT SUM(total_earnings) FROM affiliates")->fetchColumn();
$totalCommission = $totalCommission ?: 0;

// Total pending payouts
$totalPending = $pdo->query("SELECT SUM(pending_payout) FROM affiliates")->fetchColumn();
$totalPending = $totalPending ?: 0;

// Pending approvals (users who have 'pending' status)
$pendingApprovals = $pdo->query("SELECT COUNT(*) FROM users WHERE status='pending'")->fetchColumn();


/* -------------------------
   FETCH AFFILIATE DETAILS
---------------------------- */

$sql = "
    SELECT 
        a.id AS affiliate_id,
        u.id AS user_id,
        u.name,
        u.email,
        u.created_at,
        a.total_earnings,
        a.pending_payout,
        IFNULL((
            SELECT SUM(amount)
            FROM purchases
            WHERE user_id IN (
                SELECT referred_user_id 
                FROM referrals 
                WHERE referrer_id = u.id
            ) AND status='completed'
        ), 0) AS total_sales,
        (SELECT commission FROM referrals WHERE referrer_id = u.id ORDER BY id DESC LIMIT 1) AS commission_rate,
        (SELECT created_at FROM referrals WHERE referrer_id = u.id ORDER BY id DESC LIMIT 1) AS last_sale_date
    FROM affiliates a
    INNER JOIN users u ON a.user_id = u.id
    ORDER BY a.id DESC
";

$stmt = $pdo->query($sql);
$affiliates = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliate Management - Admin Panel</title>

    <!-- ✅ FIX 1: Tailwind CDN added -->
    <script src="https://cdn.tailwindcss.com"></script>

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

        <!-- MAIN CONTENT -->
        <div class="flex-1 overflow-auto">

            <!-- TOP BAR -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4 flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-800">Affiliate Management</h2>

                    <div class="flex items-center space-x-3">
                        <button class="px-4 py-2 text-primary border border-primary rounded-lg hover:bg-primary hover:text-white">
                            Bulk Payout
                        </button>
                        <button class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                            + Invite Affiliate
                        </button>
                    </div>
                </div>
            </header>

            <!-- PAGE CONTENT -->
            <main class="p-6">

                <!-- STATS -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    
                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm text-gray-500">Active Affiliates</p>
                        <p class="text-2xl font-bold"><?= $totalAffiliates ?></p>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm text-gray-500">Total Commissions</p>
                        <p class="text-2xl font-bold">₹<?= number_format($totalCommission,2) ?></p>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm text-gray-500">Pending Payouts</p>
                        <p class="text-2xl font-bold">₹<?= number_format($totalPending,2) ?></p>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <p class="text-sm text-gray-500">Pending Approvals</p>
                        <p class="text-2xl font-bold"><?= $pendingApprovals ?></p>
                    </div>

                </div>

                <!-- AFFILIATES TABLE -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-800">Affiliate Partners</h3>

                        <select class="px-4 py-2 border rounded-lg">
                            <option>All Status</option>
                            <option>Active</option>
                            <option>Pending</option>
                            <option>Suspended</option>
                        </select>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold">Affiliate</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold">Commission Rate</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold">Total Sales</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold">Earnings</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold">Last Sale</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold">Actions</th>
                            </tr>
                            </thead>

                            <tbody class="divide-y">
                            <?php foreach ($affiliates as $a): ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-primary text-white flex items-center justify-center rounded-full">
                                                <?= strtoupper(substr($a['name'],0,2)) ?>
                                            </div>
                                            <div class="ml-3">
                                                <p class="font-medium text-gray-900"><?= htmlspecialchars($a['name']) ?></p>
                                                <p class="text-gray-500 text-sm"><?= htmlspecialchars($a['email']) ?></p>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 text-sm">
                                        <?= $a['commission_rate'] ? $a['commission_rate']."%" : "—" ?>
                                    </td>

                                    <td class="px-6 py-4 text-sm">
                                        ₹<?= number_format($a['total_sales'],2) ?>
                                    </td>

                                    <td class="px-6 py-4 text-sm font-semibold text-green-700">
                                        ₹<?= number_format($a['total_earnings'],2) ?>
                                    </td>

                                    <td class="px-6 py-4 text-sm">
                                        <?= $a['last_sale_date'] ? date("M d, Y", strtotime($a['last_sale_date'])) : "—" ?>
                                    </td>

                                    <td class="px-6 py-4 text-sm">
                                        <a href="affiliate-view.php?id=<?= $a['user_id'] ?>" class="text-primary mr-3">View</a>
                                        <a href="affiliate-payout.php?id=<?= $a['user_id'] ?>" class="text-green-600 mr-3">Payout</a>
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

    <!-- ✅ FIX 2: Scripts moved to bottom of body so DOM is ready -->
    <script>
    function toggleSidebar() {
        document.getElementById("mobileSidebar").classList.toggle("hidden");
    }

    document.addEventListener("click", function (event) {
        const sidebar = document.getElementById("mobileSidebar");
        const toggleButton = event.target.closest("button");

        if (sidebar.classList.contains("hidden")) return;
        if (sidebar.contains(event.target)) return;
        if (toggleButton && toggleButton.getAttribute("onclick") === "toggleSidebar()") return;

        sidebar.classList.add("hidden");
    });
    </script>

</body>
</html>