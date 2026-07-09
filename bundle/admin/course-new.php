<?php
require_once "db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

$success = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Collect Inputs
    $title              = trim($_POST["title"]);
    $subtitle           = trim($_POST["subtitle"]);
    $short_description  = trim($_POST["short_description"]);
    $full_description   = trim($_POST["full_description"]);
    $description        = trim($_POST["description"]);
    $price              = trim($_POST["price"]);
    $is_bundle          = isset($_POST["is_bundle"]) ? 1 : 0;

    $badge              = trim($_POST["badge"]);
    $features           = trim($_POST["features"]);
    $skills             = trim($_POST["skills"]);

    // Images
    $image_url          = trim($_POST["image_url"]);
    $hero_image         = trim($_POST["hero_image"]);
    $description_image  = trim($_POST["description_image"]);

    // Ratings
    $rating             = trim($_POST["rating"]);
    $review_count       = trim($_POST["review_count"]);

    // Validation
    if ($title === "" || $price === "") {
        $error = "Title and price are required!";
    } else {

        $stmt = $pdo->prepare("
            INSERT INTO courses 
            (title, subtitle, short_description, full_description, description, price, is_bundle,
             created_by, image_url, hero_image, description_image, badge, features, skills,
             rating, review_count, created_at)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
        ");

        $stmt->execute([
            $title,
            $subtitle,
            $short_description,
            $full_description,
            $description,
            $price,
            $is_bundle,
            $admin_id,
            $image_url,
            $hero_image,
            $description_image,
            $badge,
            $features,
            $skills,
            $rating ?: 4.5,
            $review_count ?: 0
        ]);

        $success = "Course created successfully!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create New Course - Learning Platform</title>
    <script src="https://cdn.tailwindcss.com"></script>

    <script>
        function toggleSidebar() {
            document.getElementById("mobileSidebar").classList.toggle("hidden");
        }

        document.addEventListener("click", function (event) {
            const sidebar = document.getElementById("mobileSidebar");
            const toggleButton = event.target.closest("button");

            if (sidebar.classList.contains("hidden")) return;
            if (sidebar.contains(event.target)) return;
            if (toggleButton && toggleButton.getAttribute("onclick") === "toggleSidebar()") return;

            sidebar.classList.add("hidden");
        });

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

    <!-- SIDEBAR -->
    <div id="mobileSidebar" class="hidden lg:block fixed lg:static top-0 left-0 w-64 h-full bg-white shadow-lg z-40">
        <?php include "includes/sidebar.php"; ?>
    </div>

    <!-- MAIN CONTENT -->
    <div class="flex-1 overflow-auto">

        <!-- HEADER -->
        <header class="bg-white shadow-sm border-b">
            <div class="px-6 py-4 flex justify-between items-center">
                <div class="flex items-center space-x-4">
                    <a href="courses.php" class="text-gray-500 hover:text-gray-700">← Back to Courses</a>
                    <h2 class="text-2xl font-semibold text-gray-800">Create New Course</h2>
                </div>

                <button form="courseForm" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">
                    Create Course
                </button>
            </div>
        </header>

        <!-- Alerts -->
        <div class="max-w-4xl mx-auto mt-4 px-6">
            <?php if ($success): ?>
                <div class="bg-green-100 text-green-700 p-3 mb-4 rounded"><?= $success ?></div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="bg-red-100 text-red-700 p-3 mb-4 rounded"><?= $error ?></div>
            <?php endif; ?>
        </div>

        <!-- FORM -->
        <main class="p-6">
            <div class="max-w-4xl mx-auto">

                <form method="POST" id="courseForm" enctype="multipart/form-data">

                    <!-- Course Info -->
                    <div class="bg-white rounded-lg shadow mb-6">
                        <div class="px-6 py-4 border-b">
                            <h3 class="text-lg font-semibold text-gray-800">Course Information</h3>
                        </div>

                        <div class="p-6 grid grid-cols-1 gap-6">

                            <!-- Title -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course Title *</label>
                                <input name="title" required type="text" class="w-full px-4 py-2 border rounded-lg">
                            </div>

                            <!-- Subtitle -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Subtitle</label>
                                <input name="subtitle" type="text" class="w-full px-4 py-2 border rounded-lg">
                            </div>

                            <!-- Short Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Short Description</label>
                                <textarea name="short_description" rows="2" class="w-full px-4 py-2 border rounded-lg"></textarea>
                            </div>

                            <!-- Full Description -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Full Description</label>
                                <textarea name="full_description" rows="5" class="w-full px-4 py-2 border rounded-lg"></textarea>
                            </div>

                            <!-- Overview Description (old field) -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Overview Description</label>
                                <textarea name="description" rows="3" class="w-full px-4 py-2 border rounded-lg"></textarea>
                            </div>

                            <!-- Price -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Price (₹) *</label>
                                <input name="price" required type="number" step="0.01" class="w-full px-4 py-2 border rounded-lg">
                            </div>

                            <!-- Bundle Checkbox -->
                            <div class="flex items-center">
                                <input type="checkbox" name="is_bundle" class="mr-2">
                                <label class="text-sm text-gray-700">This course is a bundle</label>
                            </div>

                            <!-- Badge -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Badge</label>
                                <input name="badge" type="text" class="w-full px-4 py-2 border rounded-lg">
                            </div>

                            <!-- Features -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Features (HTML allowed)</label>
                                <textarea name="features" rows="4" class="w-full px-4 py-2 border rounded-lg"></textarea>
                            </div>

                            <!-- Skills -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Skills (comma separated)</label>
                                <input name="skills" type="text" class="w-full px-4 py-2 border rounded-lg" placeholder="UX, UI, Research">
                            </div>

                            <!-- Thumbnail -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Course Thumbnail Image URL *</label>
                                <input name="image_url" type="text" class="w-full px-4 py-2 border rounded-lg" placeholder="https://...">
                            </div>

                            <!-- Hero Image -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Hero Section Image URL</label>
                                <input name="hero_image" type="text" class="w-full px-4 py-2 border rounded-lg" placeholder="https://...">
                            </div>

                            <!-- Description Image -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Description Image URL</label>
                                <input name="description_image" type="text" class="w-full px-4 py-2 border rounded-lg" placeholder="https://...">
                            </div>

                            <!-- Rating -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Rating</label>
                                <input name="rating" type="number" step="0.1" class="w-full px-4 py-2 border rounded-lg" placeholder="4.5">
                            </div>

                            <!-- Review Count -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Review Count</label>
                                <input name="review_count" type="number" class="w-full px-4 py-2 border rounded-lg" placeholder="0">
                            </div>

                        </div>
                    </div>

                </form>

            </div>
        </main>

    </div>
</div>

</body>
</html>
