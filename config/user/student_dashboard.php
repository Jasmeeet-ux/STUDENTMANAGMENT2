<?php
require_once __DIR__ . '/../db.php';
session_start();

if (!isset($_SESSION['student_id'])) { header("Location: student_login.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['student_id']]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt2 = $pdo->prepare("SELECT * FROM user_details WHERE reg_no = ?");
$stmt2->execute([$user['reg_no']]);
$student = $stmt2->fetch(PDO::FETCH_ASSOC);

// ── Multi-course: switch context ──
if (isset($_GET['switch_course'])) {
    $sid = (int)$_GET['switch_course'];
    $sc = $pdo->prepare("SELECT * FROM student_enrollments WHERE id=? AND reg_no=?");
    $sc->execute([$sid, $user['reg_no']]);
    if ($sc->fetch()) { $_SESSION['active_enrollment_id'] = $sid; }
    header("Location: student_dashboard.php"); exit;
}

$all_enrollments = [];
try {
    $enr = $pdo->prepare("SELECT se.*, b.course_id FROM student_enrollments se LEFT JOIN batches b ON b.batch_name=se.batch_no WHERE se.reg_no=? ORDER BY se.id ASC");
    $enr->execute([$user['reg_no']]);
    $all_enrollments = $enr->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) { $all_enrollments = []; }

$active_enrollment = null;
if (!empty($all_enrollments)) {
    $active_id = $_SESSION['active_enrollment_id'] ?? 0;
    foreach ($all_enrollments as $er) {
        if ($er['id'] == $active_id) { $active_enrollment = $er; break; }
    }
    if (!$active_enrollment) {
        $active_enrollment = $all_enrollments[0];
        $_SESSION['active_enrollment_id'] = $active_enrollment['id'];
    }
}
if ($active_enrollment) {
    $student['batch_no']   = $active_enrollment['batch_no'];
    $student['coursename'] = $active_enrollment['coursename'];
}

$terms_accepted = (int)($user['terms_accepted'] ?? 1);
$reg_no = $user['reg_no'];

// ── course_id ──
$course_id_perf = null;
if (!empty($student['batch_no'])) {
    $bq = $pdo->prepare("SELECT b.course_id FROM batches b WHERE b.batch_name=? LIMIT 1");
    $bq->execute([$student['batch_no']]);
    $bRow = $bq->fetch(PDO::FETCH_ASSOC);
    if ($bRow) $course_id_perf = $bRow['course_id'] ?? null;
}

// ── Attendance (batch-specific) ──
$active_batch = $student['batch_no'] ?? '';

// Use EXACT same queries as admin performance page for consistency
$att_counts = ['P'=>0,'A'=>0,'Lv'=>0,'L'=>0,'O'=>0];
if ($student && $active_batch) {
    $stmt3 = $pdo->prepare("SELECT status, COUNT(*) as cnt FROM attendance WHERE reg_no=? AND batch_no=? GROUP BY status");
    $stmt3->execute([$reg_no, $active_batch]);
    foreach ($stmt3->fetchAll(PDO::FETCH_ASSOC) as $row) {
        if (isset($att_counts[$row['status']])) $att_counts[$row['status']] = (int)$row['cnt'];
    }
}
$p_count    = $att_counts['P'];
$a_count    = $att_counts['A'];
$lv_count   = $att_counts['Lv'];
$late_count = $att_counts['L'];
$o_count    = $att_counts['O'];
$l_count    = $lv_count;

// SAME formula as admin: total = all except Lv
$s_total = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE reg_no=? AND batch_no=? AND status != 'Lv'");
$s_total->execute([$reg_no, $active_batch]);
$total = (int)$s_total->fetchColumn();

$s_attended = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE reg_no=? AND batch_no=? AND status IN ('P','L','O')");
$s_attended->execute([$reg_no, $active_batch]);
$attended = (int)$s_attended->fetchColumn();

$att_pct = ($total > 0) ? min(100, round(($attended / $total) * 100)) : 0;

// ── Course progress (course-specific) ──
$total_topics = 0; $completed_topics = 0;
if ($course_id_perf) {
    $s = $pdo->prepare("SELECT COUNT(DISTINCT topic_name) FROM course_modules WHERE course_id=?");
    $s->execute([$course_id_perf]); $total_topics = (int)$s->fetchColumn();
    if ($total_topics > 0) {
        $s2 = $pdo->prepare("SELECT COUNT(DISTINCT cp.topic_name) FROM course_progress cp INNER JOIN course_modules cm ON cm.topic_name=cp.topic_name AND cm.course_id=? WHERE cp.reg_no=?");
        $s2->execute([$course_id_perf, $reg_no]); $completed_topics = min((int)$s2->fetchColumn(), $total_topics);
    }
}
$remaining_topics = max(0, $total_topics - $completed_topics);
$course_pct = ($total_topics > 0) ? min(100, round(($completed_topics / $total_topics) * 100)) : 0;

// ── MCQ ──
$mcq_avg_pct = 0;
if ($course_id_perf) {
    $mq = $pdo->prepare("SELECT score, total FROM mcq_attempts WHERE reg_no=? AND course_id=? AND total>0");
    $mq->execute([$reg_no, $course_id_perf]);
    $mrows = $mq->fetchAll(PDO::FETCH_ASSOC);
    if (count($mrows) > 0) {
        $sum = 0; foreach ($mrows as $r) { $sum += ($r['score']/$r['total'])*100; }
        $mcq_avg_pct = min(100, round($sum / count($mrows)));
    }
}

// ── Assignments ──
$assignment_total = 0; $assignment_completed = 0;
if ($course_id_perf) {
    $at = $pdo->prepare("SELECT COUNT(DISTINCT topic_name) FROM topic_assignments WHERE course_id=?");
    $at->execute([$course_id_perf]); $assignment_total = (int)$at->fetchColumn();
    if ($assignment_total > 0) {
        $ac = $pdo->prepare("SELECT COUNT(DISTINCT topic_name) FROM assignment_submissions WHERE reg_no=? AND course_id=?");
        $ac->execute([$reg_no, $course_id_perf]); $assignment_completed = min((int)$ac->fetchColumn(), $assignment_total);
    }
}
$assignment_remaining = max(0, $assignment_total - $assignment_completed);
$assignment_pct = ($assignment_total > 0) ? min(100, round(($assignment_completed / $assignment_total) * 100)) : 0;

// ── Overall ──
$has_any_data = ($total > 0 || $total_topics > 0 || $assignment_total > 0);
$overall_pct = $has_any_data ? min(100, round(($att_pct*0.40) + ($course_pct*0.30) + ($mcq_avg_pct*0.15) + ($assignment_pct*0.15))) : 0;

// ── Attendance Policy ──
$policy_leave_used   = $att_counts['Lv'] + $att_counts['A'];
$policy_online_used  = $att_counts['O'];
$policy_leave_allowed  = 20;
$policy_online_allowed = 10;
$start_date = $active_enrollment['startingdate'] ?? ($student['startingdate'] ?? '');
$end_date   = $active_enrollment['completeddate'] ?? ($student['completeddate'] ?? '');
if ($start_date && $end_date) {
    $diff_days = (strtotime($end_date) - strtotime($start_date)) / 86400;
    if ($diff_days >= 300) { $policy_leave_allowed = 40; $policy_online_allowed = 20; }
}
$policy_extra_off      = max(0, $policy_leave_used - $policy_leave_allowed);
$policy_fine           = $policy_extra_off * 20;
$policy_online_exceeded = $policy_online_used > $policy_online_allowed;
$policy_extra_online   = max(0, $policy_online_used - $policy_online_allowed);

// ── Donut helpers ──
$circumference = 2 * M_PI * 42;
$course_dash     = ($course_pct / 100) * $circumference;
$assignment_dash = ($assignment_pct / 100) * $circumference;

$page = $_GET['page'] ?? 'dashboard';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Dashboard — Culture of Internet</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;500;600;700;800&family=DM+Sans:ital,wght@0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
<style>
:root{
  --navy:#0d1b2a;--navy2:#1a2e45;--gold:#c39b5f;--gold-l:#d4af72;
  --gold-pale:#f6edd9;--bg:#f0f2f5;--white:#fff;--text:#0d1b2a;
  --muted:#7a8899;--border:#e4ddd2;--green:#10b981;--red:#ef4444;
  --amber:#f59e0b;--blue:#3b82f6;--purple:#8b5cf6;
  --r12:12px;--r16:16px;--r20:20px;--sb-w:200px;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
html,body{height:100%;overflow-x:hidden;}
body{font-family:"DM Sans",sans-serif;background:var(--bg);color:var(--text);display:flex;min-height:100vh;}

/* SIDEBAR */
.sidebar{width:var(--sb-w);background:var(--navy);height:100vh;position:fixed;left:0;top:0;display:flex;flex-direction:column;align-items:stretch;z-index:300;padding:20px 0 16px;border-right:1px solid rgba(195,155,95,0.12);overflow-y:auto;}
.sb-brand{display:flex;flex-direction:column;align-items:center;padding:0 16px 16px;border-bottom:1px solid rgba(195,155,95,0.1);margin-bottom:12px;}
.sb-logo{width:44px;height:44px;border-radius:12px;overflow:hidden;border:2px solid rgba(195,155,95,0.28);margin-bottom:7px;flex-shrink:0;}
.sb-logo img{width:100%;height:100%;object-fit:cover;}
.sb-brand-name{font-family:"Sora",sans-serif;font-size:8.5px;font-weight:700;color:rgba(195,155,95,0.6);letter-spacing:1.5px;text-transform:uppercase;line-height:1.5;text-align:center;}
.sb-section{font-size:8px;font-weight:700;color:rgba(195,155,95,0.3);letter-spacing:1.4px;text-transform:uppercase;padding:0 18px;margin:8px 0 4px;}
.sb-nav{flex:1;display:flex;flex-direction:column;gap:2px;padding:0 10px;}
.sb-link{display:flex;align-items:center;gap:9px;padding:8px 12px;border-radius:10px;text-decoration:none;color:rgba(255,255,255,0.42);font-size:12.5px;font-weight:500;transition:all 0.16s;cursor:pointer;position:relative;}
.sb-icon{font-size:14px;flex-shrink:0;width:17px;text-align:center;}
.sb-label{flex:1;font-size:12.5px;}
.sb-link:hover{background:rgba(195,155,95,0.08);color:rgba(255,255,255,0.78);}
.sb-link.active{background:rgba(195,155,95,0.13);color:var(--gold-l);border:1px solid rgba(195,155,95,0.17);}
.sb-link.active::before{content:"";position:absolute;left:-10px;top:50%;transform:translateY(-50%);width:3px;height:18px;background:var(--gold);border-radius:0 3px 3px 0;}
.sb-sep{height:1px;background:rgba(195,155,95,0.1);margin:10px 14px;}
.sb-bottom{display:flex;flex-direction:column;gap:2px;padding:0 10px;}
.sb-logout{display:flex;align-items:center;gap:9px;padding:8px 12px;border-radius:10px;color:rgba(239,68,68,0.55);font-size:12.5px;font-weight:500;text-decoration:none;transition:all 0.16s;}
.sb-logout:hover{background:rgba(239,68,68,0.08);color:#ef4444;}

/* MAIN */
.main{margin-left:var(--sb-w);flex:1;display:flex;flex-direction:column;min-height:100vh;overflow-x:hidden;}

/* TOPBAR */
.topbar{background:var(--white);border-bottom:1px solid var(--border);padding:14px 28px;display:flex;align-items:center;justify-content:space-between;gap:16px;position:sticky;top:0;z-index:100;}
.topbar-left{display:flex;align-items:center;gap:12px;}
.hamburger{display:none;align-items:center;justify-content:center;width:36px;height:36px;background:transparent;border:1px solid var(--border);border-radius:9px;cursor:pointer;font-size:16px;color:var(--navy);}
.page-title{font-family:"Sora",sans-serif;font-size:17px;font-weight:700;color:var(--navy);letter-spacing:-0.2px;}
.breadcrumb{font-size:12px;color:var(--muted);display:flex;align-items:center;gap:5px;}
.breadcrumb span{color:var(--gold);font-weight:600;}
.topbar-right{display:flex;align-items:center;gap:10px;}
.mode-pill{display:flex;align-items:center;gap:6px;background:var(--bg);border:1px solid var(--border);padding:6px 13px;border-radius:100px;font-size:12px;font-weight:600;color:var(--muted);}
.mode-dot{width:7px;height:7px;border-radius:50%;background:var(--green);flex-shrink:0;}
.mode-pill.offline .mode-dot{background:var(--amber);}
.notif-btn{width:36px;height:36px;border-radius:10px;background:var(--bg);border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:16px;cursor:pointer;}
.avatar{width:36px;height:36px;border-radius:50%;background:var(--navy);border:2px solid var(--gold);display:flex;align-items:center;justify-content:center;font-family:"Sora",sans-serif;font-size:13px;font-weight:700;color:var(--gold);flex-shrink:0;}

/* CONTENT */
.content{padding:20px 24px;flex:1;}

/* ── LAYOUT ──
   ROW1: [Welcome big] [info-col: Start / Complete / Add-on stacked right]
   ROW2: [Attendance] [Course Progress] [Assignments]
   ROW3: [Enrolled Course] [Attendance Policy]
*/
.row1{display:grid;grid-template-columns:1fr 230px;gap:16px;margin-bottom:16px;align-items:start;}
.row2{display:grid;grid-template-columns:1fr 1fr 1fr;gap:16px;margin-bottom:16px;}
.row3{display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;}

/* HERO */
.hero{background:linear-gradient(120deg,var(--navy) 0%,#1a3255 55%,#15294a 100%);border-radius:var(--r20);padding:28px 30px;color:#fff;position:relative;overflow:hidden;display:flex;align-items:flex-end;justify-content:space-between;gap:20px;min-height:190px;}
.hero-bg-circle1{position:absolute;top:-50px;right:-40px;width:220px;height:220px;border-radius:50%;background:rgba(195,155,95,0.1);pointer-events:none;}
.hero-bg-circle2{position:absolute;bottom:-60px;left:40%;width:160px;height:160px;border-radius:50%;background:rgba(195,155,95,0.06);pointer-events:none;}
.hero-left{position:relative;z-index:1;}
.hero-eyebrow{font-size:10px;font-weight:700;letter-spacing:2px;text-transform:uppercase;color:var(--gold);margin-bottom:6px;}
.hero-name{font-family:"Sora",sans-serif;font-size:23px;font-weight:800;margin-bottom:4px;letter-spacing:-0.4px;line-height:1.15;}
.hero-sub{font-size:12px;color:rgba(255,255,255,0.48);margin-bottom:14px;}
.hero-tags{display:flex;gap:7px;flex-wrap:wrap;}
.hero-tag{display:flex;align-items:center;gap:6px;background:rgba(255,255,255,0.07);border:1px solid rgba(195,155,95,0.2);padding:4px 11px;border-radius:100px;font-size:11px;font-weight:500;color:rgba(255,255,255,0.72);}
.hero-tag-dot{width:5px;height:5px;border-radius:50%;background:var(--gold);flex-shrink:0;}
.hero-stat{position:relative;z-index:1;background:rgba(255,255,255,0.07);border:1px solid rgba(195,155,95,0.18);border-radius:var(--r16);padding:14px 18px;text-align:center;flex-shrink:0;min-width:100px;}
.hero-stat-num{font-family:"Sora",sans-serif;font-size:26px;font-weight:800;color:var(--gold);line-height:1;margin-bottom:4px;}
.hero-stat-lbl{font-size:10px;color:rgba(255,255,255,0.5);font-weight:600;letter-spacing:0.5px;}

/* INFO TILES (right of hero) */
.info-col{display:flex;flex-direction:column;gap:10px;}
.info-tile{background:var(--white);border-radius:var(--r12);border:1px solid var(--border);box-shadow:0 1px 6px rgba(13,27,42,0.05);padding:12px 14px;display:flex;align-items:center;gap:10px;}
.info-tile:nth-child(1){border-left:3px solid var(--gold);}
.info-tile:nth-child(2){border-left:3px solid var(--blue);}
.info-tile:nth-child(3){border-left:3px solid var(--green);}
.info-icon{width:32px;height:32px;border-radius:9px;background:var(--gold-pale);display:flex;align-items:center;justify-content:center;font-size:14px;flex-shrink:0;}
.info-val{font-family:"Sora",sans-serif;font-size:11px;font-weight:700;color:var(--navy);white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:140px;}
.info-lbl{font-size:9.5px;color:var(--muted);font-weight:500;margin-top:1px;}

/* DASH CARDS */
.dash-card{background:var(--white);border-radius:var(--r16);border:1px solid var(--border);box-shadow:0 1px 8px rgba(13,27,42,0.05);overflow:hidden;transition:transform 0.18s,box-shadow 0.18s;}
a.dash-card{text-decoration:none;color:inherit;display:block;}
a.dash-card:hover{transform:translateY(-3px);box-shadow:0 10px 28px rgba(13,27,42,0.1);}
.card-header{padding:16px 18px 0;display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;}
.card-label{font-size:10px;font-weight:700;letter-spacing:1.3px;text-transform:uppercase;color:var(--muted);}
.card-arrow{font-size:13px;color:var(--gold);opacity:0;transition:opacity 0.18s;}
a.dash-card:hover .card-arrow{opacity:1;}
.card-body{padding:0 18px 18px;}
.course-card-inner{padding:18px;}
.course-active-badge{display:inline-flex;align-items:center;gap:5px;background:var(--gold-pale);color:#7a5c2a;padding:4px 10px;border-radius:100px;font-size:9.5px;font-weight:700;letter-spacing:0.5px;text-transform:uppercase;margin-bottom:10px;}
.course-active-dot{width:5px;height:5px;border-radius:50%;background:var(--gold);}
.course-title{font-family:"Sora",sans-serif;font-size:13px;font-weight:700;color:var(--navy);line-height:1.4;margin-bottom:12px;}
.course-meta{display:flex;flex-direction:column;gap:5px;}
.course-meta-row{display:flex;align-items:center;gap:7px;font-size:11px;color:var(--muted);font-weight:500;}
.course-meta-icon{font-size:12px;}
.donut-wrap{position:relative;width:104px;height:104px;margin:4px auto 12px;}
.donut-wrap svg{transform:rotate(-90deg);display:block;}
.donut-center{position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;white-space:nowrap;}
.donut-num{font-family:"Sora",sans-serif;font-size:17px;font-weight:700;color:var(--navy);line-height:1;}
.donut-lbl{font-size:8.5px;color:var(--muted);font-weight:600;margin-top:2px;}
.legend-row{display:flex;justify-content:space-between;gap:4px;}
.leg-item{flex:1;text-align:center;padding:6px 3px;border-radius:8px;min-width:0;}
.leg-item.p{background:#f0fdf4;}.leg-item.a{background:#fff1f2;}.leg-item.l{background:#fffbeb;}
.leg-dot{width:6px;height:6px;border-radius:50%;margin:0 auto 3px;}
.leg-item.p .leg-dot{background:var(--green);}.leg-item.a .leg-dot{background:var(--red);}.leg-item.l .leg-dot{background:var(--amber);}
.leg-num{font-size:12px;font-weight:800;}
.leg-item.p .leg-num{color:var(--green);}.leg-item.a .leg-num{color:var(--red);}.leg-item.l .leg-num{color:var(--amber);}
.leg-lbl{font-size:8px;color:var(--muted);font-weight:600;}
.card-cta{font-size:11px;color:var(--gold);font-weight:700;text-align:center;margin-top:10px;letter-spacing:0.2px;}

/* ATTENDANCE POLICY BOX */
.policy-inner{padding:18px 20px;}
.policy-top{display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;}
.policy-title{font-family:"Sora",sans-serif;font-size:12px;font-weight:700;color:var(--navy);}
.policy-duration{font-size:10px;color:var(--muted);font-weight:500;}
.policy-badge{font-size:10px;font-weight:700;padding:3px 9px;border-radius:20px;}
.policy-badge.ok{background:#dcfce7;color:#15803d;}
.policy-badge.warn{background:#fef9c3;color:#854d0e;}
.policy-badge.danger{background:#fee2e2;color:#991b1b;}
.policy-row{margin-bottom:12px;}
.policy-row-head{display:flex;align-items:center;justify-content:space-between;margin-bottom:5px;}
.policy-lbl{font-size:11px;font-weight:600;color:var(--muted);display:flex;align-items:center;gap:4px;}
.policy-cnt{font-family:"Sora",sans-serif;font-size:11.5px;font-weight:700;}
.policy-cnt.safe{color:#15803d;}.policy-cnt.warn{color:#d97706;}.policy-cnt.over{color:#dc2626;}
.policy-bar{height:6px;background:#f1f5f9;border-radius:10px;overflow:hidden;margin-bottom:3px;}
.policy-bar-fill{height:100%;border-radius:10px;}
.policy-bar-fill.green{background:linear-gradient(90deg,#22c55e,#16a34a);}
.policy-bar-fill.amber{background:linear-gradient(90deg,#f59e0b,#d97706);}
.policy-bar-fill.red{background:linear-gradient(90deg,#ef4444,#dc2626);}
.policy-bar-fill.blue{background:linear-gradient(90deg,#6366f1,#4f46e5);}
.policy-bar-fill.purple{background:linear-gradient(90deg,#a855f7,#9333ea);}
.policy-hint{font-size:9.5px;color:var(--muted);}
.policy-divider{height:1px;background:var(--border);margin:12px 0;}
.policy-fine{border-radius:10px;padding:11px 14px;display:flex;align-items:center;justify-content:space-between;}
.policy-fine.zero{background:#f0fdf4;border:1px solid #bbf7d0;}
.policy-fine.due{background:#fff7ed;border:1px solid #fed7aa;}
.policy-fine-left{}
.policy-fine-tag{font-size:9.5px;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:4px;}
.policy-fine-tag.zero{color:#15803d;}.policy-fine-tag.due{color:#9a3412;}
.policy-fine-detail{font-size:10.5px;color:#92400e;}
.policy-fine-amt{font-family:"Sora",sans-serif;font-size:22px;font-weight:800;}
.policy-fine-amt.zero{color:#16a34a;}.policy-fine-amt.due{color:#c2410c;}
.policy-online-warn{background:#fdf4ff;border:1px solid #e9d5ff;border-radius:8px;padding:8px 12px;margin-top:10px;display:flex;align-items:center;gap:7px;font-size:11px;font-weight:600;color:#7e22ce;}

/* PROFILE */
.profile-hero{background:linear-gradient(120deg,var(--navy) 0%,#1a3255 100%);border-radius:var(--r20);padding:30px 36px;color:#fff;margin-bottom:22px;display:flex;align-items:center;gap:22px;position:relative;overflow:hidden;}
.profile-hero::before{content:"";position:absolute;top:-40px;right:-40px;width:200px;height:200px;border-radius:50%;background:rgba(195,155,95,0.07);pointer-events:none;}
.p-avatar{width:76px;height:76px;border-radius:50%;background:linear-gradient(135deg,var(--gold),var(--gold-l));display:flex;align-items:center;justify-content:center;font-family:"Sora",sans-serif;font-size:28px;font-weight:700;color:var(--navy);border:3px solid rgba(255,255,255,0.1);flex-shrink:0;}
.p-name{font-family:"Sora",sans-serif;font-size:22px;font-weight:700;margin-bottom:3px;}
.p-email{font-size:12.5px;color:rgba(255,255,255,0.45);margin-bottom:12px;}
.p-chips{display:flex;gap:7px;flex-wrap:wrap;}
.p-chip{background:rgba(195,155,95,0.1);border:1px solid rgba(195,155,95,0.18);color:var(--gold-l);padding:4px 12px;border-radius:100px;font-size:11.5px;font-weight:600;}
.detail-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(270px,1fr));gap:16px;}
.detail-card{background:var(--white);border-radius:var(--r16);padding:22px;box-shadow:0 1px 6px rgba(13,27,42,0.04);border:1px solid var(--border);}
.detail-head{font-family:"Sora",sans-serif;font-size:13px;font-weight:700;color:var(--navy);margin-bottom:14px;padding-bottom:10px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:7px;}
.d-row{display:flex;justify-content:space-between;align-items:flex-start;padding:8px 0;border-bottom:1px solid #f3ede4;gap:12px;}
.d-row:last-child{border-bottom:none;}
.d-lbl{font-size:12px;color:var(--muted);font-weight:500;min-width:105px;flex-shrink:0;}
.d-val{font-size:13px;font-weight:600;color:var(--navy);text-align:right;word-break:break-word;}
.d-val.gold{color:var(--gold);font-weight:700;}
.empty-state{text-align:center;padding:60px 20px;color:var(--muted);}
.empty-state .ei{font-size:44px;margin-bottom:12px;}
.empty-state h3{font-family:"Sora",sans-serif;font-size:17px;font-weight:700;color:var(--navy);margin-bottom:5px;}

/* MOBILE */
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(10,18,30,0.65);backdrop-filter:blur(4px);z-index:200;}
.sidebar-overlay.active{display:block;}
.sidebar-mobile-panel{position:fixed;left:0;top:0;bottom:0;width:220px;background:var(--navy);z-index:250;transform:translateX(-100%);transition:transform 0.3s cubic-bezier(.4,0,.2,1);display:flex;flex-direction:column;padding:22px 0;border-right:1px solid rgba(195,155,95,0.1);}
.sidebar-mobile-panel.open{transform:translateX(0);}
.mob-brand{display:flex;align-items:center;gap:11px;padding:0 18px 18px;border-bottom:1px solid rgba(195,155,95,0.1);margin-bottom:14px;}
.mob-logo{width:36px;height:36px;border-radius:10px;overflow:hidden;border:2px solid rgba(195,155,95,0.2);}
.mob-logo img{width:100%;height:100%;object-fit:cover;}
.mob-brand-text strong{display:block;font-family:"Sora",sans-serif;font-size:11px;font-weight:700;color:#fff;line-height:1.3;}
.mob-brand-text span{font-size:9.5px;color:rgba(195,155,95,0.55);}
.mob-nav{flex:1;display:flex;flex-direction:column;gap:2px;padding:0 10px;overflow-y:auto;}
.mob-label{font-size:9px;font-weight:700;color:rgba(195,155,95,0.38);letter-spacing:1.3px;text-transform:uppercase;padding:10px 8px 3px;}
.mob-link{display:flex;align-items:center;gap:10px;color:rgba(255,255,255,0.48);padding:9px 10px;border-radius:10px;text-decoration:none;font-size:13px;font-weight:500;transition:all 0.15s;}
.mob-link:hover,.mob-link.active{background:rgba(195,155,95,0.1);color:var(--gold-l);}
.mob-icon{font-size:15px;width:18px;text-align:center;flex-shrink:0;}
.mob-bottom{padding:12px;border-top:1px solid rgba(195,155,95,0.1);}
.mob-logout{display:flex;align-items:center;gap:10px;color:rgba(239,68,68,0.6);padding:9px 10px;border-radius:10px;text-decoration:none;font-size:13px;font-weight:500;transition:all 0.15s;}
.mob-logout:hover{background:rgba(239,68,68,0.07);color:#ef4444;}

@media(max-width:1100px){.row2{grid-template-columns:1fr 1fr;}.row3{grid-template-columns:1fr;}}
@media(max-width:768px){
  .sidebar{display:none;}.main{margin-left:0;}.hamburger{display:flex;}
  .row1,.row2,.row3{grid-template-columns:1fr;}
  .hero{padding:22px;flex-direction:column;align-items:flex-start;min-height:auto;}
  .hero-stat{width:100%;display:flex;align-items:center;gap:12px;padding:14px 18px;text-align:left;}
}
</style>
</head>
<body>
<div class="sidebar-overlay" id="sOverlay" onclick="closeMob()"></div>

<!-- SIDEBAR -->
<div class="sidebar">
  <div class="sb-brand">
    <div class="sb-logo"><img src="../imgs/COI logo.png" alt="COI"></div>
    <div class="sb-brand-name">Culture of<br>Internet</div>
  </div>
  <div class="sb-nav">
    <div class="sb-section">Main</div>
    <a href="?page=dashboard" class="sb-link <?= $page=='dashboard'?'active':'' ?>"><span class="sb-icon">&#127968;</span><span class="sb-label">Dashboard</span></a>
    <a href="?page=profile"   class="sb-link <?= $page=='profile'  ?'active':'' ?>"><span class="sb-icon">&#128100;</span><span class="sb-label">My Profile</span></a>
    <a href="mycourse.php"    class="sb-link"><span class="sb-icon">&#128218;</span><span class="sb-label">My Course</span></a>
    <a href="/JVR/job?search=<?= urlencode($student['coursename'] ?? '') ?>" target="_blank" class="sb-link"><span class="sb-icon">&#128188;</span><span class="sb-label">Jobs</span></a>
    <div class="sb-sep"></div>
    <div class="sb-section">Legal</div>
    <a href="terms.php" class="sb-link"><span class="sb-icon"><?= $terms_accepted?'&#9989;':'&#128196;' ?></span><span class="sb-label">Terms &amp; Conditions</span></a>
  </div>
  <div class="sb-bottom">
    <div class="sb-sep"></div>
    <a href="student_logout.php" class="sb-logout"><span class="sb-icon">&#128682;</span><span class="sb-label">Logout</span></a>
  </div>
</div>

<!-- MOBILE SIDEBAR -->
<div class="sidebar-mobile-panel" id="mobPanel">
  <div class="mob-brand">
    <div class="mob-logo"><img src="../imgs/COI logo.png" alt="COI"></div>
    <div class="mob-brand-text"><strong>CULTURE OF<br>INTERNET</strong><span>Student Portal</span></div>
  </div>
  <div class="mob-nav">
    <div class="mob-label">Main</div>
    <a href="?page=dashboard" class="mob-link <?= $page=='dashboard'?'active':'' ?>"><span class="mob-icon">&#127968;</span>Dashboard</a>
    <a href="?page=profile"   class="mob-link <?= $page=='profile'  ?'active':'' ?>"><span class="mob-icon">&#128100;</span>My Profile</a>
    <a href="mycourse.php"    class="mob-link"><span class="mob-icon">&#128218;</span>My Course</a>
    <a href="/JVR/job?search=<?= urlencode($student['coursename'] ?? '') ?>" target="_blank" class="mob-link"><span class="mob-icon">&#128188;</span>Jobs</a>
    <div class="mob-label">Legal</div>
    <a href="terms.php" class="mob-link"><span class="mob-icon"><?= $terms_accepted?'&#9989;':'&#128196;' ?></span>Terms &amp; Conditions</a>
  </div>
  <div class="mob-bottom">
    <a href="student_logout.php" class="mob-logout"><span class="mob-icon">&#128682;</span>Logout</a>
  </div>
</div>

<!-- MAIN -->
<div class="main">
  <div class="topbar">
    <div class="topbar-left">
      <button class="hamburger" onclick="openMob()">&#9776;</button>
      <div>
        <div class="page-title"><?= $page=='profile'?'My Profile':'Dashboard' ?></div>
        <div class="breadcrumb">COI &rsaquo; <span><?= $page=='profile'?'Profile':'Overview' ?></span></div>
      </div>
    </div>
    <div class="topbar-right">
      <div class="mode-pill <?= ($_SESSION['mode']??'online')=='offline'?'offline':'' ?>">
        <span class="mode-dot"></span>
        <?= ($_SESSION['mode']??'online')=='offline'?'Offline':'Online' ?>
      </div>
      <div class="notif-btn">&#128276;</div>
      <div class="avatar"><?= strtoupper(substr($user['name']??'S',0,1)) ?></div>
    </div>
  </div>

  <div class="content">
  <?php if($page=='dashboard'): ?>
    <?php if($student): ?>

    <!-- ROW 1: WELCOME + INFO TILES -->
    <div class="row1">
      <div class="hero">
        <div class="hero-bg-circle1"></div><div class="hero-bg-circle2"></div>
        <div class="hero-left">
          <div class="hero-eyebrow">Welcome back</div>
          <div class="hero-name"><?= htmlspecialchars($student['name']??$user['name']??'Student') ?> &#128075;</div>
          <div class="hero-sub">Here's your learning overview for today.</div>
          <div class="hero-tags">
            <div class="hero-tag"><span class="hero-tag-dot"></span><?= htmlspecialchars($user['reg_no']??'N/A') ?></div>
            <div class="hero-tag"><span class="hero-tag-dot"></span><?= htmlspecialchars($student['batch_no']??'N/A') ?></div>
            <div class="hero-tag"><span class="hero-tag-dot"></span>Since <?= date('M Y',strtotime($student['startingdate'])) ?></div>
          </div>
        </div>
        <div class="hero-stat">
          <div class="hero-stat-num"><?= $overall_pct ?>%</div>
          <div class="hero-stat-lbl">Overall Progress</div>
        </div>
      </div>
      <div class="info-col">
        <div class="info-tile">
          <div class="info-icon">&#128197;</div>
          <div><div class="info-val"><?= htmlspecialchars($student['startingdate']) ?></div><div class="info-lbl">Start Date</div></div>
        </div>
        <div class="info-tile">
          <div class="info-icon">&#127919;</div>
          <div><div class="info-val"><?= htmlspecialchars($student['completeddate']) ?></div><div class="info-lbl">Completion</div></div>
        </div>
        <div class="info-tile">
          <div class="info-icon">&#11088;</div>
          <div><div class="info-val"><?= htmlspecialchars($student['addonvalue']??'N/A') ?></div><div class="info-lbl">Add-on Value</div></div>
        </div>
      </div>
    </div>

    <!-- ROW 2: ATTENDANCE + COURSE PROGRESS + ASSIGNMENTS -->
    <div class="row2">
      <a href="session.php" class="dash-card">
        <div class="card-header"><div class="card-label">Attendance</div><div class="card-arrow">&#8594;</div></div>
        <div class="card-body">
          <div class="donut-wrap">
            <svg width="104" height="104" viewBox="0 0 104 104">
              <circle cx="52" cy="52" r="42" fill="none" stroke="#f1ead8" stroke-width="11"/>
              <?php if($total>0): ?>
              <?php if($p_count>0): ?><circle cx="52" cy="52" r="42" fill="none" stroke="#10b981" stroke-width="11" stroke-dasharray="<?= ($p_count/$total)*$circumference ?> <?= $circumference ?>" stroke-dashoffset="0"/><?php endif; ?>
              <?php if($a_count>0): ?><circle cx="52" cy="52" r="42" fill="none" stroke="#ef4444" stroke-width="11" stroke-dasharray="<?= ($a_count/$total)*$circumference ?> <?= $circumference ?>" stroke-dashoffset="<?= -($p_count/$total)*$circumference ?>"/><?php endif; ?>
              <?php if($late_count>0): ?><circle cx="52" cy="52" r="42" fill="none" stroke="#f97316" stroke-width="11" stroke-dasharray="<?= ($late_count/$total)*$circumference ?> <?= $circumference ?>" stroke-dashoffset="<?= -(($p_count+$a_count)/$total)*$circumference ?>"/><?php endif; ?>
              <?php if($o_count>0): ?><circle cx="52" cy="52" r="42" fill="none" stroke="#a855f7" stroke-width="11" stroke-dasharray="<?= ($o_count/$total)*$circumference ?> <?= $circumference ?>" stroke-dashoffset="<?= -(($p_count+$a_count+$late_count)/$total)*$circumference ?>"/><?php endif; ?>
              <?php endif; ?>
            </svg>
            <div class="donut-center"><div class="donut-num"><?= $total>0?$att_pct.'%':'—' ?></div><div class="donut-lbl"><?= $total>0?'Present':'No data' ?></div></div>
          </div>
          <div class="legend-row" style="flex-wrap:wrap;gap:3px;">
            <div class="leg-item p"><div class="leg-dot"></div><div class="leg-num"><?= $p_count ?></div><div class="leg-lbl">P</div></div>
            <div class="leg-item a"><div class="leg-dot"></div><div class="leg-num"><?= $a_count ?></div><div class="leg-lbl">A</div></div>
            <div class="leg-item" style="flex:1;text-align:center;padding:6px 3px;border-radius:8px;background:#fff7ed;"><div class="leg-dot" style="background:#f97316;margin:0 auto 3px;width:6px;height:6px;border-radius:50%;"></div><div class="leg-num" style="font-size:12px;font-weight:800;color:#ea580c;"><?= $late_count ?></div><div class="leg-lbl">Late</div></div>
            <div class="leg-item" style="flex:1;text-align:center;padding:6px 3px;border-radius:8px;background:#fdf4ff;"><div class="leg-dot" style="background:#a855f7;margin:0 auto 3px;width:6px;height:6px;border-radius:50%;"></div><div class="leg-num" style="font-size:12px;font-weight:800;color:#9333ea;"><?= $o_count ?></div><div class="leg-lbl">Online</div></div>
            <div class="leg-item l"><div class="leg-dot"></div><div class="leg-num" style="color:#d97706;"><?= $lv_count ?></div><div class="leg-lbl">Leave</div></div>
          </div>
          <div class="card-cta">View Sessions &#8594;</div>
        </div>
      </a>

      <a href="mycourse.php" class="dash-card">
        <div class="card-header"><div class="card-label">Course Progress</div><div class="card-arrow">&#8594;</div></div>
        <div class="card-body">
          <div class="donut-wrap">
            <svg width="104" height="104" viewBox="0 0 104 104">
              <circle cx="52" cy="52" r="42" fill="none" stroke="#f1ead8" stroke-width="11"/>
              <?php if($completed_topics>0): ?><circle cx="52" cy="52" r="42" fill="none" stroke="#3b82f6" stroke-width="11" stroke-dasharray="<?= $course_dash ?> <?= $circumference ?>" stroke-dashoffset="0"/><?php endif; ?>
            </svg>
            <div class="donut-center"><div class="donut-num"><?= $course_pct ?>%</div><div class="donut-lbl">Completed</div></div>
          </div>
          <div class="legend-row">
            <div class="leg-item p"><div class="leg-dot" style="background:#3b82f6;"></div><div class="leg-num"><?= $completed_topics ?>/<?= $total_topics ?></div><div class="leg-lbl">Done</div></div>
            <div class="leg-item a"><div class="leg-dot"></div><div class="leg-num"><?= $remaining_topics ?></div><div class="leg-lbl">Left</div></div>
          </div>
          <div class="card-cta">Go to Course &#8594;</div>
        </div>
      </a>

      <a href="mycourse.php" class="dash-card">
        <div class="card-header"><div class="card-label">Assignments</div><div class="card-arrow">&#8594;</div></div>
        <div class="card-body">
          <div class="donut-wrap">
            <svg width="104" height="104" viewBox="0 0 104 104">
              <circle cx="52" cy="52" r="42" fill="none" stroke="#f1ead8" stroke-width="11"/>
              <?php if($assignment_completed>0): ?><circle cx="52" cy="52" r="42" fill="none" stroke="#8b5cf6" stroke-width="11" stroke-dasharray="<?= $assignment_dash ?> <?= $circumference ?>" stroke-dashoffset="0"/><?php endif; ?>
            </svg>
            <div class="donut-center"><div class="donut-num"><?= $assignment_pct ?>%</div><div class="donut-lbl">Submitted</div></div>
          </div>
          <div class="legend-row">
            <div class="leg-item p"><div class="leg-dot" style="background:#8b5cf6;"></div><div class="leg-num"><?= $assignment_completed ?>/<?= $assignment_total ?></div><div class="leg-lbl">Done</div></div>
            <div class="leg-item a"><div class="leg-dot"></div><div class="leg-num"><?= $assignment_remaining ?></div><div class="leg-lbl">Left</div></div>
          </div>
          <div class="card-cta">View Assignments &#8594;</div>
        </div>
      </a>
    </div>

    <!-- ROW 3: ENROLLED COURSE + ATTENDANCE POLICY -->
    <div class="row3">

      <!-- ENROLLED COURSE -->
      <div class="dash-card">
        <div class="course-card-inner">
          <div class="card-label" style="margin-bottom:10px;">Enrolled Course</div>
          <div class="course-active-badge"><span class="course-active-dot"></span>Active</div>
          <div class="course-title"><?= htmlspecialchars($student['coursename']) ?></div>
          <div class="course-meta">
            <div class="course-meta-row"><span class="course-meta-icon">&#127979;</span><?= htmlspecialchars($student['batch_no']??'N/A') ?></div>
            <div class="course-meta-row"><span class="course-meta-icon">&#128197;</span><?= htmlspecialchars($student['startingdate']) ?></div>
          </div>
        </div>
      </div>

      <!-- ATTENDANCE POLICY -->
      <?php
        $off_pct        = $policy_leave_allowed > 0 ? min(100, round(($policy_leave_used / $policy_leave_allowed) * 100)) : 0;
        $online_pct_bar = $policy_online_allowed > 0 ? min(100, round(($policy_online_used / $policy_online_allowed) * 100)) : 0;
        $off_bar   = $policy_leave_used >= $policy_leave_allowed ? 'red' : ($off_pct >= 75 ? 'amber' : 'green');
        $on_bar    = $policy_online_exceeded ? 'purple' : 'blue';
        $off_cls   = $policy_leave_used >= $policy_leave_allowed ? 'over' : ($off_pct >= 75 ? 'warn' : 'safe');
        $on_cls    = $policy_online_exceeded ? 'over' : 'safe';
        $badge_cls = $policy_fine > 0 ? 'danger' : ($off_pct >= 75 || $policy_online_exceeded ? 'warn' : 'ok');
        $badge_txt = $policy_fine > 0 ? '⚠️ Fine Active' : ($off_pct >= 75 ? '⚠️ Near Limit' : '✓ Good');
      ?>
      <div class="dash-card">
        <div class="policy-inner">
          <div class="policy-top">
            <div>
              <div class="policy-title">📋 Attendance Policy</div>
              <div class="policy-duration"><?= $policy_leave_allowed==40 ? '1 Year Course' : '6 Month Course' ?></div>
            </div>
            <span class="policy-badge <?= $badge_cls ?>"><?= $badge_txt ?></span>
          </div>

          <!-- Off Days -->
          <div class="policy-row">
            <div class="policy-row-head">
              <div class="policy-lbl">🔴 Off Days (Absent + Leave)</div>
              <div class="policy-cnt <?= $off_cls ?>">
                <?= $policy_leave_used ?>/<?= $policy_leave_allowed ?>
                <?php if($policy_extra_off>0): ?><span style="font-size:9px;"> +<?= $policy_extra_off ?></span><?php endif; ?>
              </div>
            </div>
            <div class="policy-bar"><div class="policy-bar-fill <?= $off_bar ?>" style="width:<?= $off_pct ?>%"></div></div>
            <div class="policy-hint"><?= $policy_leave_used < $policy_leave_allowed ? ($policy_leave_allowed - $policy_leave_used).' remaining' : 'Limit reached' ?></div>
          </div>

          <!-- Online -->
          <div class="policy-row">
            <div class="policy-row-head">
              <div class="policy-lbl">💻 Online Classes</div>
              <div class="policy-cnt <?= $on_cls ?>">
                <?= $policy_online_used ?>/<?= $policy_online_allowed ?>
                <?php if($policy_extra_online>0): ?><span style="font-size:9px;"> +<?= $policy_extra_online ?></span><?php endif; ?>
              </div>
            </div>
            <div class="policy-bar"><div class="policy-bar-fill <?= $on_bar ?>" style="width:<?= $online_pct_bar ?>%"></div></div>
            <div class="policy-hint"><?= !$policy_online_exceeded ? ($policy_online_allowed - $policy_online_used).' remaining' : 'No more online classes allowed' ?></div>
          </div>

          <?php if($policy_online_exceeded): ?>
          <div class="policy-online-warn">🚫 Online class limit khatam — aur online nahi le sakte.</div>
          <?php endif; ?>

          <div class="policy-divider"></div>

          <!-- Fine -->
          <div class="policy-fine <?= $policy_fine > 0 ? 'due' : 'zero' ?>">
            <div class="policy-fine-left">
              <div class="policy-fine-tag <?= $policy_fine > 0 ? 'due' : 'zero' ?>"><?= $policy_fine > 0 ? '💰 Fine Due' : '✓ No Fine' ?></div>
              <?php if($policy_fine > 0): ?>
              <div class="policy-fine-detail"><?= $policy_extra_off ?> extra &times; ₹20/day</div>
              <?php endif; ?>
            </div>
            <div class="policy-fine-amt <?= $policy_fine > 0 ? 'due' : 'zero' ?>">₹<?= number_format($policy_fine) ?></div>
          </div>

        </div>
      </div>

    </div>

    <?php else: ?>
    <div class="empty-state"><div class="ei">&#128205;</div><h3>No Details Found</h3><p>Contact admin to add your admission details.</p></div>
    <?php endif; ?>

  <?php elseif($page=='profile'): ?>
    <div class="profile-hero">
      <div class="p-avatar"><?= strtoupper(substr($student['name']??'S',0,1)) ?></div>
      <div>
        <div class="p-name"><?= htmlspecialchars($student['name']??'') ?></div>
        <div class="p-email"><?= htmlspecialchars($student['gmail']??'') ?></div>
        <div class="p-chips">
          <span class="p-chip">&#128203; <?= htmlspecialchars($user['reg_no']) ?></span>
          <span class="p-chip">&#127891; <?= htmlspecialchars($student['qualification']??'') ?></span>
          <span class="p-chip">&#127979; <?= htmlspecialchars($student['batch_no']??'N/A') ?></span>
        </div>
      </div>
    </div>
    <div class="detail-grid">
      <div class="detail-card">
        <div class="detail-head">&#128100; Personal Information</div>
        <div class="d-row"><span class="d-lbl">Full Name</span><span class="d-val"><?= htmlspecialchars($student['name']) ?></span></div>
        <div class="d-row"><span class="d-lbl">Gender</span><span class="d-val"><?= htmlspecialchars($student['gender']??'—') ?></span></div>
        <div class="d-row"><span class="d-lbl">Date of Birth</span><span class="d-val"><?= htmlspecialchars($student['dob']) ?></span></div>
        <div class="d-row"><span class="d-lbl">Qualification</span><span class="d-val"><?= htmlspecialchars($student['qualification']) ?></span></div>
        <div class="d-row"><span class="d-lbl">Address</span><span class="d-val"><?= htmlspecialchars($student['address']??'—') ?></span></div>
        <div class="d-row"><span class="d-lbl">Reg. No.</span><span class="d-val gold"><?= htmlspecialchars($user['reg_no']) ?></span></div>
      </div>
      <div class="detail-card">
        <div class="detail-head">&#128222; Contact Information</div>
        <div class="d-row"><span class="d-lbl">Phone</span><span class="d-val"><?= htmlspecialchars($student['phoneno']) ?></span></div>
        <div class="d-row"><span class="d-lbl">WhatsApp</span><span class="d-val"><?= htmlspecialchars($student['whatsapp']) ?></span></div>
        <div class="d-row"><span class="d-lbl">Email</span><span class="d-val"><?= htmlspecialchars($student['gmail']) ?></span></div>
      </div>
      <div class="detail-card">
        <div class="detail-head">&#128218; Course Information</div>
        <div class="d-row"><span class="d-lbl">Course</span><span class="d-val gold"><?= htmlspecialchars($student['coursename']) ?></span></div>
        <div class="d-row"><span class="d-lbl">Batch</span><span class="d-val"><?= htmlspecialchars($student['batch_no']??'—') ?></span></div>
        <div class="d-row"><span class="d-lbl">Start Date</span><span class="d-val"><?= htmlspecialchars($student['startingdate']) ?></span></div>
        <div class="d-row"><span class="d-lbl">Completion</span><span class="d-val"><?= htmlspecialchars($student['completeddate']) ?></span></div>
        <div class="d-row"><span class="d-lbl">Add-on Value</span><span class="d-val"><?= htmlspecialchars($student['addonvalue']) ?></span></div>
      </div>
      <div class="detail-card">
        <div class="detail-head">&#128106; Parent Information</div>
        <div class="d-row"><span class="d-lbl">Parent Name</span><span class="d-val"><?= htmlspecialchars($student['parentname']) ?></span></div>
        <div class="d-row"><span class="d-lbl">Parent Contact</span><span class="d-val"><?= htmlspecialchars($student['parentsno']) ?></span></div>
      </div>
    </div>
  <?php endif; ?>
  </div>
</div>
<script>
function openMob(){document.getElementById("mobPanel").classList.add("open");document.getElementById("sOverlay").classList.add("active");}
function closeMob(){document.getElementById("mobPanel").classList.remove("open");document.getElementById("sOverlay").classList.remove("active");}
</script>
</body>
</html>