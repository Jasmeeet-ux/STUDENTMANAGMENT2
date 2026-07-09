<?php
require_once __DIR__ . '/../db.php';
session_start();
if (!isset($_SESSION['sub_admin_id'])) { header("Location: sub_admin_login.php"); exit; }

$sub_id = (int)$_SESSION['sub_admin_id'];

// Assigned batch ids
$ab = $pdo->prepare("SELECT batch_id FROM sub_admin_batches WHERE sub_admin_id=?");
$ab->execute([$sub_id]);
$assigned_ids = array_column($ab->fetchAll(PDO::FETCH_ASSOC),'batch_id');

// Assigned batches info
$batches = [];
if ($assigned_ids) {
    $in = implode(',', array_fill(0, count($assigned_ids), '?'));
    $bq = $pdo->prepare("SELECT b.*, c.course_name FROM batches b LEFT JOIN courses c ON c.id=b.course_id WHERE b.id IN ($in)");
    $bq->execute($assigned_ids);
    $batches = $bq->fetchAll(PDO::FETCH_ASSOC);
}

// Filter by batch
$filter_batch = $_GET['batch_id'] ?? '';
$batch_names = array_column($batches,'batch_name');

// Fetch students
$students = [];
if ($batch_names) {
    if ($filter_batch) {
        $fb_info = array_filter($batches, fn($b)=>$b['id']==$filter_batch);
        $fb_info = array_values($fb_info);
        $search_names = $fb_info ? [$fb_info[0]['batch_name']] : $batch_names;
    } else {
        $search_names = $batch_names;
    }
    $in2 = implode(',', array_fill(0, count($search_names), '?'));
    $sq = $pdo->prepare("SELECT * FROM user_details WHERE batch_no IN ($in2) ORDER BY name ASC");
    $sq->execute($search_names);
    $students = $sq->fetchAll(PDO::FETCH_ASSOC);
}

// Attendance summary per student
function getAttSummary($pdo, $reg_no, $batch_no) {
    // Same formula as admin/sub_admin_performance — batch specific, P+L+O = attended, Lv excluded
    $t = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE reg_no=? AND batch_no=? AND status != 'Lv'");
    $t->execute([$reg_no, $batch_no]); $total = (int)$t->fetchColumn();
    $p = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE reg_no=? AND batch_no=? AND status IN ('P','L','O')");
    $p->execute([$reg_no, $batch_no]); $present = (int)$p->fetchColumn();
    return ['total'=>$total,'present'=>$present,'pct'=>$total>0?round(($present/$total)*100):0];
}

function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Students | Sub Admin</title>
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
.filter-row{display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap;align-items:center;}
.filter-lbl{font-size:12px;color:#64748b;font-weight:700;}
.fb{padding:6px 14px;border-radius:7px;font-size:12.5px;font-weight:700;text-decoration:none;background:#2563eb;color:#fff;}
.fb.off{background:#f1f5f9;color:#64748b;border:1px solid #e2e8f0;}
.table-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);overflow:hidden;}
table{width:100%;border-collapse:collapse;}
thead{background:#f8fafc;}
th{padding:11px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.6px;border-bottom:2px solid #e2e8f0;}
td{padding:12px 16px;border-bottom:1px solid #f1f5f9;font-size:13.5px;color:#334155;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tbody tr:hover{background:#fafcff;}
.sno{font-weight:700;color:#94a3b8;}
.stu-name{font-weight:700;color:#0f172a;}
.stu-reg{font-size:11px;color:#94a3b8;margin-top:2px;}
.pill{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;}
.pill-blue{background:#eff6ff;color:#1d4ed8;}
.att-bar{display:flex;align-items:center;gap:8px;}
.bar-wrap{flex:1;height:7px;background:#f1f5f9;border-radius:10px;overflow:hidden;max-width:80px;}
.bar-fill{height:100%;border-radius:10px;}
.bar-g{background:#16a34a;}.bar-y{background:#d97706;}.bar-r{background:#dc2626;}
.att-pct{font-size:12.5px;font-weight:700;}
.btn-view{background:#eff6ff;color:#2563eb;border:none;padding:6px 14px;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;text-decoration:none;display:inline-block;}
.btn-view:hover{background:#dbeafe;}
.empty{text-align:center;padding:50px;color:#94a3b8;font-size:14px;}
</style>
</head>
<body>
<div class="sidebar">
  <div class="sb-brand"><h2>🎓 Sub Admin</h2><p>Culture of Internet</p></div>
  <div class="sb-sec">Main</div>
  <a href="sub_admin_dashboard.php" class="active"><span class="sb-icon">🏠</span>Dashboard</a>
  <div class="sb-sec">Students</div>
  <a href="sub_admin_students.php"><span class="sb-icon">👨‍🎓</span>My Students</a>
  <div class="sb-sec">Batches</div>
  <a href="sub_admin_batch.php"><span class="sb-icon">🏫</span>My Batches</a>
  <div class="sb-sec">Attendance</div>
  <a href="sub_admin_attendance.php"><span class="sb-icon">✅</span>Mark Attendance</a>
  <div class="sb-bottom"><a href="sub_admin_logout.php"><span class="sb-icon">🚪</span>Logout</a></div>
</div>

<div class="main">
  <div class="topbar"><h1>👨‍🎓 My Students</h1></div>

  <div class="filter-row">
    <span class="filter-lbl">Batch:</span>
    <a href="sub_admin_students.php" class="fb <?= !$filter_batch?'':'off' ?>">All</a>
    <?php foreach($batches as $b): ?>
      <a href="?batch_id=<?= $b['id'] ?>" class="fb <?= $filter_batch==$b['id']?'':'off' ?>"><?= h($b['batch_name']) ?></a>
    <?php endforeach; ?>
  </div>

  <div class="table-card">
    <table>
      <thead><tr><th>S.No.</th><th>Name</th><th>Batch</th><th>Attendance</th><th>Performance</th></tr></thead>
      <tbody>
      <?php if($students): $n=1; foreach($students as $s):
        $att = getAttSummary($pdo, $s['reg_no'], $s['batch_no'] ?? '');
        $bc = $att['pct']>=75?'bar-g':($att['pct']>=50?'bar-y':'bar-r');
        $tc = $att['pct']>=75?'#16a34a':($att['pct']>=50?'#d97706':'#dc2626');
      ?>
      <tr>
        <td class="sno"><?= $n++ ?></td>
        <td>
          <div class="stu-name"><?= h($s['name']) ?></div>
          <div class="stu-reg"><?= h($s['reg_no']) ?></div>
        </td>
        <td><?php if(!empty($s['batch_no'])): ?><span class="pill pill-blue"><?= h($s['batch_no']) ?></span><?php endif; ?></td>
        <td>
          <div class="att-bar">
            <div class="bar-wrap"><div class="bar-fill <?= $bc ?>" style="width:<?= $att['pct'] ?>%"></div></div>
            <span class="att-pct" style="color:<?= $tc ?>"><?= $att['pct'] ?>%</span>
          </div>
          <div style="font-size:11px;color:#94a3b8;margin-top:2px;"><?= $att['present'] ?>/<?= $att['total'] ?> present</div>
        </td>
        <td>
          <a href="sub_admin_performance.php?reg_no=<?= urlencode($s['reg_no']) ?>" class="btn-view">📊 View</a>
        </td>
      </tr>
      <?php endforeach; else: ?>
        <tr><td colspan="5" class="empty">No students found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
</body>
</html>