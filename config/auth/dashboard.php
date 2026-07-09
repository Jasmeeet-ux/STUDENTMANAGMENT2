<?php
require_once __DIR__ . '/../db.php';
session_start();
if (!isset($_SESSION['admin_username'])) { header("Location: login.php"); exit; }

$totalStudents  = $pdo->query("SELECT COUNT(*) FROM user_details")->fetchColumn();
$totalBatches   = $pdo->query("SELECT COUNT(*) FROM batches")->fetchColumn();
$totalCourses   = $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
$totalSubAdmins = $pdo->query("SELECT COUNT(*) FROM sub_admins")->fetchColumn();

$recentStudents = $pdo->query("
    SELECT name, reg_no, batch_no, startingdate FROM user_details
    ORDER BY STR_TO_DATE(startingdate,'%Y-%m-%d') DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

$recentBatches = $pdo->query("
    SELECT b.batch_name, b.timing_start, b.timing_end, c.course_name
    FROM batches b LEFT JOIN courses c ON c.id=b.course_id
    ORDER BY b.id DESC LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Dashboard | Admin</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:#f1f5f9;display:flex;}

/* ── SIDEBAR ── */
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

/* ── MAIN ── */
.main{margin-left:245px;flex:1;padding:30px;min-height:100vh;}
.topbar{margin-bottom:28px;}
.topbar h1{font-size:22px;font-weight:800;color:#0f172a;}
.topbar p{font-size:13px;color:#64748b;margin-top:4px;}

.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:28px;}
.stat-card{background:#fff;border-radius:14px;padding:22px 20px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);transition:transform 0.15s,box-shadow 0.15s;}
.stat-card:hover{transform:translateY(-3px);box-shadow:0 6px 20px rgba(0,0,0,0.08);}
.stat-icon{font-size:30px;margin-bottom:10px;}
.stat-val{font-size:32px;font-weight:800;line-height:1;margin-bottom:4px;}
.stat-lbl{font-size:11.5px;color:#64748b;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;}
.c-blue{color:#2563eb;}.c-green{color:#16a34a;}.c-purple{color:#7c3aed;}.c-orange{color:#ea580c;}

.sec-title{font-size:11.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:12px;}
.qa-row{display:flex;gap:12px;flex-wrap:wrap;margin-bottom:28px;}
.qa-btn{display:inline-flex;align-items:center;gap:8px;padding:11px 20px;border-radius:10px;font-size:13.5px;font-weight:700;text-decoration:none;transition:opacity 0.15s;}
.qa-btn:hover{opacity:0.85;}
.qa-blue{background:#2563eb;color:#fff;}.qa-green{background:#16a34a;color:#fff;}
.qa-purple{background:#7c3aed;color:#fff;}.qa-slate{background:#475569;color:#fff;}

.content-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;}
.card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);overflow:hidden;}
.card-head{padding:15px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;}
.card-head h3{font-size:14px;font-weight:700;color:#0f172a;}
.card-head a{font-size:12px;color:#2563eb;font-weight:600;text-decoration:none;}
.card-head a:hover{text-decoration:underline;}
.mini-table{width:100%;border-collapse:collapse;}
.mini-table td{padding:11px 20px;font-size:13px;color:#334155;border-bottom:1px solid #f8fafc;vertical-align:middle;}
.mini-table tr:last-child td{border-bottom:none;}
.mini-table tr:hover td{background:#fafcff;}
.s-name{font-weight:600;color:#0f172a;}
.s-sub{font-size:11px;color:#94a3b8;margin-top:1px;}
.pill{display:inline-block;padding:2px 9px;border-radius:20px;font-size:11px;font-weight:700;}
.pill-blue{background:#eff6ff;color:#1d4ed8;}
.empty-card{padding:30px;text-align:center;color:#94a3b8;font-size:13px;}
</style>
</head>
<body>

<div class="sidebar">
  <div class="sb-brand"><h2>🎓 Admin Panel</h2><p>Culture of Internet</p></div>
  <div class="sb-sec">Main</div>
  <a href="dashboard.php" class="active"><span class="sb-icon">🏠</span>Dashboard</a>
  <div class="sb-sec">Students</div>
  <a href="add_student.php"><span class="sb-icon">➕</span>Add Student</a>
  <a href="students_list.php"><span class="sb-icon">📋</span>Students List</a>
  <div class="sb-sec">Batches &amp; Courses</div>
  <a href="batch.php"><span class="sb-icon">🏫</span>Batches</a>
  <a href="view_batch.php"><span class="sb-icon">👁️</span>View Batch</a>
  <a href="courses.php"><span class="sb-icon">📚</span>Courses</a>
  <div class="sb-sec">Sub Admins</div>
  <a href="add_sub_admin.php"><span class="sb-icon">👤</span>Add Sub Admin</a>
  <a href="sub_admins_list.php"><span class="sb-icon">👥</span>Sub Admins List</a>
  <div class="sb-bottom"><a href="logout.php"><span class="sb-icon">🚪</span>Logout</a></div>
</div>

<div class="main">
  <div class="topbar">
    <h1>🏠 Dashboard</h1>
    <p>Welcome back, <strong><?= h($_SESSION['admin_username']) ?></strong>! Here's your overview.</p>
  </div>

  <div class="stats">
    <div class="stat-card"><div class="stat-icon">👨‍🎓</div><div class="stat-val c-blue"><?= $totalStudents ?></div><div class="stat-lbl">Total Students</div></div>
    <div class="stat-card"><div class="stat-icon">🏫</div><div class="stat-val c-green"><?= $totalBatches ?></div><div class="stat-lbl">Total Batches</div></div>
    <div class="stat-card"><div class="stat-icon">📚</div><div class="stat-val c-purple"><?= $totalCourses ?></div><div class="stat-lbl">Total Courses</div></div>
    <div class="stat-card"><div class="stat-icon">👤</div><div class="stat-val c-orange"><?= $totalSubAdmins ?></div><div class="stat-lbl">Sub Admins</div></div>
  </div>

  <div class="sec-title">Quick Actions</div>
  <div class="qa-row">
    <a href="add_student.php"   class="qa-btn qa-blue">➕ Add Student</a>
    <a href="batch.php"         class="qa-btn qa-green">🏫 Manage Batches</a>
    <a href="view_batch.php"    class="qa-btn qa-purple">👁️ View Batch</a>
    <a href="add_sub_admin.php" class="qa-btn qa-slate">👤 Add Sub Admin</a>
  </div>

  <div class="content-grid">
    <div class="card">
      <div class="card-head"><h3>👨‍🎓 Recent Students</h3><a href="students_list.php">View All →</a></div>
      <?php if($recentStudents): ?>
      <table class="mini-table">
        <?php foreach($recentStudents as $s): ?>
        <tr>
          <td><div class="s-name"><?= h($s['name']) ?></div><div class="s-sub"><?= h($s['reg_no']) ?></div></td>
          <td><?php if(!empty($s['batch_no'])): ?><span class="pill pill-blue"><?= h($s['batch_no']) ?></span><?php endif; ?></td>
          <td style="color:#94a3b8;font-size:12px;"><?= !empty($s['startingdate'])?date('d M Y',strtotime($s['startingdate'])):'—' ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <?php else: ?><div class="empty-card">No students yet.</div><?php endif; ?>
    </div>

    <div class="card">
      <div class="card-head"><h3>🏫 Recent Batches</h3><a href="batch.php">View All →</a></div>
      <?php if($recentBatches): ?>
      <table class="mini-table">
        <?php foreach($recentBatches as $b): ?>
        <tr>
          <td>
            <div class="s-name"><?= h($b['batch_name']) ?></div>
            <?php if(!empty($b['course_name'])): ?><div class="s-sub"><?= h($b['course_name']) ?></div><?php endif; ?>
          </td>
          <td><?php if(!empty($b['timing_start'])): ?><span style="font-size:12px;color:#64748b;">⏰ <?= date('g:i A',strtotime($b['timing_start'])) ?> – <?= date('g:i A',strtotime($b['timing_end'])) ?></span><?php endif; ?></td>
        </tr>
        <?php endforeach; ?>
      </table>
      <?php else: ?><div class="empty-card">No batches yet.</div><?php endif; ?>
    </div>
  </div>
</div>
</body>
</html>