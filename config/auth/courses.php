<?php
require_once __DIR__ . '/../db.php';
session_start();
if (!isset($_SESSION['admin_username'])) { header("Location: login.php"); exit; }

// Create course
if (isset($_POST['create_course'])) {
    $name = trim($_POST['course_name']);
    $code = trim($_POST['course_code']);
    $dur  = trim($_POST['duration']);
    if ($name) {
        $pdo->prepare("INSERT INTO courses (course_name,course_code,duration) VALUES (?,?,?)")
            ->execute([$name,$code,$dur]);
    }
    header("Location: courses.php?created=1"); exit;
}

// Delete course
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM courses WHERE id=?")->execute([(int)$_GET['delete']]);
    header("Location: courses.php?deleted=1"); exit;
}

// Fetch courses with stats
$courses = $pdo->query("
    SELECT c.*,
        (SELECT COUNT(DISTINCT module_name) FROM course_modules WHERE course_id=c.id) as mod_count,
        (SELECT COUNT(*) FROM course_modules WHERE course_id=c.id) as topic_count,
        (SELECT COUNT(*) FROM batches WHERE course_id=c.id) as batch_count,
        (SELECT COUNT(DISTINCT ud.reg_no) FROM user_details ud INNER JOIN batches b ON b.batch_name=ud.batch_no WHERE b.course_id=c.id) as stu_count
    FROM courses c ORDER BY c.id DESC
")->fetchAll(PDO::FETCH_ASSOC);

function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Courses | Admin</title>
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
.alert{padding:12px 18px;border-radius:10px;margin-bottom:18px;font-size:13.5px;font-weight:500;}
.alert-ok{background:#dcfce7;border:1px solid #bbf7d0;color:#166534;}
.layout{display:grid;grid-template-columns:340px 1fr;gap:22px;align-items:start;}
.form-card{background:#fff;border-radius:14px;padding:24px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);}
.form-card h2{font-size:15px;font-weight:800;color:#0f172a;margin-bottom:18px;padding-bottom:12px;border-bottom:2px solid #f1f5f9;}
.field{margin-bottom:14px;}
.field label{display:block;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:5px;}
.field input{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13.5px;font-family:'DM Sans',sans-serif;color:#0f172a;background:#fafafa;outline:none;transition:border-color 0.2s;}
.field input:focus{border-color:#2563eb;background:#fff;}
.btn-create{width:100%;padding:12px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;font-family:'DM Sans',sans-serif;cursor:pointer;}
.btn-create:hover{opacity:0.9;}
.sec-title{font-size:13px;font-weight:700;color:#0f172a;margin-bottom:14px;}
.courses-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(260px,1fr));gap:16px;}
.course-card{background:#fff;border-radius:14px;padding:22px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);transition:transform 0.15s,box-shadow 0.15s;}
.course-card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,0.08);}
.cc-icon{width:44px;height:44px;border-radius:12px;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:20px;margin-bottom:12px;}
.cc-name{font-size:15px;font-weight:800;color:#0f172a;margin-bottom:2px;}
.cc-code{font-size:12px;color:#64748b;margin-bottom:12px;}
.cc-stats{display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:16px;}
.cc-stat{background:#f8fafc;border-radius:8px;padding:8px 10px;text-align:center;}
.cc-stat-val{font-size:18px;font-weight:800;color:#0f172a;}
.cc-stat-lbl{font-size:10px;color:#64748b;font-weight:600;text-transform:uppercase;margin-top:1px;}
.cc-actions{display:flex;gap:8px;}
.btn-modules{flex:1;padding:8px;background:#eff6ff;color:#2563eb;border:none;border-radius:8px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;text-decoration:none;text-align:center;transition:background 0.15s;}
.btn-modules:hover{background:#dbeafe;}
.btn-sessions{flex:1;padding:8px;background:#f0fdf4;color:#16a34a;border:none;border-radius:8px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;text-decoration:none;text-align:center;transition:background 0.15s;}
.btn-sessions:hover{background:#dcfce7;}
.btn-del-c{padding:8px 12px;background:#fee2e2;color:#dc2626;border:none;border-radius:8px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;}
.btn-del-c:hover{background:#fecaca;}
.empty{text-align:center;padding:40px;color:#94a3b8;font-size:14px;}
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:999;align-items:center;justify-content:center;}
.overlay.on{display:flex;}
.modal{background:#fff;border-radius:16px;padding:32px 28px;width:100%;max-width:360px;box-shadow:0 20px 60px rgba(0,0,0,0.2);text-align:center;}
.modal .m-icon{font-size:44px;margin-bottom:12px;}
.modal h3{font-size:17px;font-weight:700;color:#0f172a;margin-bottom:6px;}
.modal p{font-size:13px;color:#64748b;margin-bottom:22px;}
.modal-btns{display:flex;gap:10px;}
.btn-conf{flex:1;padding:11px;background:#ef4444;color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;text-decoration:none;text-align:center;}
.btn-conf:hover{background:#dc2626;}
.btn-canc{flex:1;padding:11px;background:#f1f5f9;color:#475569;border:none;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;}
.btn-canc:hover{background:#e2e8f0;}
</style>
</head>
<body>
<div class="sidebar">
  <div class="sb-brand"><h2>🎓 Admin Panel</h2><p>Culture of Internet</p></div>
  <div class="sb-sec">Main</div>
  <a href="dashboard.php"><span class="sb-icon">🏠</span>Dashboard</a>
  <div class="sb-sec">Students</div>
  <a href="add_student.php"><span class="sb-icon">➕</span>Add Student</a>
  <a href="students_list.php"><span class="sb-icon">📋</span>Students List</a>
  <div class="sb-sec">Batches &amp; Courses</div>
  <a href="batch.php"><span class="sb-icon">🏫</span>Batches</a>
  <a href="view_batch.php"><span class="sb-icon">👁️</span>View Batch</a>
  <a href="courses.php" class="active"><span class="sb-icon">📚</span>Courses</a>
  <div class="sb-sec">Sub Admins</div>
  <a href="add_sub_admin.php"><span class="sb-icon">👤</span>Add Sub Admin</a>
  <a href="sub_admins_list.php"><span class="sb-icon">👥</span>Sub Admins List</a>
  <div class="sb-bottom"><a href="logout.php"><span class="sb-icon">🚪</span>Logout</a></div>
</div>

<div class="main">
  <div class="topbar"><h1>📚 Courses</h1></div>
  <?php if(isset($_GET['created'])): ?><div class="alert alert-ok">✅ Course created!</div><?php endif; ?>
  <?php if(isset($_GET['deleted'])): ?><div class="alert alert-ok">🗑️ Course deleted.</div><?php endif; ?>

  <div class="layout">
    <div class="form-card">
      <h2>➕ Create New Course</h2>
      <form method="POST">
        <div class="field"><label>Course Name</label><input type="text" name="course_name" placeholder="e.g. Digital Marketing" required></div>
        <div class="field"><label>Course Code</label><input type="text" name="course_code" placeholder="e.g. DM101"></div>
        <div class="field"><label>Duration</label><input type="text" name="duration" placeholder="e.g. 6 months"></div>
        <button type="submit" name="create_course" class="btn-create">📚 Create Course</button>
      </form>
    </div>

    <div>
      <div class="sec-title">All Courses (<?= count($courses) ?>)</div>
      <?php if($courses): ?>
      <div class="courses-grid">
        <?php foreach($courses as $c): ?>
        <div class="course-card">
          <div class="cc-icon">📚</div>
          <div class="cc-name"><?= h($c['course_name']) ?></div>
          <div class="cc-code"><?= h($c['course_code']??'') ?> <?= !empty($c['duration'])?'· '.$c['duration']:'' ?></div>
          <div class="cc-stats">
            <div class="cc-stat"><div class="cc-stat-val"><?= $c['mod_count'] ?></div><div class="cc-stat-lbl">Modules</div></div>
            <div class="cc-stat"><div class="cc-stat-val"><?= $c['topic_count'] ?></div><div class="cc-stat-lbl">Topics</div></div>
            <div class="cc-stat"><div class="cc-stat-val"><?= $c['batch_count'] ?></div><div class="cc-stat-lbl">Batches</div></div>
            <div class="cc-stat"><div class="cc-stat-val"><?= $c['stu_count'] ?></div><div class="cc-stat-lbl">Students</div></div>
          </div>
          <div class="cc-actions">
            <a href="course_modules.php?course_id=<?= $c['id'] ?>" class="btn-modules">🗂️ Modules</a>
            <a href="course_sessions.php?course_id=<?= $c['id'] ?>" class="btn-sessions">📅 Sessions</a>
            <button class="btn-del-c" onclick="openDel(<?= $c['id'] ?>,'<?= h($c['course_name']) ?>')">🗑️</button>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php else: ?><div class="empty">No courses yet. Create one from the form.</div><?php endif; ?>
    </div>
  </div>
</div>

<div class="overlay" id="delOverlay">
  <div class="modal">
    <div class="m-icon">⚠️</div><h3>Delete Course?</h3><p id="delMsg"></p>
    <div class="modal-btns">
      <a href="#" id="delLink" class="btn-conf">🗑️ Yes, Delete</a>
      <button class="btn-canc" onclick="closeDel()">Cancel</button>
    </div>
  </div>
</div>
<script>
function openDel(id,name){document.getElementById('delMsg').textContent='Delete "'+name+'"? Cannot be undone.';document.getElementById('delLink').href='courses.php?delete='+id;document.getElementById('delOverlay').classList.add('on');}
function closeDel(){document.getElementById('delOverlay').classList.remove('on');}
document.getElementById('delOverlay').addEventListener('click',function(e){if(e.target===this)closeDel();});
</script>
</body>
</html>