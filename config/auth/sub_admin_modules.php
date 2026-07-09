<?php
require_once __DIR__ . '/../db.php';
session_start();
if (!isset($_SESSION['sub_admin_id'])) { header("Location: sub_admin_login.php"); exit; }

$sub_admin_id   = $_SESSION['sub_admin_id'];
$sub_admin_name = $_SESSION['sub_admin_name'] ?? 'Sub Admin';

$batch_id = (int)($_GET['batch_id'] ?? 0);
if (!$batch_id) { header("Location: sub_admin_batch.php"); exit; }

// Security: verify this batch is assigned to sub admin
$check = $pdo->prepare("SELECT b.*, c.course_name, c.id as course_id FROM sub_admin_batches sab JOIN batches b ON b.id=sab.batch_id LEFT JOIN courses c ON c.id=b.course_id WHERE sab.sub_admin_id=? AND b.id=?");
$check->execute([$sub_admin_id, $batch_id]);
$batchInfo = $check->fetch(PDO::FETCH_ASSOC);
if (!$batchInfo) { header("Location: sub_admin_batch.php"); exit; }

$course_id = (int)($batchInfo['course_id'] ?? 0);
if (!$course_id) { header("Location: sub_admin_batch.php?err=no_course"); exit; }

// ── Add Topics ──
if (isset($_POST['add_topics'])) {
    $mod    = trim($_POST['module_name']);
    $topics = $_POST['topics'] ?? [];
    $videos = $_POST['videos'] ?? [];
    if ($mod && !empty($topics)) {
        $oq = $pdo->prepare("SELECT COALESCE(MAX(topic_order),0) FROM course_modules WHERE course_id=? AND module_name=?");
        $oq->execute([$course_id, $mod]);
        $start_order = (int)$oq->fetchColumn() + 1;
        $stmt = $pdo->prepare("INSERT INTO course_modules (course_id,module_name,topic_name,video_url,topic_order) VALUES (?,?,?,?,?)");
        foreach ($topics as $i => $topic) {
            $topic = trim($topic); if ($topic === '') continue;
            $video = trim($videos[$i] ?? '');
            $stmt->execute([$course_id, $mod, $topic, $video, $start_order++]);
        }
    }
    header("Location: sub_admin_modules.php?batch_id=$batch_id&added=1"); exit;
}

// ── Edit Topic ──
if (isset($_POST['edit_topic'])) {
    $id    = (int)$_POST['topic_id'];
    $mod   = trim($_POST['module_name']);
    $topic = trim($_POST['topic_name']);
    $video = trim($_POST['video_url']);
    $order = (int)($_POST['topic_order'] ?? 0);
    $pdo->prepare("UPDATE course_modules SET module_name=?,topic_name=?,video_url=?,topic_order=? WHERE id=? AND course_id=?")
        ->execute([$mod,$topic,$video,$order,$id,$course_id]);
    header("Location: sub_admin_modules.php?batch_id=$batch_id&edited=1"); exit;
}

// ── Delete Topic ──
if (isset($_GET['del_topic'])) {
    $pdo->prepare("DELETE FROM course_modules WHERE id=? AND course_id=?")->execute([(int)$_GET['del_topic'],$course_id]);
    header("Location: sub_admin_modules.php?batch_id=$batch_id"); exit;
}

// ── Delete Module ──
if (isset($_GET['del_module'])) {
    $pdo->prepare("DELETE FROM course_modules WHERE course_id=? AND module_name=?")->execute([$course_id,$_GET['del_module']]);
    header("Location: sub_admin_modules.php?batch_id=$batch_id"); exit;
}

// ── Add MCQ ──
if (isset($_POST['add_mcq'])) {
    $mod=$_POST['mcq_module']; $topic=$_POST['mcq_topic']; $q=$_POST['question'];
    $oa=$_POST['option_a']; $ob=$_POST['option_b']; $oc=$_POST['option_c']; $od=$_POST['option_d']; $correct=$_POST['correct_ans'];
    if ($topic && $q) {
        $pdo->prepare("INSERT INTO topic_mcq (course_id,module_name,topic_name,question,option_a,option_b,option_c,option_d,correct_ans) VALUES (?,?,?,?,?,?,?,?,?)")
            ->execute([$course_id,trim($mod),trim($topic),trim($q),trim($oa),trim($ob),trim($oc),trim($od),trim($correct)]);
    }
    header("Location: sub_admin_modules.php?batch_id=$batch_id&mcq=1"); exit;
}

// ── Delete MCQ ──
if (isset($_GET['del_mcq'])) {
    $pdo->prepare("DELETE FROM topic_mcq WHERE id=? AND course_id=?")->execute([(int)$_GET['del_mcq'],$course_id]);
    header("Location: sub_admin_modules.php?batch_id=$batch_id&tab=mcq"); exit;
}

// ── Upload Assignment ──
if (isset($_POST['upload_assignment'])) {
    $mod=trim($_POST['ass_module']); $topic=trim($_POST['ass_topic']);
    $title=trim($_POST['ass_title']); $inst=trim($_POST['instructions']);
    $file_path='';
    if (!empty($_FILES['assignment_file']['name'])) {
        $upload_dir = __DIR__ . '/../../uploads/assignments/';
        if (!is_dir($upload_dir)) mkdir($upload_dir,0755,true);
        $fname = time().'_'.basename($_FILES['assignment_file']['name']);
        move_uploaded_file($_FILES['assignment_file']['tmp_name'],$upload_dir.$fname);
        $file_path = 'uploads/assignments/'.$fname;
    }
    if ($topic && $title) {
        $pdo->prepare("INSERT INTO topic_assignments (course_id,module_name,topic_name,title,file_path,instructions) VALUES (?,?,?,?,?,?)")
            ->execute([$course_id,$mod,$topic,$title,$file_path,$inst]);
    }
    header("Location: sub_admin_modules.php?batch_id=$batch_id&ass=1"); exit;
}

// Fetch modules
$modules = [];
$mq = $pdo->prepare("SELECT * FROM course_modules WHERE course_id=? ORDER BY module_name, topic_order ASC");
$mq->execute([$course_id]);
foreach ($mq->fetchAll(PDO::FETCH_ASSOC) as $row) $modules[$row['module_name']][] = $row;

// MCQ counts
$mcq_counts = [];
$mcqq = $pdo->prepare("SELECT topic_name, COUNT(*) as cnt FROM topic_mcq WHERE course_id=? GROUP BY topic_name");
$mcqq->execute([$course_id]);
foreach ($mcqq->fetchAll(PDO::FETCH_ASSOC) as $r) $mcq_counts[$r['topic_name']] = $r['cnt'];

// Assignment counts
$ass_counts = [];
$assq = $pdo->prepare("SELECT topic_name, COUNT(*) as cnt FROM topic_assignments WHERE course_id=? GROUP BY topic_name");
$assq->execute([$course_id]);
foreach ($assq->fetchAll(PDO::FETCH_ASSOC) as $r) $ass_counts[$r['topic_name']] = $r['cnt'];

$tab = $_GET['tab'] ?? 'modules';

$all_topics = [];
foreach ($modules as $mod => $topics)
    foreach ($topics as $t) $all_topics[] = ['module'=>$mod,'topic'=>$t['topic_name']];

function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Modules — <?= h($batchInfo['batch_name']) ?></title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:#f1f5f9;display:flex;}
.sidebar{width:245px;min-width:245px;background:#0f172a;height:100vh;position:fixed;top:0;left:0;display:flex;flex-direction:column;overflow-y:auto;z-index:200;}
.sidebar::-webkit-scrollbar{width:3px;}.sidebar::-webkit-scrollbar-thumb{background:#1e293b;border-radius:3px;}
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
.btn-back:hover{background:#e2e8f0;}
.alert{padding:12px 18px;border-radius:10px;margin-bottom:18px;font-size:13.5px;font-weight:500;}
.alert-ok{background:#dcfce7;border:1px solid #bbf7d0;color:#166534;}
.tabs{display:flex;gap:4px;margin-bottom:22px;background:#fff;border-radius:12px;padding:5px;border:1px solid #e2e8f0;width:fit-content;box-shadow:0 1px 6px rgba(0,0,0,0.04);}
.tab-btn{padding:9px 22px;border-radius:9px;font-size:13.5px;font-weight:700;text-decoration:none;color:#64748b;transition:all 0.15s;}
.tab-btn:hover{background:#f1f5f9;color:#0f172a;}
.tab-btn.active{background:#2563eb;color:#fff;}
.layout{display:grid;grid-template-columns:420px 1fr;gap:22px;align-items:start;}
.form-card{background:#fff;border-radius:14px;padding:22px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);position:sticky;top:20px;}
.form-card h2{font-size:14px;font-weight:800;color:#0f172a;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #f1f5f9;}
.field{margin-bottom:13px;}
.field label{display:block;font-size:10.5px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:5px;}
.field input,.field select,.field textarea{width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13.5px;font-family:'DM Sans',sans-serif;color:#0f172a;background:#fafafa;outline:none;transition:border-color 0.2s;}
.field input:focus,.field select:focus,.field textarea:focus{border-color:#2563eb;background:#fff;}
.field textarea{resize:vertical;min-height:70px;}
.btn-submit{width:100%;padding:11px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:700;font-family:'DM Sans',sans-serif;cursor:pointer;margin-top:4px;}
.btn-submit:hover{opacity:0.9;}
.subparts-label{font-size:10.5px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:8px;display:flex;align-items:center;justify-content:space-between;}
.sub-count-badge{background:#eff6ff;color:#2563eb;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;}
.subparts-list{display:flex;flex-direction:column;gap:8px;margin-bottom:10px;}
.subpart-row{background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:10px;padding:10px 12px;display:flex;align-items:flex-start;gap:8px;}
.subpart-num{width:26px;height:26px;border-radius:50%;background:#2563eb;color:#fff;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;flex-shrink:0;margin-top:2px;}
.subpart-fields{flex:1;display:flex;flex-direction:column;gap:6px;}
.subpart-fields input{width:100%;padding:8px 10px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13px;font-family:'DM Sans',sans-serif;outline:none;background:#fff;color:#0f172a;}
.subpart-fields input:focus{border-color:#2563eb;}
.btn-remove-sub{width:28px;height:28px;border-radius:7px;background:#fee2e2;color:#dc2626;border:none;cursor:pointer;font-size:18px;line-height:1;display:flex;align-items:center;justify-content:center;flex-shrink:0;font-family:sans-serif;margin-top:2px;}
.btn-remove-sub:hover{background:#fecaca;}
.btn-add-sub{width:100%;padding:10px;background:#f0fdf4;color:#16a34a;border:2px dashed #86efac;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;margin-bottom:12px;}
.btn-add-sub:hover{background:#dcfce7;}
.module-block{background:#fff;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);margin-bottom:16px;overflow:hidden;}
.module-head{padding:14px 20px;background:linear-gradient(135deg,#1e3a8a,#2563eb);display:flex;align-items:center;justify-content:space-between;}
.module-head h3{font-size:14px;font-weight:800;color:#fff;}
.module-head-right{display:flex;align-items:center;gap:8px;}
.mod-count{background:rgba(255,255,255,0.2);color:#fff;padding:3px 10px;border-radius:20px;font-size:12px;font-weight:700;}
.btn-del-mod{background:rgba(239,68,68,0.3);color:#fff;border:none;padding:5px 12px;border-radius:6px;font-size:11.5px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;text-decoration:none;}
.btn-del-mod:hover{background:rgba(239,68,68,0.6);}
.topic-row{display:flex;align-items:center;gap:12px;padding:12px 20px;border-bottom:1px solid #f1f5f9;transition:background 0.15s;}
.topic-row:last-child{border-bottom:none;}
.topic-row:hover{background:#fafcff;}
.topic-num{width:28px;height:28px;border-radius:50%;background:#eff6ff;color:#2563eb;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;}
.topic-info{flex:1;}
.topic-name{font-size:13.5px;font-weight:600;color:#0f172a;}
.topic-badges{display:flex;gap:6px;margin-top:4px;flex-wrap:wrap;}
.tbadge{padding:2px 8px;border-radius:6px;font-size:11px;font-weight:700;}
.tb-video{background:#eff6ff;color:#2563eb;}.tb-mcq{background:#f5f3ff;color:#6d28d9;}.tb-ass{background:#fff7ed;color:#c2410c;}
.topic-actions{display:flex;gap:6px;}
.btn-edit-t{background:#f1f5f9;color:#475569;border:none;padding:5px 10px;border-radius:7px;font-size:11.5px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;}
.btn-edit-t:hover{background:#e2e8f0;}
.btn-del-t{background:#fee2e2;color:#dc2626;border:none;padding:5px 10px;border-radius:7px;font-size:11.5px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;text-decoration:none;display:inline-flex;align-items:center;}
.btn-del-t:hover{background:#fecaca;}
.no-modules{text-align:center;padding:50px;color:#94a3b8;font-size:14px;}
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:999;align-items:center;justify-content:center;}
.overlay.on{display:flex;}
.modal{background:#fff;border-radius:16px;padding:28px;width:100%;max-width:440px;box-shadow:0 20px 60px rgba(0,0,0,0.2);}
.modal h3{font-size:16px;font-weight:800;color:#0f172a;margin-bottom:16px;}
.modal-btns{display:flex;gap:10px;margin-top:16px;}
.btn-save-edit{flex:1;padding:11px;background:#2563eb;color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;}
.btn-close-modal{flex:1;padding:11px;background:#f1f5f9;color:#475569;border:none;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;}
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
      <h1>🗂️ Modules — <?= h($batchInfo['batch_name']) ?></h1>
      <p><?= h($batchInfo['course_name'] ?? '') ?> &nbsp;|&nbsp; Manage modules, MCQs and assignments</p>
    </div>
    <a href="sub_admin_batch.php" class="btn-back">← My Batches</a>
  </div>

  <?php if(isset($_GET['added'])): ?><div class="alert alert-ok">✅ Subparts added!</div><?php endif; ?>
  <?php if(isset($_GET['edited'])): ?><div class="alert alert-ok">✅ Subpart updated!</div><?php endif; ?>
  <?php if(isset($_GET['mcq'])): ?><div class="alert alert-ok">✅ MCQ added!</div><?php endif; ?>
  <?php if(isset($_GET['ass'])): ?><div class="alert alert-ok">✅ Assignment uploaded!</div><?php endif; ?>

  <div class="tabs">
    <a href="?batch_id=<?= $batch_id ?>&tab=modules"    class="tab-btn <?= $tab==='modules'?'active':'' ?>">🗂️ Modules & Subparts</a>
    <a href="?batch_id=<?= $batch_id ?>&tab=mcq"        class="tab-btn <?= $tab==='mcq'?'active':'' ?>">🧠 MCQ</a>
    <a href="?batch_id=<?= $batch_id ?>&tab=assignment" class="tab-btn <?= $tab==='assignment'?'active':'' ?>">📁 Assignment</a>
  </div>

  <?php if($tab==='modules'): ?>
  <div class="layout">
    <div class="form-card">
      <h2>➕ Add Module & Subparts</h2>
      <form method="POST" id="addForm">
        <div class="field">
          <label>Module Name</label>
          <input type="text" name="module_name" placeholder="e.g. SEO – Search Engine Optimization" list="mod-list" autocomplete="off" required>
          <datalist id="mod-list">
            <?php foreach(array_keys($modules) as $mn): ?><option value="<?= h($mn) ?>"><?php endforeach; ?>
          </datalist>
        </div>
        <div class="subparts-label"><span>Subparts</span><span class="sub-count-badge" id="subCountBadge">1 subpart</span></div>
        <div class="subparts-list" id="subpartsList">
          <div class="subpart-row" id="row-1">
            <div class="subpart-num">1</div>
            <div class="subpart-fields">
              <input type="text" name="topics[]" placeholder="Subpart name" required>
              <input type="url"  name="videos[]" placeholder="Video URL (optional)">
            </div>
          </div>
        </div>
        <button type="button" class="btn-add-sub" onclick="addRow()">➕ Add Another Subpart</button>
        <button type="submit" name="add_topics" class="btn-submit">💾 Save Module & Subparts</button>
      </form>
    </div>
    <div>
      <?php if($modules): foreach($modules as $mod_name => $topics): ?>
      <div class="module-block">
        <div class="module-head">
          <h3>📂 <?= h($mod_name) ?></h3>
          <div class="module-head-right">
            <span class="mod-count"><?= count($topics) ?> subparts</span>
            <a href="?batch_id=<?= $batch_id ?>&del_module=<?= urlencode($mod_name) ?>" class="btn-del-mod" onclick="return confirm('Delete entire module?')">🗑️ Delete Module</a>
          </div>
        </div>
        <?php foreach($topics as $i=>$t): ?>
        <div class="topic-row">
          <div class="topic-num"><?= $i+1 ?></div>
          <div class="topic-info">
            <div class="topic-name"><?= h($t['topic_name']) ?></div>
            <div class="topic-badges">
              <?php if(!empty($t['video_url'])): ?><span class="tbadge tb-video">🎥 Video</span><?php endif; ?>
              <?php if(!empty($mcq_counts[$t['topic_name']])): ?><span class="tbadge tb-mcq">🧠 <?= $mcq_counts[$t['topic_name']] ?> MCQ</span><?php endif; ?>
              <?php if(!empty($ass_counts[$t['topic_name']])): ?><span class="tbadge tb-ass">📁 Assignment</span><?php endif; ?>
            </div>
          </div>
          <div class="topic-actions">
            <button class="btn-edit-t" onclick="openEdit(<?= $t['id'] ?>,'<?= h($mod_name) ?>','<?= h($t['topic_name']) ?>','<?= h($t['video_url']??'') ?>',<?= (int)($t['topic_order']??0) ?>)">✏️ Edit</button>
            <a href="?batch_id=<?= $batch_id ?>&del_topic=<?= $t['id'] ?>" class="btn-del-t" onclick="return confirm('Delete?')">🗑️</a>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endforeach; else: ?>
      <div class="no-modules">📭 No modules yet. Add your first module!</div>
      <?php endif; ?>
    </div>
  </div>

  <?php elseif($tab==='mcq'): ?>
  <div class="layout">
    <div class="form-card" style="position:static;">
      <h2>🧠 Add MCQ Question</h2>
      <form method="POST">
        <div class="field">
          <label>Select Subpart</label>
          <select name="mcq_topic" required onchange="setMcqModule(this)">
            <option value="" disabled selected>Select subpart</option>
            <?php foreach($all_topics as $at): ?>
              <option value="<?= h($at['topic']) ?>" data-module="<?= h($at['module']) ?>"><?= h($at['module']) ?> → <?= h($at['topic']) ?></option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" name="mcq_module" id="mcq_module_hidden">
        </div>
        <div class="field"><label>Question</label><textarea name="question" placeholder="Enter question..." required></textarea></div>
        <div class="field"><label>Option A</label><input type="text" name="option_a" required></div>
        <div class="field"><label>Option B</label><input type="text" name="option_b" required></div>
        <div class="field"><label>Option C</label><input type="text" name="option_c"></div>
        <div class="field"><label>Option D</label><input type="text" name="option_d"></div>
        <div class="field">
          <label>Correct Answer</label>
          <select name="correct_ans" required>
            <option value="A">A</option><option value="B">B</option><option value="C">C</option><option value="D">D</option>
          </select>
        </div>
        <button type="submit" name="add_mcq" class="btn-submit">🧠 Add MCQ</button>
      </form>
    </div>
    <div>
      <?php if($modules): foreach($modules as $mod=>$topics): foreach($topics as $t):
          $tq=$pdo->prepare("SELECT * FROM topic_mcq WHERE course_id=? AND topic_name=?");
          $tq->execute([$course_id,$t['topic_name']]); $tqs=$tq->fetchAll(PDO::FETCH_ASSOC);
          if(!$tqs) continue; ?>
        <div class="module-block">
          <div class="module-head"><h3>🧠 <?= h($t['topic_name']) ?></h3><span class="mod-count"><?= count($tqs) ?> questions</span></div>
          <?php foreach($tqs as $i=>$q): ?>
          <div class="topic-row">
            <div class="topic-num"><?= $i+1 ?></div>
            <div class="topic-info">
              <div class="topic-name"><?= h($q['question']) ?></div>
              <div class="topic-badges">
                <span class="tbadge tb-video">A: <?= h($q['option_a']) ?></span>
                <span class="tbadge tb-video">B: <?= h($q['option_b']) ?></span>
                <span class="tbadge tb-mcq">✅ <?= h($q['correct_ans']) ?></span>
              </div>
            </div>
            <a href="?batch_id=<?= $batch_id ?>&tab=mcq&del_mcq=<?= $q['id'] ?>" class="btn-del-t" onclick="return confirm('Delete MCQ?')">🗑️</a>
          </div>
          <?php endforeach; ?>
        </div>
      <?php endforeach; endforeach;
      else: ?><div class="no-modules">📭 Add subparts first, then add MCQs.</div><?php endif; ?>
    </div>
  </div>

  <?php elseif($tab==='assignment'): ?>
  <div class="layout">
    <div class="form-card" style="position:static;">
      <h2>📁 Upload Assignment</h2>
      <form method="POST" enctype="multipart/form-data">
        <div class="field">
          <label>Select Subpart</label>
          <select name="ass_topic" required onchange="setAssModule(this)">
            <option value="" disabled selected>Select subpart</option>
            <?php foreach($all_topics as $at): ?>
              <option value="<?= h($at['topic']) ?>" data-module="<?= h($at['module']) ?>"><?= h($at['module']) ?> → <?= h($at['topic']) ?></option>
            <?php endforeach; ?>
          </select>
          <input type="hidden" name="ass_module" id="ass_module_hidden">
        </div>
        <div class="field"><label>Assignment Title</label><input type="text" name="ass_title" placeholder="e.g. Week 1 Assignment" required></div>
        <div class="field"><label>Instructions</label><textarea name="instructions" placeholder="Write instructions..."></textarea></div>
        <div class="field"><label>Upload File (PDF/DOC)</label><input type="file" name="assignment_file" accept=".pdf,.doc,.docx,.ppt,.pptx"></div>
        <button type="submit" name="upload_assignment" class="btn-submit">📁 Upload Assignment</button>
      </form>
    </div>
    <div>
      <?php
      $allAss=$pdo->prepare("SELECT * FROM topic_assignments WHERE course_id=? ORDER BY topic_name");
      $allAss->execute([$course_id]); $allAss=$allAss->fetchAll(PDO::FETCH_ASSOC);
      ?>
      <?php if($allAss): ?>
      <div class="module-block">
        <div class="module-head"><h3>📁 Uploaded Assignments</h3><span class="mod-count"><?= count($allAss) ?></span></div>
        <?php foreach($allAss as $i=>$a): ?>
        <div class="topic-row">
          <div class="topic-num"><?= $i+1 ?></div>
          <div class="topic-info">
            <div class="topic-name"><?= h($a['title']) ?></div>
            <div class="topic-badges">
              <span class="tbadge tb-video"><?= h($a['topic_name']) ?></span>
              <?php if(!empty($a['file_path'])): ?>
                <a href="../../<?= h($a['file_path']) ?>" target="_blank" class="tbadge tb-mcq" style="text-decoration:none;">📥 Download</a>
              <?php endif; ?>
            </div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?><div class="no-modules">📭 No assignments uploaded yet.</div><?php endif; ?>
    </div>
  </div>
  <?php endif; ?>
</div>

<div class="overlay" id="editOverlay">
  <div class="modal">
    <h3>✏️ Edit Subpart</h3>
    <form method="POST">
      <input type="hidden" name="topic_id" id="edit_id">
      <div class="field"><label>Module Name</label><input type="text" name="module_name" id="edit_mod" required></div>
      <div class="field"><label>Subpart Name</label><input type="text" name="topic_name" id="edit_topic" required></div>
      <div class="field"><label>Video URL</label><input type="url" name="video_url" id="edit_video"></div>
      <div class="field"><label>Order</label><input type="number" name="topic_order" id="edit_order" min="0"></div>
      <div class="modal-btns">
        <button type="submit" name="edit_topic" class="btn-save-edit">💾 Save</button>
        <button type="button" class="btn-close-modal" onclick="closeEdit()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
let rowCount=1;
function addRow(){rowCount++;const list=document.getElementById('subpartsList');const div=document.createElement('div');div.className='subpart-row';div.id='row-'+rowCount;div.innerHTML=`<div class="subpart-num">${rowCount}</div><div class="subpart-fields"><input type="text" name="topics[]" placeholder="Subpart name" required><input type="url" name="videos[]" placeholder="Video URL (optional)"></div><button type="button" class="btn-remove-sub" onclick="removeRow(${rowCount})">×</button>`;list.appendChild(div);updateBadge();div.querySelector('input[type=text]').focus();}
function removeRow(id){const el=document.getElementById('row-'+id);if(el)el.remove();renumber();}
function renumber(){document.querySelectorAll('#subpartsList .subpart-row').forEach((row,i)=>{row.querySelector('.subpart-num').textContent=i+1;});updateBadge();}
function updateBadge(){const n=document.querySelectorAll('#subpartsList .subpart-row').length;document.getElementById('subCountBadge').textContent=n+' subpart'+(n>1?'s':'');}
function openEdit(id,mod,topic,video,order){document.getElementById('edit_id').value=id;document.getElementById('edit_mod').value=mod;document.getElementById('edit_topic').value=topic;document.getElementById('edit_video').value=video;document.getElementById('edit_order').value=order;document.getElementById('editOverlay').classList.add('on');}
function closeEdit(){document.getElementById('editOverlay').classList.remove('on');}
document.getElementById('editOverlay').addEventListener('click',function(e){if(e.target===this)closeEdit();});
function setMcqModule(sel){document.getElementById('mcq_module_hidden').value=sel.options[sel.selectedIndex].dataset.module||'';}
function setAssModule(sel){document.getElementById('ass_module_hidden').value=sel.options[sel.selectedIndex].dataset.module||'';}
</script>
</body>
</html>