<?php
require_once __DIR__ . '/../db.php';
session_start();
if (!isset($_SESSION['admin_username'])) { header("Location: login.php"); exit; }

$reg_no = $_GET['reg_no'] ?? '';
if (!$reg_no) { header("Location: students_list.php"); exit; }

$student = $pdo->prepare("SELECT * FROM user_details WHERE reg_no=?");
$student->execute([$reg_no]);
$student = $student->fetch(PDO::FETCH_ASSOC);
if (!$student) { header("Location: students_list.php"); exit; }

$userLogin = $pdo->prepare("SELECT * FROM users WHERE reg_no=?");
$userLogin->execute([$reg_no]);
$userLogin = $userLogin->fetch(PDO::FETCH_ASSOC);

$batchInfo = null;
$course_id_perf = null;
if (!empty($student['batch_no'])) {
    $bq = $pdo->prepare("SELECT b.*, c.course_name, c.id as course_id FROM batches b LEFT JOIN courses c ON c.id=b.course_id WHERE b.batch_name=? LIMIT 1");
    $bq->execute([$student['batch_no']]);
    $batchInfo = $bq->fetch(PDO::FETCH_ASSOC);
    if ($batchInfo) $course_id_perf = $batchInfo['course_id'] ?? null;
}

// 1. Attendance (40%)
// Attendance: filter by student's active batch
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

// 2. Videos / Course Progress (30%)
$totalTopics = 0;
if ($course_id_perf) {
    $s4=$pdo->prepare("SELECT COUNT(DISTINCT topic_name) FROM course_modules WHERE course_id=?");
    $s4->execute([$course_id_perf]);
    $totalTopics=(int)$s4->fetchColumn();
}
// Progress: only count topics belonging to this course
if ($course_id_perf) {
    $s3=$pdo->prepare("SELECT COUNT(DISTINCT cp.topic_name) FROM course_progress cp INNER JOIN course_modules cm ON cm.topic_name=cp.topic_name AND cm.course_id=? WHERE cp.reg_no=?");
    $s3->execute([$course_id_perf, $reg_no]);
    $completedTopics=(int)$s3->fetchColumn();
} else { $completedTopics = 0; }
$completedTopics = min($completedTopics, $totalTopics);
$coursePct = $totalTopics>0 ? min(100,round(($completedTopics/$totalTopics)*100)) : 0;

// 3. MCQ (15%) — avg score % across all attempts
$mcqRowsQ=$pdo->prepare("SELECT score, total FROM mcq_attempts WHERE reg_no=? AND total>0" . ($course_id_perf ? " AND course_id=?" : ""));
$mcqRowsQ->execute($course_id_perf ? [$reg_no, $course_id_perf] : [$reg_no]);
$mcqRowsAll=$mcqRowsQ->fetchAll(PDO::FETCH_ASSOC);
$mcqAvgPct = 0;
if (count($mcqRowsAll) > 0) {
    $sum = 0;
    foreach ($mcqRowsAll as $r) { $sum += ($r['score']/$r['total'])*100; }
    $mcqAvgPct = min(100, round($sum / count($mcqRowsAll)));
}

// 4. Assignment (15%) — submitted vs total available in course
$totalAss = 0;
if ($course_id_perf) {
    $s5=$pdo->prepare("SELECT COUNT(DISTINCT topic_name) FROM topic_assignments WHERE course_id=?");
    $s5->execute([$course_id_perf]);
    $totalAss=(int)$s5->fetchColumn();
}
if ($course_id_perf) {
    $s6=$pdo->prepare("SELECT COUNT(DISTINCT topic_name) FROM assignment_submissions WHERE reg_no=? AND course_id=?");
    $s6->execute([$reg_no, $course_id_perf]);
} else {
    $s6=$pdo->prepare("SELECT COUNT(DISTINCT topic_name) FROM assignment_submissions WHERE reg_no=?");
    $s6->execute([$reg_no]);
}
$submittedAss=(int)$s6->fetchColumn();
$submittedAss = $totalAss>0 ? min($submittedAss,$totalAss) : $submittedAss;
$assPct = $totalAss>0 ? min(100,round(($submittedAss/$totalAss)*100)) : 0;

// 5. Overall = Attendance 40% + Videos 30% + MCQ 15% + Assignment 15%
$hasData = ($attTotal > 0 || $totalTopics > 0 || $totalAss > 0);

// Base URL for file downloads
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on' ? 'https' : 'http')
    . '://' . $_SERVER['HTTP_HOST']
    . rtrim(dirname(dirname(dirname($_SERVER['SCRIPT_NAME']))), '/');
$overallPct = $hasData ? min(100, round(($attPct*0.40) + ($coursePct*0.30) + ($mcqAvgPct*0.15) + ($assPct*0.15))) : 0;

$mcqQ=$pdo->prepare("SELECT * FROM mcq_attempts WHERE reg_no=? ORDER BY attempted_at DESC");$mcqQ->execute([$reg_no]);$mcqAttempts=$mcqQ->fetchAll(PDO::FETCH_ASSOC);
$assQ=$pdo->prepare("SELECT * FROM assignment_submissions WHERE reg_no=? ORDER BY submitted_at DESC");$assQ->execute([$reg_no]);$assignments=$assQ->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD']==='POST' && isset($_POST['save_grade'])) {
    $pdo->prepare("UPDATE assignment_submissions SET grade=?,feedback=?,score=? WHERE id=? AND reg_no=?")
        ->execute([$_POST['grade'],$_POST['feedback'],$_POST['score']??null,(int)$_POST['ass_id'],$reg_no]);
    header("Location: student_performance.php?reg_no=".urlencode($reg_no)."&saved=1"); exit;
}

function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
function detailRow($k,$v){ if(empty($v))return; echo '<div class="d-row"><span class="d-key">'.h($k).'</span><span class="d-val">'.h($v).'</span></div>'; }
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
.btn-back:hover{background:#e2e8f0;}
.alert-ok{background:#dcfce7;border:1px solid #bbf7d0;color:#166534;padding:12px 18px;border-radius:10px;margin-bottom:18px;font-size:13.5px;font-weight:500;}
.stu-banner{background:linear-gradient(135deg,#1e3a8a,#2563eb);border-radius:16px;padding:24px;color:#fff;margin-bottom:22px;display:flex;align-items:center;gap:20px;}
.stu-avatar{width:64px;height:64px;border-radius:50%;background:rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;font-size:26px;font-weight:800;flex-shrink:0;}
.stu-info h2{font-size:20px;font-weight:800;}
.stu-info p{font-size:13px;opacity:0.8;margin-top:3px;}
.stu-tags{display:flex;gap:8px;margin-top:10px;flex-wrap:wrap;}
.stu-tag{background:rgba(255,255,255,0.2);padding:4px 12px;border-radius:20px;font-size:12px;font-weight:600;}

/* Rings — 4 cards now */
.rings{display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:22px;}
.ring-card{background:#fff;border-radius:14px;padding:20px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);text-align:center;}
/* Overall ring card highlighted */
.ring-card.overall{border:2px solid #f59e0b;background:linear-gradient(135deg,#fffbeb,#fff);}
.ring-wrap{position:relative;width:80px;height:80px;margin:0 auto 12px;}
.ring-wrap svg{transform:rotate(-90deg);}
.ring-pct{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-size:16px;font-weight:800;}
.ring-lbl{font-size:12px;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;}
.ring-sub{font-size:11px;color:#94a3b8;margin-top:3px;}

.detail-grid{display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:22px;}
.card{background:#fff;border-radius:14px;padding:22px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);}
.card h3{font-size:14px;font-weight:700;color:#0f172a;margin-bottom:14px;padding-bottom:10px;border-bottom:2px solid #f1f5f9;}
.card.full{grid-column:span 2;}
.d-row{display:flex;align-items:flex-start;gap:12px;padding:8px 0;border-bottom:1px solid #f8fafc;}
.d-row:last-child{border-bottom:none;}
.d-key{font-size:11.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.4px;min-width:130px;}
.d-val{font-size:13.5px;color:#0f172a;font-weight:500;word-break:break-all;}
.cred-box{background:#f8fafc;border:1px solid #e2e8f0;border-radius:10px;padding:14px 16px;}
.cred-row{display:flex;align-items:center;justify-content:space-between;padding:6px 0;border-bottom:1px solid #f1f5f9;}
.cred-row:last-child{border-bottom:none;}
.cred-key{font-size:11.5px;font-weight:700;color:#64748b;text-transform:uppercase;}
.cred-val{font-size:13.5px;color:#0f172a;font-weight:700;font-family:monospace;background:#fff;padding:3px 10px;border-radius:6px;border:1px solid #e2e8f0;}
.data-table{width:100%;border-collapse:collapse;}
.data-table th{padding:9px 12px;text-align:left;font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #e2e8f0;background:#f8fafc;}
.data-table td{padding:10px 12px;font-size:13px;color:#334155;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
.data-table tr:last-child td{border-bottom:none;}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;}
.gA{background:#dcfce7;color:#15803d;}.gB{background:#dbeafe;color:#1d4ed8;}
.gC{background:#fef9c3;color:#92400e;}.gF{background:#fee2e2;color:#dc2626;}
.gP{background:#f1f5f9;color:#64748b;}
.grade-form{display:flex;gap:6px;flex-wrap:wrap;align-items:center;}
.grade-form input,.grade-form select{padding:5px 8px;border:1px solid #e2e8f0;border-radius:7px;font-size:12px;font-family:'DM Sans',sans-serif;outline:none;}
.grade-form select{max-width:80px;}
.grade-form input[type=number]{max-width:70px;}
.grade-form textarea{padding:5px 8px;border:1px solid #e2e8f0;border-radius:7px;font-size:12px;font-family:'DM Sans',sans-serif;outline:none;resize:none;width:130px;height:34px;}
.btn-save{padding:5px 12px;background:#2563eb;color:#fff;border:none;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;}
.btn-save:hover{background:#1d4ed8;}
.empty{text-align:center;padding:24px;color:#94a3b8;font-size:13px;}
</style>
</head>
<body>
<div class="sidebar">
  <div class="sb-brand"><h2>🎓 Admin Panel</h2><p>Culture of Internet</p></div>
  <div class="sb-sec">Main</div>
  <a href="dashboard.php"><span class="sb-icon">🏠</span>Dashboard</a>
  <div class="sb-sec">Students</div>
  <a href="add_student.php"><span class="sb-icon">➕</span>Add Student</a>
  <a href="students_list.php" class="active"><span class="sb-icon">📋</span>Students List</a>
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
    <h1>📊 Student Performance</h1>
    <a href="students_list.php" class="btn-back">← Back to List</a>
  </div>
  <?php if(isset($_GET['saved'])): ?><div class="alert-ok">✅ Grade saved!</div><?php endif; ?>

  <div class="stu-banner">
    <div class="stu-avatar"><?= strtoupper(substr($student['name'],0,1)) ?></div>
    <div class="stu-info">
      <h2><?= h($student['name']) ?></h2>
      <p>Reg: <?= h($student['reg_no']) ?> &nbsp;|&nbsp; <?= h($student['coursename']??'N/A') ?></p>
      <div class="stu-tags">
        <?php if(!empty($student['batch_no'])): ?><span class="stu-tag">🏫 <?= h($student['batch_no']) ?></span><?php endif; ?>
        <?php if(!empty($student['mode'])): ?><span class="stu-tag">📡 <?= ucfirst(h($student['mode'])) ?></span><?php endif; ?>
        <?php if(!empty($student['startingdate'])): ?><span class="stu-tag">📅 Joined <?= date('d M Y',strtotime($student['startingdate'])) ?></span><?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ✅ 4 Rings: Overall + Attendance + Course Progress + Assignments -->
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

  <div class="detail-grid">

    <div class="card">
      <h3>🔐 Login Credentials</h3>
      <div class="cred-box">
        <div class="cred-row">
          <span class="cred-key">Login ID</span>
          <span class="cred-val"><?= h($student['reg_no']) ?></span>
        </div>
        <div class="cred-row">
          <span class="cred-key">Password</span>
          <span class="cred-val"><?= h($userLogin['password'] ?? 'N/A') ?></span>
        </div>
      </div>
    </div>

    <div class="card">
      <h3>👤 Personal Details</h3>
      <?php
        detailRow('Name',          $student['name']          ?? '');
        detailRow('Gender',        $student['gender']        ?? '');
        detailRow('Date of Birth', $student['dob']           ?? '');
        detailRow('Qualification', $student['qualification'] ?? '');
        detailRow('Address',       $student['address']       ?? '');
      ?>
    </div>

    <div class="card">
      <h3>📞 Contact Details</h3>
      <?php
        detailRow('Phone',        $student['phoneno']    ?? '');
        detailRow('WhatsApp',     $student['whatsapp']   ?? '');
        detailRow('Email',        $student['gmail']      ?? '');
        detailRow('Parent Name',  $student['parentname'] ?? '');
        detailRow('Parent Phone', $student['parentsno']  ?? '');
      ?>
    </div>

    <div class="card">
      <h3>📝 Admission Details</h3>
      <?php
        detailRow('Reg Number',    $student['reg_no']       ?? '');
        detailRow('Course',        $student['coursename']   ?? '');
        detailRow('Batch',         $student['batch_no']     ?? '');
        detailRow('Mode',          $student['mode']         ?? '');
        detailRow('Start Date',    !empty($student['startingdate'])  ? date('d M Y',strtotime($student['startingdate']))  : '');
        detailRow('Complete Date', !empty($student['completeddate']) ? date('d M Y',strtotime($student['completeddate'])) : '');
        detailRow('Add-on Value',  $student['addonvalue']   ?? '');
      ?>
    </div>

    <div class="card full">
      <h3>📁 Assignment Submissions (<?= count($assignments) ?>)</h3>
      <?php if($assignments): ?>
      <table class="data-table">
        <thead><tr><th>#</th><th>Topic</th><th>Submitted</th><th>File</th><th>Score</th><th>Grade</th><th>Give Grade & Feedback</th></tr></thead>
        <tbody>
        <?php $n=1; foreach($assignments as $a):
          $g=$a['grade']??''; $gc=$g?'g'.strtoupper($g[0]):'gP';
        ?>
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
          <td><span class="badge <?= $gc ?>"><?= $g?:'Pending' ?></span></td>
          <td>
            <form method="POST" class="grade-form">
              <input type="hidden" name="ass_id" value="<?= $a['id'] ?>">
              <select name="grade">
                <option value="">Grade</option>
                <?php foreach(['A+','A','B+','B','C','D','F'] as $gr): ?>
                  <option value="<?= $gr ?>" <?= ($a['grade']??'')===$gr?'selected':'' ?>><?= $gr ?></option>
                <?php endforeach; ?>
              </select>
              <input type="number" name="score" placeholder="Score" value="<?= h($a['score']??'') ?>" min="0" max="100">
              <textarea name="feedback" placeholder="Feedback..."><?= h($a['feedback']??'') ?></textarea>
              <button type="submit" name="save_grade" class="btn-save">💾 Save</button>
            </form>
          </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
      </table>
      <?php else: ?><div class="empty">📭 No assignments submitted yet.</div><?php endif; ?>
    </div>

    <div class="card full">
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
      <?php else: ?><div class="empty">📭 No MCQ attempts yet.</div><?php endif; ?>
    </div>

  </div>
</div>
</body>
</html>