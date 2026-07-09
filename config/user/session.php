<?php
require_once __DIR__ . '/../db.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$stmt = $pdo->prepare("SELECT id, reg_no, name FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { header("Location: student_login.php"); exit; }

$stmt2 = $pdo->prepare("SELECT * FROM user_details WHERE reg_no = ?");
$stmt2->execute([$user['reg_no']]);
$student = $stmt2->fetch(PDO::FETCH_ASSOC);

// Get student's batch info
$batch = null;
if (!empty($student['batch_no'])) {
    $stmtB = $pdo->prepare("SELECT * FROM batches WHERE batch_name = ? OR id = ? LIMIT 1");
    $stmtB->execute([$student['batch_no'], $student['batch_no']]);
    $batch = $stmtB->fetch(PDO::FETCH_ASSOC);
}

// Get course
$course = null;
if (!empty($student['coursename'])) {
    $stmtC = $pdo->prepare("SELECT * FROM courses WHERE course_name = ? LIMIT 1");
    $stmtC->execute([$student['coursename']]);
    $course = $stmtC->fetch(PDO::FETCH_ASSOC);
}

// Batch timing string
$batchTiming = '—';
if ($batch) {
    $batchTiming = date('g:i A', strtotime($batch['timing_start'])) . ' – ' . date('g:i A', strtotime($batch['timing_end']));
}

// Month/Year
$year  = isset($_GET['year'])  ? (int)$_GET['year']  : (int)date('Y');
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
if ($month < 1)  { $month = 1; }
if ($month > 12) { $month = 12; }
$firstDay = sprintf('%04d-%02d-01', $year, $month);
$lastDay  = date('Y-m-t', strtotime($firstDay));

$prevMonth = $month - 1; $prevYear = $year;
if ($prevMonth < 1)  { $prevMonth = 12; $prevYear--; }
$nextMonth = $month + 1; $nextYear = $year;
if ($nextMonth > 12) { $nextMonth = 1;  $nextYear++; }

$sessions = [];
if (!empty($student['batch_no'])) {
    $stmt3 = $pdo->prepare("
        SELECT id, date, topic, status, session_type
        FROM class_sessions
        WHERE batch_no = ? AND date BETWEEN ? AND ?
        ORDER BY date ASC
    ");
    $stmt3->execute([$student['batch_no'], $firstDay, $lastDay]);
    $sessions = $stmt3->fetchAll(PDO::FETCH_ASSOC);
}

// Attendance map
$attMap = [];
$stmt4 = $pdo->prepare("SELECT date, status FROM attendance WHERE reg_no = ? AND date BETWEEN ? AND ?");
$stmt4->execute([$user['reg_no'], $firstDay, $lastDay]);
foreach ($stmt4->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $attMap[$row['date']] = $row['status'];
}

// Build JS sessions
$jsSessions = [];
foreach ($sessions as $s) {
    $type = $s['session_type'] ?? 'lecture';
    $jsSessions[] = [
        'id'     => (int)$s['id'],
        'date'   => $s['date'],
        'day'    => date('l', strtotime($s['date'])),
        'time'   => $batchTiming,
        'topic'  => $s['topic'],
        'status' => $s['status'],
        'type'   => $type,
        'att'    => $attMap[$s['date']] ?? null,
    ];
}

$allSessions = [];
if (!empty($student['batch_no'])) {
    $stmtAll = $pdo->prepare("SELECT date, status FROM class_sessions WHERE batch_no = ?");
    $stmtAll->execute([$student['batch_no']]);
    $allSessions = $stmtAll->fetchAll(PDO::FETCH_ASSOC);
}

$totalClasses = count($allSessions);
$allAttMap = [];
$stmtAtt = $pdo->prepare("SELECT date, status FROM attendance WHERE reg_no = ?");
$stmtAtt->execute([$user['reg_no']]);
foreach ($stmtAtt->fetchAll(PDO::FETCH_ASSOC) as $row) $allAttMap[$row['date']] = $row['status'];

$completed = 0;
foreach ($allSessions as $s) {
    if (isset($allAttMap[$s['date']]) && in_array($allAttMap[$s['date']], ['P','L'])) $completed++;
}
$remaining   = max(0, $totalClasses - $completed);
$progressPct = $totalClasses > 0 ? round(($completed / $totalClasses) * 100) : 0;

$pCount = 0; $aCount = 0; $lCount = 0;
foreach ($allAttMap as $st) {
    if ($st === 'P') $pCount++;
    if ($st === 'A') $aCount++;
    if ($st === 'L') $lCount++;
}

function h($v) { return htmlspecialchars($v ?? '—', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sessions | COI</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --navy:#0d1b2a;--navy2:#1a2e45;--gold:#c39b5f;--gold-l:#d4af72;
  --gold-pale:#f6edd9;--bg:#f0f2f5;--white:#fff;--text:#0d1b2a;
  --muted:#7a8899;--border:#e4ddd2;--green:#10b981;--red:#ef4444;
  --amber:#f59e0b;--blue:#3b82f6;--light:#f0f2f5;--accent:#c39b5f;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:"DM Sans",sans-serif;background:var(--bg);color:var(--text);}

/* ── WRAPPER ── */
.wrapper{display:flex;height:100vh;flex-direction:column;}

/* ── TOPBAR ── */
.topbar{height:56px;background:var(--navy);border-bottom:1px solid rgba(195,155,95,0.15);display:flex;align-items:center;padding:0 24px;gap:14px;flex-shrink:0;z-index:100;}
.logo{display:flex;align-items:center;gap:9px;text-decoration:none;}
.logo-icon{width:30px;height:30px;border-radius:8px;background:rgba(195,155,95,0.15);border:1px solid rgba(195,155,95,0.3);display:flex;align-items:center;justify-content:center;font-size:14px;color:var(--gold-l);font-weight:800;}
.logo-text{font-family:"Sora",sans-serif;font-size:13px;font-weight:700;color:#fff;}
.topbar-divider{width:1px;height:20px;background:rgba(195,155,95,0.15);}
.home-link{display:flex;align-items:center;gap:6px;text-decoration:none;color:rgba(255,255,255,0.5);font-size:12.5px;font-weight:500;padding:5px 10px;border-radius:7px;transition:all 0.15s;}
.home-link:hover{background:rgba(195,155,95,0.1);color:var(--gold-l);}
.top-tabs{display:flex;align-items:center;gap:4px;margin-left:8px;}
.top-tab{padding:6px 14px;border-radius:7px;font-size:12.5px;font-weight:500;color:rgba(255,255,255,0.45);cursor:pointer;text-decoration:none;transition:all 0.15s;}
.top-tab.active{color:var(--gold-l);font-weight:600;background:rgba(195,155,95,0.1);}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:10px;}
.stu-name{font-size:12.5px;font-weight:600;color:rgba(255,255,255,0.55);}
.btn-logout{padding:6px 14px;background:rgba(239,68,68,0.1);color:#f87171;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;border:1px solid rgba(239,68,68,0.2);}
.btn-logout:hover{background:rgba(239,68,68,0.2);}
.sidebar-toggle{display:none;align-items:center;justify-content:center;width:34px;height:34px;background:rgba(195,155,95,0.1);border:1px solid rgba(195,155,95,0.2);border-radius:8px;cursor:pointer;color:var(--gold-l);font-size:17px;flex-shrink:0;}

/* ── BODY ROW ── */
.body-row{display:flex;flex:1;overflow:hidden;position:relative;}

/* ── SIDEBAR OVERLAY ── */
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:199;}
.sidebar-overlay.active{display:block;}

/* ── SIDEBAR ── */
.sidebar{width:270px;background:var(--navy);display:flex;flex-direction:column;height:100%;flex-shrink:0;overflow-y:auto;transition:transform 0.3s ease;border-right:1px solid rgba(195,155,95,0.1);}
.sidebar::-webkit-scrollbar{width:2px;}
.sidebar::-webkit-scrollbar-thumb{background:rgba(195,155,95,0.2);}
.sidebar-head{padding:18px 16px 14px;}
.course-label{font-size:9.5px;font-weight:700;color:rgba(195,155,95,0.45);text-transform:uppercase;letter-spacing:1.2px;margin-bottom:6px;}
.course-name{font-family:"Sora",sans-serif;font-size:12.5px;font-weight:700;color:var(--gold-l);line-height:1.4;margin-bottom:14px;}
.stat-row{display:flex;gap:8px;margin-bottom:14px;}
.stat-box{flex:1;background:rgba(195,155,95,0.07);border:1px solid rgba(195,155,95,0.12);border-radius:9px;padding:10px;text-align:center;}
.stat-box .s-num{font-family:"Sora",sans-serif;font-size:18px;font-weight:800;color:#fff;line-height:1;}
.stat-box .s-lbl{font-size:9.5px;color:rgba(255,255,255,0.3);font-weight:500;margin-top:3px;}
.progress-top{display:flex;justify-content:space-between;font-size:10.5px;color:rgba(255,255,255,0.3);margin-bottom:5px;}
.progress-bar{height:4px;background:rgba(255,255,255,0.07);border-radius:4px;overflow:hidden;}
.progress-fill{height:100%;border-radius:4px;background:linear-gradient(90deg,var(--gold),var(--gold-l));}
.batch-info{margin-top:14px;border-top:1px solid rgba(195,155,95,0.1);padding-top:14px;}
.batch-row{display:flex;align-items:center;gap:8px;margin-bottom:10px;}
.batch-row .b-icon{font-size:12px;color:rgba(255,255,255,0.25);}
.batch-row .b-text{font-size:11.5px;color:rgba(255,255,255,0.3);font-weight:500;}
.batch-row .b-val{font-size:11.5px;color:rgba(255,255,255,0.6);font-weight:600;margin-left:auto;text-align:right;}
.timing-highlight{background:rgba(195,155,95,0.1);border:1px solid rgba(195,155,95,0.2);border-radius:10px;padding:12px;margin-top:10px;text-align:center;}
.timing-highlight .t-label{font-size:9.5px;font-weight:700;color:rgba(195,155,95,0.5);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;}
.timing-highlight .t-time{font-family:"Sora",sans-serif;font-size:16px;font-weight:800;color:var(--gold-l);}
.timing-highlight .t-batch{font-size:11px;color:rgba(255,255,255,0.35);margin-top:3px;}
.att-summary{margin-top:14px;border-top:1px solid rgba(195,155,95,0.1);padding-top:14px;}
.att-summary .sum-title{font-size:9.5px;font-weight:700;color:rgba(195,155,95,0.4);text-transform:uppercase;letter-spacing:1px;margin-bottom:10px;}
.sum-row{display:flex;gap:6px;}
.sum-badge{flex:1;border-radius:8px;padding:8px 6px;text-align:center;}
.sum-badge.p{background:rgba(16,185,129,0.1);border:1px solid rgba(16,185,129,0.2);}
.sum-badge.a{background:rgba(239,68,68,0.1);border:1px solid rgba(239,68,68,0.2);}
.sum-badge.l{background:rgba(245,158,11,0.1);border:1px solid rgba(245,158,11,0.2);}
.sum-badge .sb-num{font-family:"Sora",sans-serif;font-size:16px;font-weight:800;}
.sum-badge.p .sb-num{color:var(--green);}
.sum-badge.a .sb-num{color:var(--red);}
.sum-badge.l .sb-num{color:var(--amber);}
.sum-badge .sb-lbl{font-size:9.5px;color:rgba(255,255,255,0.3);margin-top:2px;}

/* ── MAIN ── */
.main{flex:1;overflow:hidden;display:flex;flex-direction:column;min-width:0;}

/* ── MONTH BAR ── */
.month-bar{padding:12px 24px 10px;background:var(--white);border-bottom:1px solid var(--border);display:flex;align-items:flex-start;justify-content:space-between;gap:16px;flex-wrap:wrap;box-shadow:0 1px 6px rgba(13,27,42,0.04);}
.month-title{font-family:"Sora",sans-serif;font-size:15px;font-weight:700;color:var(--navy);}
.month-sub{font-size:11px;color:var(--muted);margin-top:2px;}
.month-nav{display:flex;align-items:center;gap:10px;}
.nav-arrow{display:flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;background:var(--bg);border:1px solid var(--border);color:var(--navy);text-decoration:none;font-size:17px;font-weight:700;transition:all 0.15s;}
.nav-arrow:hover{background:var(--navy);color:var(--gold-l);border-color:var(--navy);}
.nav-month-lbl{font-size:12px;font-weight:600;color:var(--muted);min-width:90px;text-align:center;}
.month-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:3px;}
.month-day-hdr{font-size:9.5px;text-transform:uppercase;color:var(--muted);text-align:center;padding-bottom:2px;}
.month-cell{font-size:11px;text-align:center;padding:4px 2px;border-radius:6px;cursor:default;position:relative;min-width:24px;}
.month-cell.has-session{background:var(--gold-pale);cursor:pointer;}
.month-cell.has-session:hover{background:#eeddb8;}
.month-cell.selected{background:var(--navy)!important;color:var(--gold-l)!important;}
.month-cell.today{font-weight:700;border:1.5px solid var(--gold);}
.month-cell-dot{width:4px;height:4px;border-radius:50%;background:var(--gold);position:absolute;bottom:2px;left:50%;transform:translateX(-50%);}
.month-cell.selected .month-cell-dot{background:var(--gold-l);}
.dot-comm{background:var(--amber)!important;}
.dot-doubt{background:var(--blue)!important;}

/* ── SESSIONS LIST ── */
.sessions-scroll{flex:1;overflow-y:auto;padding:20px 24px;}
.sessions-scroll::-webkit-scrollbar{width:4px;}
.sessions-scroll::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px;}
.day-group{display:flex;gap:20px;margin-bottom:10px;}
.day-label{width:70px;flex-shrink:0;padding-top:16px;text-align:right;}
.day-label .d-num{font-family:"Sora",sans-serif;font-size:18px;font-weight:700;line-height:1;color:var(--navy);}
.day-label .d-name{font-size:11px;color:var(--muted);margin-top:2px;}
.day-content{flex:1;min-width:0;}
.session-card{background:var(--white);border:1px solid var(--border);border-radius:12px;padding:14px 16px;margin-bottom:8px;transition:box-shadow 0.15s,transform 0.15s;border-left:3px solid transparent;box-shadow:0 1px 6px rgba(13,27,42,0.04);}
.session-card:hover{box-shadow:0 4px 16px rgba(13,27,42,0.08);transform:translateY(-1px);}
.session-card.lecture{border-left-color:var(--navy);}
.session-card.communication{border-left-color:var(--gold);}
.session-card.doubt{border-left-color:var(--blue);}
.session-card.cancelled{opacity:0.55;border-left-color:var(--red);}
.s-type-badge{display:inline-flex;align-items:center;gap:4px;font-size:10px;font-weight:700;padding:2px 9px;border-radius:20px;margin-bottom:6px;}
.s-type-badge.lecture{background:rgba(13,27,42,0.07);color:var(--navy);}
.s-type-badge.communication{background:var(--gold-pale);color:#7a5c2a;}
.s-type-badge.doubt{background:#eff6ff;color:#1e40af;}
.s-title{font-family:"Sora",sans-serif;font-size:13.5px;font-weight:700;margin-bottom:6px;color:var(--navy);}
.session-card.cancelled .s-title{text-decoration:line-through;color:var(--muted);}
.s-meta{display:flex;align-items:center;gap:12px;font-size:11.5px;color:var(--muted);margin-bottom:8px;flex-wrap:wrap;}
.s-footer{display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.status-badge{padding:3px 10px;border-radius:20px;font-size:10.5px;font-weight:700;}
.status-badge.completed{background:#dcfce7;color:#166534;}
.status-badge.cancelled{background:#fee2e2;color:#991b1b;}
.status-badge.scheduled{background:var(--gold-pale);color:#7a5c2a;}
.att-circle{width:24px;height:24px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;}
.att-circle.P{background:#dcfce7;color:#16a34a;}
.att-circle.A{background:#fee2e2;color:#dc2626;}
.att-circle.L{background:#fef3c7;color:#d97706;}
.show-all-btn{display:inline-block;margin:0 24px 10px;padding:7px 16px;border-radius:8px;background:var(--gold-pale);border:1px solid var(--border);font-size:12px;font-weight:600;color:var(--navy);cursor:pointer;transition:all 0.15s;}
.show-all-btn:hover{background:var(--navy);color:var(--gold-l);}
.empty-state{text-align:center;padding:60px 20px;color:var(--muted);}
.empty-state .e-icon{font-size:40px;margin-bottom:12px;}
.empty-state h3{font-family:"Sora",sans-serif;font-size:16px;font-weight:700;margin-bottom:6px;color:var(--navy);}

/* ══════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════ */

/* ── Tablet: ≤900px ── */
@media(max-width:900px){
  .sidebar{width:240px;}
  .sessions-scroll{padding:16px;}
  .month-bar{padding:10px 16px 10px;}
}

/* ── Mobile: ≤768px ── */
@media(max-width:768px){
  /* body scrollable instead of fixed height */
  body{height:auto;overflow:auto;}
  .wrapper{height:auto;min-height:100vh;}

  /* Topbar */
  .topbar{padding:0 12px;gap:8px;height:50px;position:sticky;top:0;z-index:200;}
  .logo-text{display:none;}
  .topbar-divider{display:none;}
  .home-link span{display:none;}
  .home-link{padding:5px 7px;}
  .stu-name{display:none;}
  .btn-logout{padding:6px 10px;font-size:11.5px;}
  .sidebar-toggle{display:flex;}

  /* Body row: stacked */
  .body-row{flex-direction:column;overflow:visible;height:auto;}

  /* Sidebar: fixed drawer */
  .sidebar{
    position:fixed;
    top:50px;
    left:0;
    height:calc(100vh - 50px);
    width:280px;
    z-index:200;
    transform:translateX(-100%);
    box-shadow:4px 0 24px rgba(0,0,0,0.2);
  }
  .sidebar.open{transform:translateX(0);}

  /* Main: full width, natural scroll */
  .main{overflow:visible;height:auto;flex:none;}

  /* Month bar: stack title + calendar vertically */
  .month-bar{
    flex-direction:column;
    align-items:stretch;
    gap:10px;
    padding:12px 14px 10px;
  }
  .month-bar > div:last-child{
    align-items:center;
  }

  /* Calendar: slightly smaller cells on small screens */
  .month-grid{gap:2px;}
  .month-cell{font-size:10px;padding:3px 1px;min-width:20px;}

  /* Sessions list */
  .sessions-scroll{
    flex:none;
    overflow:visible;
    padding:14px 14px 24px;
    height:auto;
  }
  #showAllWrap{padding:8px 14px 0;}
  .show-all-btn{margin:0;}

  /* Day group: tighter */
  .day-group{gap:12px;}
  .day-label{width:50px;padding-top:14px;}
  .day-label .d-num{font-size:16px;}
  .day-label .d-name{font-size:10px;}

  /* Session card */
  .session-card{padding:12px 14px;}
  .s-title{font-size:13px;}
  .s-meta{font-size:11px;gap:8px;}
}

/* ── Small phones: ≤480px ── */
@media(max-width:480px){
  .topbar{padding:0 10px;}
  .top-tab{padding:5px 10px;font-size:12px;}
  .month-bar{padding:10px 10px 8px;}
  .month-grid{gap:1px;}
  .month-cell{font-size:9px;padding:3px 0;min-width:18px;}
  .sessions-scroll{padding:10px 10px 20px;}
  .day-group{gap:8px;}
  .day-label{width:42px;}
  .day-label .d-num{font-size:14px;}
  .session-card{padding:10px 12px;}
  .s-title{font-size:12.5px;}
}
</style>
</head>
<body>
<div class="wrapper">
  <div class="topbar">
    <button class="sidebar-toggle" onclick="openSidebar()">☰</button>
    <a class="logo" href="student_dashboard.php">
      <div class="logo-icon">C</div>
      <span class="logo-text">Culture of Internet</span>
    </a>
    <div class="topbar-divider"></div>
    <a class="home-link" href="student_dashboard.php">🏠 Dashboard</a>
    <div class="top-tabs">
      <a class="top-tab" href="mycourse.php">Modules</a>
      <a class="top-tab active" href="session.php">Sessions</a>
    </div>
    <div class="topbar-right">
      <span class="stu-name">👤 <?= h($user['name']) ?></span>
      <a href="student_logout.php" class="btn-logout">Logout</a>
    </div>
  </div>

  <div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

  <div class="body-row">
    <div class="sidebar" id="sidebar">
      <div class="sidebar-head">
        <div class="course-label">Currently Enrolled</div>
        <div class="course-name"><?= h($student['coursename']) ?></div>
        <div class="stat-row">
          <div class="stat-box"><div class="s-num"><?= $totalClasses ?></div><div class="s-lbl">Total</div></div>
          <div class="stat-box"><div class="s-num"><?= $completed ?></div><div class="s-lbl">Attended</div></div>
          <div class="stat-box"><div class="s-num"><?= $remaining ?></div><div class="s-lbl">Left</div></div>
        </div>
        <div class="progress-top"><span>Progress</span><span><?= $progressPct ?>%</span></div>
        <div class="progress-bar"><div class="progress-fill" style="width:<?= $progressPct ?>%"></div></div>
        <div class="batch-info">
          <div class="batch-row">
            <span class="b-icon">👥</span>
            <span class="b-text">Batch</span>
            <span class="b-val"><?= h($student['batch_no']) ?></span>
          </div>
          <div class="batch-row">
            <span class="b-icon">📅</span>
            <span class="b-text">Duration</span>
            <span class="b-val"><?= h($student['startingdate']) ?><br>to <?= h($student['completeddate']) ?></span>
          </div>
        </div>
        <div class="timing-highlight">
          <div class="t-label">Your Class Timing</div>
          <div class="t-time"><?= $batchTiming ?></div>
          <div class="t-batch"><?= h($student['batch_no']) ?></div>
        </div>
        <div class="att-summary">
          <div class="sum-title">Attendance</div>
          <div class="sum-row">
            <div class="sum-badge p"><div class="sb-num"><?= $pCount ?></div><div class="sb-lbl">Present</div></div>
            <div class="sum-badge a"><div class="sb-num"><?= $aCount ?></div><div class="sb-lbl">Absent</div></div>
            <div class="sum-badge l"><div class="sb-num"><?= $lCount ?></div><div class="sb-lbl">Late</div></div>
          </div>
        </div>
      </div>
    </div>

    <div class="main">
      <div class="month-bar">
        <div>
          <div class="month-title"><?= date('F Y', strtotime($firstDay)) ?></div>
          <div class="month-sub">📌 Tap a date to filter · 🟡 Communication · 🟣 Doubt</div>
        </div>
        <div style="display:flex;flex-direction:column;align-items:center;gap:8px;">
          <div class="month-grid" id="monthGrid">
            <div class="month-day-hdr">Su</div><div class="month-day-hdr">Mo</div>
            <div class="month-day-hdr">Tu</div><div class="month-day-hdr">We</div>
            <div class="month-day-hdr">Th</div><div class="month-day-hdr">Fr</div>
            <div class="month-day-hdr">Sa</div>
          </div>
          <div class="month-nav">
            <a href="?year=<?= $prevYear ?>&month=<?= $prevMonth ?>" class="nav-arrow">&#8592;</a>
            <span class="nav-month-lbl"><?= date('M Y', strtotime($firstDay)) ?></span>
            <a href="?year=<?= $nextYear ?>&month=<?= $nextMonth ?>" class="nav-arrow">&#8594;</a>
          </div>
        </div>
      </div>

      <div id="showAllWrap" style="display:none;padding-top:10px;">
        <span class="show-all-btn" onclick="showAll()">✕ Show all sessions</span>
      </div>

      <div class="sessions-scroll" id="sessionsList"></div>
    </div>
  </div>
</div>

<script>
const sessions    = <?= json_encode($jsSessions) ?>;
const targetYear  = <?= (int)$year ?>;
const targetMonth = <?= (int)$month ?>;
const todayStr    = '<?= date('Y-m-d') ?>';

function openSidebar() {
  document.getElementById('sidebar').classList.add('open');
  document.getElementById('sidebarOverlay').classList.add('active');
}
function closeSidebar() {
  document.getElementById('sidebar').classList.remove('open');
  document.getElementById('sidebarOverlay').classList.remove('active');
}

function renderMonthCalendar() {
  const grid = document.getElementById('monthGrid');
  while (grid.children.length > 7) grid.removeChild(grid.lastChild);
  const firstDayOfWeek = new Date(targetYear, targetMonth - 1, 1).getDay();
  const daysInMonth    = new Date(targetYear, targetMonth, 0).getDate();
  const sessionMap     = {};
  sessions.forEach(s => { sessionMap[s.date] = s.type; });
  for (let i = 0; i < firstDayOfWeek; i++) {
    const cell = document.createElement('div');
    cell.className = 'month-cell';
    grid.appendChild(cell);
  }
  for (let d = 1; d <= daysInMonth; d++) {
    const dateStr = `${targetYear}-${String(targetMonth).padStart(2,'0')}-${String(d).padStart(2,'0')}`;
    const cell = document.createElement('div');
    cell.className = 'month-cell';
    cell.textContent = d;
    if (dateStr === todayStr) cell.classList.add('today');
    if (sessionMap[dateStr]) {
      cell.classList.add('has-session');
      const dot = document.createElement('div');
      dot.className = 'month-cell-dot';
      if (sessionMap[dateStr] === 'communication') dot.classList.add('dot-comm');
      if (sessionMap[dateStr] === 'doubt') dot.classList.add('dot-doubt');
      cell.appendChild(dot);
      cell.addEventListener('click', () => {
        document.querySelectorAll('.month-cell').forEach(c => c.classList.remove('selected'));
        cell.classList.add('selected');
        renderSessions(sessions.filter(s => s.date === dateStr));
        document.getElementById('showAllWrap').style.display = 'block';
      });
    } else {
      cell.style.opacity = '0.3';
    }
    grid.appendChild(cell);
  }
}

function showAll() {
  document.querySelectorAll('.month-cell').forEach(c => c.classList.remove('selected'));
  document.getElementById('showAllWrap').style.display = 'none';
  renderSessions(sessions);
}

const typeLabels = {
  lecture:       '📘 Lecture',
  communication: '🗣️ Communication',
  doubt:         '🙋 Doubt Session'
};

function renderSessions(list) {
  const container = document.getElementById('sessionsList');
  if (!list.length) {
    container.innerHTML = `<div class="empty-state"><div class="e-icon">📭</div><h3>No Sessions</h3><p>No sessions scheduled for this period.</p></div>`;
    return;
  }
  let html = '';
  list.forEach(s => {
    const dt = new Date(s.date + 'T00:00:00');
    const dayNum  = dt.getDate();
    const dayName = dt.toLocaleDateString('en-US', {weekday:'short'});
    const type    = s.type || 'lecture';
    const statusBadge = `<span class="status-badge ${s.status}">${
      s.status === 'completed' ? '✅ Completed' :
      s.status === 'cancelled' ? '❌ Cancelled' : '🕐 Scheduled'
    }</span>`;
    const attCircle = s.att ? `<div class="att-circle ${s.att}">${s.att}</div>` : '';
    html += `
      <div class="day-group">
        <div class="day-label">
          <div class="d-num">${dayNum}</div>
          <div class="d-name">${dayName}</div>
        </div>
        <div class="day-content">
          <div class="session-card ${type} ${s.status === 'cancelled' ? 'cancelled' : ''}">
            <span class="s-type-badge ${type}">${typeLabels[type] || type}</span>
            <div class="s-title">${s.topic}</div>
            <div class="s-meta"><span>⏰ ${s.time}</span></div>
            <div class="s-footer">${statusBadge}${attCircle}</div>
          </div>
        </div>
      </div>`;
  });
  container.innerHTML = html;
}

renderMonthCalendar();
renderSessions(sessions);
</script>
</body>
</html>