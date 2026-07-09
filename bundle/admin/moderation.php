<?php
require_once "../db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if(!$admin_id){ header("Location: admin-login.php"); exit; }

// Stats
$pending = $pdo->query("SELECT COUNT(*) FROM moderation_queue WHERE status='pending'")->fetchColumn();
$approved_today = $pdo->query("SELECT COUNT(*) FROM moderation_queue WHERE status='approved' AND DATE(reviewed_at)=CURDATE()")->fetchColumn();
$flagged = $pdo->query("SELECT COUNT(*) FROM moderation_queue WHERE priority='high'")->fetchColumn();
$auto_rejected = $pdo->query("SELECT COUNT(*) FROM moderation_queue WHERE status='auto_rejected'")->fetchColumn();

$stmt = $pdo->query("
    SELECT mq.*, u.name AS submitter
    FROM moderation_queue mq
    LEFT JOIN users u ON mq.submitted_by = u.id
    ORDER BY mq.id DESC
");
$queue = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Content Moderation - Learning Platform</title>
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
                        primary: "#3B82F6",
                        "primary-dark": "#1E40AF"
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
            <header class="bg-white shadow-sm border-b sticky top-0 z-20">
                <div class="px-4 lg:px-6 py-4 flex flex-col lg:flex-row justify-between lg:items-center gap-3">
                    <h2 class="text-xl lg:text-2xl font-semibold text-gray-800">Content Moderation</h2>

                    <div class="flex flex-col sm:flex-row gap-2">
                        <button class="px-4 py-2 text-primary border border-primary rounded-lg hover:bg-primary hover:text-white transition">
                            Bulk Approve
                        </button>
                        <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700">
                            Bulk Reject
                        </button>
                    </div>
                </div>
            </header>

            <main class="p-4 lg:p-6">

                <!-- STATS -->
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">

                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">Pending</p>
                        <p class="text-2xl font-bold"><?= $pending ?></p>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">Approved Today</p>
                        <p class="text-2xl font-bold"><?= $approved_today ?></p>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">Flagged</p>
                        <p class="text-2xl font-bold"><?= $flagged ?></p>
                    </div>

                    <div class="bg-white p-4 rounded-lg shadow">
                        <p class="text-sm text-gray-500">Auto Rejected</p>
                        <p class="text-2xl font-bold"><?= $auto_rejected ?></p>
                    </div>

                </div>

                <!-- MODERATION QUEUE -->
                <div class="bg-white rounded-lg shadow mb-6">
                    
                    <div class="px-4 py-4 border-b flex flex-col lg:flex-row lg:justify-between gap-3">
                        <h3 class="text-lg font-semibold">Review Queue</h3>

                        <div class="flex flex-col sm:flex-row gap-2">
                            <select class="px-3 py-2 border rounded-lg">
                                <option>All Content</option>
                                <option>Courses</option>
                                <option>Lessons</option>
                            </select>

                            <select class="px-3 py-2 border rounded-lg">
                                <option>All Priority</option>
                                <option>High</option>
                                <option>Medium</option>
                            </select>
                        </div>
                    </div>

                    <div class="divide-y">

                        <?php foreach($queue as $item): ?>
                        <div class="p-4 space-y-3 <?= $item['priority']=='high' ? 'bg-red-50' : '' ?>">

                            <div class="flex items-start gap-4">

                                <input type="checkbox" class="mt-2 border-gray-300">

                                <div class="flex-1">

                                    <div class="flex flex-col lg:flex-row justify-between gap-2">
                                        <div class="flex items-center flex-wrap gap-2">

                                            <span class="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                                <?= ucfirst($item["content_type"]) ?>
                                            </span>

                                            <span class="px-2 py-1 bg-gray-200 text-gray-700 text-xs rounded-full">
                                                Priority: <?= ucfirst($item["priority"]) ?>
                                            </span>

                                            <span class="text-xs text-gray-500">
                                                <?= date("M d, H:i A", strtotime($item["created_at"])) ?>
                                            </span>

                                        </div>

                                        <div class="flex gap-2">
                                            <a href="moderation-approve.php?id=<?= $item['id'] ?>"
                                               class="px-3 py-1 bg-green-600 text-white text-sm rounded w-full sm:w-auto">
                                                Approve
                                            </a>

                                            <a href="moderation-reject.php?id=<?= $item['id'] ?>"
                                               class="px-3 py-1 bg-red-600 text-white text-sm rounded w-full sm:w-auto">
                                                Reject
                                            </a>
                                        </div>
                                    </div>

                                    <h4 class="text-lg font-medium"><?= $item['title'] ?></h4>

                                    <p class="text-gray-600 text-sm">
                                        <?= $item['reason'] ?>
                                    </p>

                                    <p class="text-xs text-gray-500">
                                        Submitted by: <b><?= $item['submitter'] ?: "System" ?></b>
                                    </p>

                                </div>
                            </div>

                        </div>
                        <?php endforeach; ?>

                    </div>
                </div>

                <!-- MODERATION POLICIES (Responsive) -->
                <div class="bg-white rounded-lg shadow p-4">

                    <h3 class="text-lg font-semibold mb-3">Moderation Policies</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

                        <div>
                            <h4 class="font-medium mb-2">Content Guidelines</h4>
                            <ul class="space-y-2 text-sm text-gray-600">
                                <li>✔ Educational only</li>
                                <li>✔ Licensed content</li>
                                <li>✔ Good quality</li>
                                <li>❌ No spam</li>
                                <li>❌ No offensive content</li>
                            </ul>
                        </div>

                        <div>
                            <h4 class="font-medium mb-2">Auto-Moderation</h4>

                            <div class="space-y-3">
                                <label><input type="checkbox" checked> Keyword filter</label><br>
                                <label><input type="checkbox" checked> New instructor approval</label><br>
                                <label><input type="checkbox"> Auto-approve trusted</label><br>
                                <label><input type="checkbox" checked> Detect duplicate</label>
                            </div>

                            <button class="mt-3 px-4 py-2 bg-primary text-white rounded">
                                Update Settings
                            </button>
                        </div>

                    </div>

                </div>

            </main>

        </div>
    </div>

</body>
</html>
