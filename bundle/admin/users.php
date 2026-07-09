<?php
// users.php
require_once "db.php";
require_once "admin-auth.php";

$admin_id = validateAdminSession($pdo);
if (!$admin_id) {
    header("Location: admin-login.php");
    exit;
}

// Check if users table has a 'status' column (optional)
$hasStatusCol = false;
try {
    $col = $pdo->query("SHOW COLUMNS FROM `users` LIKE 'status'")->fetch();
    if ($col) $hasStatusCol = true;
} catch (\Exception $e) {
    // ignore - fallback behaviour below
}

// Pagination
$perPage = 20;
$page = isset($_GET['page']) && is_numeric($_GET['page']) && $_GET['page']>0 ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $perPage;

// Filters
$q = trim($_GET['q'] ?? '');
$role = trim($_GET['role'] ?? '');
$status = trim($_GET['status'] ?? '');

// Build SQL with subqueries for enrolled count and last_active
$sqlBase = "
    FROM users u
    LEFT JOIN (
        SELECT user_id, COUNT(*) AS enrolled_count
        FROM purchases
        WHERE status = 'completed'
        GROUP BY user_id
    ) p ON p.user_id = u.id
    LEFT JOIN (
        SELECT user_id, MAX(created_at) AS last_active
        FROM sessions
        GROUP BY user_id
    ) s ON s.user_id = u.id
";

// WHERE clauses
$where = [];
$params = [];

if ($q !== '') {
    $where[] = "(u.name LIKE ? OR u.email LIKE ? OR u.referral_code LIKE ?)";
    $like = "%$q%";
    $params[] = $like;
    $params[] = $like;
    $params[] = $like;
}
if ($role !== '') {
    $where[] = "u.role = ?";
    $params[] = $role;
}
if ($status !== '' && $hasStatusCol) {
    $where[] = "u.status = ?";
    $params[] = $status;
}

// total count for pagination
$countSql = "SELECT COUNT(*) AS c " . $sqlBase;
if (!empty($where)) {
    $countSql .= " WHERE " . implode(" AND ", $where);
}
$stmt = $pdo->prepare($countSql);
$stmt->execute($params);
$totalRows = (int)$stmt->fetchColumn();
$totalPages = (int)ceil($totalRows / $perPage);

// final select
$selectSql = "SELECT u.*, COALESCE(p.enrolled_count,0) AS enrolled, s.last_active " . $sqlBase;
if (!empty($where)) {
    $selectSql .= " WHERE " . implode(" AND ", $where);
}
$selectSql .= " ORDER BY u.id DESC LIMIT $perPage OFFSET $offset";

$stmt = $pdo->prepare($selectSql);
$stmt->execute($params);
$users = $stmt->fetchAll();

function e($v){ return htmlspecialchars($v, ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title>User Management - Admin</title>
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

    <!-- Main content -->
    <div class="flex-1 overflow-auto">
        <header class="bg-white shadow-sm border-b">
            <div class="px-6 py-4 flex justify-between items-center">
                <h2 class="text-2xl font-semibold text-gray-800">User Management</h2>
                <a href="user-edit.php" class="px-4 py-2 bg-primary text-white rounded-lg hover:bg-primary-dark">+ Add User</a>
            </div>
        </header>

        <main class="p-6">
            <!-- Stats (simple totals) -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Total Users</p>
                            <p class="text-2xl font-bold text-gray-900"><?= number_format($totalRows) ?></p>
                        </div>
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">👥</div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <?php
                            // quick role counts (students / instructors / admins)
                            $rstmt = $pdo->query("SELECT role, COUNT(*) AS c FROM users GROUP BY role");
                            $roleCounts = [];
                            while($r = $rstmt->fetch()) $roleCounts[$r['role']] = $r['c'];
                            ?>
                            <p class="text-sm font-medium text-gray-500">Students</p>
                            <p class="text-2xl font-bold text-gray-900"><?= number_format($roleCounts['student'] ?? 0) ?></p>
                        </div>
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">🎓</div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Instructors</p>
                            <p class="text-2xl font-bold text-gray-900"><?= number_format($roleCounts['instructor'] ?? 0) ?></p>
                        </div>
                        <div class="w-8 h-8 bg-yellow-100 rounded-full flex items-center justify-center">🏫</div>
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-gray-500">Admins</p>
                            <p class="text-2xl font-bold text-gray-900"><?= number_format($roleCounts['admin'] ?? 0) ?></p>
                        </div>
                        <div class="w-8 h-8 bg-red-100 rounded-full flex items-center justify-center">⚙️</div>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow mb-6 p-4">
                <form method="get" class="flex flex-wrap gap-3 items-center">
                    <input type="text" name="q" value="<?= e($q) ?>" placeholder="Search users (name, email, referral)" class="px-4 py-2 border border-gray-300 rounded-lg w-64" />
                    <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Roles</option>
                        <option value="student" <?= $role==='student'?'selected':'' ?>>Student</option>
                        <option value="instructor" <?= $role==='instructor'?'selected':'' ?>>Instructor</option>
                        <option value="admin" <?= $role==='admin'?'selected':'' ?>>Admin</option>
                    </select>

                    <?php if ($hasStatusCol): ?>
                        <select name="status" class="px-4 py-2 border border-gray-300 rounded-lg">
                            <option value="">All Status</option>
                            <option value="active" <?= $status==='active'?'selected':'' ?>>Active</option>
                            <option value="inactive" <?= $status==='inactive'?'selected':'' ?>>Inactive</option>
                            <option value="pending" <?= $status==='pending'?'selected':'' ?>>Pending</option>
                            <option value="suspended" <?= $status==='suspended'?'selected':'' ?>>Suspended</option>
                        </select>
                    <?php endif; ?>

                    <button class="px-4 py-2 bg-primary text-white rounded-lg">Apply Filters</button>
                    <a href="users.php" class="px-4 py-2 border rounded-lg">Reset</a>
                </form>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b">
                    <h3 class="text-lg font-semibold text-gray-800">All Users</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left">User</th>
                                <th class="px-6 py-3 text-left">Role</th>
                                <th class="px-6 py-3 text-left">Location</th>
                                <th class="px-6 py-3 text-left">Enrolled</th>
                                <th class="px-6 py-3 text-left">Progress</th>
                                <th class="px-6 py-3 text-left">Join Date</th>
                                <th class="px-6 py-3 text-left">Last Active</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-left">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php if (empty($users)): ?>
                                <tr><td colspan="9" class="p-4 text-center text-gray-500">No users found.</td></tr>
                            <?php endif; ?>

                            <?php foreach ($users as $u): ?>
                                <?php
                                    // determine status fallback if column not present
                                    $statusLabel = 'Inactive';
                                    if ($hasStatusCol) {
                                        $statusLabel = $u['status'] ?? 'inactive';
                                    } else {
                                        // fallback: active if has session row with future expires_at
                                        $ses = $pdo->prepare("SELECT COUNT(*) FROM sessions WHERE user_id = ? AND (expires_at IS NULL OR expires_at > NOW())");
                                        $ses->execute([$u['id']]);
                                        $hasSession = (int)$ses->fetchColumn() > 0;
                                        $statusLabel = $hasSession ? 'active' : 'inactive';
                                    }

                                    // last active (from s.last_active)
                                    $lastActive = $u['last_active'] ? date('M d, Y H:i', strtotime($u['last_active'])) : '—';

                                    // small heuristics for progress display:
                                    // we don't have a progress % in users table; use average completed lessons as a placeholder (optional)
                                    // here we show enrolled count as stored in query
                                    $enrolled = intval($u['enrolled'] ?? 0);

                                    // progress% placeholder: if enrolments>0 -> fetch completed lessons count / total lessons for those courses is expensive;
                                    // we display dash or arbitrary sample if you want to compute properly later.
                                    $progressDisplay = $u['progress_percent'] ?? '—';
                                ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <?php
                                                $initials = 'U';
                                                if (!empty($u['name'])) {
                                                    $parts = preg_split('/\s+/', $u['name']);
                                                    $initials = substr($parts[0],0,1) . (isset($parts[1])?substr($parts[1],0,1):'');
                                                    $initials = strtoupper($initials);
                                                }
                                            ?>
                                            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-medium mr-4"><?= e($initials) ?></div>
                                            <div>
                                                <div class="text-sm font-medium text-gray-900"><?= e($u['name']) ?></div>
                                                <div class="text-sm text-gray-500"><?= e($u['email']) ?></div>
                                                <div class="text-xs text-gray-400">ID: #USR<?= str_pad($u['id'],6,'0',STR_PAD_LEFT) ?></div>
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if ($u['role']==='instructor'): ?>
                                            <span class="px-2 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">Instructor</span>
                                        <?php elseif ($u['role']==='admin'): ?>
                                            <span class="px-2 py-1 text-xs font-medium bg-red-100 text-red-800 rounded-full">Admin</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">Student</span>
                                        <?php endif; ?>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= e($u['location'] ?? '—') ?></td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= number_format($enrolled) ?> <?= ($u['role']==='instructor') ? 'Teaching' : 'Enrolled' ?></td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-full bg-gray-200 rounded-full h-2 mr-2">
                                                <?php
                                                    // lightweight progress heuristic: use 0% for students with no enrollments, else random placeholder
                                                    $pct = ($enrolled>0) ? min(100, 20 + ($enrolled * 3)) : 0;
                                                ?>
                                                <div class="bg-green-600 h-2 rounded-full" style="width: <?= $pct ?>%"></div>
                                            </div>
                                            <span class="text-xs text-gray-600"><?= $pct ?>%</span>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= date("M d, Y", strtotime($u['created_at'])) ?></td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?= e($lastActive) ?></td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php
                                            $badgeCol = 'bg-gray-100 text-gray-800';
                                            if ($statusLabel==='active' || $statusLabel==='Active') $badgeCol='bg-green-100 text-green-800';
                                            if ($statusLabel==='pending' || $statusLabel==='Pending') $badgeCol='bg-yellow-100 text-yellow-800';
                                            if ($statusLabel==='suspended' || $statusLabel==='Suspended') $badgeCol='bg-red-100 text-red-800';
                                        ?>
                                        <span class="px-2 py-1 text-xs font-medium <?= $badgeCol ?> rounded-full"><?= ucfirst($statusLabel) ?></span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="user-view.php?id=<?= $u['id'] ?>" class="text-primary hover:text-primary-dark mr-3">View</a>
                                        <a href="user-edit.php?id=<?= $u['id'] ?>" class="text-indigo-600 mr-3">Edit</a>

                                        <?php if ($hasStatusCol): ?>
                                            <?php if ($statusLabel==='suspended'): ?>
                                                <a href="user-toggle.php?id=<?= $u['id'] ?>&action=activate" onclick="return confirm('Activate this user?')" class="text-green-600 mr-3">Activate</a>
                                            <?php else: ?>
                                                <a href="user-toggle.php?id=<?= $u['id'] ?>&action=suspend" onclick="return confirm('Suspend this user?')" class="text-red-600 mr-3">Suspend</a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <!-- fallback: show a delete action if no status column -->
                                            <a href="user-toggle.php?id=<?= $u['id'] ?>&action=toggle_session" onclick="return confirm('Toggle session (force logout) for this user?')" class="text-yellow-600 mr-3">Force Logout</a>
                                        <?php endif; ?>

                                        <a href="user-delete.php?id=<?= $u['id'] ?>" onclick="return confirm('Delete this user? All related data will be removed.')" class="text-red-600">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Footer / Pagination -->
                <div class="px-6 py-4 border-t flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Showing <?= min($totalRows, $offset+1) ?> to <?= min($totalRows, $offset + count($users)) ?> of <?= number_format($totalRows) ?> results
                    </div>
                    <div class="flex space-x-2">
                        <?php if($page>1): ?>
                            <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page-1])) ?>" class="px-3 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50">Previous</a>
                        <?php endif; ?>

                        <?php for($p=1;$p<=$totalPages;$p++): ?>
                            <a href="?<?= http_build_query(array_merge($_GET,['page'=>$p])) ?>" class="px-3 py-2 text-sm <?= $p===$page ? 'bg-primary text-white rounded' : 'border border-gray-300 rounded hover:bg-gray-50' ?>"><?= $p ?></a>
                        <?php endfor; ?>

                        <?php if($page<$totalPages): ?>
                            <a href="?<?= http_build_query(array_merge($_GET,['page'=>$page+1])) ?>" class="px-3 py-2 text-sm border border-gray-300 rounded hover:bg-gray-50">Next</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>
</body>
</html>
