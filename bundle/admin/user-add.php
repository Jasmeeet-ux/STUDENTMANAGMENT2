<?php
// user-add.php
require_once "db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
  header("Location: admin-login.php");
  exit;
}

// Only admin can add
$adminStmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
$adminStmt->execute([$admin_id]);
$admin = $adminStmt->fetch();
if (!$admin || $admin['role'] !== 'admin') {
  header("Location: users.php");
  exit;
}

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $name = trim($_POST['name'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = trim($_POST['password'] ?? '');
  $role = in_array($_POST['role'] ?? 'student', ['student', 'instructor', 'admin']) ? $_POST['role'] : 'student';

  if ($name === '') $errors[] = "Name required";
  if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email required";
  if ($password === '') $password = bin2hex(random_bytes(4)); // auto-gen if empty

  // check email unique
  $chk = $pdo->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
  $chk->execute([$email]);
  if ($chk->fetch()) $errors[] = "Email already exists";

  if (empty($errors)) {
    $hash = password_hash($password, PASSWORD_DEFAULT);
    // generate simple referral code
    $ref = strtoupper(substr(preg_replace('/\W+/', '', $name), 0, 4)) . rand(1000, 9999);
    $ins = $pdo->prepare("INSERT INTO users (name,email,password,role,referral_code,timezone,created_at) VALUES (?,?,?,?,?,?,NOW())");
    $ins->execute([$name, $email, $hash, $role, $ref, $pdo->quote('Asia/Kolkata')]); // timezone default if needed
    $newId = $pdo->lastInsertId();

    // optional: insert user_log for creation
    $log = $pdo->prepare("INSERT INTO user_logs (user_id, activity, ip_address, device_info) VALUES (?,?,?,?)");
    $log->execute([$newId, 'other', $_SERVER['REMOTE_ADDR'] ?? null, $_SERVER['HTTP_USER_AGENT'] ?? null]);

    header("Location: user-view.php?id=" . $newId);
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
  <title>Add User</title>
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
      <header class="bg-white p-4 border-b flex justify-between">
        <h2 class="text-xl">Add User</h2><a href="users.php" class="px-3 py-2 border rounded">Back</a>
      </header>
      <main class="p-6 max-w-2xl">
        <?php if (!empty($errors)): ?><div class="bg-red-100 text-red-700 p-3 rounded mb-4"><?php foreach ($errors as $er) echo "<div>" . e($er) . "</div>"; ?></div><?php endif; ?>

        <form method="post" class="bg-white p-6 rounded shadow space-y-4">
          <div><label class="block text-sm">Name</label><input name="name" class="w-full p-2 border rounded"></div>
          <div><label class="block text-sm">Email</label><input name="email" type="email" class="w-full p-2 border rounded"></div>
          <div><label class="block text-sm">Password (leave blank to auto-generate)</label><input name="password" type="text" class="w-full p-2 border rounded"></div>
          <div>
            <label class="block text-sm">Role</label>
            <select name="role" class="p-2 border rounded">
              <option value="student">Student</option>
              <option value="instructor">Instructor</option>
              <option value="admin">Admin</option>
            </select>
          </div>
          <div class="flex justify-end space-x-2">
            <a class="px-4 py-2 border rounded" href="users.php">Cancel</a>
            <button class="px-4 py-2 bg-primary text-white rounded">Create</button>
          </div>
        </form>
      </main>
    </div>
  </div>
</body>

</html>