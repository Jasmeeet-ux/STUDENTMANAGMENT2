<?php
// user-edit.php
require_once "db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
  header("Location: admin-login.php");
  exit;
}

// fetch admin info to check permission (only admin can update role)
$adminStmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$adminStmt->execute([$admin_id]);
$admin = $adminStmt->fetch();

// load user
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
  header("Location: users.php");
  exit;
}

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) {
  header("Location: users.php");
  exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $location = trim($_POST['location'] ?? '');
  $timezone = trim($_POST['timezone'] ?? '');
  $dob = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
  $bio = trim($_POST['bio'] ?? '');
  $role = $user['role'];

  // only admins may change role
  if ($admin && $admin['role'] === 'admin' && isset($_POST['role'])) {
    $allowed = ['student', 'instructor', 'admin'];
    if (in_array($_POST['role'], $allowed, true)) $role = $_POST['role'];
  }

  if ($name === '') $errors[] = "Name is required.";
  if ($email === '') $errors[] = "Email is required.";

  if (empty($errors)) {
    $up = $pdo->prepare("UPDATE users SET name=?, email=?, phone=?, location=?, timezone=?, date_of_birth=?, bio=?, role=? WHERE id=?");
    $up->execute([$name, $email, $phone, $location, $timezone, $dob, $bio, $role, $id]);
    header("Location: user-view.php?id=" . $id);
    exit;
  }
}

function e($v)
{
  return htmlspecialchars($v, ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html>

<head>
  <meta charset="utf-8">
  <title>Edit User</title>
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
    <div class="flex-1 overflow-auto">
      <header class="bg-white p-4 border-b">
        <div class="flex justify-between">
          <h2 class="text-xl font-semibold">Edit User</h2>
          <a href="user-view.php?id=<?= $id ?>" class="px-3 py-2 border rounded">Back</a>
        </div>
      </header>

      <main class="p-6 max-w-3xl">
        <?php if (!empty($errors)): ?>
          <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <?php foreach ($errors as $err) echo "<div>" . e($err) . "</div>"; ?>
          </div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="space-y-4 bg-white p-6 rounded shadow">
          <div>
            <label class="block text-sm font-medium">Name</label>
            <input name="name" value="<?= e($user['name']) ?>" class="w-full p-2 border rounded">
          </div>

          <div>
            <label class="block text-sm font-medium">Email</label>
            <input name="email" type="email" value="<?= e($user['email']) ?>" class="w-full p-2 border rounded">
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium">Phone</label>
              <input name="phone" value="<?= e($user['phone']) ?>" class="w-full p-2 border rounded">
            </div>
            <div>
              <label class="block text-sm font-medium">Location</label>
              <input name="location" value="<?= e($user['location']) ?>" class="w-full p-2 border rounded">
            </div>
          </div>

          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium">Timezone</label>
              <input name="timezone" value="<?= e($user['timezone']) ?>" class="w-full p-2 border rounded">
            </div>
            <div>
              <label class="block text-sm font-medium">Date of Birth</label>
              <input name="date_of_birth" type="date" value="<?= e($user['date_of_birth']) ?>" class="w-full p-2 border rounded">
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium">Bio</label>
            <textarea name="bio" class="w-full p-2 border rounded"><?= e($user['bio']) ?></textarea>
          </div>

          <?php if ($admin && $admin['role'] === 'admin'): ?>
            <div>
              <label class="block text-sm font-medium">Role</label>
              <select name="role" class="p-2 border rounded">
                <option value="student" <?= $user['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                <option value="instructor" <?= $user['role'] === 'instructor' ? 'selected' : '' ?>>Instructor</option>
                <option value="admin" <?= $user['role'] === 'admin' ? 'selected' : '' ?>>Admin</option>
              </select>
            </div>
          <?php endif; ?>

          <div class="flex justify-end space-x-2">
            <a href="user-view.php?id=<?= $id ?>" class="px-4 py-2 border rounded">Cancel</a>
            <button type="submit" class="px-4 py-2 bg-primary text-white rounded">Save</button>
          </div>
        </form>
      </main>
    </div>
  </div>
</body>

</html>