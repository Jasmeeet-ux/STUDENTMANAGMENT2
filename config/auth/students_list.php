<?php
require_once __DIR__ . '/../db.php';
session_start();
if (!isset($_SESSION['admin_username'])) { header("Location: login.php"); exit; }

if (isset($_POST['delete_student'])) {
    $reg = $_POST['reg_no'];
    $pdo->prepare("DELETE FROM user_details WHERE reg_no=?")->execute([$reg]);
    $pdo->prepare("DELETE FROM users WHERE reg_no=?")->execute([$reg]);
    header("Location: students_list.php?deleted=1"); exit;
}

$students = $pdo->query("
    SELECT * FROM user_details
    ORDER BY STR_TO_DATE(startingdate,'%Y-%m-%d') DESC
")->fetchAll(PDO::FETCH_ASSOC);

function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Students List | Admin</title>
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
.topbar{display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;}
.topbar h1{font-size:22px;font-weight:800;color:#0f172a;}
.alert-ok{background:#dcfce7;border:1px solid #bbf7d0;color:#166534;padding:12px 18px;border-radius:10px;margin-bottom:18px;font-size:13.5px;font-weight:500;}
.search-row{display:flex;align-items:center;gap:14px;margin-bottom:16px;}
.search-wrap{position:relative;flex:1;max-width:400px;}
.search-wrap input{width:100%;padding:10px 14px 10px 40px;border:1.5px solid #e2e8f0;border-radius:10px;font-size:13.5px;font-family:'DM Sans',sans-serif;background:#fff;outline:none;transition:border-color 0.2s;}
.search-wrap input:focus{border-color:#2563eb;box-shadow:0 0 0 3px rgba(37,99,235,0.1);}
.search-wrap .s-ico{position:absolute;left:13px;top:50%;transform:translateY(-50%);font-size:15px;color:#94a3b8;}
.count-lbl{font-size:13px;color:#64748b;}
.count-lbl span{font-weight:700;color:#0f172a;}
.table-card{background:#fff;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);overflow:hidden;}
table{width:100%;border-collapse:collapse;}
thead{background:#f8fafc;}
th{padding:11px 16px;text-align:left;font-size:10.5px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.6px;border-bottom:2px solid #e2e8f0;}
td{padding:12px 16px;border-bottom:1px solid #f1f5f9;font-size:13.5px;color:#334155;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
tbody tr:hover{background:#fafcff;}
.sno{font-weight:700;color:#94a3b8;font-size:13px;}
.stu-name{font-weight:700;color:#0f172a;}
.stu-month{font-size:11px;color:#94a3b8;margin-top:2px;}
.pill{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;}
.pill-blue{background:#eff6ff;color:#1d4ed8;}
.btn-view{background:#eff6ff;color:#2563eb;border:none;padding:6px 14px;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;text-decoration:none;display:inline-block;transition:background 0.15s;}
.btn-view:hover{background:#dbeafe;}
.btn-edit{background:#f0fdf4;color:#16a34a;border:none;padding:6px 14px;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;text-decoration:none;display:inline-block;transition:background 0.15s;margin-left:6px;}
.btn-edit:hover{background:#dcfce7;}
.btn-del{background:#fee2e2;color:#dc2626;border:none;padding:6px 14px;border-radius:7px;font-size:12px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;margin-left:6px;transition:background 0.15s;}
.btn-del:hover{background:#fecaca;}
.no-res{text-align:center;padding:40px;color:#94a3b8;font-size:14px;}
.overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.45);backdrop-filter:blur(3px);z-index:999;align-items:center;justify-content:center;}
.overlay.on{display:flex;}
.modal{background:#fff;border-radius:16px;padding:32px 28px;width:100%;max-width:360px;box-shadow:0 20px 60px rgba(0,0,0,0.2);text-align:center;}
.modal .m-icon{font-size:44px;margin-bottom:12px;}
.modal h3{font-size:17px;font-weight:700;color:#0f172a;margin-bottom:6px;}
.modal p{font-size:13px;color:#64748b;margin-bottom:22px;line-height:1.5;}
.modal-btns{display:flex;gap:10px;}
.btn-conf{flex:1;padding:11px;background:#ef4444;color:#fff;border:none;border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;font-family:'DM Sans',sans-serif;}
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
  <div class="topbar"><h1>📋 Students List</h1></div>
  <?php if(isset($_GET['deleted'])): ?><div class="alert-ok">✅ Student deleted successfully!</div><?php endif; ?>
  <?php if(isset($_GET['updated'])): ?><div class="alert-ok">✅ Student details updated successfully!</div><?php endif; ?>

  <div class="search-row">
    <div class="search-wrap">
      <span class="s-ico">🔍</span>
      <input type="text" id="searchInput" placeholder="Search by name or S.No..." onkeyup="doSearch()">
    </div>
    <div class="count-lbl">Total: <span id="visCount"><?= count($students) ?></span> students</div>
  </div>

  <div class="table-card">
    <table>
      <thead><tr><th>S.No.</th><th>Name</th><th>Phone</th><th>Batch</th><th>Actions</th></tr></thead>
      <tbody id="stuTbody">
      <?php if($students): $n=1; foreach($students as $row):
        $ml=''; if(!empty($row['startingdate'])){$ts=strtotime($row['startingdate']);if($ts)$ml=date('M Y',$ts);}
      ?>
      <tr class="stu-row" data-name="<?= h(strtolower($row['name'])) ?>" data-sno="<?= $n ?>">
        <td class="sno"><?= $n++ ?></td>
        <td>
          <div class="stu-name"><?= h($row['name']) ?></div>
          <?php if($ml): ?><div class="stu-month">📅 <?= $ml ?></div><?php endif; ?>
        </td>
        <td><?= h($row['phoneno']??'—') ?></td>
        <td><?php if(!empty($row['batch_no'])): ?><span class="pill pill-blue"><?= h($row['batch_no']) ?></span><?php else: ?>—<?php endif; ?></td>
        <td>
          <a href="student_performance.php?reg_no=<?= urlencode($row['reg_no']) ?>" class="btn-view">👁️ View</a>
          <a href="edit_student.php?reg_no=<?= urlencode($row['reg_no']) ?>" class="btn-edit">✏️ Edit</a>
          <button class="btn-del" onclick="openDel('<?= h($row['reg_no']) ?>','<?= h($row['name']) ?>')">🗑️ Delete</button>
        </td>
      </tr>
      <?php endforeach; else: ?>
      <tr><td colspan="5" class="no-res">No students found.</td></tr>
      <?php endif; ?>
      </tbody>
    </table>
    <div id="noRes" class="no-res" style="display:none;">No students match your search.</div>
  </div>
</div>

<div class="overlay" id="delOverlay">
  <div class="modal">
    <div class="m-icon">⚠️</div>
    <h3>Delete Student?</h3>
    <p id="delMsg"></p>
    <form method="POST">
      <input type="hidden" name="reg_no" id="delReg">
      <div class="modal-btns">
        <button type="submit" name="delete_student" class="btn-conf">🗑️ Yes, Delete</button>
        <button type="button" class="btn-canc" onclick="closeDel()">Cancel</button>
      </div>
    </form>
  </div>
</div>

<script>
function doSearch(){
  const q=document.getElementById('searchInput').value.toLowerCase().trim();
  const rows=document.querySelectorAll('.stu-row');
  let vis=0;
  rows.forEach(r=>{const m=!q||r.dataset.name.includes(q)||r.dataset.sno.includes(q);r.style.display=m?'':'none';if(m)vis++;});
  document.getElementById('visCount').textContent=vis;
  document.getElementById('noRes').style.display=vis===0?'block':'none';
}
function openDel(reg,name){
  document.getElementById('delReg').value=reg;
  document.getElementById('delMsg').textContent='Delete "'+name+'"? This cannot be undone.';
  document.getElementById('delOverlay').classList.add('on');
}
function closeDel(){document.getElementById('delOverlay').classList.remove('on');}
document.getElementById('delOverlay').addEventListener('click',function(e){if(e.target===this)closeDel();});
</script>
</body>
</html>