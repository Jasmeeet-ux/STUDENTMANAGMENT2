<?php
require_once "db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

// Fetch stats
$totalCourses = $pdo->query("SELECT COUNT(*) AS c FROM courses")->fetch()['c'];
$publishedCount = $totalCourses; // Adjust if you add status column in future
$draftCount = 0; 
$avgRating = 4.6; // You don't have rating system in DB now

// Fetch all courses
$stmt = $pdo->query("
    SELECT c.*, u.name AS instructor
    FROM courses c
    LEFT JOIN users u ON c.created_by = u.id
    ORDER BY c.id DESC
");
$courses = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management - Learning Platform</title>
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

        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="px-6 py-4 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Course Management</h2>
                <a href="course-new.php" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                    + Add New Course
                </a>
            </div>
        </header>

        <!-- Course Management Content -->
        <main class="p-6">

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">

                <!-- Total Courses -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Courses</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $totalCourses ?></p>
                        </div>
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                            <svg class="h-5 w-5 text-blue-600" fill="none" stroke="currentColor">
                                <path stroke-width="2" d="M12 6l8 4-8 4-8-4 8-4z"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Published -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Published</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $publishedCount ?></p>
                        </div>
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                            <svg class="h-5 w-5 text-green-600" fill="none" stroke="currentColor">
                                <path stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Draft -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Draft</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $draftCount ?></p>
                        </div>
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">
                            <svg class="h-5 w-5 text-yellow-600" fill="none" stroke="currentColor">
                                <path stroke-width="2" d="M4 7h16M4 12h16M4 17h16"/>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Avg Rating -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Avg Rating</p>
                            <p class="text-2xl font-bold text-gray-900"><?= $avgRating ?></p>
                        </div>
                        <div class="w-8 h-8 bg-purple-100 rounded-full flex items-center justify-center">
                            <svg class="h-5 w-5 text-purple-600" fill="none" stroke="currentColor">
                                <path stroke-width="2" d="M12 2l3.09 6L22 9l-5 4.9L18.18 22 12 18.2 5.82 22 7 13.9 2 9l6.91-1z"/>
                            </svg>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Courses Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">All Courses</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Course</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Instructor</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>

                        <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($courses as $c): ?>
                            <tr>
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="w-12 h-12 bg-blue-100 rounded-lg mr-4 flex items-center justify-center">
                                            <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor">
                                                <path stroke-width="2" d="M12 6l8 4-8 4-8-4 8-4z"/>
                                            </svg>
                                        </div>

                                        <div>
                                            <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($c['title']) ?></div>
                                            <div class="text-xs text-gray-400">Created: <?= date("M d, Y", strtotime($c['created_at'])) ?></div>
                                        </div>
                                    </div>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <?= htmlspecialchars($c['instructor'] ?? "Unknown") ?>
                                </td>

                                <td class="px-6 py-4 text-sm text-gray-900">
                                    ₹<?= number_format($c['price']) ?>
                                </td>

                                <td class="px-6 py-4 text-sm">
                                    <a href="course-edit.php?id=<?= $c['id'] ?>" class="text-primary hover:text-primary-dark mr-3">Edit</a>
                                    <a href="course-view.php?id=<?= $c['id'] ?>" class="text-gray-600 hover:text-gray-800 mr-3">View</a>
                                    <a href="course-delete.php?id=<?= $c['id'] ?>" onclick="return confirm('Delete this course?')" class="text-red-600 hover:text-red-900">Delete</a>
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
