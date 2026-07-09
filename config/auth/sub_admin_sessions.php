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

// Topic suggestions
$topics_list = [];
if ($course_id) {
    $ts = $pdo->prepare("SELECT DISTINCT topic_name FROM course_modules WHERE course_id=? ORDER BY topic_order ASC");
    $ts->execute([$course_id]);
    $topics_list = array_column($ts->fetchAll(PDO::FETCH_ASSOC), 'topic_name');
}
$used_stmt = $pdo->prepare("SELECT DISTINCT topic FROM class_sessions WHERE course_id=? ORDER BY topic ASC");
$used_stmt->execute([$course_id]);
$used_topics = array_column($used_stmt->fetchAll(PDO::FETCH_ASSOC), 'topic');
$all_suggestions = array_unique(array_merge($topics_list, $used_topics));

// ── Edit Session ──
if (isset($_POST['edit_session'])) {
    $sid = (int)$_POST['sid'];
    $date = trim($_POST['date']); $start = trim($_POST['start_time']); $end = trim($_POST['end_time']);
    $topic = trim($_POST['topic'] ?? ''); $sess_type = trim($_POST['session_type'] ?? 'lecture'); $status = trim($_POST['status'] ?? 'scheduled');
    if ($sid && $date && $topic) {
        $pdo->prepare("UPDATE class_sessions SET date=?,start_time=?,end_time=?,topic=?,session_type=?,status=? WHERE id=? AND course_id=? AND batch_id=?")
            ->execute([$date,$start?:null,$end?:null,$topic,$sess_type,$status,$sid,$course_id,$batch_id]);
    }
    header("Location: sub_admin_sessions.php?batch_id=$batch_id&edited=1"); exit;
}

// ── Add Session ──
if (isset($_POST['add_session'])) {
    $date = trim($_POST['date']); $start = trim($_POST['start_time']); $end = trim($_POST['end_time']);
    $topic = trim($_POST['topic'] ?? ''); $sess_type = trim($_POST['session_type'] ?? 'lecture');
    if ($date && $topic) {
        $pdo->prepare("INSERT INTO class_sessions (course_id,batch_id,batch_no,date,start_time,end_time,topic,status,session_type) VALUES (?,?,?,?,?,?,?,?,?)")
            ->execute([$course_id,$batch_id,$batchInfo['batch_name'],$date,$start?:null,$end?:null,$topic,'scheduled',$sess_type]);
    }
    header("Location: sub_admin_sessions.php?batch_id=$batch_id&added=1"); exit;
}

// ── Status Change ──
if (isset($_GET['action'], $_GET['sid'])) {
    $sid = (int)$_GET['sid'];
    $map = ['cancel'=>'cancelled','reschedule'=>'scheduled'];
    $ns = $map[$_GET['action']] ?? null;
    if ($ns) $pdo->prepare("UPDATE class_sessions SET status=? WHERE id=? AND batch_id=?")->execute([$ns,$sid,$batch_id]);
    header("Location: sub_admin_sessions.php?batch_id=$batch_id"); exit;
}

// ── Delete ──
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM class_sessions WHERE id=? AND batch_id=?")->execute([(int)$_GET['delete'],$batch_id]);
    header("Location: sub_admin_sessions.php?batch_id=$batch_id"); exit;
}

// Fetch sessions
$sessions = $pdo->prepare("SELECT * FROM class_sessions WHERE batch_id=? ORDER BY date DESC, start_time DESC");
$sessions->execute([$batch_id]);
$sessions = $sessions->fetchAll(PDO::FETCH_ASSOC);

function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sessions — <?= h($batchInfo['batch_name']) ?></title>
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
.layout{display:grid;grid-template-columns:320px 1fr;gap:22px;align-items:start;}
.form-card{background:#fff;border-radius:14px;padding:22px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);position:sticky;top:20px;}
.form-card h2{font-size:14px;font-weight:800;color:#0f172a;margin-bottom:16px;padding-bottom:10px;border-bottom:2px solid #f1f5f9;}
.field{margin-bottom:13px;}
.field label{display:block;font-size:10.5px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:5px;}
.field input,.field select{width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13.5px;font-family:'DM Sans',sans-serif;color:#0f172a;background:#fafafa;outline:none;transition:border-color 0.2s;}
.field input:focus,.field select:focus{border-color:#2563eb;background:#fff;}
.fields-row{display:grid;grid-template-columns:1fr 1fr;gap:10px;}
.btn-add{width:100%;padding:11px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:700;font-family:'DM Sans',sans-serif;cursor:pointer;}
.btn-add:hover{opacity:0.9;}
.sess-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);overflow:hidden;}
.sess-card-head{padding:14px 20px;border-bottom:1px solid #f1f5f9;}
.sess-card-head h2{font-size:14px;font-weight:700;color:#0f172a;}
table{width:100%;border-collapse:collapse;}
th{padding:10px 14px;text-align:left;font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;border-bottom:2px solid #e2e8f0;background:#f8fafc;}
td{padding:12px 14px;font-size:13px;color:#334155;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tbody tr:hover{background:#fafcff;}
.badge{display:inline-flex;align-items:center;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;}
.scheduled{background:#eff6ff;color:#1d4ed8;}.completed{background:#dcfce7;color:#15803d;}.cancelled{background:#fee2e2;color:#dc2626;}
.lecture{background:#eff6ff;color:#1d4ed8;}.communication{background:#fff7ed;color:#c2410c;}.doubt{background:#f5f3ff;color:#6d28d9;}.online{background:#f0fdf4;color:#15803d;}
.act-btns{display:flex;gap:5px;flex-wrap:wrap;}
.btn-sm{padding:4px 10px;border-radius:6px;font-size:11.5px;font-weight:700;border:none;cursor:pointer;font-family:'DM Sans',sans-serif;text-decoration:none;display:inline-block;}
.bce{background:#eff6ff;color:#2563eb;}.bca{background:#fee2e2;color:#dc2626;}.bcr{background:#f3f4f6;color:#374151;}.bcd{background:#fef3c7;color:#92400e;}
.empty{text-align:center;padding:40px;color:#94a3b8;}
.modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:999;align-items:center;justify-content:center;}
.modal-overlay.on{display:flex;}
.modal{background:#fff;border-radius:16px;padding:28px;width:100%;max-width:460px;box-shadow:0 20px 60px rgba(0,0,0,0.2);}
.modal h3{font-size:16px;font-weight:800;color:#0f172a;margin-bottom:18px;padding-bottom:10px;border-bottom:2px solid #f1f5f9;}
.modal .field label{display:block;font-size:10.5px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:5px;}
.modal .field input,.modal .field select{width:100%;padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13.5px;font-family:'DM Sans',sans-serif;color:#0f172a;outline:none;}
.modal-btns{display:flex;gap:10px;margin-top:18px;}
.btn-save-m{flex:1;padding:11px;background:#2563eb;color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;}
.btn-close-m{flex:1;padding:11px;background:#f1f5f9;color:#475569;border:none;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;}
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
      <h1>📅 Sessions — <?= h($batchInfo['batch_name']) ?></h1>
      <p><?= h($batchInfo['course_name'] ?? '') ?> &nbsp;|&nbsp; Add and manage sessions</p>
    </div>
    <a href="sub_admin_batch.php" class="btn-back">← My Batches</a>
  </div>

  <?php if(isset($_GET['added'])): ?><div class="alert alert-ok">✅ Session added!</div><?php endif; ?>
  <?php if(isset($_GET['edited'])): ?><div class="alert alert-ok">✏️ Session updated!</div><?php endif; ?>

  <div class="layout">
    <div class="form-card">
      <h2>➕ Add New Session</h2>
      <form method="POST">
        <!-- Batch fixed — only this batch -->
        <div class="field">
          <label>Batch</label>
          <div style="padding:9px 12px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13.5px;font-weight:700;color:#0f172a;background:#f1f5f9;">
            🏫 <?= h($batchInfo['batch_name']) ?>
          </div>
        </div>
        <div class="field">
          <label>Session Type</label>
          <select name="session_type">
            <option value="lecture">📘 Lecture</option>
            <option value="communication">🗣️ Communication</option>
            <option value="doubt">🙋 Doubt Session</option>
            <option value="online">💻 Online</option>
          </select>
        </div>
        <div class="field">
          <label>Topic</label>
          <input type="text" name="topic" placeholder="Select or type topic" list="topics-datalist" autocomplete="off" required>
          <datalist id="topics-datalist">
            <?php foreach($all_suggestions as $t): ?><option value="<?= h($t) ?>"><?php endforeach; ?>
          </datalist>
        </div>
        <div class="field"><label>Date</label><input type="date" name="date" required></div>
        <div class="fields-row">
          <div class="field"><label>Start Time</label><input type="time" name="start_time" value="<?= $batchInfo['timing_start'] ? date('H:i',strtotime($batchInfo['timing_start'])) : '' ?>"></div>
          <div class="field"><label>End Time</label><input type="time" name="end_time" value="<?= $batchInfo['timing_end'] ? date('H:i',strtotime($batchInfo['timing_end'])) : '' ?>"></div>
        </div>
        <button type="submit" name="add_session" class="btn-add">📅 Add Session</button>
      </form>
    </div>

    <div>
      <div class="sess-card">
        <div class="sess-card-head"><h2>📋 Sessions (<?= count($sessions) ?>)</h2></div>
        <?php if($sessions): ?>
        <table>
          <thead><tr><th>Date</th><th>Type</th><th>Topic</th><th>Time</th><th>Status</th><th>Actions</th></tr></thead>
          <tbody>
          <?php foreach($sessions as $s): ?>
          <tr>
            <td><strong><?= date('d M',strtotime($s['date'])) ?></strong><br><span style="font-size:11px;color:#94a3b8;"><?= date('D',strtotime($s['date'])) ?></span></td>
            <td><span class="badge <?= h($s['session_type']??'lecture') ?>"><?= ucfirst(h($s['session_type']??'lecture')) ?></span></td>
            <td style="max-width:160px;"><?= h($s['topic']) ?></td>
            <td style="font-size:12px;color:#64748b;"><?= !empty($s['start_time'])?date('g:i A',strtotime($s['start_time'])).'<br>'.date('g:i A',strtotime($s['end_time'])):'—' ?></td>
            <td><span class="badge <?= $s['status'] ?>"><?= ucfirst($s['status']) ?></span></td>
            <td>
              <div class="act-btns">
                <button class="btn-sm bce" onclick="openEdit(<?= $s['id'] ?>,'<?= h($s['topic']) ?>','<?= $s['date'] ?>','<?= $s['start_time']?substr($s['start_time'],0,5):'' ?>','<?= $s['end_time']?substr($s['end_time'],0,5):'' ?>','<?= h($s['session_type']??'lecture') ?>','<?= h($s['status']) ?>')">✏️ Edit</button>
                <?php if($s['status']==='scheduled'): ?><a href="?batch_id=<?= $batch_id ?>&action=cancel&sid=<?= $s['id'] ?>" class="btn-sm bca">❌ Cancel</a><?php endif; ?>
                <?php if($s['status']==='cancelled'): ?><a href="?batch_id=<?= $batch_id ?>&action=reschedule&sid=<?= $s['id'] ?>" class="btn-sm bcr">🔄 Reschedule</a><?php endif; ?>
                <a href="?batch_id=<?= $batch_id ?>&delete=<?= $s['id'] ?>" class="btn-sm bcd" onclick="return confirm('Delete?')">🗑️</a>
              </div>
            </td>
          </tr>
          <?php endforeach; ?>
          </tbody>
        </table>
        <?php else: ?><div class="empty">📭 No sessions yet. Add one from the form.</div><?php endif; ?>
      </div>
    </div>
  </div>
</div>

<div class="modal-overlay" id="editOverlay">
  <div class="modal">
    <h3>✏️ Edit Session</h3>
    <form method="POST">
      <input type="hidden" name="edit_session" value="1">
      <input type="hidden" name="sid" id="editSid">
      <div class="field"><label>Topic</label><input type="text" name="topic" id="editTopic" list="topics-datalist" required></div>
      <div class="field">
        <label>Session Type</label>
        <select name="session_type" id="editType">
          <option value="lecture">📘 Lecture</option>
          <option value="communication">🗣️ Communication</option>
          <option value="doubt">🙋 Doubt Session</option>
          <option value="online">💻 Online</option>
        </select>
      </div>
      <div class="field">
        <label>Status</label>
        <select name="status" id="editStatus">
          <option value="scheduled">Scheduled</option>
          <option value="completed">Completed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="field"><label>Date</label><input type="date" name="date" id="editDate" required></div>
      <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;">
        <div class="field"><label>Start Time</label><input type="time" name="start_time" id="editStart"></div>
        <div class="field"><label>End Time</label><input type="time" name="end_time" id="editEnd"></div>
      </div>
      <div class="modal-btns">
        <button type="submit" class="btn-save-m">💾 Save Changes</button>
        <button type="button" class="btn-close-m" onclick="closeEdit()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function openEdit(id,topic,date,start,end,type,status){
    document.getElementById('editSid').value=id;document.getElementById('editTopic').value=topic;
    document.getElementById('editDate').value=date;document.getElementById('editStart').value=start;
    document.getElementById('editEnd').value=end;document.getElementById('editType').value=type;
    document.getElementById('editStatus').value=status;
    document.getElementById('editOverlay').classList.add('on');
}
function closeEdit(){document.getElementById('editOverlay').classList.remove('on');}
document.getElementById('editOverlay').addEventListener('click',function(e){if(e.target===this)closeEdit();});
</script>
</body>
</html>