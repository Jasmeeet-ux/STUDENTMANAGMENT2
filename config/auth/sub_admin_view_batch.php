<?php
require_once __DIR__ . '/../db.php';
session_start();
if (!isset($_SESSION['sub_admin_id'])) { header("Location: sub_admin_login.php"); exit; }

$sub_id   = (int)$_SESSION['sub_admin_id'];
$batch_id = (int)($_GET['id'] ?? 0);
if (!$batch_id) { header("Location: sub_admin_dashboard.php"); exit; }

// Verify this batch is assigned to sub admin
$chk = $pdo->prepare("SELECT id FROM sub_admin_batches WHERE sub_admin_id=? AND batch_id=?");
$chk->execute([$sub_id, $batch_id]);
if (!$chk->fetchColumn()) { header("Location: sub_admin_dashboard.php"); exit; }

// Batch info
$bq = $pdo->prepare("SELECT b.*, c.course_name, c.id as course_id FROM batches b LEFT JOIN courses c ON c.id=b.course_id WHERE b.id=?");
$bq->execute([$batch_id]);
$batch = $bq->fetch(PDO::FETCH_ASSOC);
if (!$batch) { header("Location: sub_admin_dashboard.php"); exit; }

// ✅ POST handler at TOP before any HTML output
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_attendance'])) {
    $att_date   = $_POST['att_date']  ?? '';
    $batch_name = $_POST['batch_no']  ?? $batch['batch_name'];
    if ($att_date && !empty($_POST['status'])) {
        foreach ($_POST['status'] as $reg => $status) {
            if (!in_array($status, ['P','A','L'])) continue;
            $exists = $pdo->prepare("SELECT id FROM attendance WHERE reg_no=? AND date=?");
            $exists->execute([$reg, $att_date]);
            if ($exists->fetchColumn()) {
                $pdo->prepare("UPDATE attendance SET status=?, batch_no=? WHERE reg_no=? AND date=?")
                    ->execute([$status, $batch_name, $reg, $att_date]);
            } else {
                $pdo->prepare("INSERT INTO attendance (reg_no,date,status,batch_no) VALUES (?,?,?,?)")
                    ->execute([$reg, $att_date, $status, $batch_name]);
            }
        }
    }
    header("Location: ?id=$batch_id&tab=attendance&date={$att_date}&saved=1");
    exit;
}

$course_id = (int)($batch['course_id'] ?? 0);

$students = $pdo->prepare("SELECT * FROM user_details WHERE batch_no=? ORDER BY name ASC");
$students->execute([$batch['batch_name']]);
$students = $students->fetchAll(PDO::FETCH_ASSOC);

$modules = [];
if ($course_id) {
    $mq = $pdo->prepare("SELECT * FROM course_modules WHERE course_id=? ORDER BY module_name, topic_order ASC");
    $mq->execute([$course_id]);
    foreach ($mq->fetchAll(PDO::FETCH_ASSOC) as $row) $modules[$row['module_name']][] = $row;
}

$sessions = $pdo->prepare("SELECT * FROM class_sessions WHERE batch_no=? ORDER BY date DESC");
$sessions->execute([$batch['batch_name']]);
$sessions = $sessions->fetchAll(PDO::FETCH_ASSOC);

$tab      = $_GET['tab'] ?? 'modules';
$att_date = $_GET['date'] ?? date('Y-m-d');

$existing_att = [];
foreach ($students as $s) {
    $eq = $pdo->prepare("SELECT status FROM attendance WHERE reg_no=? AND date=?");
    $eq->execute([$s['reg_no'], $att_date]);
    $existing_att[$s['reg_no']] = $eq->fetchColumn() ?: 'P';
}

$sub_admin_name = $_SESSION['sub_admin_name'] ?? 'Sub Admin';

function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= h($batch['batch_name']) ?> | Sub Admin</title>
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
.page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:22px;}
.page-header h1{font-size:21px;font-weight:800;color:#0f172a;}
.page-header p{font-size:13px;color:#64748b;margin-top:4px;}
.btn-back{background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;}
.batch-bar{background:#fff;border-radius:14px;padding:18px 22px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);margin-bottom:22px;display:flex;align-items:center;gap:24px;flex-wrap:wrap;}
.bb-item{display:flex;flex-direction:column;gap:2px;}
.bb-label{font-size:10.5px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;}
.bb-val{font-size:14px;font-weight:700;color:#0f172a;}
.bb-div{width:1px;height:36px;background:#e2e8f0;}
.tabs{display:flex;gap:4px;margin-bottom:22px;background:#fff;border-radius:12px;padding:5px;border:1px solid #e2e8f0;width:fit-content;box-shadow:0 1px 6px rgba(0,0,0,0.04);}
.tab-btn{padding:9px 22px;border-radius:9px;font-size:13.5px;font-weight:700;text-decoration:none;color:#64748b;transition:all 0.15s;}
.tab-btn:hover{background:#f1f5f9;color:#0f172a;}
.tab-btn.active{background:#2563eb;color:#fff;}
.module-block{background:#fff;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);margin-bottom:16px;overflow:hidden;}
.module-head{padding:14px 20px;background:linear-gradient(135deg,#1e3a8a,#2563eb);display:flex;align-items:center;justify-content:space-between;}
.module-head h3{font-size:14px;font-weight:800;color:#fff;}
.module-head span{background:rgba(255,255,255,0.2);color:#fff;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;}
.topic-row{display:flex;align-items:center;gap:14px;padding:13px 20px;border-bottom:1px solid #f1f5f9;}
.topic-row:last-child{border-bottom:none;}
.topic-num{width:28px;height:28px;border-radius:50%;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;}
.topic-name{font-size:13.5px;font-weight:600;color:#0f172a;}
.sess-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);overflow:hidden;}
table{width:100%;border-collapse:collapse;}
th{padding:10px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #e2e8f0;background:#f8fafc;}
td{padding:12px 16px;font-size:13.5px;color:#334155;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tbody tr:hover td{background:#fafcff;}
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;}
.scheduled{background:#eff6ff;color:#1d4ed8;}.completed{background:#dcfce7;color:#15803d;}.cancelled{background:#fee2e2;color:#dc2626;}
.att-wrap{background:#fff;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);overflow:hidden;}
.att-header-bar{padding:16px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;}
.att-header-bar h2{font-size:15px;font-weight:700;color:#0f172a;}
.att-controls{padding:20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
.att-controls label{font-size:12px;font-weight:700;color:#64748b;text-transform:uppercase;}
.att-controls input[type=date]{padding:8px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13.5px;font-family:'DM Sans',sans-serif;outline:none;}
.att-controls input[type=date]:focus{border-color:#2563eb;}
.btn-load{padding:9px 18px;background:#2563eb;color:#fff;border:none;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;}
.att-table-wrap{padding:20px;}
.att-table{width:100%;border-collapse:collapse;}
.att-table th{padding:10px 14px;text-align:left;font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;border-bottom:2px solid #e2e8f0;background:#f8fafc;}
.att-table td{padding:11px 14px;font-size:13.5px;color:#334155;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.att-table tr:last-child td{border-bottom:none;}
.rg{display:flex;gap:6px;}
.rg label{display:flex;align-items:center;gap:5px;padding:5px 12px;border-radius:8px;cursor:pointer;font-size:12.5px;font-weight:700;border:2px solid transparent;}
.rg input[type=radio]{display:none;}
.lP{background:#f0fdf4;color:#15803d;border-color:#bbf7d0;}
.lA{background:#fee2e2;color:#dc2626;border-color:#fca5a5;}
.lL{background:#fef9c3;color:#92400e;border-color:#fde68a;}
.rg input:checked + .lP{background:#16a34a;color:#fff;border-color:#16a34a;}
.rg input:checked + .lA{background:#dc2626;color:#fff;border-color:#dc2626;}
.rg input:checked + .lL{background:#d97706;color:#fff;border-color:#d97706;}
.btn-save-att{display:block;margin:16px 20px 20px;padding:12px;background:linear-gradient(135deg,#16a34a,#15803d);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;width:calc(100% - 40px);}
.empty{text-align:center;padding:40px;color:#94a3b8;}
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
  <a href="sub_admin_batch.php"><span class="sb-icon">🏫</span>My Batches</a>
  <div class="sb-sec">Attendance</div>
  <a href="sub_admin_attendance.php"><span class="sb-icon">✅</span>Mark Attendance</a>
  <div class="sb-bottom"><a href="sub_admin_logout.php"><span class="sb-icon">🚪</span>Logout</a></div>
</div>

<div class="main">
  <div class="page-header">
    <div>
      <h1>👁️ <?= h($batch['batch_name']) ?></h1>
      <p><?= h($batch['course_name']??'') ?> &nbsp;·&nbsp; <?= count($students) ?> students</p>
    </div>
    <a href="sub_admin_dashboard.php" class="btn-back">← Dashboard</a>
  </div>

  <div class="batch-bar">
    <div class="bb-item"><span class="bb-label">Batch</span><span class="bb-val"><?= h($batch['batch_name']) ?></span></div>
    <div class="bb-div"></div>
    <div class="bb-item"><span class="bb-label">Course</span><span class="bb-val"><?= h($batch['course_name']??'—') ?></span></div>
    <div class="bb-div"></div>
    <div class="bb-item"><span class="bb-label">Timing</span><span class="bb-val"><?= !empty($batch['timing_start'])?date('g:i A',strtotime($batch['timing_start'])).' – '.date('g:i A',strtotime($batch['timing_end'])):'—' ?></span></div>
    <div class="bb-div"></div>
    <div class="bb-item"><span class="bb-label">Students</span><span class="bb-val"><?= count($students) ?></span></div>
  </div>

  <div class="tabs">
    <a href="?id=<?= $batch_id ?>&tab=modules"    class="tab-btn <?= $tab==='modules'?'active':'' ?>">📚 Modules</a>
    <a href="?id=<?= $batch_id ?>&tab=sessions"   class="tab-btn <?= $tab==='sessions'?'active':'' ?>">📅 Sessions</a>
    <a href="?id=<?= $batch_id ?>&tab=attendance" class="tab-btn <?= $tab==='attendance'?'active':'' ?>">✅ Attendance</a>
  </div>

  <!-- MODULES -->
  <?php if($tab==='modules'): ?>
    <?php if($modules): foreach($modules as $mn=>$topics): ?>
    <div class="module-block">
      <div class="module-head"><h3>📂 <?= h($mn) ?></h3><span><?= count($topics) ?> topics</span></div>
      <?php foreach($topics as $i=>$t): ?>
      <div class="topic-row">
        <div class="topic-num"><?= $i+1 ?></div>
        <div class="topic-name"><?= h($t['topic_name']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
    <?php endforeach; else: ?>
      <div class="empty">📭 No modules found.</div>
    <?php endif; ?>

  <!-- SESSIONS -->
  <?php elseif($tab==='sessions'): ?>
    <div class="sess-card">
      <?php if($sessions): ?>
      <table>
        <thead><tr><th>Date</th><th>Type</th><th>Topic</th><th>Time</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach($sessions as $s): ?>
        <tr>
          <td><strong><?= date('d M Y',strtotime($s['date'])) ?></strong></td>
          <td><?= ucfirst(h($s['session_type']??'lecture')) ?></td>
          <td style="font-weight:600;"><?= h($s['topic']) ?></td>
          <td style="font-size:12px;color:#64748b;"><?= !empty($s['start_time'])?date('g:i A',strtotime($s['start_time'])).' – '.date('g:i A',strtotime($s['end_time'])):'—' ?></td>
          <td><span class="badge <?= h($s['status']) ?>"><?= ucfirst(h($s['status'])) ?></span></td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?><div class="empty">📭 No sessions yet.</div><?php endif; ?>
    </div>

  <!-- ATTENDANCE -->
  <?php elseif($tab==='attendance'): ?>
    <div class="att-wrap">
      <div class="att-header-bar">
        <h2>✅ Mark Attendance</h2>
        <?php if(isset($_GET['saved'])): ?>
          <span style="background:#dcfce7;color:#166534;padding:5px 14px;border-radius:8px;font-size:13px;font-weight:700;">✅ Saved!</span>
        <?php endif; ?>
      </div>
      <div class="att-controls">
        <form method="GET" style="display:flex;align-items:center;gap:10px;">
          <input type="hidden" name="id"  value="<?= $batch_id ?>">
          <input type="hidden" name="tab" value="attendance">
          <label>Date:</label>
          <input type="date" name="date" value="<?= h($att_date) ?>">
          <button type="submit" class="btn-load">Load</button>
        </form>
        <span style="font-size:13px;color:#64748b;">📅 <?= date('d F Y',strtotime($att_date)) ?></span>
      </div>
      <?php if($students): ?>
      <form method="POST">
        <input type="hidden" name="att_date" value="<?= h($att_date) ?>">
        <input type="hidden" name="batch_no"  value="<?= h($batch['batch_name']) ?>">
        <div class="att-table-wrap">
          <table class="att-table">
            <thead><tr><th>#</th><th>Name</th><th>Reg No</th><th>Attendance</th></tr></thead>
            <tbody>
            <?php $n=1; foreach($students as $s): $cur=$existing_att[$s['reg_no']]??'P'; ?>
            <tr>
              <td><?= $n++ ?></td>
              <td style="font-weight:600;"><?= h($s['name']) ?></td>
              <td style="color:#64748b;font-size:12px;"><?= h($s['reg_no']) ?></td>
              <td>
                <div class="rg">
                  <input type="radio" name="status[<?= h($s['reg_no']) ?>]" id="P<?= h($s['reg_no']) ?>" value="P" <?= $cur==='P'?'checked':'' ?>>
                  <label for="P<?= h($s['reg_no']) ?>" class="lP">✅ P</label>
                  <input type="radio" name="status[<?= h($s['reg_no']) ?>]" id="A<?= h($s['reg_no']) ?>" value="A" <?= $cur==='A'?'checked':'' ?>>
                  <label for="A<?= h($s['reg_no']) ?>" class="lA">❌ A</label>
                  <input type="radio" name="status[<?= h($s['reg_no']) ?>]" id="L<?= h($s['reg_no']) ?>" value="L" <?= $cur==='L'?'checked':'' ?>>
                  <label for="L<?= h($s['reg_no']) ?>" class="lL">🟡 L</label>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
          </table>
        </div>
        <button type="submit" name="save_attendance" class="btn-save-att">💾 Save Attendance for <?= date('d M Y',strtotime($att_date)) ?></button>
      </form>
      <?php else: ?><div class="empty">📭 No students in this batch.</div><?php endif; ?>
    </div>
  <?php endif; ?>
</div>
</body>
</html>