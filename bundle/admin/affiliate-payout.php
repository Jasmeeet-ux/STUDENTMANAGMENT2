<?php
require_once "../db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

// Fetch pending payout affiliates
$stmt = $pdo->prepare("
    SELECT a.*, u.name, u.email 
    FROM affiliates a
    JOIN users u ON u.id = a.user_id
    WHERE a.pending_payout > 0
    ORDER BY a.pending_payout DESC
");
$stmt->execute();
$pending = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliate Payouts</title>
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

        <!-- Top Bar -->
        <header class="bg-white shadow-sm border-b">
            <div class="px-6 py-4 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Affiliate Payouts</h2>
                <a href="affiliate-settings.php" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                    Settings
                </a>
            </div>
        </header>

        <main class="p-6">

            <!-- Payout Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-800">Pending Payouts</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Affiliate</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pending Payout</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Earnings</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                        </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">

                        <?php if (count($pending) === 0): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                    No pending payouts.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($pending as $row): ?>
                            <tr>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= htmlspecialchars($row['name']) ?>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= htmlspecialchars($row['email']) ?>
                                </td>

                                <td class="px-6 py-4 text-sm text-green-600 font-semibold">
                                    ₹<?= number_format($row['pending_payout'], 2) ?>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-900">
                                    ₹<?= number_format($row['total_earnings'], 2) ?>
                                </td>

                                <td class="px-6 py-4 text-sm">
                                    <a href="process-payout.php?id=<?= $row['id'] ?>"
                                       class="text-primary hover:text-primary-dark">
                                        Payout Now
                                    </a>
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
