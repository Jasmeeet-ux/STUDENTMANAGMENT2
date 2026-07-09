<?php
require_once __DIR__ . '/../db.php';
session_start();
if (!isset($_SESSION['sub_admin_id'])) { header("Location: sub_admin_login.php"); exit; }

$sub_admin_id   = $_SESSION['sub_admin_id'];
$sub_admin_name = $_SESSION['sub_admin_name'] ?? 'Sub Admin';

// Fetch only assigned batches
$batches = $pdo->prepare("
    SELECT b.*, c.course_name,
        (SELECT COUNT(*) FROM user_details ud WHERE ud.batch_no = b.batch_name) as stu_count
    FROM sub_admin_batches sab
    JOIN batches b ON b.id = sab.batch_id
    LEFT JOIN courses c ON c.id = b.course_id
    WHERE sab.sub_admin_id = ?
    ORDER BY b.id DESC
");
$batches->execute([$sub_admin_id]);
$batches = $batches->fetchAll(PDO::FETCH_ASSOC);

function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Batches | Sub Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:#f1f5f9;display:flex;}
.sidebar{width:245px;min-width:245px;background:#0f172a;height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;overflow-y:auto;z-index:200;}
.sidebar::-webkit-scrollbar{width:3px;}
.sidebar::-webkit-scrollbar-thumb{background:#1e293b;border-radius:3px;}
.sb-brand{padding:22px 20px 16px;border-bottom:1px solid #1e293b;}
.sb-brand h2{font-size:15px;font-weight:800;color:#fff;}
.sb-brand p{font-size:11px;color:#475569;margin-top:3px;}
.sb-sec{font-size:9.5px;font-weight:700;color:#334155;text-transform:uppercase;letter-spacing:1.2px;padding:14px 20px 5px;}
.sidebar a{display:flex;align-items:center;gap:10px;color:#94a3b8;padding:9px 20px;text-decoration:none;font-size:13px;font-weight:500;transition:all 0.15s;border-left:3px solid transparent;}
.sidebar a:hover{background:#1e293b;color:#e2e8f0;border-left-color:#334155;}
.sidebar a.active{background:#1e3a8a;color:#fff;font-weight:700;border-left-color:#3b82f6;}
.sb-icon{font-size:15px;width:20px;text-align:center;}
.sb-bottom{margin-top:auto;border-top:1px solid #1e293b;padding:8px 0;}
.main{margin-left:245px;flex:1;padding:30px;min-height:100vh;}
.topbar{margin-bottom:24px;}
.topbar h1{font-size:22px;font-weight:800;color:#0f172a;}
.topbar p{font-size:13px;color:#64748b;margin-top:4px;}
.batches-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(290px,1fr));gap:18px;}
.bc{background:#fff;border-radius:16px;padding:22px;border:1px solid #e2e8f0;box-shadow:0 1px 8px rgba(0,0,0,0.05);transition:transform 0.15s,box-shadow 0.15s;}
.bc:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(0,0,0,0.09);}
.bc-top{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:12px;}
.bc-name{font-size:17px;font-weight:800;color:#0f172a;}
.bc-course{font-size:12px;color:#2563eb;font-weight:600;margin-top:3px;}
.bc-count{background:#eff6ff;color:#1d4ed8;padding:4px 12px;border-radius:20px;font-size:12px;font-weight:700;white-space:nowrap;}
.bc-meta{display:flex;flex-direction:column;gap:5px;margin-bottom:16px;}
.bc-meta span{font-size:12.5px;color:#64748b;display:flex;align-items:center;gap:6px;}
.bc-actions{display:grid;grid-template-columns:1fr 1fr;gap:8px;}
.bc-actions a{padding:9px 8px;border-radius:9px;font-size:12.5px;font-weight:700;cursor:pointer;text-decoration:none;text-align:center;transition:background 0.15s;}
.btn-students{background:#eff6ff;color:#2563eb;}
.btn-students:hover{background:#dbeafe;}
.btn-attendance{background:#f0fdf4;color:#16a34a;}
.btn-attendance:hover{background:#dcfce7;}
.btn-modules{background:#f5f3ff;color:#6d28d9;}
.btn-modules:hover{background:#ede9fe;}
.btn-sessions{background:#fff7ed;color:#c2410c;}
.btn-sessions:hover{background:#ffedd5;}
.empty{text-align:center;padding:60px 20px;color:#94a3b8;}
.empty-icon{font-size:48px;margin-bottom:12px;}
.empty h3{font-size:16px;font-weight:600;color:#64748b;}
</style>
</head>
<body>
<div class="sidebar">
  <div class="sb-brand"><h2>🎓 Sub Admin</h2><p><?= h($sub_admin_name) ?></p></div>
  <div class="sb-sec">Main</div>
  <a href="sub_admin_dashboard.php"><span class="sb-icon">🏠</span>Dashboard</a>
  <div class="sb-sec">Students</div>
  <a href="sub_admin_students.php"><span class="sb-icon">👨‍🎓</span>My Students</a>
  <div class="sb-sec">Batches</div>
  <a href="sub_admin_batch.php" class="active"><span class="sb-icon">🏫</span>My Batches</a>
  <div class="sb-sec">Attendance</div>
  <a href="sub_admin_attendance.php"><span class="sb-icon">✅</span>Mark Attendance</a>
  <div class="sb-bottom"><a href="sub_admin_logout.php"><span class="sb-icon">🚪</span>Logout</a></div>
</div>

<div class="main">
  <div class="topbar">
    <h1>🏫 My Batches</h1>
    <p>You have <?= count($batches) ?> assigned batch<?= count($batches)!=1?'es':'' ?></p>
  </div>

  <?php if($batches): ?>
  <div class="batches-grid">
    <?php foreach($batches as $b): ?>
    <div class="bc">
      <div class="bc-top">
        <div>
          <div class="bc-name"><?= h($b['batch_name']) ?></div>
          <?php if(!empty($b['course_name'])): ?><div class="bc-course">📚 <?= h($b['course_name']) ?></div><?php endif; ?>
        </div>
        <span class="bc-count">👨‍🎓 <?= $b['stu_count'] ?></span>
      </div>
      <div class="bc-meta">
        <?php if(!empty($b['timing_start'])): ?>
          <span>⏰ <?= date('g:i A',strtotime($b['timing_start'])) ?> – <?= date('g:i A',strtotime($b['timing_end'])) ?></span>
        <?php endif; ?>
        <?php if(!empty($b['day_type'])): ?>
          <span>📆 <?= ucfirst(h($b['day_type'])) ?></span>
        <?php endif; ?>
        <?php if(!empty($b['duration'])): ?>
          <span>⌛ <?= h($b['duration']) ?></span>
        <?php endif; ?>
      </div>
      <div class="bc-actions">
        <a href="sub_admin_students.php?batch=<?= urlencode($b['batch_name']) ?>" class="btn-students">👨‍🎓 Students</a>
        <a href="sub_admin_attendance.php?batch=<?= urlencode($b['batch_name']) ?>" class="btn-attendance">✅ Attendance</a>
        <a href="sub_admin_modules.php?batch_id=<?= $b['id'] ?>" class="btn-modules">🗂️ Modules</a>
        <a href="sub_admin_sessions.php?batch_id=<?= $b['id'] ?>" class="btn-sessions">📅 Sessions</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
  <div class="empty">
    <div class="empty-icon">🏫</div>
    <h3>No batches assigned yet</h3>
    <p>Contact your admin to get batches assigned</p>
  </div>
  <?php endif; ?>
</div>
</body>
</html>