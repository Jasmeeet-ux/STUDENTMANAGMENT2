<?php
require_once "db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

// VALIDATE LESSON ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid lesson ID");
}

$lesson_id = intval($_GET['id']);

// FETCH LESSON
$stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
$stmt->execute([$lesson_id]);
$lesson = $stmt->fetch();

if (!$lesson) die("Lesson not found.");

// FETCH COURSES
$courses = $pdo->query("SELECT id, title FROM courses ORDER BY title ASC")->fetchAll();

// FETCH MODULES FOR SELECTED COURSE (FIXED)
$modulesStmt = $pdo->prepare("
    SELECT id, title, module_number 
    FROM modules 
    WHERE course_id = ? 
    ORDER BY module_number ASC
");
$modulesStmt->execute([$lesson['course_id']]);
$modules = $modulesStmt->fetchAll();

$errors = [];
$success = "";

// ====================== POST UPDATE ===========================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $course_id     = intval($_POST['course_id']);
    $module_id     = intval($_POST['module_id']);
    $lesson_number = intval($_POST['lesson_number']);
    $title         = trim($_POST['title']);
    $description   = trim($_POST['description']);
    $duration      = intval($_POST['duration']);
    $lesson_type   = trim($_POST['lesson_type']);
    $objectives    = trim($_POST['objectives']);
    $is_preview    = isset($_POST['is_preview']) ? 1 : 0;
    $order_no      = intval($_POST['order_no']);

    $video_url = trim($_POST['video_url'] ?? $lesson['video_url']);

    // ===== VIDEO UPLOAD =====
    if (!empty($_FILES['video_file']['name'])) {
        $file = $_FILES['video_file'];
        $allowed = ['mp4','mov','avi','mkv'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid video format.";
        } else {
            $uploadDir = __DIR__ . "/uploads/lessons/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

            $basename = uniqid("vid_") . "." . $ext;
            $target = $uploadDir . $basename;

            if (move_uploaded_file($file['tmp_name'], $target)) {

                // DELETE OLD VIDEO
                if ($lesson['video_url'] && strpos($lesson['video_url'], "uploads/lessons/") === 0) {
                    @unlink(__DIR__ . "/" . $lesson['video_url']);
                }

                $video_url = "uploads/lessons/" . $basename;

            } else {
                $errors[] = "Failed to upload video.";
            }
        }
    }

    // ===== RESOURCE UPLOAD =====
    $resource_url = $lesson['resource_url'];
    if (!empty($_FILES['resource_file']['name'])) {

        $file = $_FILES['resource_file'];
        $allowedRes = ['pdf','zip','docx','pptx'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowedRes)) {
            $errors[] = "Invalid resource format.";
        } else {
            $uploadDir = __DIR__ . "/uploads/lessons/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

            $basename = uniqid("res_") . "." . $ext;
            $target = $uploadDir . $basename;

            if (move_uploaded_file($file['tmp_name'], $target)) {

                // DELETE OLD RESOURCE
                if ($lesson['resource_url'] && strpos($lesson['resource_url'], "uploads/lessons/") === 0) {
                    @unlink(__DIR__ . "/" . $lesson['resource_url']);
                }

                $resource_url = "uploads/lessons/" . $basename;
            }
        }
    }

    // FINAL UPDATE
    if (empty($errors)) {

        $update = $pdo->prepare("
            UPDATE lessons SET 
                course_id=?, 
                module_id=?, 
                lesson_number=?, 
                title=?, 
                description=?, 
                duration=?, 
                lesson_type=?, 
                objectives=?, 
                video_url=?, 
                resource_url=?, 
                is_preview=?, 
                order_no=? 
            WHERE id=?
        ");

        $update->execute([
            $course_id,
            $module_id,
            $lesson_number,
            $title,
            $description,
            $duration,
            $lesson_type,
            $objectives,
            $video_url,
            $resource_url,
            $is_preview,
            $order_no,
            $lesson_id
        ]);

        $success = "Lesson updated successfully!";

        // REFRESH
        $stmt = $pdo->prepare("SELECT * FROM lessons WHERE id = ?");
        $stmt->execute([$lesson_id]);
        $lesson = $stmt->fetch();
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Edit Lesson</title>
<script src="https://cdn.tailwindcss.com"></script>

<script>
// Load modules when course changes
function loadModules(courseId) {
    fetch("load-modules.php?course_id=" + courseId)
    .then(res => res.json())
    .then(data => {
        let moduleSelect = document.getElementById("module_id");
        moduleSelect.innerHTML = "";
        data.forEach(m => {
            moduleSelect.innerHTML += `<option value="${m.id}">${m.title}</option>`;
        });
    });
}
</script>

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


    <div class="flex-1 overflow-auto">

        <header class="bg-white shadow-sm border-b p-6">
            <h2 class="text-2xl font-semibold">Edit Lesson: <?= htmlspecialchars($lesson['title']) ?></h2>
        </header>

        <main class="p-6 max-w-3xl mx-auto">

            <?php if ($success): ?>
            <div class="p-3 bg-green-100 text-green-700 rounded mb-4"><?= $success ?></div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
            <div class="p-3 bg-red-100 text-red-700 rounded mb-4">
                <?php foreach($errors as $e) echo "<div>$e</div>"; ?>
            </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="space-y-4">

                <!-- COURSE -->
                <div>
                    <label class="block mb-1">Course</label>
                    <select name="course_id" class="input" onchange="loadModules(this.value)">
                        <?php foreach($courses as $c): ?>
                        <option value="<?= $c['id'] ?>" <?= $c['id']==$lesson['course_id'] ? 'selected':'' ?>>
                            <?= htmlspecialchars($c['title']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- MODULE -->
                <div>
                    <label class="block mb-1">Module (Chapter)</label>
                    <select name="module_id" id="module_id" class="input">
                        <?php foreach($modules as $m): ?>
                        <option value="<?= $m['id'] ?>" <?= $lesson['module_id']==$m['id'] ? 'selected':'' ?>>
                            <?= htmlspecialchars($m['title']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- LESSON NUMBER -->
                <div>
                    <label class="block mb-1">Lesson Number</label>
                    <input type="number" name="lesson_number" class="input" value="<?= $lesson['lesson_number'] ?>">
                </div>

                <!-- TITLE -->
                <div>
                    <label class="block mb-1">Lesson Title</label>
                    <input name="title" class="input" value="<?= htmlspecialchars($lesson['title']) ?>">
                </div>

                <!-- DURATION -->
                <div>
                    <label class="block mb-1">Duration (minutes)</label>
                    <input name="duration" type="number" class="input" value="<?= $lesson['duration'] ?>">
                </div>

                <!-- TYPE -->
                <div>
                    <label class="block mb-1">Lesson Type</label>
                    <select name="lesson_type" class="input">
                        <?php foreach(['video','free','paid','quiz'] as $t): ?>
                        <option value="<?= $t ?>" <?= $lesson['lesson_type']==$t ? 'selected':'' ?>><?= ucfirst($t) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- DESCRIPTION -->
                <div>
                    <label class="block mb-1">Description</label>
                    <textarea name="description" class="input" rows="3"><?= htmlspecialchars($lesson['description']) ?></textarea>
                </div>

                <!-- OBJECTIVES -->
                <div>
                    <label class="block mb-1">Learning Objectives</label>
                    <textarea name="objectives" class="input" rows="2"><?= htmlspecialchars($lesson['objectives']) ?></textarea>
                </div>

                <!-- VIDEO -->
                <div>
                    <label class="block mb-1">Video File / URL</label>

                    <?php if ($lesson['video_url']): ?>
                    <a href="<?= htmlspecialchars($lesson['video_url']) ?>" target="_blank" class="text-primary">View Current Video</a>
                    <?php endif; ?>

                    <input type="file" name="video_file" class="input mt-2">
                    <label class="block mt-2 text-sm text-gray-500">OR Video URL</label>
                    <input name="video_url" class="input" value="<?= htmlspecialchars($lesson['video_url']) ?>">
                </div>

                <!-- RESOURCE -->
                <div>
                    <label class="block mb-1">Resource File</label>

                    <?php if ($lesson['resource_url']): ?>
                    <a href="<?= htmlspecialchars($lesson['resource_url']) ?>" target="_blank" class="text-primary">View Resource</a>
                    <?php endif; ?>

                    <input type="file" name="resource_file" class="input mt-2">
                </div>

                <!-- PREVIEW + ORDER -->
                <div class="flex items-center gap-4">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_preview" <?= $lesson['is_preview'] ? 'checked':'' ?> class="mr-2">
                        Free Preview
                    </label>

                    <label class="flex items-center">
                        Order:
                        <input type="number" name="order_no" class="input ml-2 w-24" value="<?= $lesson['order_no'] ?>">
                    </label>
                </div>

                <div class="flex justify-end gap-3">
                    <a href="lessons.php" class="px-4 py-2 border rounded">Cancel</a>
                    <button class="px-6 py-2 bg-primary text-white rounded">Save Changes</button>
                </div>

            </form>

        </main>

    </div>
</div>

<style>
.input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
}
</style>

<script>
function loadModules(courseId, selectedModule = null) {
    fetch("load-modules.php?course_id=" + courseId)
    .then(res => res.json())
    .then(data => {
        let moduleSelect = document.getElementById("module_id");
        moduleSelect.innerHTML = "";
        
        data.forEach(m => {
            moduleSelect.innerHTML += `
                <option value="${m.id}" ${selectedModule == m.id ? "selected" : ""}>
                    ${m.title}
                </option>`;
        });
    });
}

// Load correct modules when page opens
document.addEventListener("DOMContentLoaded", function() {
    loadModules(<?= $lesson['course_id'] ?>, <?= $lesson['module_id'] ?>);
});
</script>


</body>
</html>
