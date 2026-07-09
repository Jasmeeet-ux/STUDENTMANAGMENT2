<?php
require "db.php";
require "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

$courses = $pdo->query("SELECT id, title FROM courses ORDER BY title ASC")->fetchAll();
$errors = [];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $course_id = intval($_POST['course_id']);
    $module_number = intval($_POST['module_number']);
    $title = trim($_POST['title']);
    $duration = trim($_POST['duration']);

    if (!$course_id) $errors[] = "Select a course.";
    if ($module_number <= 0) $errors[] = "Module number must be at least 1.";
    if ($title === "") $errors[] = "Module title is required.";

    if (empty($errors)) {
        $stmt = $pdo->prepare("
            INSERT INTO modules (course_id, module_number, title, duration, created_at)
            VALUES (?, ?, ?, ?, NOW())
        ");
        $stmt->execute([$course_id, $module_number, $title, $duration]);
        header("Location: modules.php");
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Add Module</title>
    <script src="https://cdn.tailwindcss.com"></script>
<script>
    function toggleSidebar() {
        document.getElementById("mobileSidebar").classList.toggle("hidden");
    }

    // Close sidebar when clicking outside (mobile)
    document.addEventListener("click", function (event) {
        const sidebar = document.getElementById("mobileSidebar");
        const toggleButton = event.target.closest("button");

        if (sidebar.classList.contains("hidden")) return;

        if (sidebar.contains(event.target)) return;
        if (toggleButton && toggleButton.getAttribute("onclick") === "toggleSidebar()") return;

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

    <!-- Sidebar -->
    <div id="mobileSidebar" class="hidden lg:block fixed lg:static top-0 left-0 w-64 h-full bg-white shadow-lg z-40">
        <?php include "includes/sidebar.php"; ?>
    </div>

    <div class="flex-1 p-6">

        <h2 class="text-2xl font-semibold mb-4">Add New Module</h2>

        <?php if ($errors): ?>
            <div class="p-3 bg-red-100 text-red-700 mb-4">
                <?php foreach ($errors as $e) echo "<div>$e</div>" ?>
            </div>
        <?php endif; ?>

        <form method="post" class="space-y-4 bg-white p-4 rounded-lg shadow">

            <div>
                <label class="block mb-1">Course *</label>
                <select name="course_id" class="border p-2 w-full">
                    <option value="">Select Course</option>
                    <?php foreach ($courses as $c): ?>
                        <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div>
                <label class="block mb-1">Module Number *</label>
                <input type="number" name="module_number" class="border p-2 w-full">
            </div>

            <div>
                <label class="block mb-1">Module Title *</label>
                <input type="text" name="title" class="border p-2 w-full">
            </div>

            <div>
                <label class="block mb-1">Duration (optional)</label>
                <input type="text" name="duration" class="border p-2 w-full">
            </div>

            <button class="px-4 py-2 bg-primary text-white rounded">Add Module</button>

        </form>

    </div>
</div>

</body>
</html>
