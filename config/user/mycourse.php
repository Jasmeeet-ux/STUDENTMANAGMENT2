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
$reg_no = $user['reg_no'];

// Get student details
$stmt2 = $pdo->prepare("SELECT * FROM user_details WHERE reg_no = ?");
$stmt2->execute([$reg_no]);
$student = $stmt2->fetch(PDO::FETCH_ASSOC);

// ── Multi-course: load all enrollments ──
$all_enrollments = [];
try {
    $enr = $pdo->prepare("SELECT se.*, b.course_id as enr_course_id FROM student_enrollments se LEFT JOIN batches b ON b.batch_name=se.batch_no WHERE se.reg_no=? ORDER BY se.id ASC");
    $enr->execute([$reg_no]);
    $all_enrollments = $enr->fetchAll(PDO::FETCH_ASSOC);
} catch(Exception $e) { $all_enrollments = []; }

// Switch course if requested
if (isset($_GET['switch_course']) && !isset($_POST['action'])) {
    $sid = (int)$_GET['switch_course'];
    $chk = $pdo->prepare("SELECT id FROM student_enrollments WHERE id=? AND reg_no=?");
    $chk->execute([$sid, $reg_no]);
    if ($chk->fetch()) { $_SESSION['active_enrollment_id'] = $sid; }
    header("Location: mycourse.php"); exit;
}

// Determine active enrollment
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

// Use active enrollment's batch — fallback to user_details
$batch_no = $active_enrollment['batch_no'] ?? ($student['batch_no'] ?? '');

/* ── AJAX: Save topic complete ── */
if (isset($_POST['action']) && $_POST['action'] === 'mark_complete') {
    $topic  = $_POST['topic']  ?? '';
    $module = $_POST['module'] ?? '';
    if ($topic && $module) {
        $pdo->prepare("INSERT IGNORE INTO course_progress (reg_no, topic_name, module_name) VALUES (?,?,?)")
            ->execute([$reg_no, $topic, $module]);
        echo "saved";
    } else { echo "error"; }
    exit;
}

/* ── AJAX: Get completed topics ── */
if (isset($_GET['get_progress'])) {
    $stmt = $pdo->prepare("SELECT topic_name FROM course_progress WHERE reg_no = ?");
    $stmt->execute([$reg_no]);
    echo json_encode(array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'topic_name'));
    exit;
}

// Load completed topics
$stmt3 = $pdo->prepare("SELECT topic_name FROM course_progress WHERE reg_no = ?");
$stmt3->execute([$reg_no]);
$completedTopics = array_column($stmt3->fetchAll(PDO::FETCH_ASSOC), 'topic_name');

// ── Get course_id from student's batch ──
$course_id = null;
if (!empty($batch_no)) {
    $stmtB = $pdo->prepare("SELECT course_id FROM batches WHERE batch_name = ? LIMIT 1");
    $stmtB->execute([$batch_no]);
    $batchRow = $stmtB->fetch(PDO::FETCH_ASSOC);
    $course_id = $batchRow['course_id'] ?? null;
}

// Load modules by course_id
$modules_data = [];
$has_modules = false;
try {
    if ($course_id) {
        $stmtM = $pdo->prepare("SELECT module_name, topic_name, topic_order, video_url FROM course_modules WHERE course_id = ? ORDER BY topic_order ASC");
        $stmtM->execute([$course_id]);
        $rows = $stmtM->fetchAll(PDO::FETCH_ASSOC);
        foreach ($rows as $row) {
            $modules_data[$row['module_name']][] = [
                'topic'     => $row['topic_name'],
                'order'     => $row['topic_order'],
                'video_url' => $row['video_url'] ?? ''
            ];
        }
        $has_modules = !empty($modules_data);
    }
} catch(Exception $e) { $has_modules = false; }

// Total topics count
$total_topics = 0;
foreach ($modules_data as $topics) $total_topics += count($topics);

// Flat list for next/prev navigation
$all_topics_flat = [];
foreach ($modules_data as $mod => $topics) {
    foreach ($topics as $t) {
        $all_topics_flat[] = ['module' => $mod, 'topic' => $t['topic'], 'video_url' => $t['video_url']];
    }
}

// ── Base URL for file downloads ──
$base_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
            . '://' . $_SERVER['HTTP_HOST']
            . rtrim(dirname(dirname($_SERVER['SCRIPT_NAME'])), '/');

// ── Load assignments for this course ──
$assignments_map = [];
if ($course_id) {
    try {
        $stmtA = $pdo->prepare("SELECT topic_name, title, instructions, file_path FROM topic_assignments WHERE course_id = ?");
        $stmtA->execute([$course_id]);
        foreach ($stmtA->fetchAll(PDO::FETCH_ASSOC) as $arow) {
            if (!empty($arow['file_path'])) {
                $arow['download_url'] = $base_url . '/' . ltrim($arow['file_path'], '/');
            } else {
                $arow['download_url'] = '';
            }
            $assignments_map[$arow['topic_name']] = $arow;
        }
    } catch(Exception $e) {}
}

// ── Load student's submitted assignments ──
$submitted_assignments = [];
try {
    $stmtSub = $pdo->prepare("SELECT topic_name, file_path, submitted_at, grade, feedback FROM assignment_submissions WHERE reg_no = ?");
    $stmtSub->execute([$reg_no]);
    foreach ($stmtSub->fetchAll(PDO::FETCH_ASSOC) as $subrow) {
        $submitted_assignments[$subrow['topic_name']] = $subrow;
    }
} catch(Exception $e) {}

// ── Handle assignment submission ──
if (isset($_POST['action']) && $_POST['action'] === 'submit_assignment') {
    $topic_name = trim($_POST['topic'] ?? '');
    
    if (!$topic_name || !$course_id) {
        echo "error_no_course"; exit;
    }
    if (empty($_FILES['assignment_file']['name'])) {
        echo "error_no_file"; exit;
    }
    if ($_FILES['assignment_file']['error'] !== UPLOAD_ERR_OK) {
        echo "error_upload_" . $_FILES['assignment_file']['error']; exit;
    }
    
    $upload_dir = __DIR__ . '/../../uploads/student_assignments/';
    
    if (!is_dir($upload_dir)) {
        @mkdir($upload_dir, 0755, true);
    }
    
    if (!is_dir($upload_dir)) {
        echo "error_mkdir_" . $upload_dir; exit;
    }
    
    $ext = strtolower(pathinfo($_FILES['assignment_file']['name'], PATHINFO_EXTENSION));
    $allowed = ['pdf','jpg','jpeg','png','doc','docx'];
    if (!in_array($ext, $allowed)) {
        echo "error_filetype"; exit;
    }
    
    $fname = 'sub_' . preg_replace('/[^a-z0-9]/', '', strtolower($reg_no)) . '_' . md5($topic_name) . '.' . $ext;
    $full_path = $upload_dir . $fname;
    
    if (move_uploaded_file($_FILES['assignment_file']['tmp_name'], $full_path)) {
        $file_path = 'uploads/student_assignments/' . $fname;
        $pdo->prepare("INSERT INTO assignment_submissions (reg_no, course_id, topic_name, file_path, submitted_at)
                       VALUES (?,?,?,?,NOW())
                       ON DUPLICATE KEY UPDATE file_path=VALUES(file_path), submitted_at=NOW()")
            ->execute([$reg_no, $course_id, $topic_name, $file_path]);
        echo "submitted"; exit;
    } else {
        echo "error_move|dir:" . $upload_dir . "|tmp:" . $_FILES['assignment_file']['tmp_name']; exit;
    }
}

// ── Load MCQs for this course ──
$mcq_map = [];
if ($course_id) {
    try {
        $stmtMCQ = $pdo->prepare("SELECT topic_name, id, question, option_a, option_b, option_c, option_d, correct_ans FROM topic_mcq WHERE course_id = ? ORDER BY id ASC");
        $stmtMCQ->execute([$course_id]);
        foreach ($stmtMCQ->fetchAll(PDO::FETCH_ASSOC) as $qrow) {
            $mcq_map[$qrow['topic_name']][] = $qrow;
        }
    } catch(Exception $e) {}
}

// ── Load student's MCQ attempts ──
$mcq_attempts = [];
try {
    $stmtAttempt = $pdo->prepare("SELECT topic_name, score, total FROM mcq_attempts WHERE reg_no = ?");
    $stmtAttempt->execute([$reg_no]);
    foreach ($stmtAttempt->fetchAll(PDO::FETCH_ASSOC) as $arow) {
        $mcq_attempts[$arow['topic_name']] = $arow;
    }
} catch(Exception $e) {}

// ── Handle MCQ submission ──
if (isset($_POST['action']) && $_POST['action'] === 'submit_mcq') {
    $topic_name = trim($_POST['topic'] ?? '');
    $answers    = $_POST['answers'] ?? [];
    if ($topic_name && $course_id && !empty($mcq_map[$topic_name])) {
        $checkAttempt = $pdo->prepare("SELECT id FROM mcq_attempts WHERE reg_no = ? AND topic_name = ?");
        $checkAttempt->execute([$reg_no, $topic_name]);
        if ($checkAttempt->fetch()) {
            echo json_encode(['error' => 'already_attempted']); exit;
        }
        $questions = $mcq_map[$topic_name];
        $score = 0;
        $total = count($questions);
        foreach ($questions as $q) {
            $selected = $answers[$q['id']] ?? '';
            if (strtoupper($selected) === strtoupper($q['correct_ans'])) $score++;
        }
        $pdo->prepare("INSERT INTO mcq_attempts (reg_no, course_id, topic_name, score, total)
                       VALUES (?,?,?,?,?)")
            ->execute([$reg_no, $course_id, $topic_name, $score, $total]);
        echo json_encode(['score' => $score, 'total' => $total]); exit;
    }
    echo json_encode(['error' => 1]); exit;
}

function h($v) { return htmlspecialchars($v ?? '', ENT_QUOTES, 'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Course | COI</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{
  --navy:#0d1b2a;--navy2:#1a2e45;--gold:#c39b5f;--gold-l:#d4af72;
  --gold-pale:#f6edd9;--bg:#f0f2f5;--white:#fff;--text:#0d1b2a;
  --muted:#7a8899;--border:#e4ddd2;--green:#10b981;--red:#ef4444;
  --amber:#f59e0b;--blue:#3b82f6;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:"DM Sans",sans-serif;background:var(--bg);color:var(--text);}

/* ── TOPBAR ── */
.topbar{height:56px;background:var(--navy);border-bottom:1px solid rgba(195,155,95,0.15);display:flex;align-items:center;padding:0 24px;gap:14px;position:sticky;top:0;z-index:100;}
.logo{display:flex;align-items:center;gap:9px;text-decoration:none;}
.logo-icon{width:30px;height:30px;border-radius:8px;background:rgba(195,155,95,0.15);border:1px solid rgba(195,155,95,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold-l);font-weight:800;font-size:14px;}
.logo-text{font-family:"Sora",sans-serif;font-size:13px;font-weight:700;color:#fff;}
.topbar-divider{width:1px;height:20px;background:rgba(195,155,95,0.15);}
.home-link{display:flex;align-items:center;gap:6px;text-decoration:none;color:rgba(255,255,255,0.5);font-size:12.5px;font-weight:500;padding:5px 10px;border-radius:7px;transition:all 0.15s;}
.home-link:hover{background:rgba(195,155,95,0.1);color:var(--gold-l);}
.topbar-right{margin-left:auto;display:flex;align-items:center;gap:10px;}
.stu-name{font-size:12.5px;font-weight:600;color:rgba(255,255,255,0.6);}
.btn-logout{padding:6px 14px;background:rgba(239,68,68,0.1);color:#f87171;border-radius:8px;text-decoration:none;font-size:12px;font-weight:700;border:1px solid rgba(239,68,68,0.2);}
.btn-logout:hover{background:rgba(239,68,68,0.2);}

/* ── LAYOUT ── */
.layout{display:flex;height:calc(100vh - 56px);}

/* ── SIDEBAR ── */
.sidebar{width:290px;flex-shrink:0;background:var(--navy);border-right:1px solid rgba(195,155,95,0.12);display:flex;flex-direction:column;overflow:hidden;transition:transform 0.3s ease;}
.sidebar-head{padding:18px 18px 14px;border-bottom:1px solid rgba(195,155,95,0.1);}
.sidebar-head h3{font-family:"Sora",sans-serif;font-size:13px;font-weight:700;color:var(--gold-l);margin-bottom:3px;}
.sidebar-head-sub{font-size:11px;color:rgba(255,255,255,0.3);margin-bottom:10px;}
.progress-bar-wrap{background:rgba(255,255,255,0.08);border-radius:999px;height:5px;margin-top:6px;}
.progress-bar-fill{height:5px;border-radius:999px;background:linear-gradient(90deg,var(--gold),var(--gold-l));transition:width 0.4s;}
.progress-label{display:flex;justify-content:space-between;font-size:10px;color:rgba(255,255,255,0.3);margin-top:4px;}
.sidebar-scroll{flex:1;overflow-y:auto;padding:8px 0;}
.sidebar-scroll::-webkit-scrollbar{width:2px;}
.sidebar-scroll::-webkit-scrollbar-thumb{background:rgba(195,155,95,0.2);border-radius:3px;}
.mod-group{margin-bottom:2px;}
.mod-header{display:flex;align-items:center;gap:9px;padding:10px 16px;cursor:pointer;user-select:none;transition:background 0.15s;}
.mod-header:hover{background:rgba(195,155,95,0.06);}
.mod-check{width:17px;height:17px;border-radius:50%;border:2px solid rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:9px;color:transparent;flex-shrink:0;transition:all 0.2s;}
.mod-check.done{background:var(--green);border-color:var(--green);color:#fff;}
.mod-title{font-size:12.5px;font-weight:600;color:rgba(255,255,255,0.7);flex:1;}
.mod-count{font-size:10px;color:rgba(195,155,95,0.6);background:rgba(195,155,95,0.08);padding:2px 7px;border-radius:20px;}
.mod-arrow{font-size:10px;color:rgba(255,255,255,0.25);transition:transform 0.2s;}
.mod-group.open .mod-arrow{transform:rotate(90deg);}
.topics-list{display:none;padding:0 0 4px;}
.mod-group.open .topics-list{display:block;}
.topic-item{display:flex;align-items:center;gap:9px;padding:8px 16px 8px 42px;cursor:pointer;transition:all 0.15s;border-left:2px solid transparent;}
.topic-item:hover{background:rgba(195,155,95,0.05);}
.topic-item.active{background:rgba(195,155,95,0.1);border-left-color:var(--gold);}
.topic-item.done .t-check{background:var(--green);border-color:var(--green);color:#fff;}
.t-check{width:15px;height:15px;border-radius:50%;border:1.5px solid rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:8px;color:transparent;flex-shrink:0;transition:all 0.2s;}
.t-name{font-size:12px;color:rgba(255,255,255,0.5);flex:1;line-height:1.4;}
.topic-item.active .t-name{color:var(--gold-l);font-weight:600;}

/* ── CONTENT AREA ── */
.content{flex:1;overflow-y:auto;display:flex;flex-direction:column;min-width:0;background:var(--bg);}
.content::-webkit-scrollbar{width:4px;}
.content::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px;}
.no-modules{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px;text-align:center;color:var(--muted);}
.no-modules .nm-icon{font-size:52px;margin-bottom:14px;}
.no-modules h3{font-family:"Sora",sans-serif;font-size:17px;font-weight:700;color:var(--navy);margin-bottom:6px;}
.no-modules p{font-size:13.5px;line-height:1.6;}

/* ── VIDEO SECTION ── */
.video-section{padding:22px 26px 0;}
.video-breadcrumb{font-size:11.5px;color:var(--muted);margin-bottom:8px;}
.video-breadcrumb span{color:var(--gold);font-weight:600;}
.video-title{font-family:"Sora",sans-serif;font-size:19px;font-weight:700;color:var(--navy);margin-bottom:14px;}
.video-container{display:flex;gap:18px;margin-bottom:18px;}
.video-wrap{width:570px;flex-shrink:0;}
.video-thumb{width:100%;aspect-ratio:16/9;border-radius:14px;background:var(--navy);overflow:hidden;position:relative;cursor:pointer;}
.video-thumb img{width:100%;height:100%;object-fit:cover;}
.play-btn{position:absolute;inset:0;display:flex;align-items:center;justify-content:center;background:rgba(0,0,0,0.35);transition:background 0.2s;}
.play-btn:hover{background:rgba(0,0,0,0.5);}
.play-btn svg{width:54px;height:54px;}
.video-iframe-wrap{width:100%;aspect-ratio:16/9;border-radius:14px;overflow:hidden;display:none;}
.video-iframe-wrap iframe{width:100%;height:100%;border:none;}
.no-video-box{width:100%;aspect-ratio:16/9;border-radius:14px;background:var(--white);border:2px dashed var(--border);display:flex;flex-direction:column;align-items:center;justify-content:center;color:var(--muted);gap:8px;}
.no-video-box .nv-icon{font-size:34px;}
.no-video-box p{font-size:13px;font-weight:500;}
.video-side{flex:1;display:flex;flex-direction:column;gap:11px;min-width:0;}
.detail-card{background:var(--white);border-radius:12px;border:1px solid var(--border);padding:15px;box-shadow:0 1px 6px rgba(13,27,42,0.05);}
.detail-card h4{font-size:10.5px;font-weight:700;color:var(--muted);text-transform:uppercase;letter-spacing:0.6px;margin-bottom:9px;}
.detail-card ul{padding-left:16px;}
.detail-card ul li{font-size:12.5px;color:var(--text);margin-bottom:5px;line-height:1.5;}
.detail-card p{font-size:12.5px;color:var(--text);line-height:1.6;}

/* ── MARK AREA ── */
.mark-area{padding:0 26px 20px;display:flex;align-items:center;gap:12px;flex-wrap:wrap;}
.btn-mark{padding:10px 26px;background:linear-gradient(135deg,var(--navy),var(--navy2));color:var(--gold-l);border:1px solid rgba(195,155,95,0.3);border-radius:10px;font-size:13.5px;font-weight:700;cursor:pointer;font-family:"DM Sans",sans-serif;transition:all 0.15s;}
.btn-mark:hover{background:var(--navy2);border-color:var(--gold);}
.btn-mark:disabled{background:var(--border);color:var(--muted);cursor:default;border-color:transparent;}
.btn-mark.done-btn{background:linear-gradient(135deg,#22c55e,#16a34a);color:#fff;border-color:transparent;}
.nav-btns{display:flex;gap:8px;margin-left:auto;}
.btn-nav{padding:9px 16px;background:var(--white);color:var(--muted);border:1px solid var(--border);border-radius:9px;font-size:12.5px;font-weight:600;cursor:pointer;font-family:"DM Sans",sans-serif;transition:all 0.15s;}
.btn-nav:hover{background:var(--bg);border-color:var(--gold);}
.btn-nav.next{background:var(--navy);color:var(--gold-l);border-color:rgba(195,155,95,0.3);}
.btn-nav.next:hover{background:var(--navy2);}
.pick-topic{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:40px;text-align:center;color:var(--muted);}
.pick-topic .pt-icon{font-size:50px;margin-bottom:12px;}
.pick-topic h3{font-family:"Sora",sans-serif;font-size:16px;font-weight:700;color:var(--navy);margin-bottom:5px;}
.toast{position:fixed;bottom:26px;right:26px;background:var(--navy);color:var(--gold-l);padding:11px 20px;border-radius:10px;font-size:13px;font-weight:600;box-shadow:0 4px 20px rgba(0,0,0,0.25);border:1px solid rgba(195,155,95,0.2);display:none;z-index:999;}
.toast.show{display:flex;align-items:center;gap:8px;}

/* ── COURSE SWITCHER ── */
.course-switcher{background:var(--navy);border-bottom:1px solid rgba(195,155,95,0.12);padding:10px 22px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
.cs-label{font-size:10px;font-weight:700;color:rgba(195,155,95,0.4);text-transform:uppercase;letter-spacing:1px;white-space:nowrap;}
.cs-pill{display:inline-flex;align-items:center;gap:7px;padding:6px 14px;border-radius:20px;font-size:12px;font-weight:600;text-decoration:none;border:1px solid rgba(195,155,95,0.2);color:rgba(255,255,255,0.5);background:rgba(195,155,95,0.05);transition:all 0.15s;white-space:nowrap;}
.cs-pill:hover{border-color:var(--gold);color:var(--gold-l);background:rgba(195,155,95,0.1);}
.cs-pill.active{background:rgba(195,155,95,0.15);color:var(--gold-l);border-color:var(--gold);}
.cs-dot{width:5px;height:5px;border-radius:50%;background:currentColor;opacity:0.7;flex-shrink:0;}

/* ── ASSIGNMENT CARD ── */
.assign-card{background:var(--white);border:1.5px solid var(--border);border-left:3px solid var(--amber);border-radius:14px;padding:18px 20px;}
.assign-header{display:flex;align-items:center;gap:10px;margin-bottom:10px;}
.assign-badge{background:var(--gold-pale);color:#7a5c2a;font-size:11px;font-weight:800;padding:3px 10px;border-radius:20px;white-space:nowrap;}
.assign-title{font-family:"Sora",sans-serif;font-size:14px;font-weight:700;color:var(--navy);}
.assign-instructions{font-size:12.5px;color:var(--text);line-height:1.6;margin-bottom:12px;background:var(--gold-pale);padding:10px 14px;border-radius:8px;border-left:3px solid var(--gold);}
.btn-download-assign{display:inline-flex;align-items:center;gap:6px;background:var(--white);border:1.5px solid var(--gold);color:var(--navy);padding:7px 16px;border-radius:8px;font-size:12.5px;font-weight:700;text-decoration:none;margin-bottom:12px;}
.btn-download-assign:hover{background:var(--gold-pale);}
.assign-upload{display:flex;align-items:center;gap:12px;margin-top:4px;flex-wrap:wrap;}
.btn-upload-assign{display:inline-flex;align-items:center;gap:6px;background:var(--navy);color:var(--gold-l);padding:9px 18px;border-radius:8px;font-size:13px;font-weight:700;border:1px solid rgba(195,155,95,0.3);}
.btn-upload-assign:hover{background:var(--navy2);}
.assign-hint{font-size:11.5px;color:var(--muted);}
.assign-submitted{display:flex;align-items:flex-start;gap:12px;background:#f0fdf4;border:1.5px solid #86efac;border-radius:10px;padding:12px 14px;margin-top:4px;flex-wrap:wrap;}
.assign-submitted-icon{font-size:22px;flex-shrink:0;}
.assign-sub-label{font-size:13px;font-weight:700;color:#166534;}
.assign-sub-date{font-size:11.5px;color:#4ade80;margin-top:2px;}
.assign-grade{font-size:12.5px;color:#15803d;margin-top:4px;}
.assign-feedback{font-size:12px;color:#166534;font-style:italic;margin-top:3px;}
.btn-resubmit{margin-left:auto;background:#fff;border:1.5px solid #86efac;color:#166534;padding:6px 12px;border-radius:7px;font-size:12px;font-weight:700;white-space:nowrap;}

/* ── MCQ CARD ── */
.mcq-card{background:var(--white);border:1.5px solid var(--border);border-left:3px solid var(--navy);border-radius:14px;padding:18px 20px;}
.mcq-header{display:flex;align-items:center;gap:10px;margin-bottom:14px;}
.mcq-badge{background:rgba(13,27,42,0.07);color:var(--navy);font-size:11px;font-weight:800;padding:3px 10px;border-radius:20px;white-space:nowrap;}
.mcq-title{font-family:"Sora",sans-serif;font-size:14px;font-weight:700;color:var(--navy);}
.mcq-start-box{text-align:center;padding:16px 0 6px;}
.mcq-start-icon{font-size:32px;margin-bottom:8px;}
.mcq-start-text{font-size:13px;color:var(--muted);margin-bottom:14px;}
.btn-start-mcq{background:var(--navy);color:var(--gold-l);border:1px solid rgba(195,155,95,0.3);padding:10px 28px;border-radius:9px;font-size:13px;font-weight:700;cursor:pointer;font-family:"DM Sans",sans-serif;}
.btn-start-mcq:hover{background:var(--navy2);}
.mcq-result-box{border:1.5px solid var(--border);border-radius:10px;padding:14px 18px;text-align:center;margin-bottom:12px;}
.mcq-score{font-family:"Sora",sans-serif;font-size:28px;font-weight:800;}
.mcq-score-label{font-size:13px;font-weight:700;margin-top:2px;}
.mcq-score-sub{font-size:12px;margin-top:4px;color:var(--muted);}
.btn-retry-mcq{background:var(--white);border:1.5px solid var(--border);color:var(--navy);padding:8px 18px;border-radius:8px;font-size:12.5px;font-weight:700;cursor:pointer;font-family:"DM Sans",sans-serif;}
.mcq-question{margin-bottom:16px;background:var(--bg);border-radius:10px;padding:14px 16px;border:1px solid var(--border);}
.mcq-q-text{font-size:13.5px;font-weight:600;color:var(--navy);margin-bottom:10px;line-height:1.5;}
.mcq-options{display:flex;flex-direction:column;gap:7px;}
.mcq-option{display:flex;align-items:center;gap:10px;padding:8px 12px;border:1.5px solid var(--border);border-radius:8px;cursor:pointer;transition:all 0.15s;background:var(--white);}
.mcq-option:hover{background:var(--gold-pale);border-color:var(--gold);}
.mcq-option input{accent-color:var(--navy);}
.mcq-opt-letter{font-size:10px;font-weight:800;background:rgba(13,27,42,0.07);color:var(--navy);width:20px;height:20px;border-radius:50%;display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.mcq-opt-text{font-size:13px;color:var(--text);}
.btn-submit-mcq{width:100%;padding:11px;background:var(--navy);color:var(--gold-l);border:1px solid rgba(195,155,95,0.3);border-radius:9px;font-size:14px;font-weight:700;cursor:pointer;font-family:"DM Sans",sans-serif;margin-top:6px;}
.btn-submit-mcq:hover{background:var(--navy2);}

/* ── MOBILE SIDEBAR TOGGLE ── */
.sidebar-toggle{display:none;align-items:center;justify-content:center;width:34px;height:34px;background:rgba(195,155,95,0.1);border:1px solid rgba(195,155,95,0.2);border-radius:8px;cursor:pointer;color:var(--gold-l);font-size:17px;flex-shrink:0;}
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.55);z-index:299;}
.sidebar-overlay.active{display:block;}

/* ══════════════════════════════════════════
   RESPONSIVE
══════════════════════════════════════════ */

/* ── Tablet: 768–1024px ── */
@media(max-width:1024px){
  .sidebar{width:260px;}
  .video-wrap{width:420px;}
  .video-section{padding:20px 20px 0;}
  .mark-area{padding:0 20px 16px;}
  #assignmentSection,#mcqSection{padding-left:20px!important;padding-right:20px!important;}
}

/* ── Mobile: ≤768px ── */
@media(max-width:768px){

  /* Topbar: tighter on mobile */
  .topbar{padding:0 12px;gap:8px;height:50px;}
  .logo-text{display:none;}          /* hide text, keep icon */
  .topbar-divider{display:none;}
  .home-link span{display:none;}      /* hide text, keep emoji */
  .home-link{padding:5px 7px;}
  .stu-name{display:none;}
  .btn-logout{padding:6px 10px;font-size:11.5px;}
  .sidebar-toggle{display:flex;}

  /* Sidebar: slide-over drawer */
  .layout{height:calc(100vh - 50px);position:relative;}
  .sidebar{
    position:fixed;
    top:50px;
    left:0;
    height:calc(100vh - 50px);
    width:280px;
    z-index:300;
    transform:translateX(-100%);
    box-shadow:4px 0 20px rgba(0,0,0,0.15);
  }
  .sidebar.open{transform:translateX(0);}

  /* Content: full width */
  .content{width:100%;overflow-x:hidden;}

  /* Video section: stack vertically */
  .video-section{padding:16px 14px 0;}
  .video-title{font-size:16px;margin-bottom:12px;}
  .video-container{flex-direction:column;gap:12px;}
  .video-wrap{width:100%;}
  .video-side{width:100%;}

  /* Mark area */
  .mark-area{padding:10px 14px 14px;gap:10px;}
  .btn-mark{padding:10px 20px;font-size:13px;flex:1;text-align:center;}
  .nav-btns{margin-left:0;width:100%;justify-content:space-between;}
  .btn-nav{flex:1;text-align:center;padding:10px 10px;}

  /* Assignment & MCQ sections */
  #assignmentSection,#mcqSection{padding-left:14px!important;padding-right:14px!important;}
  .assign-header{flex-wrap:wrap;}
  .assign-submitted{flex-direction:column;}
  .btn-resubmit{margin-left:0;margin-top:8px;align-self:flex-start;}

  /* Pick topic state */
  .pick-topic{padding:30px 20px;}
  .pick-topic .pt-icon{font-size:38px;}
  .pick-topic h3{font-size:15px;}

  /* Toast: full-width bottom */
  .toast{left:14px;right:14px;bottom:16px;justify-content:center;}
}

/* ── Small phones: ≤480px ── */
@media(max-width:480px){
  .topbar{padding:0 10px;gap:6px;}
  .video-section{padding:12px 12px 0;}
  .video-title{font-size:15px;}
  .mark-area{padding:8px 12px 12px;}
  #assignmentSection,#mcqSection{padding-left:12px!important;padding-right:12px!important;}
  .assign-card,.mcq-card{padding:14px 14px;}
  .btn-mark{font-size:12.5px;padding:9px 16px;}
  .btn-nav{font-size:12px;padding:9px 8px;}
  .detail-card{padding:12px;}
  .detail-card h4{font-size:11px;}
  .detail-card ul li,.detail-card p{font-size:12px;}
}
</style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<div class="topbar">
    <button class="sidebar-toggle" onclick="openSidebar()">☰</button>
    <a href="student_dashboard.php" class="logo">
        <div class="logo-icon">C</div>
        <span class="logo-text">Culture of Internet</span>
    </a>
    <div class="topbar-divider"></div>
    <a href="student_dashboard.php" class="home-link">🏠 Dashboard</a>
    <a href="session.php" class="home-link">📅 Sessions</a>
    <div class="topbar-right">
        <span class="stu-name">👤 <?= h($user['name']) ?></span>
        <a href="student_logout.php" class="btn-logout">Logout</a>
    </div>
</div>

<?php if (count($all_enrollments) > 1): ?>
<div class="course-switcher">
  <span class="cs-label">📚 My Courses:</span>
  <?php foreach ($all_enrollments as $er): 
    $is_act = ($er['id'] == ($_SESSION['active_enrollment_id'] ?? 0));
    // Short name: extract code in brackets if exists
    preg_match('/\(([^)]+)\)/', $er['coursename'], $m);
    $short = $m[1] ?? substr($er['coursename'], 0, 28);
  ?>
  <a href="mycourse.php?switch_course=<?= $er['id'] ?>" class="cs-pill <?= $is_act ? 'active' : '' ?>">
    <span class="cs-dot"></span>
    <?= htmlspecialchars($short) ?>
    <span style="opacity:0.6;font-size:11px;"><?= htmlspecialchars($er['batch_no']) ?></span>
  </a>
  <?php endforeach; ?>
</div>
<?php endif; ?>
<div class="layout">
    <div class="sidebar" id="sidebar">
        <div class="sidebar-head">
            <h3>📚 Course Modules</h3>
            <?php if ($has_modules): ?>
            <div style="font-size:11px;color:#94a3b8;margin-top:2px;"><?= $total_topics ?> topics · <?= count($modules_data) ?> modules</div>
            <div class="progress-bar-wrap" style="margin-top:8px;">
                <div class="progress-bar-fill" id="progressFill" style="width:0%"></div>
            </div>
            <div class="progress-label">
                <span id="progressDone">0 done</span>
                <span id="progressPct">0% Complete</span>
            </div>
            <?php endif; ?>
        </div>

        <div class="sidebar-scroll">
            <?php if (!$has_modules): ?>
                <div style="padding:30px 20px;text-align:center;color:#94a3b8;">
                    <div style="font-size:36px;margin-bottom:10px;">📭</div>
                    <p style="font-size:13px;">Course content coming soon!</p>
                </div>
            <?php else: ?>
                <?php $mod_index = 0; foreach ($modules_data as $mod_name => $topics): $mod_index++; ?>
                <div class="mod-group" id="mod_<?= $mod_index ?>">
                    <div class="mod-header" onclick="toggleModule(<?= $mod_index ?>)">
                        <div class="mod-check" id="modcheck_<?= $mod_index ?>"></div>
                        <span class="mod-title"><?= h($mod_name) ?></span>
                        <span class="mod-count"><?= count($topics) ?></span>
                        <span class="mod-arrow">›</span>
                    </div>
                    <div class="topics-list">
                        <?php foreach ($topics as $t): ?>
                        <div class="topic-item"
                             id="ti_<?= md5($t['topic']) ?>"
                             data-topic="<?= h($t['topic']) ?>"
                             data-module="<?= h($mod_name) ?>"
                             data-video="<?= h($t['video_url']) ?>"
                             onclick="loadTopic(this)">
                            <div class="t-check">✓</div>
                            <span class="t-name"><?= h($t['topic']) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <div class="content" id="mainContent">
        <?php if (!$has_modules): ?>
        <div class="no-modules">
            <div class="nm-icon">🚧</div>
            <h3>Course Content Coming Soon!</h3>
            <p>Your course modules are being prepared.<br>Please check back soon.</p>
        </div>
        <?php else: ?>
        <div class="pick-topic" id="pickTopicState">
            <div class="pt-icon">👈</div>
            <h3>Select a topic to start learning</h3>
            <p style="font-size:13px;margin-top:4px;">Choose any module from the left sidebar</p>
        </div>

        <div id="topicContent" style="display:none;flex-direction:column;flex:1;">
            <div class="video-section">
                <div class="video-breadcrumb" id="breadcrumb">Module › Topic</div>
                <div class="video-title" id="topicTitle">Topic Title</div>
                <div class="video-container">
                    <div class="video-wrap">
                        <div id="videoThumb" class="video-thumb" onclick="playVideo()">
                            <img id="thumbImg" src="" alt="Video Thumbnail">
                            <div class="play-btn">
                                <svg viewBox="0 0 80 80" fill="none">
                                    <circle cx="40" cy="40" r="40" fill="rgba(255,255,255,0.15)"/>
                                    <polygon points="32,24 60,40 32,56" fill="white"/>
                                </svg>
                            </div>
                        </div>
                        <div class="video-iframe-wrap" id="videoIframeWrap">
                            <iframe id="videoIframe" src="" allowfullscreen allow="autoplay; encrypted-media"></iframe>
                        </div>
                        <div class="no-video-box" id="noVideoBox" style="display:none;">
                            <div class="nv-icon">🎬</div>
                            <p>Video will be available soon</p>
                        </div>
                    </div>
                    <div class="video-side" id="detailsPanel">
                        <div class="detail-card">
                            <h4>What You'll Learn</h4>
                            <ul id="learnList"></ul>
                        </div>
                        <div class="detail-card">
                            <h4 id="detailTitle2">Topic Overview</h4>
                            <p id="detailText2"></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mark-area">
                <button class="btn-mark" id="markBtn" onclick="markComplete()">✓ Mark as Complete</button>
                <div class="nav-btns">
                    <button class="btn-nav" onclick="prevTopic()">‹ Previous</button>
                    <button class="btn-nav next" onclick="nextTopic()">Next ›</button>
                </div>
            </div>
            <!-- ASSIGNMENT SECTION -->
            <div id="assignmentSection" style="display:none;padding:0 28px 16px;"></div>
            <!-- MCQ SECTION -->
            <div id="mcqSection" style="display:none;padding:0 28px 28px;"></div>
        </div>
        <?php endif; ?>
    </div>
</div>

<div class="toast" id="toast"></div>

<script>
const completedTopics    = <?= json_encode($completedTopics) ?>;
const allTopicsFlat      = <?= json_encode(array_values($all_topics_flat)) ?>;
const assignmentsMap     = <?= json_encode($assignments_map) ?>;
const submittedAssign    = <?= json_encode($submitted_assignments) ?>;
const mcqMap             = <?= json_encode($mcq_map) ?>;
const mcqAttempts        = <?= json_encode($mcq_attempts) ?>;
let currentTopicEl    = null;
let currentVideoId    = '';
let doneSet           = new Set(completedTopics);

window.addEventListener('DOMContentLoaded', () => {
    markDoneItems();
    updateProgress();
    const firstMod = document.querySelector('.mod-group');
    if (firstMod) firstMod.classList.add('open');
});

function openSidebar() {
    document.getElementById('sidebar').classList.add('open');
    document.getElementById('sidebarOverlay').classList.add('active');
}
function closeSidebar() {
    document.getElementById('sidebar').classList.remove('open');
    document.getElementById('sidebarOverlay').classList.remove('active');
}

function toggleModule(idx) {
    document.getElementById('mod_' + idx).classList.toggle('open');
}

function loadTopic(el) {
    document.querySelectorAll('.topic-item').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
    currentTopicEl = el;

    const topic    = el.dataset.topic;
    const module   = el.dataset.module;
    const videoUrl = el.dataset.video;

    document.getElementById('pickTopicState').style.display = 'none';
    const content = document.getElementById('topicContent');
    content.style.display = 'flex';

    document.getElementById('breadcrumb').innerHTML = `<span>${module}</span> › ${topic}`;
    document.getElementById('topicTitle').textContent = topic;

    currentVideoId = extractVideoId(videoUrl);
    const thumb    = document.getElementById('videoThumb');
    const iframeWrp = document.getElementById('videoIframeWrap');
    const noVid    = document.getElementById('noVideoBox');

    iframeWrp.style.display = 'none';
    document.getElementById('videoIframe').src = '';

    if (currentVideoId) {
        thumb.style.display = 'block';
        noVid.style.display = 'none';
        document.getElementById('thumbImg').src = `https://img.youtube.com/vi/${currentVideoId}/hqdefault.jpg`;
    } else {
        thumb.style.display = 'none';
        noVid.style.display = 'flex';
    }

    const btn = document.getElementById('markBtn');
    if (doneSet.has(topic)) {
        btn.textContent = '✓ Completed!';
        btn.classList.add('done-btn');
        btn.disabled = true;
    } else {
        btn.textContent = '✓ Mark as Complete';
        btn.classList.remove('done-btn');
        btn.disabled = false;
    }

    document.getElementById('learnList').innerHTML = [
        'Core concepts of ' + topic,
        'Practical applications in real world',
        'Industry best practices',
        'Hands-on implementation steps'
    ].map(i => `<li>${i}</li>`).join('');
    document.getElementById('detailTitle2').textContent = 'Topic Overview';
    document.getElementById('detailText2').textContent = `This topic covers ${topic} as part of the ${module} module. Watch the full video and take notes for best results.`;

    renderAssignment(topic);
    renderMCQ(topic);

    el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });

    // Close sidebar on mobile after topic selection
    if (window.innerWidth <= 768) closeSidebar();
}

function extractVideoId(url) {
    if (!url) return '';
    const m = url.match(/(?:youtu\.be\/|v=|embed\/)([a-zA-Z0-9_-]{11})/);
    return m ? m[1] : '';
}

function playVideo() {
    if (!currentVideoId) return;
    document.getElementById('videoThumb').style.display = 'none';
    document.getElementById('videoIframeWrap').style.display = 'block';
    document.getElementById('videoIframe').src = `https://www.youtube.com/embed/${currentVideoId}?autoplay=1&rel=0`;
}

function markComplete() {
    if (!currentTopicEl) return;
    const topic  = currentTopicEl.dataset.topic;
    const module = currentTopicEl.dataset.module;
    if (doneSet.has(topic)) return;

    const fd = new FormData();
    fd.append('action', 'mark_complete');
    fd.append('topic',  topic);
    fd.append('module', module);

    fetch('mycourse.php', { method: 'POST', body: fd })
        .then(r => r.text())
        .then(res => {
            if (res === 'saved') {
                doneSet.add(topic);
                currentTopicEl.classList.add('done');
                const btn = document.getElementById('markBtn');
                btn.textContent = '✓ Completed!';
                btn.classList.add('done-btn');
                btn.disabled = true;
                markDoneItems();
                updateProgress();
                showToast('✅ Topic marked as complete!');
                setTimeout(() => nextTopic(), 1200);
            }
        });
}

function markDoneItems() {
    document.querySelectorAll('.topic-item').forEach(el => {
        if (doneSet.has(el.dataset.topic)) {
            el.classList.add('done');
            el.querySelector('.t-check').textContent = '✓';
        }
    });
}

function updateProgress() {
    const total = document.querySelectorAll('.topic-item').length;
    const done  = document.querySelectorAll('.topic-item.done').length;
    const pct   = total > 0 ? Math.round((done / total) * 100) : 0;
    const fill  = document.getElementById('progressFill');
    if (fill) fill.style.width = pct + '%';
    const pctEl = document.getElementById('progressPct');
    if (pctEl) pctEl.textContent = pct + '% Complete';
    const doneEl = document.getElementById('progressDone');
    if (doneEl) doneEl.textContent = done + ' done';

    document.querySelectorAll('.mod-group').forEach(grp => {
        const topics     = grp.querySelectorAll('.topic-item');
        const doneTopics = grp.querySelectorAll('.topic-item.done');
        const check      = grp.querySelector('.mod-check');
        if (check && topics.length > 0 && topics.length === doneTopics.length) {
            check.classList.add('done');
            check.textContent = '✓';
        } else if (check) {
            check.classList.remove('done');
            check.textContent = '';
        }
    });
}

function getCurrentFlatIndex() {
    if (!currentTopicEl) return -1;
    const topic = currentTopicEl.dataset.topic;
    return allTopicsFlat.findIndex(t => t.topic === topic);
}

function nextTopic() {
    const idx = getCurrentFlatIndex();
    if (idx === -1 || idx >= allTopicsFlat.length - 1) return;
    const next = allTopicsFlat[idx + 1];
    const el = document.querySelector(`.topic-item[data-topic="${CSS.escape(next.topic)}"]`);
    if (el) { el.closest('.mod-group')?.classList.add('open'); loadTopic(el); }
}

function prevTopic() {
    const idx = getCurrentFlatIndex();
    if (idx <= 0) return;
    const prev = allTopicsFlat[idx - 1];
    const el = document.querySelector(`.topic-item[data-topic="${CSS.escape(prev.topic)}"]`);
    if (el) { el.closest('.mod-group')?.classList.add('open'); loadTopic(el); }
}

function showToast(msg) {
    const t = document.getElementById('toast');
    t.textContent = msg;
    t.classList.add('show');
    setTimeout(() => t.classList.remove('show'), 2500);
}

function renderAssignment(topic) {
    const box = document.getElementById('assignmentSection');
    if (!box) return;
    const assign = assignmentsMap[topic];
    if (!assign) {
        box.innerHTML = '';
        box.style.display = 'none';
        return;
    }
    box.style.display = 'block';
    const sub = submittedAssign[topic];
    let submitHtml = '';
    if (sub) {
        submitHtml = `
        <div class="assign-submitted">
            <span class="assign-submitted-icon">✅</span>
            <div>
                <div class="assign-sub-label">Assignment Submitted</div>
                <div class="assign-sub-date">Submitted on ${sub.submitted_at?.substring(0,10) || ''}</div>
                ${sub.grade ? `<div class="assign-grade">Grade: <strong>${sub.grade}</strong></div>` : ''}
                ${sub.feedback ? `<div class="assign-feedback">"${sub.feedback}"</div>` : ''}
            </div>
            <label class="btn-resubmit" style="cursor:pointer;">Re-submit
                <input type="file" style="display:none;" onchange="uploadAssignment(this,'${topic.replace(/'/g,"\\'")}')">
            </label>
        </div>`;
    } else {
        submitHtml = `
        <div class="assign-upload">
            <label class="btn-upload-assign" style="cursor:pointer;">
                📎 Upload Your Answer
                <input type="file" style="display:none;" onchange="uploadAssignment(this,'${topic.replace(/'/g,"\\'")}')">
            </label>
            <span class="assign-hint">PDF, image, or Word doc</span>
        </div>`;
    }
    box.innerHTML = `
    <div class="assign-card">
        <div class="assign-header">
            <span class="assign-badge">📋 Assignment</span>
            <span class="assign-title">${assign.title}</span>
        </div>
        ${assign.instructions ? `<div class="assign-instructions">${assign.instructions}</div>` : ''}
        ${assign.download_url ? `<a href="${assign.download_url}" target="_blank" class="btn-download-assign">⬇ Download Assignment File</a>` : ''}
        ${submitHtml}
    </div>`;
}

function uploadAssignment(input, topic) {
    if (!input.files[0]) return;
    const file = input.files[0];
    if (file.size > 10 * 1024 * 1024) { showToast('❌ File too large! Max 10MB allowed.'); return; }
    const fd = new FormData();
    fd.append('action', 'submit_assignment');
    fd.append('topic', topic);
    fd.append('assignment_file', file);
    showToast('⏳ Uploading...');
    fetch('mycourse.php', { method: 'POST', body: fd })
        .then(r => r.text())
        .then(res => {
            if (res === 'submitted') {
                submittedAssign[topic] = { submitted_at: new Date().toISOString() };
                renderAssignment(topic);
                showToast('✅ Assignment submitted!');
            } else {
                alert('Upload Error:\n' + res);
            }
        })
        .catch(() => showToast('❌ Network error. Try again.'));
}

function renderMCQ(topic) {
    const box = document.getElementById('mcqSection');
    if (!box) return;
    const questions = mcqMap[topic];
    if (!questions || questions.length === 0) {
        box.innerHTML = '';
        box.style.display = 'none';
        return;
    }
    box.style.display = 'block';
    const attempt = mcqAttempts[topic];
    if (attempt) {
        const pct = Math.round((attempt.score / attempt.total) * 100);
        const color = pct >= 70 ? '#166534' : pct >= 40 ? '#92400e' : '#991b1b';
        const bg    = pct >= 70 ? '#dcfce7' : pct >= 40 ? '#fef9c3' : '#fee2e2';
        box.innerHTML = `
        <div class="mcq-card">
            <div class="mcq-header">
                <span class="mcq-badge">📝 MCQ Quiz</span>
                <span class="mcq-title">${questions.length} Questions</span>
            </div>
            <div class="mcq-result-box" style="background:${bg};border-color:${color}30;">
                <div class="mcq-score" style="color:${color}">${attempt.score}/${attempt.total}</div>
                <div class="mcq-score-label" style="color:${color}">${pct}% Score</div>
                <div class="mcq-score-sub">${pct >= 70 ? '🎉 Great job!' : pct >= 40 ? '👍 Keep practicing' : '📖 Review the topic again'}</div>
                <div style="font-size:11px;color:#94a3b8;margin-top:8px;">Quiz already attempted</div>
            </div>
        </div>`;
        return;
    }
    box.innerHTML = `
    <div class="mcq-card">
        <div class="mcq-header">
            <span class="mcq-badge">📝 MCQ Quiz</span>
            <span class="mcq-title">${questions.length} Questions</span>
        </div>
        <div class="mcq-start-box">
            <div class="mcq-start-icon">🧠</div>
            <div class="mcq-start-text">Test your understanding with a quick quiz!</div>
            <button class="btn-start-mcq" onclick="startMCQ('${topic.replace(/'/g,"\\'")}')">▶ Start Quiz</button>
        </div>
    </div>`;
}

function startMCQ(topic) {
    const questions = mcqMap[topic];
    if (!questions) return;
    const box = document.getElementById('mcqSection');
    let html = `
    <div class="mcq-card">
        <div class="mcq-header">
            <span class="mcq-badge">📝 MCQ Quiz</span>
            <span class="mcq-title">${questions.length} Questions</span>
        </div>
        <form id="mcqForm">`;
    questions.forEach((q, i) => {
        html += `
        <div class="mcq-question">
            <div class="mcq-q-text">Q${i+1}. ${q.question}</div>
            <div class="mcq-options">
                ${['a','b','c','d'].map(opt => `
                <label class="mcq-option">
                    <input type="radio" name="ans_${q.id}" value="${opt.toUpperCase()}">
                    <span class="mcq-opt-letter">${opt.toUpperCase()}</span>
                    <span class="mcq-opt-text">${q['option_'+opt]}</span>
                </label>`).join('')}
            </div>
        </div>`;
    });
    html += `
        <button type="button" class="btn-submit-mcq" onclick="submitMCQ('${topic.replace(/'/g,"\\'")}')">Submit Quiz</button>
        </form>
    </div>`;
    box.innerHTML = html;
}

function submitMCQ(topic) {
    const questions = mcqMap[topic];
    const fd = new FormData();
    fd.append('action', 'submit_mcq');
    fd.append('topic', topic);
    let allAnswered = true;
    questions.forEach(q => {
        const sel = document.querySelector(`input[name="ans_${q.id}"]:checked`);
        if (!sel) { allAnswered = false; return; }
        fd.append(`answers[${q.id}]`, sel.value);
    });
    if (!allAnswered) { showToast('⚠️ Please answer all questions!'); return; }
    fetch('mycourse.php', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(res => {
            if (res.score !== undefined) {
                mcqAttempts[topic] = { score: res.score, total: res.total };
                renderMCQ(topic);
                showToast(`✅ Quiz submitted! Score: ${res.score}/${res.total}`);
            }
        });
}
</script>
</body>
</html>