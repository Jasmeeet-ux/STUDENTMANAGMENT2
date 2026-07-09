<?php
// user-view.php
require_once "db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) { header("Location: admin-login.php"); exit; }

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header("Location: users.php"); exit; }

// Fetch user
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
if (!$user) { header("Location: users.php"); exit; }

// Enrolled courses (from purchases)
$enStmt = $pdo->prepare("SELECT p.course_id, c.title, COUNT(*) AS times FROM purchases p LEFT JOIN courses c ON p.course_id=c.id WHERE p.user_id = ? GROUP BY p.course_id ORDER BY p.purchased_at DESC");
$enStmt->execute([$id]);
$enrolled = $enStmt->fetchAll();

// Last sessions info
$sStmt = $pdo->prepare("SELECT session_token, device_info, ip_address, created_at, expires_at FROM sessions WHERE user_id = ? ORDER BY created_at DESC LIMIT 5");
$sStmt->execute([$id]);
$sessions = $sStmt->fetchAll();

// Recent user_logs
$logStmt = $pdo->prepare("SELECT activity, ip_address, device_info, created_at FROM user_logs WHERE user_id = ? ORDER BY created_at DESC LIMIT 10");
$logStmt->execute([$id]);
$logs = $logStmt->fetchAll();

function e($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>View User - <?= e($user['name']) ?></title>
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
    <header class="bg-white shadow-sm border-b p-4">
      <div class="flex justify-between items-center">
        <h1 class="text-xl font-semibold">User: <?= e($user['name']) ?></h1>
        <div>
          <a href="user-edit.php?id=<?= $user['id'] ?>" class="px-3 py-2 bg-indigo-600 text-white rounded">Edit</a>
          <a href="users.php" class="px-3 py-2 border rounded ml-2">Back</a>
        </div>
      </div>
    </header>

    <main class="p-6">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded shadow p-4">
          <div class="flex items-center space-x-4">
            <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center text-xl font-bold">
              <?= e(strtoupper(substr($user['name'],0,1))) ?>
            </div>
            <div>
              <div class="text-lg font-semibold"><?= e($user['name']) ?></div>
              <div class="text-sm text-gray-600"><?= e($user['email']) ?></div>
              <div class="text-xs text-gray-500 mt-1">ID: #USR<?= str_pad($user['id'],6,'0',STR_PAD_LEFT) ?></div>
              <div class="mt-2">
                <span class="px-2 py-1 text-xs rounded <?= $user['role']==='admin' ? 'bg-red-100 text-red-700' : ($user['role']==='instructor' ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700') ?>">
                  <?= e(ucfirst($user['role'])) ?>
                </span>
                <span class="px-2 py-1 text-xs rounded <?= ($user['status'] ?? 'active')==='suspended' ? 'bg-yellow-100 text-yellow-800' : 'bg-gray-100 text-gray-800' ?> ml-2">
                  <?= ucfirst($user['status'] ?? 'active') ?>
                </span>
              </div>
            </div>
          </div>

          <div class="mt-4 text-sm text-gray-700">
            <p><strong>Phone:</strong> <?= e($user['phone'] ?? '—') ?></p>
            <p><strong>Location:</strong> <?= e($user['location'] ?? '—') ?></p>
            <p><strong>Timezone:</strong> <?= e($user['timezone'] ?? '—') ?></p>
            <p><strong>Joined:</strong> <?= date("M d, Y", strtotime($user['created_at'])) ?></p>
            <?php if(!empty($user['date_of_birth'])): ?>
              <p><strong>DOB:</strong> <?= e($user['date_of_birth']) ?></p>
            <?php endif; ?>
          </div>
        </div>

        <div class="md:col-span-2 space-y-6">
          <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold mb-2">Enrolled / Purchased Courses</h3>
            <?php if(empty($enrolled)): ?>
              <div class="text-sm text-gray-500">No purchases found.</div>
            <?php else: ?>
              <ul class="space-y-2">
                <?php foreach($enrolled as $ec): ?>
                  <li class="flex justify-between">
                    <div>
                      <div class="font-medium"><?= e($ec['title'] ?? 'Unknown Course') ?></div>
                      <div class="text-xs text-gray-500">Course ID: <?= e($ec['course_id']) ?></div>
                    </div>
                    <div class="text-sm text-gray-700"><?= intval($ec['times']) ?> times</div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>

          <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold mb-2">Active Sessions (last 5)</h3>
            <?php if(empty($sessions)): ?>
              <div class="text-sm text-gray-500">No active sessions found.</div>
            <?php else: ?>
              <table class="w-full text-sm">
                <thead class="text-left text-gray-600"><tr><th>Device</th><th>IP</th><th>Created At</th><th>Expires</th></tr></thead>
                <tbody>
                  <?php foreach($sessions as $s): ?>
                    <tr class="border-t">
                      <td class="py-2"><?= e($s['device_info'] ?? '—') ?></td>
                      <td class="py-2"><?= e($s['ip_address'] ?? '—') ?></td>
                      <td class="py-2"><?= e($s['created_at']) ?></td>
                      <td class="py-2"><?= e($s['expires_at'] ?? '—') ?></td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            <?php endif; ?>
          </div>

          <div class="bg-white rounded shadow p-4">
            <h3 class="font-semibold mb-2">Recent Activity (user_logs)</h3>
            <?php if(empty($logs)): ?>
              <div class="text-sm text-gray-500">No logs found.</div>
            <?php else: ?>
              <ul class="text-sm space-y-2">
                <?php foreach($logs as $l): ?>
                  <li class="flex justify-between">
                    <div>
                      <div class="font-medium"><?= e(ucfirst($l['activity'])) ?></div>
                      <div class="text-xs text-gray-500"><?= e($l['device_info'] ?? '') ?> · <?= e($l['ip_address'] ?? '') ?></div>
                    </div>
                    <div class="text-xs text-gray-500"><?= e($l['created_at']) ?></div>
                  </li>
                <?php endforeach; ?>
              </ul>
            <?php endif; ?>
          </div>
        </div>
      </div>
    </main>
  </div>
</div>
</body>
</html>
