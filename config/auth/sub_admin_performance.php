<?php
require_once __DIR__ . '/../db.php';
session_start();
if (!isset($_SESSION['sub_admin_id'])) { header("Location: sub_admin_login.php"); exit; }

$sub_admin_id   = $_SESSION['sub_admin_id'];
$sub_admin_name = $_SESSION['sub_admin_name'] ?? 'Sub Admin';

$reg_no = $_GET['reg_no'] ?? '';
if (!$reg_no) { header("Location: sub_admin_students.php"); exit; }

$student = $pdo->prepare("SELECT * FROM user_details WHERE reg_no=?");
$student->execute([$reg_no]);
$student = $student->fetch(PDO::FETCH_ASSOC);
if (!$student) { header("Location: sub_admin_students.php"); exit; }

$batchInfo = null;
$course_id_perf = null;
if (!empty($student['batch_no'])) {
    $bq = $pdo->prepare("SELECT b.*, c.course_name, c.id as course_id FROM batches b LEFT JOIN courses c ON c.id=b.course_id WHERE b.batch_name=? LIMIT 1");
    $bq->execute([$student['batch_no']]);
    $batchInfo = $bq->fetch(PDO::FETCH_ASSOC);
    if ($batchInfo) $course_id_perf = $batchInfo['course_id'] ?? null;
}

// 1. Attendance — batch specific, P+L+O = attended, Lv excluded
$active_batch_perf = $student['batch_no'] ?? '';
// Total = class_sessions held for this batch (what admin actually added)
$ts=$pdo->prepare("SELECT COUNT(*) FROM class_sessions WHERE batch_no=?");
$ts->execute([$active_batch_perf]); $attTotal=(int)$ts->fetchColumn();
// Fallback: if no sessions added yet, use attendance markings count
if ($attTotal === 0) {
    $tf=$pdo->prepare("SELECT COUNT(*) FROM attendance WHERE reg_no=? AND batch_no=? AND status != 'Lv'");
    $tf->execute([$reg_no, $active_batch_perf]); $attTotal=(int)$tf->fetchColumn();
}
$s2=$pdo->prepare("SELECT COUNT(*) FROM attendance WHERE reg_no=? AND batch_no=? AND status IN ('P','L','O')");
$s2->execute([$reg_no, $active_batch_perf]); $attPresent=(int)$s2->fetchColumn();
$attPct = $attTotal>0 ? min(100,round(($attPresent/$attTotal)*100)) : 0;

// 2. Course Progress — only topics for this course
$totalTopics = 0; $completedTopics = 0;
if ($course_id_perf) {
    $s4=$pdo->prepare("SELECT COUNT(DISTINCT topic_name) FROM course_modules WHERE course_id=?");
    $s4->execute([$course_id_perf]); $totalTopics=(int)$s4->fetchColumn();
    if ($totalTopics > 0) {
        $s3=$pdo->prepare("SELECT COUNT(DISTINCT cp.topic_name) FROM course_progress cp INNER JOIN course_modules cm ON cm.topic_name=cp.topic_name AND cm.course_id=? WHERE cp.reg_no=?");
        $s3->execute([$course_id_perf, $reg_no]); $completedTopics=min((int)$s3->fetchColumn(), $totalTopics);
    }
}
$coursePct = $totalTopics>0 ? min(100,round(($completedTopics/$totalTopics)*100)) : 0;

// 3. MCQ — only for this course
$mcqAvgPct = 0;
if ($course_id_perf) {
    $mcqRowsQ=$pdo->prepare("SELECT score, total FROM mcq_attempts WHERE reg_no=? AND course_id=? AND total>0");
    $mcqRowsQ->execute([$reg_no, $course_id_perf]);
    $mcqRowsAll=$mcqRowsQ->fetchAll(PDO::FETCH_ASSOC);
    if (count($mcqRowsAll) > 0) {
        $sum = 0;
        foreach ($mcqRowsAll as $r) { $sum += ($r['score']/$r['total'])*100; }
        $mcqAvgPct = min(100, round($sum / count($mcqRowsAll)));
    }
}

// 4. Assignments — only for this course
$totalAss = 0; $submittedAss = 0;
if ($course_id_perf) {
    $s5=$pdo->prepare("SELECT COUNT(DISTINCT topic_name) FROM topic_assignments WHERE course_id=?");
    $s5->execute([$course_id_perf]); $totalAss=(int)$s5->fetchColumn();
    if ($totalAss > 0) {
        $s6=$pdo->prepare("SELECT COUNT(DISTINCT topic_name) FROM assignment_submissions WHERE reg_no=? AND course_id=?");
        $s6->execute([$reg_no, $course_id_perf]); $submittedAss=min((int)$s6->fetchColumn(), $totalAss);
    }
}
$assPct = $totalAss>0 ? min(100,round(($submittedAss/$totalAss)*100)) : 0;

// 5. Overall — 0 jab tak koi data nahi
$hasData = ($attTotal > 0 || $totalTopics > 0 || $totalAss > 0);

$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$overallPct = $hasData ? min(100, round(($attPct*0.40) + ($coursePct*0.30) + ($mcqAvgPct*0.15) + ($assPct*0.15))) : 0;

$mcqQ=$pdo->prepare("SELECT * FROM mcq_attempts WHERE reg_no=?" . ($course_id_perf ? " AND course_id=?" : "") . " ORDER BY attempted_at DESC");
$mcqQ->execute($course_id_perf ? [$reg_no, $course_id_perf] : [$reg_no]);
$mcqAttempts=$mcqQ->fetchAll(PDO::FETCH_ASSOC);
$assQ=$pdo->prepare("SELECT * FROM assignment_submissions WHERE reg_no=?" . ($course_id_perf ? " AND course_id=?" : "") . " ORDER BY submitted_at DESC");
$assQ->execute($course_id_perf ? [$reg_no, $course_id_perf] : [$reg_no]);
$assignments=$assQ->fetchAll(PDO::FETCH_ASSOC);

function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= h($student['name']) ?> — Performance</title>
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
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:24px;}
.topbar h1{font-size:20px;font-weight:800;color:#0f172a;}
.btn-back{background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;}
.stu-banner{background:linear-gradient(135deg,#1e3a8a,#2563eb);border-radius:16px;padding:24px;color:#fff;margin-bottom:22px;display:flex;align-items:center;gap:20px;}
.stu-avatar{width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:800;flex-shrink:0;}
.stu-info h2{font-size:20px;font-weight:800;}
.stu-info p{font-size:13px;opacity:0.8;margin-top:3px;}
.stu-tags{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;}
.stu-tag{background:rgba(255,255,255,0.2);padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;}
.rings{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:22px;}
.ring-card{background:#fff;border-radius:14px;padding:20px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);text-align:center;}
.ring-card.overall{border:2px solid #f59e0b;background:linear-gradient(135deg,#fffbeb,#fff);}
.ring-wrap{position:relative;width:80px;height:80px;margin:0 auto 12px;}
.ring-wrap svg{transform:rotate(-90deg);}
.ring-pct{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;}
.ring-lbl{font-size:12px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;}
.ring-sub{font-size:11px;color:#94a3b8;margin-top:3px;}
.card{background:#fff;border-radius:14px;padding:22px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);margin-bottom:20px;}
.card h3{font-size:14px;font-weight:700;color:#0f172a;margin-bottom:14px;padding-bottom:10px;border-bottom:2px solid #f1f5f9;}
.data-table{width:100%;border-collapse:collapse;}
.data-table th{padding:9px 12px;text-align:left;font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #e2e8f0;background:#f8fafc;}
.data-table td{padding:10px 12px;font-size:13px;color:#334155;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.data-table tr:last-child td{border-bottom:none;}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;}
.gA{background:#dcfce7;color:#15803d;}.gB{background:#dbeafe;color:#1d4ed8;}.gP{background:#f1f5f9;color:#64748b;}
.empty{text-align:center;padding:24px;color:#94a3b8;font-size:13px;}
</style>
</head>
<body>

<div class="sidebar">
  <div class="sb-brand"><h2>🎓 Sub Admin</h2><p><?= h($sub_admin_name) ?></p></div>
  <div class="sb-sec">Main</div>
  <a href="sub_admin_dashboard.php"><span class="sb-icon">🏠</span>Dashboard</a>
  <div class="sb-sec">Students</div>
  <a href="sub_admin_students.php" class="active"><span class="sb-icon">👨‍🎓</span>My Students</a>
  <div class="sb-sec">Batches</div>
  <a href="sub_admin_batch.php"><span class="sb-icon">🏫</span>My Batches</a>
  <div class="sb-sec">Attendance</div>
  <a href="sub_admin_attendance.php"><span class="sb-icon">✅</span>Mark Attendance</a>
  <div class="sb-bottom"><a href="sub_admin_logout.php"><span class="sb-icon">🚪</span>Logout</a></div>
</div>

<div class="main">
  <div class="topbar">
    <h1>📊 Student Performance</h1>
    <a href="sub_admin_students.php" class="btn-back">← Back</a>
  </div>

  <div class="stu-banner">
    <div class="stu-avatar"><?= strtoupper(substr($student['name'],0,1)) ?></div>
    <div class="stu-info">
      <h2><?= h($student['name']) ?></h2>
      <p>Reg: <?= h($student['reg_no']) ?> &nbsp;|&nbsp; <?= h($student['coursename']??'N/A') ?></p>
      <div class="stu-tags">
        <?php if(!empty($student['batch_no'])): ?><span class="stu-tag">🏫 <?= h($student['batch_no']) ?></span><?php endif; ?>
        <?php if(!empty($student['mode'])): ?><span class="stu-tag">📡 <?= ucfirst(h($student['mode'])) ?></span><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- 4 Rings: Overall + Attendance + Course Progress + Assignments -->
  <div class="rings">
    <?php
    $overallColor = $overallPct>=75?'#16a34a':($overallPct>=50?'#d97706':'#dc2626');
    $c2=2*M_PI*34;
    $rings=[
      ['pct'=>$overallPct, 'color'=>$overallColor,'lbl'=>'Overall Progress','sub'=>'Att 40% + Videos 30% + MCQ+Ass 30%','overall'=>true],
      ['pct'=>$attPct,     'color'=>'#2563eb',    'lbl'=>'Attendance',      'sub'=>"$attPresent / $attTotal classes",'overall'=>false],
      ['pct'=>$coursePct,  'color'=>'#16a34a',    'lbl'=>'Course Progress', 'sub'=>"$completedTopics / $totalTopics topics",'overall'=>false],
      ['pct'=>$assPct,     'color'=>'#7c3aed',    'lbl'=>'Assignments',     'sub'=>"$submittedAss / $totalAss submitted",'overall'=>false],
    ];
    foreach($rings as $r):
      $d=($r['pct']/100)*$c2;
    ?>
    <div class="ring-card <?= $r['overall']?'overall':'' ?>">
      <div class="ring-wrap">
        <svg width="80" height="80" viewBox="0 0 80 80">
          <circle cx="40" cy="40" r="34" fill="none" stroke="#f1f5f9" stroke-width="8"/>
          <circle cx="40" cy="40" r="34" fill="none" stroke="<?= $r['color'] ?>" stroke-width="8"
            stroke-dasharray="<?= round($d,2) ?> <?= round($c2,2) ?>" stroke-linecap="round"/>
        </svg>
        <div class="ring-pct" style="color:<?= $r['color'] ?>"><?= $r['pct'] ?>%</div>
      </div>
      <div class="ring-lbl"><?= $r['lbl'] ?></div>
      <div class="ring-sub"><?= $r['sub'] ?></div>
    </div>
    <?php endforeach; ?>
  </div>

  <div class="card">
    <h3>📁 Assignments (<?= count($assignments) ?>)</h3>
    <?php if($assignments): ?>
    <table class="data-table">
      <thead><tr><th>#</th><th>Topic</th><th>Submitted</th><th>File</th><th>Score</th><th>Grade</th></tr></thead>
      <tbody>
      <?php $n=1; foreach($assignments as $a): $g=$a['grade']??''; ?>
      <tr>
        <td><?= $n++ ?></td>
        <td><?= h($a['topic_name']) ?></td>
        <td style="font-size:12px;color:#64748b;"><?= date('d M Y',strtotime($a['submitted_at'])) ?></td>
        <td>
          <?php if(!empty($a['file_path'])): ?>
            <a href="<?= $base_url . '/' . ltrim(h($a['file_path']),'/') ?>" target="_blank"
               style="display:inline-flex;align-items:center;gap:5px;background:#eff6ff;color:#2563eb;padding:4px 10px;border-radius:6px;font-size:12px;font-weight:700;text-decoration:none;">
              📥 Download
            </a>
          <?php else: ?>
            <span style="font-size:12px;color:#94a3b8;">No file</span>
          <?php endif; ?>
        </td>
        <td style="font-weight:700;"><?= h($a['score']??'—') ?></td>
        <td><span class="badge <?= $g?'gA':'gP' ?>"><?= $g?:'Pending' ?></span></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?><div class="empty">📭 No assignments submitted.</div><?php endif; ?>
  </div>

  <div class="card">
    <h3>🧠 MCQ Attempts (<?= count($mcqAttempts) ?>)</h3>
    <?php if($mcqAttempts): ?>
    <table class="data-table">
      <thead><tr><th>#</th><th>Topic</th><th>Score</th><th>Total</th><th>%</th><th>Date</th></tr></thead>
      <tbody>
      <?php $n=1; foreach($mcqAttempts as $m):
        $p=$m['total']>0?round(($m['score']/$m['total'])*100):0;
        $pc=$p>=80?'#16a34a':($p>=50?'#ca8a04':'#dc2626');
      ?>
      <tr>
        <td><?= $n++ ?></td>
        <td><?= h($m['topic_name']) ?></td>
        <td style="font-weight:700;color:#2563eb;"><?= $m['score'] ?></td>
        <td><?= $m['total'] ?></td>
        <td><span style="font-weight:700;color:<?= $pc ?>"><?= $p ?>%</span></td>
        <td style="font-size:12px;color:#64748b;"><?= date('d M Y',strtotime($m['attempted_at'])) ?></td>
      </tr>
      <?php endforeach; ?>
      </tbody>
    </table>
    <?php else: ?><div class="empty">📭 No MCQ attempts.</div><?php endif; ?>
  </div>
</div>
</body>
</html>