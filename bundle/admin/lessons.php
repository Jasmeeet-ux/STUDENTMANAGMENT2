<?php
require_once "db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

// Fetch course list for the filter dropdown
$coursesStmt = $pdo->query("
    SELECT id, title 
    FROM courses 
    ORDER BY title ASC
");
$coursesList = $coursesStmt->fetchAll();

// Filter by course
$filterCourse = (isset($_GET['course_id']) && is_numeric($_GET['course_id']))
                ? intval($_GET['course_id'])
                : null;

// Build main lessons query
$sql = "
    SELECT 
        l.*,
        c.title AS course_title,
        m.title AS module_title,
        m.module_number AS module_order
    FROM lessons l
    LEFT JOIN courses c ON l.course_id = c.id
    LEFT JOIN modules m ON l.module_id = m.id
";

$params = [];

// Apply filter
if ($filterCourse) {
    $sql .= " WHERE l.course_id = ?";
    $params[] = $filterCourse;
}

// Sorting priority:
// 1. Course
// 2. Module number
// 3. Lesson order_no (fallback to ID if null)
$sql .= "
    ORDER BY 
        l.course_id ASC,
        m.module_number ASC,
        COALESCE(l.order_no, l.id) ASC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$lessons = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Lesson Management</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
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

    <!-- MAIN CONTENT -->
    <div class="flex-1 overflow-auto">

        <!-- Header -->
        <header class="bg-white shadow-sm border-b">
            <div class="px-6 py-4 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">Lesson Management</h2>
                <a href="lesson-upload.php" class="px-4 py-2 bg-primary text-white rounded-lg">+ Upload Lesson</a>
            </div>
        </header>

        <main class="p-6">

            <!-- Filter -->
            <div class="bg-white rounded-lg shadow mb-6 p-4">
                <form method="get" class="flex flex-wrap gap-3 items-center">
                    <select name="course_id" class="px-4 py-2 border rounded">
                        <option value="">All Courses</option>

                        <?php foreach($coursesList as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= $filterCourse == $c['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($c['title']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <button class="px-4 py-2 bg-primary text-white rounded">Apply</button>
                    <a href="lessons.php" class="px-4 py-2 border rounded">Reset</a>
                </form>
            </div>

            <!-- Lessons Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">All Lessons</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left">Lesson</th>
                            <th class="px-4 py-2 text-left">Course</th>
                            <th class="px-4 py-2 text-left">Module</th>
                            <th class="px-4 py-2 text-left">Duration</th>
                            <th class="px-4 py-2 text-left">Type</th>
                            <th class="px-4 py-2 text-left">Preview</th>
                            <th class="px-4 py-2 text-left">Uploaded</th>
                            <th class="px-4 py-2 text-left">Actions</th>
                        </tr>
                        </thead>

                        <tbody class="bg-white divide-y">

                        <?php if (empty($lessons)): ?>
                            <tr>
                                <td colspan="8" class="p-4 text-center text-gray-500">No lessons found.</td>
                            </tr>
                        <?php endif; ?>

                        <?php foreach ($lessons as $l): ?>
    <tr>
        <td class="px-4 py-3">
            <div class="font-medium">
                Lesson <?= $l['lesson_number'] ?? $l['id'] ?>:
                <?= htmlspecialchars($l['title']) ?>
            </div>

            <?php if (!empty($l['description'])): ?>
                <div class="text-xs text-gray-500">
                    <?= htmlspecialchars(substr($l['description'], 0, 140)) ?>
                </div>
            <?php endif; ?>
        </td>

        <td class="px-4 py-3"><?= htmlspecialchars($l['course_title'] ?? '—') ?></td>

        <td class="px-4 py-3">
            <?= htmlspecialchars($l['module_title'] ?? '—') ?>
            <div class="text-xs text-gray-400">
                <?= !empty($l['module_order']) ? "Chapter " . $l['module_order'] : "" ?>
            </div>
        </td>

        <td class="px-4 py-3">
            <?= isset($l['duration']) && $l['duration'] ? intval($l['duration']) . " min" : "—" ?>
        </td>

        <td class="px-4 py-3"><?= htmlspecialchars($l['lesson_type'] ?? '—') ?></td>

        <td class="px-4 py-3">
            <?= !empty($l['is_preview'])
                ? '<span class="px-2 py-1 bg-green-100 rounded text-xs">Yes</span>'
                : '<span class="px-2 py-1 bg-gray-100 rounded text-xs">No</span>'
            ?>
        </td>

        <td class="px-4 py-3">
            <?= !empty($l['created_at']) ? date("M d, Y", strtotime($l['created_at'])) : '—' ?>
        </td>

        <td class="px-4 py-3">
            <a class="text-primary mr-3" href="lesson-edit.php?id=<?= $l['id'] ?>">Edit</a>
            <a class="text-gray-600 mr-3" href="<?= htmlspecialchars($l['video_url'] ?? '#') ?>" target="_blank">View</a>
            <a class="text-red-600"
               href="lesson-delete.php?id=<?= $l['id'] ?>"
               onclick="return confirm('Delete this lesson?')">
                Delete
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
