<?php
require_once __DIR__ . '/../db.php';
session_start();
if (!isset($_SESSION['admin_username'])) { header("Location: login.php"); exit; }

$batch_id = (int)($_GET['id'] ?? 0);
if (!$batch_id) { header("Location: batch.php"); exit; }

$bq = $pdo->prepare("SELECT b.*, c.course_name, c.id as course_id FROM batches b LEFT JOIN courses c ON c.id=b.course_id WHERE b.id=?");
$bq->execute([$batch_id]);
$batch = $bq->fetch(PDO::FETCH_ASSOC);
if (!$batch) { header("Location: batch.php"); exit; }

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

$allowed_tabs = ['modules', 'sessions', 'attendance'];
$tab = in_array($_GET['tab'] ?? '', $allowed_tabs) ? $_GET['tab'] : 'modules';

// AJAX: save attendance
if (isset($_POST['ajax_attendance'])) {
    $reg_no   = $_POST['reg_no'] ?? '';
    $date     = $_POST['date'] ?? '';
    $status   = $_POST['status'] ?? '';
    $batch_no = $batch['batch_name'];
    if ($reg_no && $date && in_array($status, ['P','A','Lv','L','O','DEL'])) {
        if ($status === 'DEL') {
            $pdo->prepare("DELETE FROM attendance WHERE reg_no=? AND date=?")->execute([$reg_no, $date]);
            echo json_encode(['success'=>true,'status'=>'DEL']); exit;
        }
        $chk = $pdo->prepare("SELECT id FROM attendance WHERE reg_no=? AND date=?");
        $chk->execute([$reg_no, $date]);
        if ($chk->fetchColumn()) {
            $pdo->prepare("UPDATE attendance SET status=?, batch_no=? WHERE reg_no=? AND date=?")->execute([$status,$batch_no,$reg_no,$date]);
        } else {
            $pdo->prepare("INSERT INTO attendance (reg_no,date,status,batch_no) VALUES (?,?,?,?)")->execute([$reg_no,$date,$status,$batch_no]);
        }
        echo json_encode(['success'=>true,'status'=>$status]);
    } else {
        echo json_encode(['success'=>false]);
    }
    exit;
}

// Fetch all attendance for this batch students
$att_data = [];
foreach ($students as $s) {
    $aq = $pdo->prepare("SELECT date, status FROM attendance WHERE reg_no=?");
    $aq->execute([$s['reg_no']]);
    foreach ($aq->fetchAll(PDO::FETCH_ASSOC) as $r) {
        $att_data[$s['reg_no']][$r['date']] = $r['status'];
    }
}

function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title><?= h($batch['batch_name']) ?> | Admin</title>
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
.page-header{display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:20px;}
.page-header h1{font-size:21px;font-weight:800;color:#0f172a;}
.page-header p{font-size:13px;color:#64748b;margin-top:4px;}
.btn-back{background:#f1f5f9;color:#475569;border:1px solid #e2e8f0;padding:8px 16px;border-radius:8px;text-decoration:none;font-size:13px;font-weight:600;}
.batch-bar{background:#fff;border-radius:14px;padding:16px 22px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);margin-bottom:22px;display:flex;align-items:center;gap:28px;flex-wrap:wrap;}
.bb-item{display:flex;flex-direction:column;gap:2px;}
.bb-label{font-size:10px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;}
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
td{padding:12px 16px;font-size:13px;color:#334155;border-bottom:1px solid #f1f5f9;vertical-align:middle;}
tr:last-child td{border-bottom:none;}
.badge{display:inline-block;padding:3px 10px;border-radius:20px;font-size:11.5px;font-weight:700;}
.scheduled{background:#eff6ff;color:#1d4ed8;}.completed{background:#dcfce7;color:#15803d;}.cancelled{background:#fee2e2;color:#dc2626;}
.empty{text-align:center;padding:40px;color:#94a3b8;}
.att-layout{display:grid;grid-template-columns:260px 1fr;gap:22px;align-items:start;}
.stu-list{background:#fff;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);overflow:hidden;position:sticky;top:20px;max-height:75vh;overflow-y:auto;}
.stu-list::-webkit-scrollbar{width:3px;}
.stu-list::-webkit-scrollbar-thumb{background:#e2e8f0;border-radius:3px;}
.stu-list-head{padding:14px 18px;font-size:13px;font-weight:800;color:#0f172a;position:sticky;top:0;z-index:2;background:#fff;border-bottom:2px solid #e2e8f0;}
.stu-item{display:flex;align-items:center;gap:10px;padding:11px 16px;border-bottom:1px solid #f1f5f9;cursor:pointer;transition:all 0.15s;}
.stu-item:last-child{border-bottom:none;}
.stu-item:hover{background:#f0f7ff;}
.stu-item.active{background:#eff6ff;border-left:3px solid #2563eb;}
.stu-avatar{width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#2563eb,#7c3aed);color:#fff;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;flex-shrink:0;}
.stu-info-name{font-size:13px;font-weight:700;color:#0f172a;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px;}
.stu-info-reg{font-size:10.5px;color:#94a3b8;}
.stu-att-pct{margin-left:auto;font-size:13px;font-weight:800;flex-shrink:0;}
.cal-panel{background:#fff;border-radius:14px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);padding:24px;}
.cal-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px;}
.cal-title{font-size:18px;font-weight:800;color:#0f172a;}
.cal-stu-badge{display:inline-flex;align-items:center;gap:6px;background:#eff6ff;color:#2563eb;padding:4px 12px;border-radius:20px;font-size:12.5px;font-weight:700;margin-top:6px;}
.cal-nav{display:flex;align-items:center;gap:8px;}
.cal-nav-btn{width:36px;height:36px;border-radius:10px;background:#f1f5f9;border:1.5px solid #e2e8f0;color:#475569;font-size:18px;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.15s;}
.cal-nav-btn:hover{background:#2563eb;color:#fff;border-color:#2563eb;}
.cal-month-lbl{font-size:13.5px;font-weight:700;color:#0f172a;min-width:100px;text-align:center;}
.att-summary{display:flex;gap:12px;margin-bottom:22px;}
.att-sum-box{flex:1;border-radius:16px;padding:16px 10px;text-align:center;}
.att-sum-box.p{background:linear-gradient(135deg,#f0fdf4,#dcfce7);border:1.5px solid #86efac;}
.att-sum-box.a{background:linear-gradient(135deg,#fef2f2,#fee2e2);border:1.5px solid #fca5a5;}
.att-sum-box.l{background:linear-gradient(135deg,#fffbeb,#fef3c7);border:1.5px solid #fde68a;}
.att-sum-box .s-num{font-size:28px;font-weight:800;line-height:1;}
.att-sum-box.p .s-num{color:#16a34a;}
.att-sum-box.a .s-num{color:#dc2626;}
.att-sum-box.l .s-num{color:#d97706;}
.att-sum-box .s-lbl{font-size:11px;font-weight:700;color:#64748b;margin-top:5px;text-transform:uppercase;letter-spacing:0.5px;}
.cal-days-hdr{display:grid;grid-template-columns:repeat(7,1fr);gap:6px;margin-bottom:10px;}
.cal-day-hdr{text-align:center;font-size:11px;font-weight:700;color:#94a3b8;text-transform:uppercase;padding:6px 0;}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:6px;}
.att-layout{display:flex;gap:14px;min-height:500px;}
.att-stu-panel{width:260px;flex-shrink:0;background:#fff;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 2px 8px rgba(0,0,0,0.05);display:flex;flex-direction:column;overflow:hidden;}
.att-stu-head{padding:13px 16px;border-bottom:1px solid #f1f5f9;font-size:13px;font-weight:700;color:#0f172a;display:flex;align-items:center;justify-content:space-between;}
.att-stu-head .count{background:#eff6ff;color:#2563eb;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;}
.att-stu-scroll{flex:1;overflow-y:auto;}
.att-stu-scroll::-webkit-scrollbar{width:3px;}
.att-stu-scroll::-webkit-scrollbar-thumb{background:#e2e8f0;border-radius:3px;}
.stu-item{display:flex;align-items:center;gap:10px;padding:11px 14px;cursor:pointer;border-bottom:1px solid #f8fafc;transition:background 0.15s;border-left:3px solid transparent;}
.stu-item:hover{background:#f8fafc;}
.stu-item.active{background:#eff6ff;border-left-color:#2563eb;}
.stu-avatar{width:32px;height:32px;border-radius:50%;background:#e0e7ff;color:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;}
.stu-info-name{font-size:13px;font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:120px;}
.stu-info-reg{font-size:11px;color:#94a3b8;margin-top:1px;}
.stu-att-pct{font-size:12px;font-weight:700;margin-left:auto;flex-shrink:0;}
.att-cal-panel{flex:1;background:#fff;border-radius:12px;border:1px solid #e2e8f0;box-shadow:0 2px 8px rgba(0,0,0,0.05);display:flex;flex-direction:column;overflow:hidden;}
.att-cal-head{padding:13px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;}
.att-cal-title{font-size:14px;font-weight:700;color:#0f172a;}
.att-cal-sub{font-size:12px;color:#94a3b8;margin-top:1px;}
.att-summary-row{display:flex;gap:7px;flex-wrap:wrap;}
.att-sum-badge{font-size:12px;font-weight:700;padding:4px 10px;border-radius:20px;}
.att-sum-badge.P{background:#dcfce7;color:#16a34a;}
.att-sum-badge.A{background:#fee2e2;color:#dc2626;}
.att-sum-badge.Lv{background:#fef9c3;color:#d97706;}
.att-sum-badge.L{background:#fff7ed;color:#c2410c;}
.att-sum-badge.O{background:#fdf4ff;color:#9333ea;}
.att-cal-body{flex:1;padding:16px 20px;overflow-y:auto;}
.att-cal-nav{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
.att-cal-month{font-size:15px;font-weight:700;color:#0f172a;}
.att-cal-nav-btns{display:flex;gap:6px;}
.att-cal-nav-btns button{width:28px;height:28px;border-radius:7px;border:none;background:#f1f5f9;cursor:pointer;font-size:15px;color:#475569;}
.att-cal-nav-btns button:hover{background:#e2e8f0;}
.att-cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;}
.cal-day-hdr{text-align:center;font-size:11px;font-weight:700;color:#94a3b8;padding:4px 0;}
.cal-cell{position:relative;text-align:center;padding:8px 4px 20px;border-radius:9px;font-size:13px;font-weight:500;color:#334155;cursor:pointer;transition:background 0.15s;min-height:44px;user-select:none;}
.cal-cell:hover{background:#f8fafc;}
.cal-cell.empty{pointer-events:none;}
.cal-cell.today{color:#2563eb;font-weight:800;background:#eff6ff;}
.cal-cell.future{color:#cbd5e1;pointer-events:none;}
.cal-cell .status-ring{position:absolute;inset:0;border-radius:9px;opacity:0.15;pointer-events:none;}
.cal-cell.att-P .status-ring{background:#22c55e;}
.cal-cell.att-A .status-ring{background:#ef4444;}
.cal-cell.att-Lv .status-ring{background:#f59e0b;}
.cal-cell.att-L .status-ring{background:#f97316;}
.cal-cell.att-O .status-ring{background:#e879f9;}
.cal-cell .status-lbl{position:absolute;bottom:4px;left:50%;transform:translateX(-50%);font-size:9px;font-weight:800;pointer-events:none;white-space:nowrap;}
.cal-cell.att-P .status-lbl{color:#16a34a;}
.cal-cell.att-A .status-lbl{color:#dc2626;}
.cal-cell.att-Lv .status-lbl{color:#d97706;}
.cal-cell.att-L .status-lbl{color:#c2410c;}
.cal-cell.att-O .status-lbl{color:#9333ea;}
.att-cal-legend{display:flex;gap:12px;margin-top:12px;padding-top:12px;border-top:1px solid #f1f5f9;flex-wrap:wrap;}
.leg{display:flex;align-items:center;gap:5px;font-size:12px;color:#64748b;}
.leg-dot{width:7px;height:7px;border-radius:50%;}
.leg-dot.P{background:#22c55e;}.leg-dot.A{background:#ef4444;}.leg-dot.Lv{background:#f59e0b;}
.leg-dot.L{background:#f97316;}.leg-dot.O{background:#e879f9;}
.vb-overlay{display:none;position:fixed;inset:0;z-index:200;}
.vb-overlay.show{display:block;}
.vb-popup{display:none;position:fixed;z-index:201;background:#fff;border-radius:14px;box-shadow:0 8px 32px rgba(0,0,0,0.16);border:1px solid #e2e8f0;padding:16px 18px;min-width:240px;}
.vb-popup.show{display:block;}
.vb-pop-date{font-size:12px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;text-align:center;}
.vb-pop-btns{display:flex;gap:7px;flex-wrap:wrap;}
.vb-pop-btn{flex:1;min-width:42px;padding:10px 5px;border-radius:9px;border:2px solid transparent;font-size:13px;font-weight:800;cursor:pointer;font-family:'DM Sans',sans-serif;text-align:center;transition:all 0.15s;}
.vb-pop-btn.P{background:#f0fdf4;color:#16a34a;border-color:#bbf7d0;}.vb-pop-btn.P:hover{background:#16a34a;color:#fff;}
.vb-pop-btn.A{background:#fff1f2;color:#ef4444;border-color:#fecdd3;}.vb-pop-btn.A:hover{background:#ef4444;color:#fff;}
.vb-pop-btn.Lv{background:#fefce8;color:#ca8a04;border-color:#fde68a;}.vb-pop-btn.Lv:hover{background:#ca8a04;color:#fff;}
.vb-pop-btn.L{background:#fff7ed;color:#c2410c;border-color:#fed7aa;}.vb-pop-btn.L:hover{background:#ea580c;color:#fff;}
.vb-pop-btn.O{background:#fdf4ff;color:#9333ea;border-color:#e9d5ff;}.vb-pop-btn.O:hover{background:#a855f7;color:#fff;}
.vb-pop-remove{margin-top:8px;text-align:center;font-size:12px;color:#94a3b8;cursor:pointer;padding:4px;}
.vb-pop-remove:hover{color:#ef4444;}
.vb-toast{position:fixed;bottom:24px;right:24px;background:#0f172a;color:#fff;padding:11px 20px;border-radius:10px;font-size:13px;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,0.2);display:none;z-index:999;align-items:center;gap:8px;}
.vb-toast.show{display:flex;}
@keyframes popIn{from{transform:scale(0.85);opacity:0;}to{transform:scale(1);opacity:1;}}
.popup-btn{width:82px;height:82px;border-radius:50%;border:none;cursor:pointer;font-size:24px;font-weight:800;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5px;transition:all 0.2s;font-family:'DM Sans',sans-serif;}
.popup-btn span{font-size:10.5px;font-weight:700;}
.popup-btn.p{background:#f0fdf4;color:#16a34a;border:2.5px solid #bbf7d0;}
.popup-btn.p:hover,.popup-btn.p.sel{background:#16a34a;color:#fff;border-color:#16a34a;transform:scale(1.1);box-shadow:0 6px 20px rgba(22,163,74,0.4);}
.popup-btn.a{background:#fef2f2;color:#dc2626;border:2.5px solid #fecaca;}
.popup-btn.a:hover,.popup-btn.a.sel{background:#dc2626;color:#fff;border-color:#dc2626;transform:scale(1.1);box-shadow:0 6px 20px rgba(220,38,38,0.4);}
.popup-btn.l{background:#fffbeb;color:#d97706;border:2.5px solid #fde68a;}
.popup-btn.l:hover,.popup-btn.l.sel{background:#d97706;color:#fff;border-color:#d97706;transform:scale(1.1);box-shadow:0 6px 20px rgba(217,119,6,0.4);}
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
  <a href="view_batch.php" class="active"><span class="sb-icon">👁️</span>View Batch</a>
  <a href="courses.php"><span class="sb-icon">📚</span>Courses</a>
  <div class="sb-sec">Sub Admins</div>
  <a href="add_sub_admin.php"><span class="sb-icon">👤</span>Add Sub Admin</a>
  <a href="sub_admins_list.php"><span class="sb-icon">👥</span>Sub Admins List</a>
  <div class="sb-bottom"><a href="logout.php"><span class="sb-icon">🚪</span>Logout</a></div>
</div>

<div class="main">
  <div class="page-header">
    <div>
      <h1>👁️ <?= h($batch['batch_name']) ?></h1>
      <p><?= h($batch['course_name']??'') ?> &nbsp;·&nbsp; <?= count($students) ?> students</p>
    </div>
    <a href="batch.php" class="btn-back">← Batches</a>
  </div>

  <div class="batch-bar">
    <div class="bb-item"><span class="bb-label">Batch</span><span class="bb-val"><?= h($batch['batch_name']) ?></span></div>
    <div class="bb-div"></div>
    <div class="bb-item"><span class="bb-label">Course</span><span class="bb-val"><?= h($batch['course_name']??'—') ?></span></div>
    <div class="bb-div"></div>
    <div class="bb-item"><span class="bb-label">Timing</span><span class="bb-val"><?= !empty($batch['timing_start'])?date('g:i A',strtotime($batch['timing_start'])).' – '.date('g:i A',strtotime($batch['timing_end'])):'—' ?></span></div>
    <div class="bb-div"></div>
    <div class="bb-item"><span class="bb-label">Days</span><span class="bb-val"><?= ucfirst(h($batch['day_type']??'—')) ?></span></div>
    <div class="bb-div"></div>
    <div class="bb-item"><span class="bb-label">Students</span><span class="bb-val"><?= count($students) ?></span></div>
  </div>

  <div class="tabs">
    <a href="?id=<?= $batch_id ?>&tab=modules"    class="tab-btn <?= $tab==='modules'?'active':'' ?>">📚 Modules</a>
    <a href="?id=<?= $batch_id ?>&tab=sessions"   class="tab-btn <?= $tab==='sessions'?'active':'' ?>">📅 Sessions</a>
    <a href="?id=<?= $batch_id ?>&tab=attendance" class="tab-btn <?= $tab==='attendance'?'active':'' ?>">✅ Attendance</a>
  </div>

  <!-- MODULES TAB -->
  <?php if($tab==='modules'): ?>
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
      <h2 style="font-size:15px;font-weight:700;color:#0f172a;">📚 Modules (<?= count($modules) ?> modules)</h2>
      <?php if($course_id): ?>
        <a href="course_modules.php?course_id=<?= $course_id ?>"
           style="background:#2563eb;color:#fff;padding:9px 20px;border-radius:9px;text-decoration:none;font-size:13px;font-weight:700;display:inline-flex;align-items:center;gap:7px;">
          ⚙️ Manage Modules
        </a>
      <?php endif; ?>
    </div>
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
      <div class="empty">📭 No modules added yet. <a href="course_modules.php?course_id=<?= $course_id ?>" style="color:#2563eb;font-weight:700;">Add modules →</a></div>
    <?php endif; ?>

  <!-- SESSIONS TAB -->
  <?php elseif($tab==='sessions'): ?>
    <div class="sess-card">
      <div style="padding:14px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;">
        <span style="font-size:14px;font-weight:700;color:#0f172a;">📅 Sessions (<?= count($sessions) ?>)</span>
        <!-- ✅ FIX: batch_id bhi pass ho raha hai ab -->
        <a href="course_sessions.php?course_id=<?= $course_id ?>&batch_id=<?= $batch_id ?>"
           style="background:#2563eb;color:#fff;padding:7px 16px;border-radius:8px;text-decoration:none;font-size:12.5px;font-weight:700;">
          ⚙️ Manage Sessions
        </a>
      </div>
      <?php if($sessions): ?>
      <table>
        <thead><tr><th>Date</th><th>Type</th><th>Topic</th><th>Time</th><th>Status</th></tr></thead>
        <tbody>
        <?php foreach($sessions as $s): ?>
        <tr>
          <td><strong><?= date('d M Y',strtotime($s['date'])) ?></strong><br><span style="font-size:11px;color:#94a3b8;"><?= date('D',strtotime($s['date'])) ?></span></td>
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

  <!-- ATTENDANCE TAB -->
  <?php elseif($tab==='attendance'): ?>
    <?php if($students): ?>
    <div class="att-layout">

      <!-- STUDENT LIST -->
      <div class="att-stu-panel">
        <div class="att-stu-head">Students <span class="count"><?= count($students) ?></span></div>
        <div class="att-stu-scroll">
        <?php foreach($students as $i=>$s):
          $att  = $att_data[$s['reg_no']] ?? [];
          $attended = count(array_filter($att, fn($v)=>in_array($v,['P','L','O'])));
          $total_s  = count(array_filter($att, fn($v)=>$v!=='Lv'));
          $pct  = $total_s>0?round(($attended/$total_s)*100):0;
          $pc   = $pct>=75?'#16a34a':($pct>=50?'#d97706':'#dc2626');
          $ini  = strtoupper(substr($s['name'],0,1));
        ?>
        <div class="stu-item <?= $i===0?'active':'' ?>"
             id="stuitem_<?= h($s['reg_no']) ?>"
             onclick="selectStudent('<?= h($s['reg_no']) ?>','<?= addslashes(h($s['name'])) ?>',this)">
          <div class="stu-avatar"><?= $ini ?></div>
          <div style="min-width:0;flex:1;">
            <div class="stu-info-name"><?= h($s['name']) ?></div>
            <div class="stu-info-reg"><?= h($s['reg_no']) ?></div>
          </div>
          <div class="stu-att-pct" id="pct_<?= h($s['reg_no']) ?>" style="color:<?= $pc ?>"><?= $pct ?>%</div>
        </div>
        <?php endforeach; ?>
        </div>
      </div>

      <!-- CALENDAR -->
      <div class="att-cal-panel">
        <div class="att-cal-head">
          <div>
            <div class="att-cal-title" id="calTitle">—</div>
            <div class="att-cal-sub" id="calStuReg">—</div>
          </div>
          <div class="att-summary-row" id="attSummary"></div>
        </div>
        <div class="att-cal-body">
          <div class="att-cal-nav">
            <span class="att-cal-month" id="calMonthLbl"></span>
            <div class="att-cal-nav-btns">
              <button onclick="prevMonth()">‹</button>
              <button onclick="nextMonth()">›</button>
            </div>
          </div>
          <div class="att-cal-grid" id="calGrid">
            <div class="cal-day-hdr">Su</div><div class="cal-day-hdr">Mo</div>
            <div class="cal-day-hdr">Tu</div><div class="cal-day-hdr">We</div>
            <div class="cal-day-hdr">Th</div><div class="cal-day-hdr">Fr</div>
            <div class="cal-day-hdr">Sa</div>
          </div>
          <div class="att-cal-legend">
            <div class="leg"><div class="leg-dot P"></div> Present</div>
            <div class="leg"><div class="leg-dot A"></div> Absent</div>
            <div class="leg"><div class="leg-dot Lv"></div> Leave</div>
            <div class="leg"><div class="leg-dot L"></div> Late</div>
            <div class="leg"><div class="leg-dot O"></div> Online</div>
          </div>
        </div>
      </div>
    </div>

    <?php else: ?>
      <div class="empty">📭 No students in this batch.</div>
    <?php endif; ?>
  <?php endif; ?>
</div>

<script>
const allAtt = <?= json_encode($att_data) ?>;
const MONTHS = ['January','February','March','April','May','June','July','August','September','October','November','December'];
const STATUS_LABEL = {P:'P',A:'A',Lv:'Lv',L:'L',O:'O'};
const STATUS_ICONS  = {P:'✅',A:'❌',Lv:'🌴',L:'⏰',O:'💻'};
const todayStr = '<?= date('Y-m-d') ?>';
let curRegNo = '<?= addslashes($students[0]['reg_no'] ?? '') ?>';
let curName  = '<?= addslashes(h($students[0]['name'] ?? '')) ?>';
let calYear  = new Date().getFullYear();
let calMonth = new Date().getMonth();
let popDate  = null;

function selectStudent(regNo, name, el) {
  document.querySelectorAll('.stu-item').forEach(x=>x.classList.remove('active'));
  el.classList.add('active');
  curRegNo = regNo; curName = name;
  document.getElementById('calTitle').textContent = name;
  document.getElementById('calStuReg').textContent = regNo;
  renderCalendar(); renderSummary();
}

function prevMonth(){ calMonth--; if(calMonth<0){calMonth=11;calYear--;} renderCalendar(); }
function nextMonth(){ calMonth++; if(calMonth>11){calMonth=0;calYear++;} renderCalendar(); }

function renderCalendar() {
  const grid = document.getElementById('calGrid');
  document.getElementById('calMonthLbl').textContent = MONTHS[calMonth] + ' ' + calYear;
  grid.querySelectorAll('.cal-cell').forEach(c=>c.remove());
  const att = allAtt[curRegNo] || {};
  const firstDay = new Date(calYear, calMonth, 1).getDay();
  const daysInMonth = new Date(calYear, calMonth+1, 0).getDate();
  for(let i=0;i<firstDay;i++){ const e=document.createElement('div'); e.className='cal-cell empty'; grid.appendChild(e); }
  for(let d=1;d<=daysInMonth;d++){
    const ds = calYear+'-'+String(calMonth+1).padStart(2,'0')+'-'+String(d).padStart(2,'0');
    const st = att[ds] || null;
    const cell = document.createElement('div');
    cell.className = 'cal-cell';
    const num = document.createElement('span'); num.textContent = d; cell.appendChild(num);
    if(ds === todayStr) cell.classList.add('today');
    if(ds > todayStr)  cell.classList.add('future');
    if(st) {
      cell.classList.add('att-'+st);
      const ring = document.createElement('div'); ring.className='status-ring'; cell.appendChild(ring);
      const lbl  = document.createElement('div'); lbl.className='status-lbl'; lbl.textContent=STATUS_LABEL[st]||st; cell.appendChild(lbl);
    }
    cell.addEventListener('click', function(e){ if(ds>todayStr) return; openPopup(ds,e); });
    grid.appendChild(cell);
  }
}

function renderSummary() {
  const att = allAtt[curRegNo] || {};
  const counts = {P:0,A:0,Lv:0,L:0,O:0};
  Object.values(att).forEach(v=>{ if(counts[v]!==undefined) counts[v]++; });
  document.getElementById('attSummary').innerHTML =
    ['P','A','Lv','L','O'].filter(k=>counts[k]>0)
      .map(k=>`<span class="att-sum-badge ${k}">${STATUS_ICONS[k]} ${counts[k]}</span>`).join('');
  // update pct in student list
  const total   = counts.P + counts.A + counts.L + counts.O;
  const attended = counts.P + counts.L + counts.O;
  const pct = total>0 ? Math.round((attended/total)*100) : 0;
  const color = pct>=75?'#16a34a':(pct>=50?'#d97706':'#dc2626');
  const el = document.getElementById('pct_'+curRegNo);
  if(el){ el.textContent=pct+'%'; el.style.color=color; }
}

function openPopup(dateStr, e) {
  popDate = dateStr;
  const parts = dateStr.split('-');
  document.getElementById('vbPopDateLbl').textContent = parts[2]+' '+MONTHS[parseInt(parts[1])-1]+' '+parts[0];
  const popup = document.getElementById('vbPopup');
  const rect  = e.target.closest('.cal-cell').getBoundingClientRect();
  let top  = rect.bottom + window.scrollY + 8;
  let left = rect.left   + window.scrollX - 60;
  if(left+260>window.innerWidth) left=window.innerWidth-270;
  if(left<8) left=8;
  popup.style.top=top+'px'; popup.style.left=left+'px';
  popup.classList.add('show'); document.getElementById('vbOverlay').classList.add('show');
}
function closePopup(){ document.getElementById('vbPopup').classList.remove('show'); document.getElementById('vbOverlay').classList.remove('show'); }

function saveStatus(status) {
  closePopup();
  if(!curRegNo || !popDate) return;
  if(status==='DEL'){ if(allAtt[curRegNo]) delete allAtt[curRegNo][popDate]; }
  else { if(!allAtt[curRegNo]) allAtt[curRegNo]={}; allAtt[curRegNo][popDate]=status; }
  renderCalendar(); renderSummary();
  const fd=new FormData();
  fd.append('ajax_attendance','1'); fd.append('reg_no',curRegNo);
  fd.append('date',popDate); fd.append('status',status);
  fd.append('batch_no','<?= addslashes($batch['batch_name']) ?>');
  fetch(window.location.href,{method:'POST',body:fd})
    .then(r=>r.json())
    .then(d=>showToast(d.success?'✅ Saved!':'❌ Error'));
}

function showToast(msg){ const t=document.getElementById('vbToast'); t.textContent=msg; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),2000); }

// Init: select first student
window.addEventListener('DOMContentLoaded',()=>{
  if(curRegNo){
    document.getElementById('calTitle').textContent = curName;
    document.getElementById('calStuReg').textContent = curRegNo;
    renderCalendar(); renderSummary();
  }
});
</script>
<div class="vb-overlay" id="vbOverlay" onclick="closePopup()"></div>
<div class="vb-popup" id="vbPopup">
    <div class="vb-pop-date" id="vbPopDateLbl"></div>
    <div class="vb-pop-btns">
        <div class="vb-pop-btn P"  onclick="saveStatus('P')">P</div>
        <div class="vb-pop-btn A"  onclick="saveStatus('A')">A</div>
        <div class="vb-pop-btn Lv" onclick="saveStatus('Lv')">Lv</div>
        <div class="vb-pop-btn L"  onclick="saveStatus('L')">L</div>
        <div class="vb-pop-btn O"  onclick="saveStatus('O')">O</div>
    </div>
    <div class="vb-pop-remove" onclick="saveStatus('DEL')">✕ Remove</div>
</div>
<div class="vb-toast" id="vbToast">✅ Saved!</div>
</body>
</html>