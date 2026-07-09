<?php
require_once "db.php";
require_once "admin-auth.php";

// Validate admin
$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

// Validate course ID
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Invalid course ID.");
}

$course_id = intval($_GET['id']);

// Fetch course
$stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
$stmt->execute([$course_id]);
$course = $stmt->fetch();

if (!$course) die("Course not found.");

$success = $error = "";

// ================== SAVE UPDATE ==================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title             = trim($_POST['title']);
    $subtitle          = trim($_POST['subtitle']);
    $short_description = trim($_POST['short_description']);
    $full_description  = trim($_POST['full_description']);
    $skills            = trim($_POST['skills']);
    $price             = floatval($_POST['price']);
    $badge             = trim($_POST['badge']);
    $features          = trim($_POST['features']);
    $hero_thumbnail    = trim($_POST['hero_thumbnail']);
    $description_image = trim($_POST['description_image']);
    $rating            = floatval($_POST['rating']);
    $review_count      = intval($_POST['review_count']);

    if ($title === "") {
        $error = "Course title is required.";
    } else {

        $stmt = $pdo->prepare("
            UPDATE courses SET
                title = ?, 
                subtitle = ?,
                short_description = ?, 
                full_description = ?, 
                skills = ?,
                price = ?, 
                badge = ?, 
                features = ?, 
                hero_thumbnail = ?, 
                description_image = ?,
                rating = ?,
                review_count = ?
            WHERE id = ?
        ");

        $stmt->execute([
            $title, $subtitle, $short_description, $full_description,
            $skills, $price, $badge, $features,
            $hero_thumbnail, $description_image,
            $rating, $review_count,
            $course_id
        ]);

        $success = "Course updated successfully!";

        // Re-fetch updated data
        $stmt = $pdo->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$course_id]);
        $course = $stmt->fetch();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Course</title>
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

    <!-- MAIN -->
    <div class="flex-1 overflow-auto">

        <!-- Header -->
        <header class="bg-white shadow-sm border-b p-6 flex justify-between">
            <h2 class="text-2xl font-semibold text-gray-800">Edit Course: <?= htmlspecialchars($course['title']) ?></h2>

            <button form="editForm" class="px-4 py-2 bg-primary text-white rounded-lg">
                Update Course
            </button>
        </header>

        <main class="p-6 max-w-4xl mx-auto">

            <?php if ($success): ?>
                <div class="p-3 bg-green-100 text-green-700 rounded mb-4"><?= $success ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="p-3 bg-red-100 text-red-700 rounded mb-4"><?= $error ?></div>
            <?php endif; ?>

            <form id="editForm" method="POST" class="space-y-6">

                <!-- BASIC INFO -->
                <div class="bg-white shadow p-6 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Basic Information</h3>

                    <input class="input" name="title" value="<?= htmlspecialchars($course['title']) ?>" placeholder="Course Title">

                    <input class="input mt-3" name="subtitle" value="<?= htmlspecialchars($course['subtitle']) ?>" placeholder="Subtitle">

                    <textarea class="input mt-3" rows="3" name="short_description" placeholder="Short Description"><?= htmlspecialchars($course['short_description']) ?></textarea>

                    <textarea class="input mt-3" rows="5" name="full_description" placeholder="Full Description"><?= htmlspecialchars($course['full_description']) ?></textarea>
                </div>

                <!-- SKILLS -->
                <div class="bg-white shadow p-6 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Skill Tags (comma separated)</h3>
                    <input class="input" name="skills" value="<?= htmlspecialchars($course['skills']) ?>" placeholder="e.g., HTML, CSS, UI Design, Animation">
                </div>

                <!-- PRICING -->
                <div class="bg-white shadow p-6 rounded-lg grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-1">Price (₹)</label>
                        <input class="input" type="number" step="0.01" name="price" value="<?= htmlspecialchars($course['price']) ?>">
                    </div>

                    <div>
                        <label class="block mb-1">Badge</label>
                        <input class="input" name="badge" value="<?= htmlspecialchars($course['badge']) ?>">
                    </div>
                </div>

                <!-- FEATURES -->
                <div class="bg-white shadow p-6 rounded-lg">
                    <h3 class="text-lg font-semibold mb-3">Features</h3>
                    <textarea class="input" rows="3" name="features"><?= htmlspecialchars($course['features']) ?></textarea>
                </div>

                <!-- IMAGES -->
                <div class="bg-white shadow p-6 rounded-lg">
                    <h3 class="text-lg font-semibold mb-4">Course Images</h3>

                    <label>Hero Thumbnail URL</label>
                    <input class="input mb-4" name="hero_thumbnail" value="<?= htmlspecialchars($course['hero_thumbnail']) ?>">

                    <label>Description Section Image URL</label>
                    <input class="input" name="description_image" value="<?= htmlspecialchars($course['description_image']) ?>">
                </div>

                <!-- RATING -->
                <div class="bg-white shadow p-6 rounded-lg grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block mb-1">Rating (0–5)</label>
                        <input class="input" type="number" step="0.1" name="rating" value="<?= htmlspecialchars($course['rating']) ?>">
                    </div>

                    <div>
                        <label class="block mb-1">Total Reviews</label>
                        <input class="input" type="number" name="review_count" value="<?= htmlspecialchars($course['review_count']) ?>">
                    </div>
                </div>

                <!-- ACTIONS -->
                <div class="flex justify-end space-x-4">
                    <a href="courses.php" class="px-4 py-2 border rounded-lg">Cancel</a>

                    <a href="course-delete.php?id=<?= $course_id ?>" 
                        class="px-4 py-2 text-red-600 border border-red-300 rounded-lg"
                        onclick="return confirm('Delete this course?')">
                        Delete
                    </a>

                    <button class="px-6 py-2 bg-primary text-white rounded-lg">
                        Update Course
                    </button>
                </div>

            </form>

        </main>

    </div>
</div>

<style>
.input {
    width: 100%;
    padding: 10px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
}
</style>

</body>
</html>
