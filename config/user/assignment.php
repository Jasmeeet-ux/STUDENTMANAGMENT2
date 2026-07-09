<?php
require_once __DIR__ . '/../db.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];
$stmt = $pdo->prepare("SELECT reg_no, name FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$user) { header("Location: student_login.php"); exit; }
$reg_no = $user['reg_no'];

$pdo->exec("CREATE TABLE IF NOT EXISTS assignment_submissions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  reg_no VARCHAR(50) NOT NULL,
  topic_name VARCHAR(255) NOT NULL,
  score INT NULL,
  is_completed TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uniq_reg_topic (reg_no, topic_name)
)");

$topic      = isset($_GET['topic'])      ? trim($_GET['topic'])      : '';
$module     = isset($_GET['module'])     ? trim($_GET['module'])     : '';
$index      = isset($_GET['index'])      ? (int)$_GET['index']       : -1;
$next_index = isset($_GET['next_index']) ? (int)$_GET['next_index']  : -1;

$baseQuestions = [
    ['q' => 'What does SEO stand for?', 'options' => ['Search Engine Optimization','Social Engagement Objective','Site Efficiency Operation','Search Email Organizer'], 'answer' => 0],
    ['q' => 'Which of these is a top-level domain (TLD)?', 'options' => ['example','.com','http','www'], 'answer' => 1],
    ['q' => 'In digital marketing, a "landing page" is mainly used to:', 'options' => ['Show company history','Collect leads or drive one focused action','Display server status','Host email accounts'], 'answer' => 1],
    ['q' => 'Which metric normally shows how many people clicked your ad or link?', 'options' => ['CTR (Click-Through Rate)','CPC (Cost Per Click)','Domain Authority','Bounce Rate'], 'answer' => 0],
    ['q' => 'Which platform is best suited for short vertical video content?', 'options' => ['LinkedIn','Instagram Reels','Email','PDF Documents'], 'answer' => 1],
];

$questionBank = [
    'What is Domain' => [
        ['q' => 'A domain name is best described as:', 'options' => ['The physical server in your office','Your website\'s human-friendly address on the internet','Your email password','A type of programming language'], 'answer' => 1],
        ['q' => 'Which of these looks like a domain name?', 'options' => ['https://','www','google.com','C:\\Program Files'], 'answer' => 2],
        ['q' => 'Domains map user-friendly names to which of the following?', 'options' => ['QR codes','IP addresses','Spreadsheets','Phone numbers'], 'answer' => 1],
    ],
];

$questions = $baseQuestions;
if ($topic && isset($questionBank[$topic])) {
    $questions = $questionBank[$topic];
}

$totalQs   = count($questions);
$submitted = false;

// ── Handle POST ──
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $topic) {
    $correct = 0;
    foreach ($questions as $idx => $q) {
        $given = isset($_POST['q'.$idx]) ? (int)$_POST['q'.$idx] : -1;
        if ($given === (int)$q['answer']) $correct++;
    }

    // Save to DB
    $pdo->prepare("INSERT INTO assignment_submissions (reg_no, topic_name, score, is_completed)
        VALUES (?,?,?,1)
        ON DUPLICATE KEY UPDATE score=VALUES(score), is_completed=1, created_at=CURRENT_TIMESTAMP")
        ->execute([$reg_no, $topic, $correct]);

    // Redirect with submitted=1 in URL
    header("Location: assignment.php?topic=".urlencode($topic)
        ."&module=".urlencode($module)
        ."&submitted=1"
        ."&index=".$index
        ."&next_index=".$next_index);
    exit;
}

$submitted = isset($_GET['submitted']) && $_GET['submitted'] == '1';

// Existing submission
$existing = null;
if ($topic) {
    $stmtEx = $pdo->prepare("SELECT score, is_completed FROM assignment_submissions WHERE reg_no = ? AND topic_name = ?");
    $stmtEx->execute([$reg_no, $topic]);
    $existing = $stmtEx->fetch(PDO::FETCH_ASSOC) ?: null;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Assignment</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--navy:#0d1b2a;--navy2:#1a2e45;--gold:#c39b5f;--gold-l:#d4af72;--gold-pale:#f6edd9;--bg:#f0f2f5;--white:#fff;--text:#0d1b2a;--muted:#7a8899;--border:#e4ddd2;--green:#10b981;}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:"DM Sans",sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}

.topbar{height:56px;background:var(--navy);border-bottom:1px solid rgba(195,155,95,0.15);display:flex;align-items:center;padding:0 24px;gap:14px;}
.logo{display:flex;align-items:center;gap:9px;text-decoration:none;}
.logo-icon{width:30px;height:30px;border-radius:8px;background:rgba(195,155,95,0.15);border:1px solid rgba(195,155,95,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold-l);font-weight:800;font-size:14px;}
.logo-text{font-family:"Sora",sans-serif;font-size:13px;font-weight:700;color:#fff;}
.topbar-divider{width:1px;height:20px;background:rgba(195,155,95,0.15);}
.home-link{display:flex;align-items:center;gap:6px;text-decoration:none;color:rgba(255,255,255,0.5);font-size:12.5px;font-weight:500;padding:5px 10px;border-radius:7px;transition:all 0.15s;}
.home-link:hover{background:rgba(195,155,95,0.1);color:var(--gold-l);}

.main{max-width:700px;margin:32px auto 40px;padding:0 16px;}
.page-title{font-family:"Sora",sans-serif;font-size:21px;font-weight:800;margin-bottom:4px;color:var(--navy);}
.page-sub{font-size:13px;color:var(--muted);margin-bottom:18px;}

.card{background:var(--white);border-radius:16px;box-shadow:0 2px 12px rgba(13,27,42,0.07);padding:28px;border:1px solid var(--border);}

.badge-row{display:flex;gap:8px;margin-bottom:16px;flex-wrap:wrap;}
.badge{font-size:11px;padding:4px 10px;border-radius:999px;font-weight:600;}
.badge.topic{background:rgba(13,27,42,0.07);color:var(--navy);}
.badge.module{background:#ecfdf5;color:#15803d;}
.badge.done{background:#dcfce7;color:#166534;}
.badge.pending{background:var(--gold-pale);color:#7a5c2a;}

/* ── SUCCESS STATE ── */
.success-state{text-align:center;padding:20px 0 10px;}
.success-icon{font-size:64px;margin-bottom:16px;animation:popIn 0.4s cubic-bezier(.22,1,.36,1);}
@keyframes popIn{from{transform:scale(0)}to{transform:scale(1)}}
.success-state h2{font-family:"Sora",sans-serif;font-size:21px;font-weight:800;color:#166534;margin-bottom:8px;}
.success-state p{font-size:14px;color:var(--muted);margin-bottom:28px;}

.btn-next{display:inline-flex;align-items:center;gap:10px;padding:13px 30px;border-radius:12px;background:var(--navy);color:var(--gold-l);font-size:14.5px;font-weight:700;text-decoration:none;border:1px solid rgba(195,155,95,0.3);cursor:pointer;box-shadow:0 4px 16px rgba(13,27,42,0.2);transition:all 0.15s;}
.btn-next:hover{background:var(--navy2);transform:translateY(-1px);}

.btn-retake{display:inline-block;margin-top:14px;font-size:12px;color:var(--gold);text-decoration:underline;cursor:pointer;}
.btn-retake:hover{color:var(--gold-l);}

/* ── QUIZ STATE ── */
.prev-result{background:var(--gold-pale);border:1px solid var(--border);border-radius:10px;padding:12px 16px;margin-bottom:16px;font-size:13px;color:#7a5c2a;}

.question-block{padding:14px 0;border-top:1px solid var(--border);}
.question-text{font-size:13.5px;font-weight:600;margin-bottom:10px;color:var(--navy);}
.options label{display:flex;align-items:flex-start;gap:8px;font-size:13px;margin-bottom:8px;cursor:pointer;padding:8px 10px;border-radius:8px;transition:background 0.1s;}
.options label:hover{background:var(--gold-pale);}
.options input[type=radio]{margin-top:2px;accent-color:var(--navy);}

.submit-row{margin-top:20px;display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;}
.btn-submit{padding:11px 26px;border-radius:8px;border:none;background:var(--green);color:#fff;font-size:14px;font-weight:700;cursor:pointer;font-family:"DM Sans",sans-serif;transition:background 0.15s;}
.btn-submit:hover{background:#059669;}
.note{font-size:12px;color:var(--muted);}

.empty{text-align:center;padding:60px 16px;color:var(--muted);}
.empty h2{font-family:"Sora",sans-serif;font-size:17px;font-weight:700;margin-bottom:6px;color:var(--navy);}
</style>
</head>
<body>

<div class="topbar">
  <a class="logo" href="#">
    <div class="logo-icon">S</div>
    <span class="logo-text">SkillPortal</span>
  </a>
  <div class="topbar-divider"></div>
  <a class="home-link" href="student_dashboard.php">🏠 Dashboard</a>
  <a class="home-link" href="mycourse.php">📚 My Course</a>
</div>

<div class="main">
  <?php if (!$topic): ?>
    <div class="empty">
      <h2>No Assignment Selected</h2>
      <p>Open any topic from <strong>My Course</strong> and click the assignment button.</p>
    </div>

  <?php elseif ($submitted || ($existing && (int)$existing['is_completed'] === 1)): ?>
    <!-- ✅ SUBMITTED STATE — stays on reload -->
    <h1 class="page-title">Assignment</h1>
    <div class="card">
      <div class="badge-row">
        <span class="badge topic">📝 <?= htmlspecialchars($topic) ?></span>
        <?php if ($module): ?><span class="badge module">📚 <?= htmlspecialchars($module) ?></span><?php endif; ?>
        <span class="badge done">✅ Completed</span>
      </div>

      <div class="success-state">
        <div class="success-icon">🎉</div>
        <h2>Assignment Submitted!</h2>
        <p>Great job! Your progress has been saved.<br>Ready for the next topic?</p>

        <?php if ($next_index >= 0): ?>
          <a href="mycourse.php?auto=1&index=<?= $next_index ?>" class="btn-next">
            ▶ Go to Next Video
          </a>
        <?php else: ?>
          <a href="mycourse.php" class="btn-next">
            📚 Back to My Course
          </a>
        <?php endif; ?>

        <br>
        <a class="btn-retake"
           href="assignment.php?topic=<?= urlencode($topic) ?>&module=<?= urlencode($module) ?>&index=<?= $index ?>&next_index=<?= $next_index ?>">
          Retake assignment
        </a>
      </div>
    </div>

  <?php else: ?>
    <!-- 📝 QUIZ FORM -->
    <h1 class="page-title">Topic Assignment</h1>
    <p class="page-sub">Answer the questions below to complete this assignment.</p>

    <div class="card">
      <div class="badge-row">
        <span class="badge topic">📝 <?= htmlspecialchars($topic) ?></span>
        <?php if ($module): ?><span class="badge module">📚 <?= htmlspecialchars($module) ?></span><?php endif; ?>
        <span class="badge pending">⏳ Pending</span>
      </div>

      <form method="post"
            action="assignment.php?topic=<?= urlencode($topic) ?>&module=<?= urlencode($module) ?>&index=<?= $index ?>&next_index=<?= $next_index ?>">

        <?php foreach ($questions as $idx => $q): ?>
          <div class="question-block">
            <div class="question-text">Q<?= $idx+1 ?>. <?= htmlspecialchars($q['q']) ?></div>
            <div class="options">
              <?php foreach ($q['options'] as $optIdx => $opt): ?>
                <label>
                  <input type="radio" name="q<?= $idx ?>" value="<?= $optIdx ?>" required>
                  <span><?= htmlspecialchars($opt) ?></span>
                </label>
              <?php endforeach; ?>
            </div>
          </div>
        <?php endforeach; ?>

        <div class="submit-row">
          <button type="submit" class="btn-submit">✅ Submit Assignment</button>
          <span class="note">Progress will update on your dashboard.</span>
        </div>
      </form>
    </div>
  <?php endif; ?>
</div>

</body>
</html>