<?php
require_once "db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

// FETCH COURSE LIST
$courses = $pdo->query("SELECT id, title FROM courses ORDER BY title ASC")->fetchAll();

$errors = [];
$success = "";

// ======================================================
//               HANDLE FORM SUBMISSION
// ======================================================
if ($_SERVER['REQUEST_METHOD'] === "POST") {

    $course_id      = intval($_POST['course_id']);
    $module_id      = intval($_POST['module_id']);
    $lesson_number  = intval($_POST['lesson_number']);
    $title          = trim($_POST['title']);
    $description    = trim($_POST['description']);
    $duration       = intval($_POST['duration']);
    $objectives     = trim($_POST['objectives']);
    $is_preview     = isset($_POST['is_preview']) ? 1 : 0;
    $order_no       = intval($_POST['order_no'] ?? 0);

    // DB ENUM (LESSONS) = free, paid, quiz
    // UI provided "video" which is NOT in ENUM → fix here
    $lesson_type = $_POST['lesson_type'] ?? 'paid';
    if ($lesson_type === "video") {
        $lesson_type = "paid"; // default for video content
    }

    // VALIDATION
    if (!$course_id) $errors[] = "Please select a course.";
    if (!$module_id) $errors[] = "Please select a module.";
    if ($lesson_number <= 0) $errors[] = "Lesson number is required.";
    if ($title === "") $errors[] = "Lesson title is required.";

    // ======================================================
    //                       HANDLE VIDEO
    // ======================================================
    $video_url = null;

    if (!empty($_FILES['video_file']['name'])) {

        $file = $_FILES['video_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['mp4','mov','avi','mkv'];

        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid video format.";
        } else {
            $uploadDir = __DIR__ . "/uploads/lessons/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

            $basename = uniqid("vid_") . "." . $ext;
            $target = $uploadDir . $basename;

            if (move_uploaded_file($file['tmp_name'], $target)) {
                $video_url = "uploads/lessons/" . $basename;
            } else {
                $errors[] = "Failed to upload video.";
            }
        }

    } else {
        // External URL
        $video_url = trim($_POST['video_url'] ?? "");
        if ($video_url === "") $video_url = null;
    }

    // ======================================================
    //                     HANDLE RESOURCE
    // ======================================================
    $resource_url = null;

    if (!empty($_FILES['resource_file']['name'])) {

        $file = $_FILES['resource_file'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['pdf','zip','docx','pptx'];

        if (!in_array($ext, $allowed)) {
            $errors[] = "Invalid resource format.";
        } else {
            $uploadDir = __DIR__ . "/uploads/lessons/";
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0775, true);

            $basename = uniqid("res_") . "." . $ext;
            $target = $uploadDir . $basename;

            if (move_uploaded_file($file['tmp_name'], $target)) {
                $resource_url = "uploads/lessons/" . $basename;
            }
        }
    }

    // ======================================================
    //                   SAVE TO DATABASE
    // ======================================================
    if (empty($errors)) {

        $stmt = $pdo->prepare("
            INSERT INTO lessons 
            (course_id, module_id, lesson_number, title, description, duration, 
             lesson_type, video_url, resource_url, objectives, is_preview, order_no, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $course_id,
            $module_id,
            $lesson_number,
            $title,
            $description,
            $duration,
            $lesson_type,
            $video_url,
            $resource_url,
            $objectives,
            $is_preview,
            $order_no
        ]);

        header("Location: lessons.php");
        exit;
    }
}
?>
<!doctype html>
<html>
<head>
<meta charset="utf-8">
<title>Upload Lesson</title>
<script src="https://cdn.tailwindcss.com"></script>

<script>
function loadModules(courseId) {
    fetch("load-modules.php?course_id=" + courseId)
    .then(res => res.json())
    .then(data => {
        let moduleSelect = document.getElementById("module_id");
        moduleSelect.innerHTML = "<option value=''>Select Module</option>";
        data.forEach(m => {
            moduleSelect.innerHTML += `<option value="${m.id}">${m.title} (Module ${m.module_number})</option>`;
        });
    });
}
</script>

<style>
.input {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
}
</style>
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
            <h2 class="text-2xl font-semibold">Upload New Lesson</h2>
        </header>

        <main class="p-6 max-w-3xl mx-auto">

            <?php if (!empty($errors)): ?>
                <div class="p-3 bg-red-100 text-red-700 rounded mb-4">
                    <?php foreach ($errors as $e) echo "<div>$e</div>"; ?>
                </div>
            <?php endif; ?>

            <form method="post" enctype="multipart/form-data" class="space-y-4">

                <!-- COURSE -->
                <div>
                    <label class="block mb-1">Course *</label>
                    <select name="course_id" class="input" required onchange="loadModules(this.value)">
                        <option value="">Select Course</option>
                        <?php foreach($courses as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['title']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- MODULE -->
                <div>
                    <label class="block mb-1">Module *</label>
                    <select name="module_id" id="module_id" class="input" required>
                        <option value="">Select Module</option>
                    </select>
                </div>

                <!-- LESSON NUMBER -->
                <div>
                    <label class="block mb-1">Lesson Number *</label>
                    <input type="number" min="1" name="lesson_number" class="input" required>
                </div>

                <!-- TITLE -->
                <div>
                    <label class="block mb-1">Lesson Title *</label>
                    <input name="title" class="input" required>
                </div>

                <!-- DURATION -->
                <div>
                    <label class="block mb-1">Duration (minutes)</label>
                    <input type="number" name="duration" class="input">
                </div>

                <!-- LESSON TYPE (DB ENUM) -->
                <div>
                    <label class="block mb-1">Lesson Type</label>
                    <select name="lesson_type" class="input">
                        <option value="free">Free</option>
                        <option value="paid">Paid</option>
                        <option value="quiz">Quiz</option>
                    </select>
                </div>

                <!-- VIDEO -->
                <div>
                    <label class="block mb-1">Video File (or enter URL)</label>
                    <input type="file" name="video_file" class="input">
                    <p class="text-xs text-gray-500">Allowed: mp4, mov, avi, mkv</p>

                    <div class="mt-2">OR External URL</div>
                    <input name="video_url" class="input mt-1" placeholder="https://...">
                </div>

                <!-- RESOURCE -->
                <div>
                    <label class="block mb-1">Resource File</label>
                    <input type="file" name="resource_file" class="input" accept=".pdf,.zip,.docx,.pptx">
                </div>

                <!-- DESCRIPTION -->
                <div>
                    <label class="block mb-1">Description</label>
                    <textarea name="description" class="input" rows="3"></textarea>
                </div>

                <!-- OBJECTIVES -->
                <div>
                    <label class="block mb-1">Learning Objectives</label>
                    <textarea name="objectives" class="input" rows="2"></textarea>
                </div>

                <!-- PREVIEW + ORDER -->
                <div class="flex items-center gap-3">
                    <label class="flex items-center">
                        <input type="checkbox" name="is_preview" class="mr-2"> Free Preview
                    </label>

                    <label class="flex items-center">
                        Order No:
                        <input type="number" name="order_no" class="input ml-2 w-24">
                    </label>
                </div>

                <!-- SUBMIT -->
                <div class="flex justify-end gap-3">
                    <a href="lessons.php" class="px-4 py-2 border rounded">Cancel</a>
                    <button class="px-6 py-2 bg-primary text-white rounded">
                        Upload Lesson
                    </button>
                </div>

            </form>

        </main>

    </div>
</div>

</body>
</html>
