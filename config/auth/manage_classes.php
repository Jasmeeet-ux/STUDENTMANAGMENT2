<?php
require_once __DIR__ . '/../db.php';
session_start();

if (!isset($_SESSION['admin_username'])) {
    header("Location: login.php");
    exit;
}

$success = "";
$error   = "";

// Fetch batches
$batches = [];
try {
    $batches = $pdo->query("SELECT id, batch_name FROM batches ORDER BY id ASC")->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) {}

// ── Handle Add Class ──
if (isset($_POST['add_class'])) {
    $batch_no   = trim($_POST['batch_no']);
    $date       = trim($_POST['date']);
    $start_time = trim($_POST['start_time']);
    $end_time   = trim($_POST['end_time']);
    $topic      = trim($_POST['topic']);
    $status     = 'scheduled';

    if (!$batch_no || !$date || !$start_time || !$end_time || !$topic) {
        $error = "All fields are required.";
    } else {
        $pdo->prepare("INSERT INTO class_sessions (batch_no, date, start_time, end_time, topic, status) VALUES (?,?,?,?,?,?)")
            ->execute([$batch_no, $date, $start_time, $end_time, $topic, $status]);
        $success = "Class scheduled successfully!";
    }
}

// ── Handle Status Update (cancel/complete/reschedule) ──
if (isset($_POST['update_status'])) {
    $id     = (int)$_POST['session_id'];
    $status = trim($_POST['status']);
    $pdo->prepare("UPDATE class_sessions SET status = ? WHERE id = ?")->execute([$status, $id]);
    header("Location: manage_classes.php?updated=1");
    exit;
}

// ── Handle Edit ──
if (isset($_POST['edit_class'])) {
    $id         = (int)$_POST['session_id'];
    $date       = trim($_POST['date']);
    $start_time = trim($_POST['start_time']);
    $end_time   = trim($_POST['end_time']);
    $topic      = trim($_POST['topic']);
    $batch_no   = trim($_POST['batch_no']);
    $pdo->prepare("UPDATE class_sessions SET date=?, start_time=?, end_time=?, topic=?, batch_no=? WHERE id=?")
        ->execute([$date, $start_time, $end_time, $topic, $batch_no, $id]);
    header("Location: manage_classes.php?updated=1");
    exit;
}

// ── Handle Delete ──
if (isset($_POST['delete_class'])) {
    $id = (int)$_POST['session_id'];
    $pdo->prepare("DELETE FROM class_sessions WHERE id = ?")->execute([$id]);
    header("Location: manage_classes.php?deleted=1");
    exit;
}

// ── Filters ──
$filter_batch  = $_GET['batch']  ?? '';
$filter_status = $_GET['status'] ?? '';
$filter_month  = $_GET['month']  ?? date('Y-m');

// Build query
$where = ["1=1"];
$params = [];

if ($filter_batch) {
    $where[] = "batch_no = ?";
    $params[] = $filter_batch;
}
if ($filter_status) {
    $where[] = "status = ?";
    $params[] = $filter_status;
}
if ($filter_month) {
    $where[] = "DATE_FORMAT(date, '%Y-%m') = ?";
    $params[] = $filter_month;
}

$whereStr = implode(' AND ', $where);
$sessions = $pdo->prepare("SELECT * FROM class_sessions WHERE $whereStr ORDER BY date DESC, start_time DESC");
$sessions->execute($params);
$sessions = $sessions->fetchAll(PDO::FETCH_ASSOC);

// Stats
$totalAll = $pdo->query("SELECT COUNT(*) FROM class_sessions")->fetchColumn();
$totalScheduled = $pdo->query("SELECT COUNT(*) FROM class_sessions WHERE status='scheduled'")->fetchColumn();
$totalCompleted = $pdo->query("SELECT COUNT(*) FROM class_sessions WHERE status='completed'")->fetchColumn();
$totalCancelled = $pdo->query("SELECT COUNT(*) FROM class_sessions WHERE status='cancelled'")->fetchColumn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Classes | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:#f1f5f9;}
.sidebar{width:240px;background:#0f172a;height:100vh;position:fixed;padding:20px;color:#fff;}
.sidebar h2{margin-bottom:30px;font-size:18px;}
.sidebar a{display:block;color:#cbd5e1;padding:12px 14px;border-radius:8px;margin-bottom:6px;text-decoration:none;font-size:14px;font-weight:500;transition:background 0.15s;}
.sidebar a:hover{background:#1e293b;color:#fff;}
.sidebar a.active{background:#2563eb;color:#fff;}
.sidebar .sep{font-size:10px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:1px;padding:10px 14px 4px;}
.main{margin-left:260px;padding:30px;}
.page-header{display:flex;justify-content:space-between;align-items:center;margin-bottom:24px;}
.page-header h1{font-size:22px;font-weight:700;color:#0f172a;}
.page-header p{font-size:13px;color:#64748b;margin-top:2px;}
.logout{background:#ef4444;color:white;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;}

/* Stats */
.stats-row{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:24px;}
.stat-card{background:#fff;padding:18px;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);border:1px solid #e2e8f0;}
.stat-card h3{font-size:12px;color:#64748b;font-weight:600;margin-bottom:6px;text-transform:uppercase;letter-spacing:0.5px;}
.stat-card p{font-size:26px;font-weight:700;color:#0f172a;}
.stat-card.blue p{color:#2563eb;}
.stat-card.green p{color:#16a34a;}
.stat-card.red p{color:#dc2626;}
.stat-card.yellow p{color:#d97706;}

/* Alert */
.alert{padding:12px 18px;border-radius:10px;margin-bottom:18px;font-size:13.5px;font-weight:500;}
.alert.success{background:#dcfce7;border:1px solid #bbf7d0;color:#166534;}
.alert.error{background:#fee2e2;border:1px solid #fecaca;color:#991b1b;}

/* Layout */
.content-grid{display:grid;grid-template-columns:380px 1fr;gap:20px;align-items:start;}

/* Add Class Form */
.form-card{background:#fff;border-radius:16px;padding:24px;box-shadow:0 2px 10px rgba(0,0,0,0.05);border:1px solid #e2e8f0;}
.form-card h2{font-size:15px;font-weight:700;color:#0f172a;margin-bottom:18px;padding-bottom:12px;border-bottom:2px solid #f1f5f9;}
.field{margin-bottom:14px;}
.field label{display:block;font-size:11.5px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;}
.field input,.field select,.field textarea{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13.5px;font-family:'DM Sans',sans-serif;color:#0f172a;background:#fafafa;outline:none;transition:border-color 0.2s;}
.field input:focus,.field select:focus,.field textarea:focus{border-color:#2563eb;background:#fff;}
.field textarea{resize:vertical;min-height:70px;}
.fields-row{display:grid;grid-template-columns:1fr 1fr;gap:12px;}
.btn-add{width:100%;padding:11px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:700;font-family:'DM Sans',sans-serif;cursor:pointer;margin-top:4px;transition:opacity 0.15s;}
.btn-add:hover{opacity:0.9;}

/* Filters */
.filter-bar{background:#fff;border-radius:12px;padding:16px 20px;margin-bottom:16px;border:1px solid #e2e8f0;display:flex;gap:12px;align-items:center;flex-wrap:wrap;}
.filter-bar select,.filter-bar input{padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;color:#0f172a;background:#fafafa;outline:none;}
.filter-bar select:focus,.filter-bar input:focus{border-color:#2563eb;}
.btn-filter{padding:8px 18px;background:#2563eb;color:#fff;border:none;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;}
.btn-clear{padding:8px 14px;background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;border-radius:8px;font-size:13px;font-weight:600;cursor:pointer;text-decoration:none;font-family:'DM Sans',sans-serif;}

/* Table */
.table-card{background:#fff;border-radius:16px;padding:20px;box-shadow:0 2px 10px rgba(0,0,0,0.05);border:1px solid #e2e8f0;overflow-x:auto;}
.table-card h2{font-size:15px;font-weight:700;color:#0f172a;margin-bottom:14px;}
table{width:100%;border-collapse:collapse;}
th{padding:10px 12px;text-align:left;font-size:11px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.6px;border-bottom:2px solid #e2e8f0;background:#f8fafc;}
td{padding:12px;font-size:13px;color:#334155;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tr:hover td{background:#fafcff;}

/* Status badges */
.badge{display:inline-flex;align-items:center;padding:3px 11px;border-radius:20px;font-size:11.5px;font-weight:700;}
.badge.scheduled{background:#eff6ff;color:#1d4ed8;}
.badge.completed{background:#dcfce7;color:#15803d;}
.badge.cancelled{background:#fee2e2;color:#dc2626;}

/* Action buttons */
.act-btns{display:flex;gap:6px;flex-wrap:wrap;}
.btn-sm{padding:5px 11px;border-radius:7px;font-size:11.5px;font-weight:700;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;transition:opacity 0.15s;}
.btn-sm:hover{opacity:0.8;}
.btn-complete-sm{background:#dcfce7;color:#15803d;}
.btn-cancel-sm{background:#fee2e2;color:#dc2626;}
.btn-edit-sm{background:#eff6ff;color:#1d4ed8;}
.btn-delete-sm{background:#fef3c7;color:#92400e;}
.btn-scheduled-sm{background:#f3f4f6;color:#374151;}

/* Edit Modal */
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:999;align-items:center;justify-content:center;}
.modal-overlay.active{display:flex;}
.modal{background:#fff;border-radius:16px;padding:28px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,0.2);}
.modal h3{font-size:17px;font-weight:700;margin-bottom:18px;padding-bottom:12px;border-bottom:2px solid #f1f5f9;}
.modal-btns{display:flex;gap:10px;margin-top:16px;}
.btn-save{flex:1;padding:11px;background:#2563eb;color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;}
.btn-save:hover{background:#1d4ed8;}
.btn-close-modal{flex:1;padding:11px;background:#f1f5f9;color:#475569;border:none;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;}
.btn-close-modal:hover{background:#e2e8f0;}

.empty-state{text-align:center;padding:40px 20px;color:#94a3b8;}
.empty-state .e-icon{font-size:36px;margin-bottom:10px;}
</style>
</head>
<body>

<div class="sidebar">
    <h2>Admin Panel</h2>
    <a href="dashboard.php">🏠 Dashboard</a>
    <div class="sep">Students</div>
    <a href="add_student.php">➕ Add Student</a>
    <a href="students_list.php">📋 Students List</a>
    <div class="sep">Sub Admins</div>
    <a href="add_sub_admin.php">👤 Add Sub Admin</a>
    <a href="sub_admins_list.php">📋 Sub Admins List</a>
    <div class="sep">Classes</div>
    <a href="manage_classes.php" class="active">📅 Manage Classes</a>
    <div class="sep">Other</div>
    <a href="attendance.php">✅ Attendance</a>
    <a href="batch.php">🏫 Batches</a>
    <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
    <div class="page-header">
        <div>
            <h1>📅 Manage Classes</h1>
            <p>Schedule, edit, cancel and manage all class sessions</p>
        </div>
        <a href="logout.php" class="logout">Logout</a>
    </div>

    <?php if ($success): ?>
        <div class="alert success">✅ <?= htmlspecialchars($success) ?></div>
    <?php endif; ?>
    <?php if ($error): ?>
        <div class="alert error">❌ <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if (isset($_GET['updated'])): ?>
        <div class="alert success">✅ Class updated successfully!</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert success">🗑️ Class deleted successfully!</div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="stats-row">
        <div class="stat-card blue"><h3>Total Classes</h3><p><?= $totalAll ?></p></div>
        <div class="stat-card yellow"><h3>Scheduled</h3><p><?= $totalScheduled ?></p></div>
        <div class="stat-card green"><h3>Completed</h3><p><?= $totalCompleted ?></p></div>
        <div class="stat-card red"><h3>Cancelled</h3><p><?= $totalCancelled ?></p></div>
    </div>

    <div class="content-grid">

        <!-- Add Class Form -->
        <div class="form-card">
            <h2>➕ Schedule New Class</h2>
            <form method="POST" action="">
                <div class="field">
                    <label>Batch</label>
                    <select name="batch_no" required>
                        <option value="" disabled selected>Select batch</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= htmlspecialchars($b['batch_name']) ?>"><?= htmlspecialchars($b['batch_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="field">
                    <label>Date</label>
                    <input type="date" name="date" required>
                </div>
                <div class="fields-row">
                    <div class="field">
                        <label>Start Time</label>
                        <input type="time" name="start_time" required>
                    </div>
                    <div class="field">
                        <label>End Time</label>
                        <input type="time" name="end_time" required>
                    </div>
                </div>
                <div class="field">
                    <label>Topic</label>
                    <textarea name="topic" placeholder="Class topic / title" required></textarea>
                </div>
                <button type="submit" name="add_class" class="btn-add">📅 Schedule Class</button>
            </form>
        </div>

        <!-- Classes List -->
        <div>
            <!-- Filter Bar -->
            <form method="GET" action="">
                <div class="filter-bar">
                    <select name="batch">
                        <option value="">All Batches</option>
                        <?php foreach ($batches as $b): ?>
                            <option value="<?= htmlspecialchars($b['batch_name']) ?>" <?= $filter_batch === $b['batch_name'] ? 'selected' : '' ?>><?= htmlspecialchars($b['batch_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="scheduled"  <?= $filter_status === 'scheduled'  ? 'selected' : '' ?>>Scheduled</option>
                        <option value="completed"  <?= $filter_status === 'completed'  ? 'selected' : '' ?>>Completed</option>
                        <option value="cancelled"  <?= $filter_status === 'cancelled'  ? 'selected' : '' ?>>Cancelled</option>
                    </select>
                    <input type="month" name="month" value="<?= htmlspecialchars($filter_month) ?>">
                    <button type="submit" class="btn-filter">Filter</button>
                    <a href="manage_classes.php" class="btn-clear">Clear</a>
                </div>
            </form>

            <div class="table-card">
                <h2>📋 Classes (<?= count($sessions) ?> found)</h2>
                <?php if (empty($sessions)): ?>
                    <div class="empty-state">
                        <div class="e-icon">📭</div>
                        <p>No classes found for selected filters.</p>
                    </div>
                <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Batch</th>
                            <th>Time</th>
                            <th>Topic</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $s): ?>
                        <tr>
                            <td>
                                <strong><?= date('d M', strtotime($s['date'])) ?></strong><br>
                                <span style="font-size:11px;color:#94a3b8;"><?= date('D', strtotime($s['date'])) ?></span>
                            </td>
                            <td style="font-weight:600;"><?= htmlspecialchars($s['batch_no']) ?></td>
                            <td style="font-size:12px;">
                                <?= date('g:i A', strtotime($s['start_time'])) ?><br>
                                <span style="color:#94a3b8;">to <?= date('g:i A', strtotime($s['end_time'])) ?></span>
                            </td>
                            <td style="max-width:180px;"><?= htmlspecialchars($s['topic']) ?></td>
                            <td>
                                <span class="badge <?= $s['status'] ?>">
                                    <?= $s['status'] === 'scheduled' ? '🕐' : ($s['status'] === 'completed' ? '✅' : '❌') ?>
                                    <?= ucfirst($s['status']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="act-btns">
                                    <?php if ($s['status'] !== 'completed'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                                        <input type="hidden" name="status" value="completed">
                                        <button type="submit" name="update_status" class="btn-sm btn-complete-sm">✅ Done</button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if ($s['status'] !== 'cancelled'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                                        <input type="hidden" name="status" value="cancelled">
                                        <button type="submit" name="update_status" class="btn-sm btn-cancel-sm">❌ Cancel</button>
                                    </form>
                                    <?php endif; ?>
                                    <?php if ($s['status'] === 'cancelled'): ?>
                                    <form method="POST" style="display:inline;">
                                        <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                                        <input type="hidden" name="status" value="scheduled">
                                        <button type="submit" name="update_status" class="btn-sm btn-scheduled-sm">🔄 Restore</button>
                                    </form>
                                    <?php endif; ?>
                                    <button class="btn-sm btn-edit-sm" onclick="openEdit(<?= htmlspecialchars(json_encode($s)) ?>)">✏️ Edit</button>
                                    <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this class?')">
                                        <input type="hidden" name="session_id" value="<?= $s['id'] ?>">
                                        <button type="submit" name="delete_class" class="btn-sm btn-delete-sm">🗑️</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <?php endif; ?>
            </div>
        </div>

    </div>
</div>

<!-- Edit Modal -->
<div class="modal-overlay" id="editModal">
    <div class="modal">
        <h3>✏️ Edit Class</h3>
        <form method="POST" action="">
            <input type="hidden" name="session_id" id="editId">
            <div class="field">
                <label>Batch</label>
                <select name="batch_no" id="editBatch">
                    <?php foreach ($batches as $b): ?>
                        <option value="<?= htmlspecialchars($b['batch_name']) ?>"><?= htmlspecialchars($b['batch_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="field">
                <label>Date</label>
                <input type="date" name="date" id="editDate" required>
            </div>
            <div class="fields-row">
                <div class="field">
                    <label>Start Time</label>
                    <input type="time" name="start_time" id="editStart" required>
                </div>
                <div class="field">
                    <label>End Time</label>
                    <input type="time" name="end_time" id="editEnd" required>
                </div>
            </div>
            <div class="field">
                <label>Topic</label>
                <textarea name="topic" id="editTopic" required></textarea>
            </div>
            <div class="modal-btns">
                <button type="submit" name="edit_class" class="btn-save">💾 Save Changes</button>
                <button type="button" class="btn-close-modal" onclick="closeEdit()">Cancel</button>
            </div>
        </form>
    </div>
</div>

<script>
function openEdit(s) {
    document.getElementById('editId').value    = s.id;
    document.getElementById('editDate').value  = s.date;
    document.getElementById('editStart').value = s.start_time;
    document.getElementById('editEnd').value   = s.end_time;
    document.getElementById('editTopic').value = s.topic;
    document.getElementById('editBatch').value = s.batch_no;
    document.getElementById('editModal').classList.add('active');
}
function closeEdit() {
    document.getElementById('editModal').classList.remove('active');
}
document.getElementById('editModal').addEventListener('click', function(e) {
    if (e.target === this) closeEdit();
});
</script>
</body>
</html>