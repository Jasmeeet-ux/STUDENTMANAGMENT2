<?php
require_once "db.php";
require_once "admin-auth.php";  // already validates session

// Fetch Stats
$totalUsers      = $pdo->query("SELECT COUNT(*) AS c FROM users")->fetch()['c'];
$totalRevenue    = $pdo->query("SELECT SUM(amount) AS amt FROM purchases WHERE status='completed'")->fetch()['amt'] ?? 0;
$totalCourses    = $pdo->query("SELECT COUNT(*) AS c FROM courses")->fetch()['c'];
$totalAffiliates = $pdo->query("SELECT COUNT(*) AS c FROM affiliates")->fetch()['c'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Learning Platform</title>
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

            <!-- Header (TOPBAR KEPT - NOT REMOVED) -->
            <header class="bg-white shadow-sm border-b">
                <div class="px-6 py-4 flex justify-between items-center">
                    <h2 class="text-2xl font-semibold text-gray-800">Dashboard</h2>

                    <div class="flex items-center space-x-4">

                        <!-- Notification Icon -->
                        <button class="p-2 text-gray-500 hover:text-gray-700">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" stroke="currentColor">
                                <path stroke-width="2" d="M15 17h4l-2-3V9a6 6 0 10-12 0v5L5 17h4m3 0v1a3 3 0 006 0v-1" />
                            </svg>
                        </button>

                        <!-- Profile Initial -->
                        <div class="w-8 h-8 bg-primary rounded-full flex items-center justify-center text-white font-semibold">
                            <?= strtoupper($_SESSION['admin_name'][0] ?? "A") ?>
                        </div>

                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <main class="p-6">

                <!-- Stats Cards (Dynamic with SVG icons) -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                    <!-- Total Users -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Total Users</p>
                                <p class="text-3xl font-bold text-gray-900"><?= $totalUsers ?></p>
                            </div>
                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">

                                <!-- USERS ICON -->
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor">
                                    <circle cx="12" cy="7" r="4" stroke-width="2"/>
                                    <path d="M6 21c0-4 3-7 6-7s6 3 6 7" stroke-width="2"/>
                                </svg>

                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-sm text-green-600">+12% from last month</span>
                        </div>
                    </div>

                    <!-- Revenue -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Revenue</p>
                                <p class="text-3xl font-bold text-gray-900">₹<?= number_format($totalRevenue) ?></p>
                            </div>
                            <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">

                                <!-- MONEY ICON -->
                                <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor">
                                    <path d="M12 8c-2 0-4 1-4 3s2 3 4 3 4 1 4 3-2 3-4 3" stroke-width="2"/>
                                    <path d="M12 4v2m0 12v2" stroke-width="2"/>
                                </svg>

                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-sm text-green-600">+8% from last month</span>
                        </div>
                    </div>

                    <!-- Active Courses -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Active Courses</p>
                                <p class="text-3xl font-bold text-gray-900"><?= $totalCourses ?></p>
                            </div>
                            <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">

                                <!-- BOOK ICON -->
                                <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor">
                                    <path d="M12 6l8 4-8 4-8-4 8-4z" stroke-width="2" />
                                </svg>

                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-sm text-green-600">+5 new this week</span>
                        </div>
                    </div>

                    <!-- Affiliates -->
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Affiliates</p>
                                <p class="text-3xl font-bold text-gray-900"><?= $totalAffiliates ?></p>
                            </div>
                            <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">

                                <!-- AFFILIATE ICON -->
                                <svg class="h-6 w-6 text-orange-600" fill="none" stroke="currentColor">
                                    <path d="M7 8l5-5 5 5m-10 8l5 5 5-5" stroke-width="2"/>
                                </svg>

                            </div>
                        </div>
                        <div class="mt-4">
                            <span class="text-sm text-green-600">+3 pending approval</span>
                        </div>
                    </div>

                </div>

                <!-- Quick Actions (FULL CONTENT KEPT) -->
                <div class="bg-white rounded-lg shadow mb-8">
                    <div class="px-6 py-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Quick Actions</h3>
                    </div>
                    <div class="p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                            <!-- Add Course -->
                            <a href="courses.php" class="p-4 border border-gray-200 rounded-lg hover:bg-blue-50 transition-colors">
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-blue-100 rounded-lg mx-auto mb-2 flex items-center justify-center">

                                        <!-- PLUS ICON -->
                                        <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor">
                                            <path stroke-width="2" d="M12 5v14m7-7H5" />
                                        </svg>

                                    </div>
                                    <p class="font-medium text-gray-800">Add Course</p>
                                </div>
                            </a>

                            <!-- Manage Users -->
                            <a href="users.php" class="p-4 border border-gray-200 rounded-lg hover:bg-blue-50 transition-colors">
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-green-100 rounded-lg mx-auto mb-2 flex items-center justify-center">

                                        <!-- USER ICON -->
                                        <svg class="h-6 w-6 text-green-600" fill="none" stroke="currentColor">
                                            <circle cx="12" cy="7" r="4" stroke-width="2"/>
                                            <path d="M6 21c0-4 3-7 6-7s6 3 6 7" stroke-width="2"/>
                                        </svg>

                                    </div>
                                    <p class="font-medium text-gray-800">Manage Users</p>
                                </div>
                            </a>

                            <!-- Review Content -->
                            <a href="moderation.php" class="p-4 border border-gray-200 rounded-lg hover:bg-blue-50 transition-colors">
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-yellow-100 rounded-lg mx-auto mb-2 flex items-center justify-center">

                                        <!-- SEARCH ICON -->
                                        <svg class="h-6 w-6 text-yellow-600" fill="none" stroke="currentColor">
                                            <circle cx="11" cy="11" r="7" stroke-width="2"/>
                                            <path d="M20 20l-4.35-4.35" stroke-width="2"/>
                                        </svg>

                                    </div>
                                    <p class="font-medium text-gray-800">Review Content</p>
                                </div>
                            </a>

                            <!-- View Payments -->
                            <a href="payments.php" class="p-4 border border-gray-200 rounded-lg hover:bg-blue-50 transition-colors">
                                <div class="text-center">
                                    <div class="w-12 h-12 bg-purple-100 rounded-lg mx-auto mb-2 flex items-center justify-center">

                                        <!-- CREDIT CARD ICON -->
                                        <svg class="h-6 w-6 text-purple-600" fill="none" stroke="currentColor">
                                            <rect x="3" y="7" width="18" height="10" rx="2" stroke-width="2"/>
                                        </svg>

                                    </div>
                                    <p class="font-medium text-gray-800">View Payments</p>
                                </div>
                            </a>

                        </div>
                    </div>
                </div>

                <!-- Recent Activities (FULL CONTENT KEPT) -->
                <div class="bg-white rounded-lg shadow">
                    <div class="px-6 py-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Recent Activities</h3>
                    </div>

                    <div class="p-6">
                        <div class="space-y-4">

                            <!-- Item 1 -->
                            <div class="flex items-center space-x-4">
                                <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center text-green-600">

                                    <!-- CHECK ICON -->
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor">
                                        <path stroke-width="2" d="M5 13l4 4L19 7" />
                                    </svg>

                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-800">
                                        New course "React Fundamentals" approved
                                    </p>
                                    <p class="text-xs text-gray-500">2 minutes ago</p>
                                </div>
                            </div>

                            <!-- Item 2 -->
                            <div class="flex items-center space-x-4">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center text-blue-600">

                                    <!-- USER ICON -->
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor">
                                        <circle cx="12" cy="7" r="4" stroke-width="2"/>
                                        <path d="M6 21c0-4 3-7 6-7s6 3 6 7" stroke-width="2"/>
                                    </svg>

                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-800">
                                        User "John Doe" enrolled in 3 courses
                                    </p>
                                    <p class="text-xs text-gray-500">5 minutes ago</p>
                                </div>
                            </div>

                            <!-- Item 3 -->
                            <div class="flex items-center space-x-4">
                                <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center text-yellow-600">

                                    <!-- MONEY ICON -->
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor">
                                        <path d="M12 8c-2 0-4 1-4 3s2 3 4 3 4 1 4 3-2 3-4 3" stroke-width="2"/>
                                        <path d="M12 4v2m0 12v2" stroke-width="2"/>
                                    </svg>

                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-800">
                                        Affiliate payout of ₹2,450 processed
                                    </p>
                                    <p class="text-xs text-gray-500">10 minutes ago</p>
                                </div>
                            </div>

                            <!-- Item 4 -->
                            <div class="flex items-center space-x-4">
                                <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center text-red-600">

                                    <!-- WARNING ICON -->
                                    <svg class="h-5 w-5" fill="none" stroke="currentColor">
                                        <path stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z" />
                                    </svg>

                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-gray-800">
                                        Content flagged for review
                                    </p>
                                    <p class="text-xs text-gray-500">15 minutes ago</p>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

            </main>

        </div>
    </div>
</body>
</html>
