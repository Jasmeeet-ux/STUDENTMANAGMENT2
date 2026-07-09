<?php
require_once "db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

// Fetch all courses
$courses = $pdo->query("SELECT id, title FROM courses ORDER BY title ASC")->fetchAll();

// Load modules with course name
$stmt = $pdo->query("
    SELECT m.*, c.title AS course_title
    FROM modules m
    LEFT JOIN courses c ON m.course_id = c.id
    ORDER BY m.course_id ASC, m.module_number ASC
");
$modules = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Modules</title>

    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        function toggleSidebar() {
            document.getElementById("mobileSidebar").classList.toggle("hidden");
        }
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

<div class="flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <div id="mobileSidebar" class="hidden lg:block fixed lg:static top-0 left-0 w-64 h-full bg-white shadow-lg z-40 overflow-y-auto">
        <?php include "includes/sidebar.php"; ?>
    </div>

    <!-- Main Content -->
    <div class="flex-1 p-4 sm:p-6 overflow-y-auto">

        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3 mb-4">
            <h2 class="text-xl sm:text-2xl font-semibold">Modules Management</h2>

            <a href="module-add.php" class="px-4 py-2 bg-primary text-white rounded-lg text-center">
                + Add Module
            </a>
        </div>

        <div class="bg-white rounded-lg shadow p-3 sm:p-4">

            <!-- 🚀 Mobile Scrollable Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-sm sm:text-base min-w-[600px]">
                    <thead>
                        <tr class="bg-gray-100 text-left">
                            <th class="p-2">Course</th>
                            <th class="p-2">Module Number</th>
                            <th class="p-2">Title</th>
                            <th class="p-2">Actions</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php if (empty($modules)): ?>
                            <tr>
                                <td colspan="4" class="p-4 text-center text-gray-500">
                                    No modules found.
                                </td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($modules as $m): ?>
                            <tr class="border-b">
                                <td class="p-2"><?= htmlspecialchars($m['course_title']) ?></td>
                                <td class="p-2">Chapter <?= $m['module_number'] ?></td>
                                <td class="p-2"><?= htmlspecialchars($m['title']) ?></td>
                                <td class="p-2">
                                    <a href="module-edit.php?id=<?= $m['id'] ?>" class="text-blue-600">Edit</a> |
                                    <a href="module-delete.php?id=<?= $m['id'] ?>" class="text-red-600"
                                       onclick="return confirm('Delete this module? Lessons linked to it will break!')">
                                       Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>

                    </tbody>
                </table>
            </div>

        </div>

    </div>
</div>

</body>
</html>
