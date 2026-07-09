<?php
require_once __DIR__ . '/../db.php';
session_start();

// 🔐 Admin protection
if (!isset($_SESSION['admin_username'])) {
    header("Location: login.php");
    exit;
}

// Ensure class_sessions table exists (safe if already created)
$pdo->exec("
CREATE TABLE IF NOT EXISTS class_sessions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    batch_no VARCHAR(50) NOT NULL,
    date DATE NOT NULL,
    start_time TIME NULL,
    end_time TIME NULL,
    topic VARCHAR(255) NOT NULL,
    status ENUM('scheduled','cancelled') NOT NULL DEFAULT 'scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_batch_date (batch_no, date)
)
");

// Batches for dropdown
$batches = $pdo->query("SELECT DISTINCT batch_no FROM user_details WHERE batch_no IS NOT NULL AND batch_no != '' ORDER BY batch_no")->fetchAll(PDO::FETCH_ASSOC);

// Selected batch / month
$selectedBatch = $_GET['batch'] ?? ($_POST['batch'] ?? '');
$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
if ($month < 1 || $month > 12) $month = (int)date('n');

$firstDay = sprintf('%04d-%02d-01', $year, $month);
$lastDay  = date('Y-m-t', strtotime($firstDay));

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' && $selectedBatch) {
        $date       = $_POST['date'] ?? '';
        $start_time = $_POST['start_time'] ?? null;
        $end_time   = $_POST['end_time'] ?? null;
        $topic      = trim($_POST['topic'] ?? '');

        if ($date && $topic !== '') {
            $stmt = $pdo->prepare("
                INSERT INTO class_sessions (batch_no, date, start_time, end_time, topic, status)
                VALUES (?,?,?,?,?, 'scheduled')
            ");
            $stmt->execute([$selectedBatch, $date, $start_time ?: null, $end_time ?: null, $topic]);
        }
    }

    if ($action === 'toggle' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $pdo->prepare("SELECT status FROM class_sessions WHERE id = ?");
        $stmt->execute([$id]);
        if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $newStatus = $row['status'] === 'cancelled' ? 'scheduled' : 'cancelled';
            $upd = $pdo->prepare("UPDATE class_sessions SET status = ? WHERE id = ?");
            $upd->execute([$newStatus, $id]);
        }
    }

    if ($action === 'delete' && isset($_POST['id'])) {
        $id = (int)$_POST['id'];
        $del = $pdo->prepare("DELETE FROM class_sessions WHERE id = ?");
        $del->execute([$id]);
    }

    // Redirect to avoid resubmission
    $qs = http_build_query([
        'batch' => $selectedBatch,
        'year'  => $year,
        'month' => $month,
    ]);
    header("Location: manage_sessions.php?$qs");
    exit;
}

// Fetch sessions for view
$sessions = [];
if ($selectedBatch) {
    $stmt = $pdo->prepare("
        SELECT id, batch_no, date, start_time, end_time, topic, status
        FROM class_sessions
        WHERE batch_no = ? AND date BETWEEN ? AND ?
        ORDER BY date, start_time
    ");
    $stmt->execute([$selectedBatch, $firstDay, $lastDay]);
    $sessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Manage Offline Sessions | Admin Panel</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }
body { font-family:'DM Sans',sans-serif; background:#f8fafc; }

.sidebar {
  width:240px; background:#0f172a;
  height:100vh; position:fixed;
  padding:20px; color:#fff;
}
.sidebar h2 { margin-bottom:30px; font-size:18px; }
.sidebar a {
  display:block; color:#cbd5f5;
  padding:12px 14px; border-radius:8px;
  margin-bottom:8px; text-decoration:none; font-size:14px;
}
.sidebar a:hover { background:#1e293b; color:#fff; }
.sidebar a.active { background:#2563eb; color:#fff; }

.main {
  margin-left:260px;
  min-height:100vh;
  display:flex; flex-direction:column;
}

.page-header {
  padding:24px 32px 18px;
  background:#fff;
  border-bottom:1px solid #e2e8f0;
  display:flex; justify-content:space-between; align-items:center;
}
.page-header h1 { font-size:20px; font-weight:700; color:#0f172a; }
.logout {
  background:#ef4444; color:white;
  padding:8px 14px; border-radius:6px;
  text-decoration:none; font-size:13px; font-weight:500;
}

.filters {
  padding:14px 32px;
  background:#fff;
  border-bottom:1px solid #e2e8f0;
  display:flex; flex-wrap:wrap; gap:12px; align-items:center;
}
.filters label {
  font-size:12px; font-weight:600; color:#475569;
}
.filters select, .filters input[type=month] {
  padding:8px 12px;
  border-radius:8px;
  border:1.5px solid #e2e8f0;
  font-size:13px; font-family:'DM Sans',sans-serif;
}
.filters button {
  padding:9px 16px; border-radius:8px;
  border:none; background:#2563eb; color:#fff;
  font-size:13px; font-weight:600; cursor:pointer;
}
.filters button.secondary { background:#e5e7eb; color:#111827; }

.content {
  padding:18px 32px 32px;
  display:grid;
  grid-template-columns: minmax(260px, 320px) minmax(0,1fr);
  gap:18px;
}

.card {
  background:#fff; border-radius:12px;
  box-shadow:0 2px 10px rgba(15,23,42,0.06);
  padding:18px 18px 20px;
}
.card h2 {
  font-size:14px; font-weight:700; color:#0f172a;
  margin-bottom:10px;
}
.card p.small { font-size:12px; color:#94a3b8; margin-bottom:10px; }

.form-row { display:flex; flex-direction:column; gap:8px; margin-bottom:12px; }
.form-row label { font-size:12px; font-weight:600; color:#475569; }
.form-row input, .form-row textarea {
  width:100%; padding:9px 11px;
  border-radius:8px; border:1.5px solid #e2e8f0;
  font-size:13px; font-family:'DM Sans',sans-serif;
}
.form-row textarea { resize:vertical; min-height:70px; }
.add-btn {
  width:100%; margin-top:6px;
  padding:10px 14px; border-radius:9px;
  border:none; background:#16a34a; color:#fff;
  font-size:13px; font-weight:700; cursor:pointer;
}
.add-btn:hover { background:#15803d; }

.sessions-table-wrap {
  max-height:520px; overflow:auto;
}
.sessions-table {
  width:100%; border-collapse:collapse; font-size:13px;
}
.sessions-table thead {
  background:#f9fafb; position:sticky; top:0; z-index:1;
}
.sessions-table th, .sessions-table td {
  padding:9px 8px; border-bottom:1px solid #e5e7eb;
  text-align:left; white-space:nowrap;
}
.sessions-table th { font-size:11px; text-transform:uppercase; color:#6b7280; letter-spacing:0.04em; }
.pill {
  display:inline-flex; align-items:center; padding:3px 9px; border-radius:999px;
  font-size:11px; font-weight:600;
}
.pill.scheduled { background:#dcfce7; color:#166534; }
.pill.cancelled { background:#fee2e2; color:#991b1b; }
.pill.offline { background:#eff6ff; color:#1d4ed8; }

.actions-cell form { display:inline; }
.btn-small {
  border:none; border-radius:6px;
  padding:4px 8px; font-size:11px; font-weight:600;
  cursor:pointer; margin-right:4px;
}
.btn-cancel { background:#fee2e2; color:#b91c1c; }
.btn-uncancel { background:#dcfce7; color:#166534; }
.btn-delete { background:#f9fafb; color:#6b7280; }
</style>
</head>
<body>

<div class="sidebar">
  <h2>Admin Panel</h2>
  <a href="dashboard.php">🏠 Dashboard</a>
  <a href="add_student.php">➕ Add Student</a>
  <a href="students_list.php">📋 Students List</a>
  <a href="attendance.php">✅ Attendance</a>
  <a href="manage_sessions.php" class="active">📅 Offline Sessions</a>
  <a href="logout.php">🚪 Logout</a>
</div>

<div class="main">
  <div class="page-header">
    <h1>Manage Offline Sessions</h1>
    <a href="logout.php" class="logout">Logout</a>
  </div>

  <form method="get" class="filters">
    <div>
      <label>Batch</label><br>
      <select name="batch" required>
        <option value="">-- Select batch --</option>
        <?php foreach ($batches as $b): ?>
          <option value="<?= h($b['batch_no']) ?>" <?= $selectedBatch === $b['batch_no'] ? 'selected' : '' ?>>
            <?= h($b['batch_no']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
    <div>
      <label>Month</label><br>
      <input type="month" name="monthpicker" value="<?= h(sprintf('%04d-%02d', $year, $month)) ?>">
    </div>
    <div style="margin-top:18px;">
      <button type="submit" onclick="
        const mp = this.form.monthpicker.value;
        if(mp){
          const [y,m] = mp.split('-');
          this.form.action='manage_sessions.php?'+
            'batch='+encodeURIComponent(this.form.batch.value)+
            '&year='+y+'&month='+parseInt(m,10);
        }
      ">Apply</button>
    </div>
  </form>

  <div class="content">
    <div class="card">
      <h2>Add New Class</h2>
      <p class="small">Create offline classes for the selected batch and month.</p>
      <?php if (!$selectedBatch): ?>
        <p class="small" style="color:#b91c1c;">Select a batch above to add sessions.</p>
      <?php else: ?>
      <form method="post">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="batch" value="<?= h($selectedBatch) ?>">
        <input type="hidden" name="year" value="<?= (int)$year ?>">
        <input type="hidden" name="month" value="<?= (int)$month ?>">

        <div class="form-row">
          <label>Date</label>
          <input type="date" name="date" required min="<?= h($firstDay) ?>" max="<?= h($lastDay) ?>">
        </div>
        <div class="form-row">
          <label>Time (Start – End)</label>
          <div style="display:flex; gap:8px;">
            <input type="time" name="start_time">
            <input type="time" name="end_time">
          </div>
        </div>
        <div class="form-row">
          <label>Topic / Class Title</label>
          <textarea name="topic" placeholder="e.g. SEO Basics, Domain & Hosting, Project Review" required></textarea>
        </div>
        <button type="submit" class="add-btn">➕ Add Class</button>
      </form>
      <?php endif; ?>
    </div>

    <div class="card">
      <h2><?= h($selectedBatch ?: 'Select a batch') ?> — <?= date('F Y', strtotime($firstDay)) ?></h2>
      <p class="small">
        Classes listed here are what students see on their Sessions page for this batch and month.
        Cancelling keeps the class in history but marks it as cancelled. Deleting removes it completely.
      </p>
      <div class="sessions-table-wrap">
        <table class="sessions-table">
          <thead>
            <tr>
              <th>Date</th>
              <th>Day</th>
              <th>Time</th>
              <th>Topic</th>
              <th>Status</th>
              <th>Mode</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
          <?php if (!$selectedBatch): ?>
            <tr><td colspan="7">Select a batch to view sessions.</td></tr>
          <?php elseif (!$sessions): ?>
            <tr><td colspan="7">No classes scheduled for this month yet.</td></tr>
          <?php else: ?>
            <?php foreach ($sessions as $s): ?>
              <?php
                $d = strtotime($s['date']);
                $dayName = date('D', $d);
                $timeStr = '—';
                if (!empty($s['start_time']) && !empty($s['end_time'])) {
                    $timeStr = date('g:i A', strtotime($s['start_time'])) . ' – ' . date('g:i A', strtotime($s['end_time']));
                }
              ?>
              <tr>
                <td><?= h(date('d M', $d)) ?></td>
                <td><?= h($dayName) ?></td>
                <td><?= h($timeStr) ?></td>
                <td><?= h($s['topic']) ?></td>
                <td>
                  <span class="pill <?= $s['status'] === 'cancelled' ? 'cancelled' : 'scheduled' ?>">
                    <?= $s['status'] === 'cancelled' ? 'Cancelled' : 'Scheduled' ?>
                  </span>
                </td>
                <td><span class="pill offline">Offline</span></td>
                <td class="actions-cell">
                  <form method="post" style="display:inline;">
                    <input type="hidden" name="action" value="toggle">
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                    <input type="hidden" name="batch" value="<?= h($selectedBatch) ?>">
                    <input type="hidden" name="year" value="<?= (int)$year ?>">
                    <input type="hidden" name="month" value="<?= (int)$month ?>">
                    <button type="submit" class="btn-small <?= $s['status'] === 'cancelled' ? 'btn-uncancel' : 'btn-cancel' ?>">
                      <?= $s['status'] === 'cancelled' ? 'Un‑cancel' : 'Cancel' ?>
                    </button>
                  </form>
                  <form method="post" style="display:inline;" onsubmit="return confirm('Delete this class completely?');">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="id" value="<?= (int)$s['id'] ?>">
                    <input type="hidden" name="batch" value="<?= h($selectedBatch) ?>">
                    <input type="hidden" name="year" value="<?= (int)$year ?>">
                    <input type="hidden" name="month" value="<?= (int)$month ?>">
                    <button type="submit" class="btn-small btn-delete">Delete</button>
                  </form>
                </td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

</body>
</html>

