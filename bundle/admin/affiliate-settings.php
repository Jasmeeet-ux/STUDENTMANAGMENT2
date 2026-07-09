<?php
require_once "../db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

// Save settings
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $default = $_POST['default_rate'];
    $premium = $_POST['premium_rate'];
    $vip = $_POST['vip_rate'];

    file_put_contents("affiliate-config.json", json_encode([
        "default" => $default,
        "premium" => $premium,
        "vip" => $vip
    ]));

    $success = "Commission settings updated!";
}

// Load settings
$config = ["default" => 20, "premium" => 25, "vip" => 30];

if (file_exists("affiliate-config.json")) {
    $config = json_decode(file_get_contents("affiliate-config.json"), true);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliate Settings</title>
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

    <!-- Main -->
    <div class="flex-1 overflow-auto">

        <!-- Top Bar -->
        <header class="bg-white shadow-sm border-b">
            <div class="px-6 py-4 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Affiliate Settings</h2>
                <a href="affiliate-payout.php" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                    Back
                </a>
            </div>
        </header>

        <main class="p-6">

            <?php if (!empty($success)): ?>
                <div class="mb-4 p-4 bg-green-100 text-green-700 rounded">
                    <?= $success ?>
                </div>
            <?php endif; ?>

            <form method="POST" class="bg-white p-6 rounded-lg shadow max-w-2xl">

                <h3 class="text-lg font-semibold text-gray-800 mb-4">Commission Rates</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Default Rate</label>
                        <input name="default_rate" type="number" value="<?= $config['default'] ?>"
                               class="w-full px-4 py-2 border rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Premium Rate</label>
                        <input name="premium_rate" type="number" value="<?= $config['premium'] ?>"
                               class="w-full px-4 py-2 border rounded-lg">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">VIP Rate</label>
                        <input name="vip_rate" type="number" value="<?= $config['vip'] ?>"
                               class="w-full px-4 py-2 border rounded-lg">
                    </div>

                </div>

                <button type="submit"
                        class="mt-6 px-6 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                    Save Settings
                </button>

            </form>

        </main>

    </div>

</div>
</body>
</html>
