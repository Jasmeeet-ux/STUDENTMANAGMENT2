<?php
require_once __DIR__ . '/../db.php';
session_start();
if (!isset($_SESSION['admin_username'])) { header("Location: login.php"); exit; }

// Delete sub admin
if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $pdo->prepare("DELETE FROM sub_admins WHERE id=?")->execute([$id]);
    $pdo->prepare("DELETE FROM sub_admin_batches WHERE sub_admin_id=?")->execute([$id]);
    header("Location: sub_admins_list.php?deleted=1"); exit;
}

// Update batches (AJAX)
if (isset($_POST['update_batches'])) {
    $id  = (int)$_POST['sub_admin_id'];
    $ids = $_POST['batch_ids'] ?? [];
    $pdo->prepare("DELETE FROM sub_admin_batches WHERE sub_admin_id=?")->execute([$id]);
    foreach ($ids as $bid) {
        $pdo->prepare("INSERT INTO sub_admin_batches (sub_admin_id,batch_id) VALUES (?,?)")->execute([$id,(int)$bid]);
    }
    echo json_encode(['success'=>true]); exit;
}

// Fetch all sub admins
$sub_admins = $pdo->query("SELECT * FROM sub_admins ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

// Fetch assigned batches per sub admin
$assigned = [];
$ab = $pdo->query("SELECT sab.sub_admin_id, b.id, b.batch_name, c.course_name
    FROM sub_admin_batches sab
    JOIN batches b ON b.id=sab.batch_id
    LEFT JOIN courses c ON c.id=b.course_id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($ab as $r) $assigned[$r['sub_admin_id']][] = $r;

// All batches for edit modal
$all_batches = $pdo->query("SELECT b.*, c.course_name FROM batches b LEFT JOIN courses c ON c.id=b.course_id ORDER BY b.batch_name")->fetchAll(PDO::FETCH_ASSOC);

function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sub Admins List | Admin</title>
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
.topbar h1{font-size:22px;font-weight:800;color:#0f172a;}
.btn-add{background:#2563eb;color:#fff;padding:9px 18px;border-radius:9px;text-decoration:none;font-size:13px;font-weight:700;}
.btn-add:hover{opacity:0.9;}
.alert-ok{background:#dcfce7;border:1px solid #bbf7d0;color:#166534;padding:12px 18px;border-radius:10px;margin-bottom:18px;font-size:13.5px;font-weight:500;}
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:18px;}
.sa-card{background:#fff;border-radius:14px;padding:22px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);transition:transform 0.15s,box-shadow 0.15s;}
.sa-card:hover{transform:translateY(-2px);box-shadow:0 6px 20px rgba(0,0,0,0.08);}
.sa-top{display:flex;align-items:center;gap:14px;margin-bottom:14px;}
.sa-avatar{width:46px;height:46px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#7c3aed);display:flex;align-items:center;justify-content:center;font-size:18px;font-weight:800;color:#fff;flex-shrink:0;}
.sa-name{font-size:15px;font-weight:800;color:#0f172a;}
.sa-user{font-size:12px;color:#64748b;margin-top:2px;}
.sa-joined{font-size:11px;color:#94a3b8;margin-top:2px;}
.batch-tags{display:flex;flex-wrap:wrap;gap:6px;margin-bottom:14px;min-height:28px;}
.btag{background:#eff6ff;color:#1d4ed8;padding:4px 10px;border-radius:20px;font-size:11.5px;font-weight:700;}
.no-batch{font-size:12px;color:#94a3b8;font-style:italic;}
.sa-actions{display:flex;gap:8px;}
.btn-edit-b{flex:1;padding:8px;background:#f0fdf4;color:#16a34a;border:none;border-radius:8px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;}
.btn-edit-b:hover{background:#dcfce7;}
.btn-del-sa{padding:8px 14px;background:#fee2e2;color:#dc2626;border:none;border-radius:8px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;}
.btn-del-sa:hover{background:#fecaca;}
.empty{text-align:center;padding:60px;color:#94a3b8;font-size:14px;}

/* Modal */
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:999;align-items:center;justify-content:center;}
.overlay.on{display:flex;}
.modal{background:#fff;border-radius:16px;padding:28px;width:100%;max-width:480px;box-shadow:0 20px 60px rgba(0,0,0,0.2);}
.modal h3{font-size:16px;font-weight:800;color:#0f172a;margin-bottom:6px;}
.modal p{font-size:13px;color:#64748b;margin-bottom:16px;}
.batch-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:8px;max-height:260px;overflow-y:auto;padding-right:4px;}
.batch-chk{display:flex;align-items:center;gap:8px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:9px;padding:9px 12px;cursor:pointer;transition:all 0.15s;}
.batch-chk:hover{border-color:#2563eb;background:#eff6ff;}
.batch-chk input{width:15px;height:15px;accent-color:#2563eb;cursor:pointer;}
.batch-chk-lbl{font-size:12.5px;font-weight:600;color:#0f172a;}
.batch-chk-sub{font-size:11px;color:#64748b;}
.modal-btns{display:flex;gap:10px;margin-top:18px;}
.btn-save-m{flex:1;padding:11px;background:#2563eb;color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;}
.btn-save-m:hover{background:#1d4ed8;}
.btn-close-m{flex:1;padding:11px;background:#f1f5f9;color:#475569;border:none;border-radius:9px;font-size:14px;font-weight:600;cursor:pointer;font-family:'DM Sans',sans-serif;}
.btn-close-m:hover{background:#e2e8f0;}
.save-msg{display:none;background:#dcfce7;color:#166534;padding:8px 14px;border-radius:8px;font-size:13px;font-weight:600;margin-top:10px;text-align:center;}
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
  <a href="courses.php"><span class="sb-icon">📚</span>Courses</a>
  <div class="sb-sec">Sub Admins</div>
  <a href="add_sub_admin.php"><span class="sb-icon">👤</span>Add Sub Admin</a>
  <a href="sub_admins_list.php" class="active"><span class="sb-icon">👥</span>Sub Admins List</a>
  <div class="sb-bottom"><a href="logout.php"><span class="sb-icon">🚪</span>Logout</a></div>
</div>

<div class="main">
  <div class="topbar">
    <h1>👥 Sub Admins</h1>
    <a href="add_sub_admin.php" class="btn-add">➕ Add Sub Admin</a>
  </div>
  <?php if(isset($_GET['deleted'])): ?><div class="alert-ok">🗑️ Sub admin deleted.</div><?php endif; ?>
  <?php if(isset($_GET['created'])): ?><div class="alert-ok">✅ Sub admin created!</div><?php endif; ?>

  <?php if($sub_admins): ?>
  <div class="grid">
    <?php foreach($sub_admins as $sa): ?>
    <div class="sa-card">
      <div class="sa-top">
        <div class="sa-avatar"><?= strtoupper(substr($sa['name'],0,1)) ?></div>
        <div>
          <div class="sa-name"><?= h($sa['name']) ?></div>
          <div class="sa-user">@<?= h($sa['username']) ?></div>
          <div class="sa-joined">Joined <?= date('d M Y',strtotime($sa['created_at'])) ?></div>
        </div>
      </div>
      <div class="batch-tags">
        <?php if(!empty($assigned[$sa['id']])): ?>
          <?php foreach($assigned[$sa['id']] as $b): ?>
            <span class="btag"><?= h($b['batch_name']) ?></span>
          <?php endforeach; ?>
        <?php else: ?>
          <span class="no-batch">No batches assigned</span>
        <?php endif; ?>
      </div>
      <div class="sa-actions">
        <button class="btn-edit-b" onclick="openEdit(<?= $sa['id'] ?>,'<?= h($sa['name']) ?>')">✏️ Edit Batches</button>
        <a href="sub_admins_list.php?delete=<?= $sa['id'] ?>" class="btn-del-sa" onclick="return confirm('Delete sub admin?')">🗑️ Delete</a>
      </div>
    </div>
    <?php endforeach; ?>
  </div>
  <?php else: ?>
    <div class="empty">👤 No sub admins yet. <a href="add_sub_admin.php" style="color:#2563eb;font-weight:700;">Add one →</a></div>
  <?php endif; ?>
</div>

<!-- Edit Batches Modal -->
<div class="overlay" id="editOverlay">
  <div class="modal">
    <h3>✏️ Edit Batches</h3>
    <p id="editName" style="color:#0f172a;font-weight:700;"></p>
    <input type="hidden" id="editSaId">
    <div class="batch-grid" id="batchCheckGrid">
      <?php foreach($all_batches as $b): ?>
      <label class="batch-chk" id="bc_<?= $b['id'] ?>">
        <input type="checkbox" class="edit-batch-chk" value="<?= $b['id'] ?>">
        <div>
          <div class="batch-chk-lbl"><?= h($b['batch_name']) ?></div>
          <?php if(!empty($b['course_name'])): ?><div class="batch-chk-sub"><?= h($b['course_name']) ?></div><?php endif; ?>
        </div>
      </label>
      <?php endforeach; ?>
    </div>
    <div id="saveMsg" class="save-msg">✅ Batches updated!</div>
    <div class="modal-btns">
      <button class="btn-save-m" onclick="saveBatches()">💾 Save</button>
      <button class="btn-close-m" onclick="closeEdit()">Cancel</button>
    </div>
  </div>
</div>

<script>
const assigned = <?= json_encode($assigned) ?>;

function openEdit(id, name) {
  document.getElementById('editSaId').value = id;
  document.getElementById('editName').textContent = name;
  document.getElementById('saveMsg').style.display = 'none';
  // Reset all checkboxes
  document.querySelectorAll('.edit-batch-chk').forEach(c => c.checked = false);
  // Check assigned ones
  const ab = assigned[id] || [];
  ab.forEach(b => {
    const el = document.querySelector('.edit-batch-chk[value="'+b.id+'"]');
    if (el) el.checked = true;
  });
  document.getElementById('editOverlay').classList.add('on');
}

function closeEdit() { document.getElementById('editOverlay').classList.remove('on'); }
document.getElementById('editOverlay').addEventListener('click', function(e){ if(e.target===this) closeEdit(); });

function saveBatches() {
  const id = document.getElementById('editSaId').value;
  const checked = [...document.querySelectorAll('.edit-batch-chk:checked')].map(c => c.value);
  const fd = new FormData();
  fd.append('update_batches', '1');
  fd.append('sub_admin_id', id);
  checked.forEach(v => fd.append('batch_ids[]', v));
  fetch('sub_admins_list.php', { method:'POST', body:fd })
    .then(r => r.json())
    .then(data => {
      if (data.success) {
        document.getElementById('saveMsg').style.display = 'block';
        setTimeout(() => location.reload(), 1000);
      }
    });
}
</script>
</body>
</html>