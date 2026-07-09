<?php
require_once __DIR__ . '/../db.php';
session_start();

if (!isset($_SESSION['sub_admin_id'])) {
    header("Location: sub_admin_login.php");
    exit;
}

$sub_admin_id = $_SESSION['sub_admin_id'];
$name         = $_SESSION['sub_admin_name'] ?? 'Sub Admin';

// Get assigned batches for this sub admin only
$batches = [];
try {
    $stmt = $pdo->prepare("
        SELECT b.id, b.batch_name
        FROM sub_admin_batches sab
        JOIN batches b ON b.id = sab.batch_id
        WHERE sab.sub_admin_id = ?
        ORDER BY b.batch_name
    ");
    $stmt->execute([$sub_admin_id]);
    $batches = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) { $batches = []; }

$batch_names = array_column($batches, 'batch_name');

/* ── AJAX: Get students (only from assigned batches) ── */
if (isset($_GET['get_students'])) {
    $batch = $_GET['batch'] ?? '';
    if (!in_array($batch, $batch_names)) { echo json_encode([]); exit; }
    $stmt = $pdo->prepare("SELECT reg_no, name, coursename FROM user_details WHERE batch_no = ? ORDER BY name");
    $stmt->execute([$batch]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

/* ── AJAX: Get attendance ── */
if (isset($_GET['get_attendance'])) {
    $reg_no = $_GET['reg_no'] ?? '';
    $stmt   = $pdo->prepare("SELECT date, status FROM attendance WHERE reg_no = ?");
    $stmt->execute([$reg_no]);
    $map = [];
    foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) $map[$r['date']] = $r['status'];
    echo json_encode($map);
    exit;
}

/* ── AJAX: Save attendance ── */
if (isset($_POST['save_single'])) {
    $reg_no   = $_POST['reg_no']   ?? '';
    $batch_no = $_POST['batch_no'] ?? '';
    $date     = $_POST['date']     ?? '';
    $status   = $_POST['status']   ?? '';

    // Security: only allow saving for assigned batches
    if (!in_array($batch_no, $batch_names)) { echo "unauthorized"; exit; }

    if ($status === 'DEL') {
        $pdo->prepare("DELETE FROM attendance WHERE reg_no=? AND date=?")->execute([$reg_no, $date]);
        echo "deleted"; exit;
    }
    if (!in_array($status, ['P','A','Lv','L','O'])) { echo "error"; exit; }

    $check = $pdo->prepare("SELECT id FROM attendance WHERE reg_no=? AND date=?");
    $check->execute([$reg_no, $date]);
    if ($check->rowCount() > 0) {
        $pdo->prepare("UPDATE attendance SET status=?, batch_no=? WHERE reg_no=? AND date=?")->execute([$status, $batch_no, $reg_no, $date]);
    } else {
        $pdo->prepare("INSERT INTO attendance (reg_no, batch_no, date, status) VALUES (?,?,?,?)")->execute([$reg_no, $batch_no, $date, $status]);
    }
    echo "saved"; exit;
}

$preselect_batch_name = $batch_names[0] ?? '';
function h($v){ return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Attendance | Sub Admin</title>
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
.main{margin-left:245px;flex:1;padding:24px;min-height:100vh;display:flex;flex-direction:column;}
.page-header{margin-bottom:18px;}
.page-header h1{font-size:21px;font-weight:800;color:#0f172a;}
.page-header p{font-size:13px;color:#64748b;margin-top:3px;}
.batch-bar{background:#fff;border-radius:12px;padding:14px 20px;margin-bottom:18px;box-shadow:0 2px 8px rgba(0,0,0,0.05);display:flex;align-items:center;gap:14px;border:1px solid #e2e8f0;}
.batch-bar label{font-size:13px;font-weight:700;color:#475569;white-space:nowrap;}
.batch-bar select{padding:9px 36px 9px 14px;border:1.5px solid #e2e8f0;border-radius:8px;font-size:13.5px;font-family:'DM Sans',sans-serif;color:#1e293b;background:#fff;cursor:pointer;min-width:200px;outline:none;}
.batch-bar select:focus{border-color:#2563eb;}
.batch-info{font-size:12.5px;color:#94a3b8;margin-left:auto;}
.no-batch-warn{background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:20px;text-align:center;color:#9a3412;font-size:14px;font-weight:500;margin-bottom:18px;}
.content-row{display:flex;gap:14px;flex:1;min-height:0;}
.students-panel{width:270px;flex-shrink:0;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);display:flex;flex-direction:column;overflow:hidden;border:1px solid #e2e8f0;}
.panel-head{padding:13px 16px;border-bottom:1px solid #f1f5f9;font-size:13px;font-weight:700;color:#0f172a;display:flex;align-items:center;justify-content:space-between;}
.panel-head .count{background:#eff6ff;color:#2563eb;padding:2px 8px;border-radius:20px;font-size:11px;font-weight:700;}
.students-scroll{flex:1;overflow-y:auto;}
.students-scroll::-webkit-scrollbar{width:3px;}
.students-scroll::-webkit-scrollbar-thumb{background:#e2e8f0;border-radius:3px;}
.student-item{display:flex;align-items:center;gap:10px;padding:11px 14px;cursor:pointer;border-bottom:1px solid #f8fafc;transition:background 0.15s;border-left:3px solid transparent;}
.student-item:hover{background:#f8fafc;}
.student-item.active{background:#eff6ff;border-left-color:#2563eb;}
.stu-avatar{width:32px;height:32px;border-radius:50%;background:#e0e7ff;color:#4f46e5;display:flex;align-items:center;justify-content:center;font-size:12px;font-weight:800;flex-shrink:0;}
.stu-info{flex:1;min-width:0;}
.stu-name{font-size:13px;font-weight:600;color:#1e293b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;}
.stu-reg{font-size:11px;color:#94a3b8;margin-top:1px;}
.stu-badges{display:flex;gap:3px;flex-shrink:0;flex-wrap:wrap;max-width:70px;justify-content:flex-end;}
.stu-badge{font-size:10px;font-weight:700;padding:1px 5px;border-radius:4px;}
.stu-badge.P{background:#dcfce7;color:#16a34a;}
.stu-badge.A{background:#fee2e2;color:#dc2626;}
.stu-badge.Lv{background:#fef9c3;color:#d97706;}
.stu-badge.L{background:#fff7ed;color:#c2410c;}
.stu-badge.O{background:#fdf4ff;color:#9333ea;}
.empty-students{padding:36px 16px;text-align:center;color:#94a3b8;}
.empty-students .e-icon{font-size:32px;margin-bottom:8px;}
.empty-students p{font-size:13px;}
.calendar-panel{flex:1;background:#fff;border-radius:12px;box-shadow:0 2px 8px rgba(0,0,0,0.05);display:flex;flex-direction:column;overflow:hidden;border:1px solid #e2e8f0;}
.cal-panel-head{padding:13px 20px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;}
.cal-stu-title{font-size:14px;font-weight:700;color:#0f172a;}
.cal-stu-sub{font-size:12px;color:#94a3b8;margin-top:1px;}
.att-summary{display:flex;gap:7px;flex-wrap:wrap;}
.att-sum-badge{font-size:12px;font-weight:700;padding:4px 10px;border-radius:20px;}
.att-sum-badge.P{background:#dcfce7;color:#16a34a;}
.att-sum-badge.A{background:#fee2e2;color:#dc2626;}
.att-sum-badge.Lv{background:#fef9c3;color:#d97706;}
.att-sum-badge.L{background:#fff7ed;color:#c2410c;}
.att-sum-badge.O{background:#fdf4ff;color:#9333ea;}
.cal-body{flex:1;padding:16px 20px;overflow-y:auto;}
.cal-nav-row{display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;}
.cal-month-lbl{font-size:15px;font-weight:700;color:#0f172a;}
.cal-nav-btns{display:flex;gap:6px;}
.cal-nav-btns button{width:28px;height:28px;border-radius:7px;border:none;background:#f1f5f9;cursor:pointer;font-size:15px;color:#475569;display:flex;align-items:center;justify-content:center;}
.cal-nav-btns button:hover{background:#e2e8f0;}
.cal-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;}
.cal-day-hdr{text-align:center;font-size:11px;font-weight:700;color:#94a3b8;padding:4px 0;}
.cal-cell{position:relative;text-align:center;padding:8px 4px 20px;border-radius:9px;font-size:13px;font-weight:500;color:#334155;cursor:pointer;transition:background 0.15s;min-height:44px;user-select:none;}
.cal-cell:hover{background:#f8fafc;}
.cal-cell.empty{pointer-events:none;}
.cal-cell.today{color:#2563eb;font-weight:800;background:#eff6ff;}
.cal-cell.future{color:#cbd5e1;pointer-events:none;}
.cal-cell .status-ring{position:absolute;inset:0;border-radius:9px;opacity:0.15;pointer-events:none;}
.cal-cell.P .status-ring{background:#22c55e;}
.cal-cell.A .status-ring{background:#ef4444;}
.cal-cell.Lv .status-ring{background:#f59e0b;}
.cal-cell.L .status-ring{background:#f97316;}
.cal-cell.O .status-ring{background:#e879f9;}
.cal-cell .status-lbl{position:absolute;bottom:4px;left:50%;transform:translateX(-50%);font-size:9px;font-weight:800;pointer-events:none;white-space:nowrap;}
.cal-cell.P .status-lbl{color:#16a34a;}
.cal-cell.A .status-lbl{color:#dc2626;}
.cal-cell.Lv .status-lbl{color:#d97706;}
.cal-cell.L .status-lbl{color:#c2410c;}
.cal-cell.O .status-lbl{color:#9333ea;}
.cal-legend{display:flex;gap:12px;margin-top:12px;padding-top:12px;border-top:1px solid #f1f5f9;flex-wrap:wrap;}
.leg{display:flex;align-items:center;gap:5px;font-size:12px;color:#64748b;}
.leg-dot{width:7px;height:7px;border-radius:50%;}
.leg-dot.P{background:#22c55e;}.leg-dot.A{background:#ef4444;}.leg-dot.Lv{background:#f59e0b;}
.leg-dot.L{background:#f97316;}.leg-dot.O{background:#e879f9;}
.cal-empty{flex:1;display:flex;align-items:center;justify-content:center;flex-direction:column;color:#94a3b8;padding:40px;text-align:center;}
.cal-empty .ce-icon{font-size:48px;margin-bottom:12px;}
.cal-empty h3{font-size:16px;font-weight:600;color:#64748b;margin-bottom:4px;}
.overlay{display:none;position:fixed;inset:0;z-index:200;}
.overlay.show{display:block;}
.pal-popup{display:none;position:fixed;z-index:201;background:#fff;border-radius:14px;box-shadow:0 8px 32px rgba(0,0,0,0.16);border:1px solid #e2e8f0;padding:16px 18px;min-width:240px;}
.pal-popup.show{display:block;}
.pop-date{font-size:12px;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:12px;text-align:center;}
.pop-btns{display:flex;gap:7px;flex-wrap:wrap;}
.pop-btn{flex:1;min-width:44px;padding:11px 6px;border-radius:9px;border:2px solid transparent;font-size:14px;font-weight:800;cursor:pointer;font-family:'DM Sans',sans-serif;text-align:center;transition:all 0.15s;}
.pop-btn.P{background:#f0fdf4;color:#16a34a;border-color:#bbf7d0;}.pop-btn.P:hover{background:#16a34a;color:#fff;}
.pop-btn.A{background:#fff1f2;color:#ef4444;border-color:#fecdd3;}.pop-btn.A:hover{background:#ef4444;color:#fff;}
.pop-btn.Lv{background:#fefce8;color:#ca8a04;border-color:#fde68a;}.pop-btn.Lv:hover{background:#ca8a04;color:#fff;}
.pop-btn.L{background:#fff7ed;color:#c2410c;border-color:#fed7aa;}.pop-btn.L:hover{background:#ea580c;color:#fff;}
.pop-btn.O{background:#fdf4ff;color:#9333ea;border-color:#e9d5ff;}.pop-btn.O:hover{background:#a855f7;color:#fff;}
.pop-remove{margin-top:8px;text-align:center;font-size:12px;color:#94a3b8;cursor:pointer;padding:4px;}
.pop-remove:hover{color:#ef4444;}
.save-toast{position:fixed;bottom:24px;right:24px;background:#0f172a;color:#fff;padding:11px 20px;border-radius:10px;font-size:13px;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,0.2);display:none;z-index:999;align-items:center;gap:8px;}
.save-toast.show{display:flex;}
</style>
</head>
<body>

<div class="sidebar">
  <div class="sb-brand"><h2>🎓 Sub Admin</h2><p><?= h($name) ?></p></div>
  <div class="sb-sec">Main</div>
  <a href="sub_admin_dashboard.php"><span class="sb-icon">🏠</span>Dashboard</a>
  <div class="sb-sec">Students</div>
  <a href="sub_admin_students.php"><span class="sb-icon">👨‍🎓</span>My Students</a>
  <div class="sb-sec">Batches</div>
  <a href="sub_admin_batch.php"><span class="sb-icon">🏫</span>My Batches</a>
  <div class="sb-sec">Attendance</div>
  <a href="sub_admin_attendance.php" class="active"><span class="sb-icon">✅</span>Mark Attendance</a>
  <div class="sb-sec">Modules &amp; Sessions</div>
  <a href="sub_admin_modules.php"><span class="sb-icon">🗂️</span>Modules</a>
  <a href="sub_admin_sessions.php"><span class="sb-icon">📅</span>Sessions</a>
  <div class="sb-bottom"><a href="sub_admin_logout.php"><span class="sb-icon">🚪</span>Logout</a></div>
</div>

<div class="main">
    <div class="page-header">
        <h1>✅ Mark Attendance</h1>
        <p>Select a batch → select a student → click any date to mark status</p>
    </div>

    <?php if (empty($batches)): ?>
    <div class="no-batch-warn">⚠️ No batches assigned to you yet. Contact your admin.</div>
    <?php else: ?>

    <div class="batch-bar">
        <label>Batch</label>
        <select id="batchSelect" onchange="loadStudents()">
            <?php foreach ($batches as $b): ?>
                <option value="<?= h($b['batch_name']) ?>"><?= h($b['batch_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <span class="batch-info" id="batchInfo">Loading...</span>
    </div>

    <div class="content-row">
        <div class="students-panel">
            <div class="panel-head">Students <span class="count" id="stuCount">0</span></div>
            <div class="students-scroll" id="studentsList">
                <div class="empty-students"><div class="e-icon">⏳</div><p>Loading...</p></div>
            </div>
        </div>

        <div class="calendar-panel">
            <div class="cal-empty" id="calEmptyState">
                <div class="ce-icon">📅</div>
                <h3>No student selected</h3>
                <p>Click a student from the list to mark attendance</p>
            </div>
            <div id="calendarContent" style="display:none;flex-direction:column;height:100%;">
                <div class="cal-panel-head">
                    <div>
                        <div class="cal-stu-title" id="calStuName">—</div>
                        <div class="cal-stu-sub" id="calStuReg">—</div>
                    </div>
                    <div class="att-summary" id="attSummary"></div>
                </div>
                <div class="cal-body">
                    <div class="cal-nav-row">
                        <span class="cal-month-lbl" id="calMonthLbl"></span>
                        <div class="cal-nav-btns">
                            <button onclick="prevMonth()">‹</button>
                            <button onclick="nextMonth()">›</button>
                        </div>
                    </div>
                    <div class="cal-grid" id="calGrid">
                        <div class="cal-day-hdr">Su</div><div class="cal-day-hdr">Mo</div>
                        <div class="cal-day-hdr">Tu</div><div class="cal-day-hdr">We</div>
                        <div class="cal-day-hdr">Th</div><div class="cal-day-hdr">Fr</div>
                        <div class="cal-day-hdr">Sa</div>
                    </div>
                    <div class="cal-legend">
                        <div class="leg"><div class="leg-dot P"></div> Present</div>
                        <div class="leg"><div class="leg-dot A"></div> Absent</div>
                        <div class="leg"><div class="leg-dot Lv"></div> Leave</div>
                        <div class="leg"><div class="leg-dot L"></div> Late</div>
                        <div class="leg"><div class="leg-dot O"></div> Online</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="overlay" id="overlay" onclick="closePopup()"></div>
<div class="pal-popup" id="palPopup">
    <div class="pop-date" id="popDateLbl"></div>
    <div class="pop-btns">
        <div class="pop-btn P"  onclick="saveStatus('P')">P</div>
        <div class="pop-btn A"  onclick="saveStatus('A')">A</div>
        <div class="pop-btn Lv" onclick="saveStatus('Lv')">Lv</div>
        <div class="pop-btn L"  onclick="saveStatus('L')">L</div>
        <div class="pop-btn O"  onclick="saveStatus('O')">O</div>
    </div>
    <div class="pop-remove" onclick="saveStatus('DEL')">✕ Remove</div>
</div>
<div class="save-toast" id="saveToast">✅ Saved!</div>

<script>
const MONTHS=['January','February','March','April','May','June','July','August','September','October','November','December'];
const STATUS_LABEL={P:'P',A:'A',Lv:'Lv',L:'L',O:'O'};
let activeBatch='', activeStudent=null, attData={};
let calYear=new Date().getFullYear(), calMonth=new Date().getMonth(), popDate='';
const todayStr=new Date().toISOString().split('T')[0];

window.addEventListener('DOMContentLoaded',()=>{
    const sel=document.getElementById('batchSelect');
    if(sel && sel.options.length>0){ activeBatch=sel.value; loadStudents(); }
});

function loadStudents(){
    activeBatch=document.getElementById('batchSelect').value;
    if(!activeBatch) return;
    activeStudent=null; attData={};
    document.getElementById('calEmptyState').style.display='flex';
    document.getElementById('calendarContent').style.display='none';
    fetch(`sub_admin_attendance.php?get_students=1&batch=${encodeURIComponent(activeBatch)}`)
        .then(r=>r.json()).then(data=>{
            document.getElementById('stuCount').textContent=data.length;
            document.getElementById('batchInfo').textContent=data.length+' students · '+activeBatch;
            if(!data.length){ document.getElementById('studentsList').innerHTML='<div class="empty-students"><div class="e-icon">😕</div><p>No students in this batch</p></div>'; return; }
            let html='';
            data.forEach(s=>{
                const ini=s.name.split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();
                html+=`<div class="student-item" id="stu_${s.reg_no}" onclick="selectStudent('${esc(s.reg_no)}','${esc(s.name)}','${esc(s.coursename)}')">
                    <div class="stu-avatar">${ini}</div>
                    <div class="stu-info"><div class="stu-name">${s.name}</div><div class="stu-reg">${s.reg_no}</div></div>
                    <div class="stu-badges" id="badges_${s.reg_no}"></div>
                </div>`;
            });
            document.getElementById('studentsList').innerHTML=html;
            data.forEach(s=>loadBadges(s.reg_no));
        });
}

function loadBadges(reg_no){
    fetch(`sub_admin_attendance.php?get_attendance=1&reg_no=${encodeURIComponent(reg_no)}`)
        .then(r=>r.json()).then(data=>{
            const counts={P:0,A:0,Lv:0,L:0,O:0};
            Object.values(data).forEach(v=>{ if(counts[v]!==undefined) counts[v]++; });
            const el=document.getElementById('badges_'+reg_no); if(!el) return;
            el.innerHTML=['P','A','Lv','L','O'].filter(k=>counts[k]>0)
                .map(k=>`<span class="stu-badge ${k}">${counts[k]}</span>`).join('');
        });
}

function selectStudent(reg_no,name,course){
    document.querySelectorAll('.student-item').forEach(el=>el.classList.remove('active'));
    document.getElementById('stu_'+reg_no)?.classList.add('active');
    activeStudent={reg_no,name,course,batch_no:activeBatch};
    document.getElementById('calStuName').textContent=name;
    document.getElementById('calStuReg').textContent=reg_no;
    document.getElementById('calEmptyState').style.display='none';
    document.getElementById('calendarContent').style.display='flex';
    fetch(`sub_admin_attendance.php?get_attendance=1&reg_no=${encodeURIComponent(reg_no)}`)
        .then(r=>r.json()).then(data=>{ attData=data; renderCalendar(); renderSummary(); });
}

function renderCalendar(){
    const grid=document.getElementById('calGrid');
    document.getElementById('calMonthLbl').textContent=MONTHS[calMonth]+' '+calYear;
    grid.querySelectorAll('.cal-cell').forEach(c=>c.remove());
    const firstDay=new Date(calYear,calMonth,1).getDay();
    const daysInMonth=new Date(calYear,calMonth+1,0).getDate();
    for(let i=0;i<firstDay;i++){ const e=document.createElement('div'); e.className='cal-cell empty'; grid.appendChild(e); }
    for(let d=1;d<=daysInMonth;d++){
        const ds=calYear+'-'+String(calMonth+1).padStart(2,'0')+'-'+String(d).padStart(2,'0');
        const cell=document.createElement('div'); cell.className='cal-cell'; cell.dataset.date=ds;
        const num=document.createElement('span'); num.textContent=d; cell.appendChild(num);
        if(ds===todayStr) cell.classList.add('today');
        if(ds>todayStr) cell.classList.add('future');
        if(attData[ds]){
            const st=attData[ds]; cell.classList.add(st);
            const ring=document.createElement('div'); ring.className='status-ring'; cell.appendChild(ring);
            const lbl=document.createElement('div'); lbl.className='status-lbl'; lbl.textContent=STATUS_LABEL[st]||st; cell.appendChild(lbl);
        }
        cell.addEventListener('click',function(e){ if(!activeStudent) return; openPopup(ds,e); });
        grid.appendChild(cell);
    }
}

function renderSummary(){
    const counts={P:0,A:0,Lv:0,L:0,O:0};
    Object.values(attData).forEach(v=>{ if(counts[v]!==undefined) counts[v]++; });
    const icons={P:'✅',A:'❌',Lv:'🌴',L:'⏰',O:'💻'};
    document.getElementById('attSummary').innerHTML=['P','A','Lv','L','O'].filter(k=>counts[k]>0)
        .map(k=>`<span class="att-sum-badge ${k}">${icons[k]} ${counts[k]}</span>`).join('');
}

function openPopup(dateStr,e){
    popDate=dateStr;
    const parts=dateStr.split('-');
    document.getElementById('popDateLbl').textContent=parts[2]+' '+MONTHS[parseInt(parts[1])-1]+' '+parts[0];
    const popup=document.getElementById('palPopup');
    const rect=e.target.closest('.cal-cell').getBoundingClientRect();
    let top=rect.bottom+window.scrollY+8, left=rect.left+window.scrollX-60;
    if(left+260>window.innerWidth) left=window.innerWidth-270;
    if(left<8) left=8;
    popup.style.top=top+'px'; popup.style.left=left+'px';
    popup.classList.add('show'); document.getElementById('overlay').classList.add('show');
}

function closePopup(){ document.getElementById('palPopup').classList.remove('show'); document.getElementById('overlay').classList.remove('show'); }

function saveStatus(status){
    closePopup(); if(!activeStudent) return;
    if(status==='DEL'){ delete attData[popDate]; }
    else { attData[popDate]=status; }
    renderCalendar(); renderSummary(); loadBadges(activeStudent.reg_no);
    const fd=new FormData();
    fd.append('save_single','1'); fd.append('reg_no',activeStudent.reg_no);
    fd.append('batch_no',activeStudent.batch_no); fd.append('date',popDate); fd.append('status',status);
    fetch('sub_admin_attendance.php',{method:'POST',body:fd}).then(r=>r.text())
        .then(res=>showToast(res==='saved'||res==='deleted'?'✅ Saved!':'❌ Error'));
}

function showToast(msg){ const t=document.getElementById('saveToast'); t.textContent=msg; t.classList.add('show'); setTimeout(()=>t.classList.remove('show'),2000); }
function prevMonth(){ calMonth--; if(calMonth<0){calMonth=11;calYear--;} renderCalendar(); }
function nextMonth(){ calMonth++; if(calMonth>11){calMonth=0;calYear++;} renderCalendar(); }
function esc(str){ return (str||'').replace(/\\/g,'\\\\').replace(/'/g,"\\'"); }
</script>
</body>
</html>