<?php
require_once '../db.php';
session_start();

if (!isset($_SESSION['student_id'])) {
    header("Location: student_login.php");
    exit;
}

$student_id = $_SESSION['student_id'];

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS terms_accepted TINYINT(1) NOT NULL DEFAULT 0");
} catch(Exception $e) {}

$stmt = $pdo->prepare("SELECT terms_accepted, name FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accept_terms'])) {
    $pdo->prepare("UPDATE users SET terms_accepted = 1 WHERE id = ?")
        ->execute([$student_id]);
    header("Location: student_dashboard.php");
    exit;
}

$already_accepted = (int)($user['terms_accepted'] ?? 0) === 1;
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Terms &amp; Conditions — Culture of Internet</title>
<link href="https://fonts.googleapis.com/css2?family=Sora:wght@400;600;700;800&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
:root{--navy:#0d1b2a;--navy2:#1a2e45;--gold:#c39b5f;--gold-l:#d4af72;--gold-pale:#f6edd9;--bg:#f0f2f5;--white:#fff;--text:#0d1b2a;--muted:#7a8899;--border:#e4ddd2;}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:"DM Sans",sans-serif;background:var(--bg);color:var(--text);min-height:100vh;}
.topbar{height:56px;background:var(--navy);border-bottom:1px solid rgba(195,155,95,0.15);display:flex;align-items:center;padding:0 28px;gap:14px;position:sticky;top:0;z-index:10;}
.logo{display:flex;align-items:center;gap:10px;text-decoration:none;}
.logo-icon{width:30px;height:30px;border-radius:8px;background:rgba(195,155,95,0.15);border:1px solid rgba(195,155,95,0.3);display:flex;align-items:center;justify-content:center;color:var(--gold-l);font-weight:800;font-size:10px;letter-spacing:-0.5px;}
.logo-text{font-family:"Sora",sans-serif;font-size:13px;font-weight:700;color:#fff;}
.topbar-divider{width:1px;height:20px;background:rgba(195,155,95,0.15);}
.back-link{display:flex;align-items:center;gap:6px;text-decoration:none;color:rgba(255,255,255,0.5);font-size:12.5px;font-weight:500;padding:5px 10px;border-radius:7px;transition:all 0.15s;}
.back-link:hover{background:rgba(195,155,95,0.1);color:var(--gold-l);}
.page-wrap{max-width:820px;margin:36px auto 60px;padding:0 20px;}
.page-header{margin-bottom:28px;}
.page-header h1{font-family:"Sora",sans-serif;font-size:24px;font-weight:800;letter-spacing:-0.5px;margin-bottom:6px;color:var(--navy);}
.page-header p{font-size:13.5px;color:var(--muted);}
.terms-card{background:var(--white);border-radius:20px;border:1px solid var(--border);box-shadow:0 4px 24px rgba(13,27,42,0.07);overflow:hidden;}
.terms-body{padding:32px 36px;max-height:520px;overflow-y:auto;scroll-behavior:smooth;}
.terms-body::-webkit-scrollbar{width:4px;}
.terms-body::-webkit-scrollbar-thumb{background:var(--border);border-radius:4px;}
.t-section{margin-bottom:20px;}
.t-section h2{font-family:"Sora",sans-serif;font-size:14.5px;font-weight:700;color:var(--navy);margin-bottom:8px;display:flex;align-items:center;gap:8px;}
.t-num{width:22px;height:22px;border-radius:50%;background:var(--navy);color:var(--gold-l);font-size:10px;font-weight:900;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;}
.t-section p{font-size:13.5px;line-height:1.75;color:#374151;margin-bottom:8px;}
.t-section ul{padding-left:20px;margin-bottom:8px;}
.t-section ul li{font-size:13.5px;line-height:1.75;color:#374151;margin-bottom:3px;}
.highlight{background:var(--gold-pale);border-left:3px solid var(--gold);padding:10px 14px;border-radius:0 8px 8px 0;margin:10px 0;font-size:13px;color:#7a5c2a;font-weight:600;}
.highlight.warn{background:#fff7ed;border-color:#f59e0b;color:#92400e;}
.highlight.green{background:#f0fdf4;border-color:#16a34a;color:#166534;}
.scroll-hint{text-align:center;padding:10px;font-size:12px;color:var(--muted);border-top:1px solid var(--border);background:var(--bg);animation:pulse 2s ease-in-out infinite;}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:0.5}}
.scroll-hint.hidden{display:none;}
.terms-footer{padding:24px 36px;border-top:1px solid var(--border);background:var(--bg);}
.read-check-wrap{display:flex;align-items:center;gap:10px;margin-bottom:18px;}
.read-check-wrap input[type=checkbox]{width:18px;height:18px;accent-color:var(--navy);cursor:pointer;flex-shrink:0;}
.read-check-wrap label{font-size:13.5px;color:#374151;font-weight:500;cursor:pointer;}
.read-check-wrap label strong{color:var(--navy);}
.btn-accept{width:100%;padding:13px;background:var(--navy);color:var(--gold-l);font-size:14.5px;font-weight:700;font-family:"DM Sans",sans-serif;border:1px solid rgba(195,155,95,0.3);border-radius:12px;cursor:pointer;box-shadow:0 4px 16px rgba(13,27,42,0.2);transition:all 0.2s;display:flex;align-items:center;justify-content:center;gap:8px;}
.btn-accept:disabled{background:var(--border);color:var(--muted);box-shadow:none;cursor:not-allowed;border-color:transparent;}
.btn-accept:not(:disabled):hover{background:var(--navy2);transform:translateY(-1px);}
.already-accepted{display:flex;align-items:center;gap:12px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:12px;padding:16px 20px;}
.already-accepted .aa-icon{font-size:28px;}
.already-accepted h4{font-family:"Sora",sans-serif;font-size:15px;font-weight:700;color:#166534;margin-bottom:2px;}
.already-accepted p{font-size:13px;color:#16a34a;}
.btn-dashboard{display:inline-block;margin-top:14px;padding:10px 24px;border-radius:10px;background:var(--navy);color:var(--gold-l);font-size:13.5px;font-weight:700;text-decoration:none;transition:background 0.15s;border:1px solid rgba(195,155,95,0.3);}
.btn-dashboard:hover{background:var(--navy2);}
</style>
</head>
<body>

<div class="topbar">
  <a class="logo" href="student_dashboard.php">
    <div class="logo-icon">COI</div>
    <span class="logo-text">Culture of Internet</span>
  </a>
  <div class="topbar-divider"></div>
  <a class="back-link" href="student_dashboard.php">&#8592; Back to Dashboard</a>
</div>

<div class="page-wrap">
  <div class="page-header">
    <h1>&#128220; Terms &amp; Conditions</h1>
    <p>Please read all terms carefully before accepting. Scroll to the bottom to accept.</p>
  </div>

  <div class="terms-card">
    <div class="terms-body" id="termsBody">

      <div class="t-section">
        <h2><span class="t-num">1</span> Code of Conduct</h2>
        <p>Any indiscipline, bullying, misuse of institute rules, irregular attendance, or spreading false or negative content about the institute may result in <strong>immediate termination without refund</strong>.</p>
        <div class="highlight warn">Management decision will be final and binding.</div>
      </div>

      <div class="t-section">
        <h2><span class="t-num">2</span> Fees &amp; EMI Policy</h2>
        <ul>
          <li>EMI must be paid within <strong>7 days</strong> of due date. Failure will result in class discontinuation.</li>
          <li>EMI dates are fixed. Changes allowed only in genuine emergencies with written proof and <strong>Rs.1,000 administrative charge</strong>.</li>
          <li>Fee details and any special discount offered to a student are <strong>strictly confidential</strong>.</li>
          <li>Disclosing fee amount, discount, or any special concession to others will result in <strong>immediate cancellation of discount</strong> and liability to pay full course fee without any discount.</li>
          <li>No EMI date change will be approved without valid documentation.</li>
        </ul>
      </div>

      <div class="t-section">
        <h2><span class="t-num">3</span> Attendance &amp; Performance Requirement</h2>
        <ul>
          <li>Minimum <strong>85% Physical Attendance</strong> mandatory (online attendance not counted).</li>
          <li>Minimum <strong>85% Homework Submission</strong> compulsory.</li>
          <li>Late arrival beyond <strong>15 minutes</strong> without prior notice will be marked absent.</li>
        </ul>
        <div class="highlight warn">Failure to maintain 85% will result in: No backup classes, No internship eligibility, No job assistance, No certificate.</div>
      </div>

      <div class="t-section">
        <h2><span class="t-num">4</span> Course Duration &amp; Leave Policy</h2>
        <ul>
          <li>Course start date and end date are <strong>fixed at admission</strong>.</li>
          <li>Maximum leave: <strong>15 days</strong> in 6-month course | <strong>30 days</strong> in 12-month course.</li>
          <li>Leave requires valid proof (medical, exam, travel).</li>
          <li>Exceeding leave limit = No backup classes or extra support.</li>
          <li>Course will be considered completed on original end date.</li>
          <li>Continuing after end date = <strong>Rs.100 per day fine</strong>.</li>
        </ul>
      </div>

      <div class="t-section">
        <h2><span class="t-num">5</span> Internship &amp; Placement</h2>
        <ul>
          <li>We provide <strong>job assistance, not job guarantee</strong>.</li>
          <li>Internship is performance-based.</li>
          <li>For Digital Marketing, Graphic Designing &amp; Full Stack Development: <strong>3-month unpaid internship</strong> mandatory for placement support.</li>
          <li>Failure to complete internship = No placement support.</li>
        </ul>
      </div>

      <div class="t-section">
        <h2><span class="t-num">6</span> Online Classes &amp; Portal Access</h2>
        <ul>
          <li>6-month course: <strong>10 free online classes</strong>.</li>
          <li>12-month course: <strong>20 free online classes</strong>.</li>
          <li>Recorded classes for revision only — <strong>sharing is strictly prohibited</strong>.</li>
          <li>Login ID &amp; Password provided to check attendance, performance analytics, class updates, and course progress.</li>
          <li>Portal access active for <strong>60 days after course completion</strong>, then permanently closed.</li>
        </ul>
      </div>

      <div class="t-section">
        <h2><span class="t-num">7</span> Communication Policy</h2>
        <p>Only written communication via official channels is valid:</p>
        <ul>
          <li>WhatsApp: <strong>+91 8130840080</strong></li>
          <li>Email: <strong>contactcultureofinternet@gmail.com</strong></li>
        </ul>
        <div class="highlight">Institute is not responsible for personal promises made outside official communication channels.</div>
      </div>

      <div class="t-section">
        <h2><span class="t-num">8</span> Fees &amp; Refund Policy</h2>
        <ul>
          <li>Registration &amp; admission fees are <strong>non-refundable</strong>.</li>
          <li>Course fees are <strong>non-transferable and non-refundable</strong> under any circumstances.</li>
          <li>If a student withdraws or discontinues, <strong>no refund will be given</strong>.</li>
        </ul>
      </div>

      <div class="t-section">
        <h2><span class="t-num">9</span> Certification Requirements</h2>
        <p>To receive certificate, student must:</p>
        <ul>
          <li>Maintain <strong>85% attendance and performance criteria</strong></li>
          <li>Complete mandatory internship (if applicable)</li>
          <li>Submit portfolio</li>
          <li>Submit testimonial</li>
        </ul>
        <div class="highlight warn">Without fulfilling all conditions, certificate will NOT be issued.</div>
      </div>

      <div class="t-section">
        <h2><span class="t-num">10</span> General Policy</h2>
        <ul>
          <li>GST (currently <strong>18%</strong>) applicable as per government rules.</li>
          <li>Institute not liable for delays due to emergencies or government restrictions.</li>
          <li>Management reserves the right to update policies when required.</li>
        </ul>
        <div class="highlight warn" style="margin-top:14px;">
          <strong>Final Declaration:</strong> By enrolling at Culture of Internet, the student confirms they have read and agreed to all terms and conditions. Violation of any rule may result in cancellation without refund. Management decision will be final.
        </div>
      </div>

    </div>

    <div class="scroll-hint" id="scrollHint">&#8595; Scroll down to read all terms before accepting</div>

    <div class="terms-footer">
      <?php if ($already_accepted): ?>
        <div class="already-accepted">
          <div class="aa-icon">&#10003;</div>
          <div>
            <h4>Terms Already Accepted</h4>
            <p>You have already accepted the Terms &amp; Conditions. Thank you!</p>
            <a href="student_dashboard.php" class="btn-dashboard">Go to Dashboard &rarr;</a>
          </div>
        </div>
      <?php else: ?>
        <form method="POST">
          <div class="read-check-wrap">
            <input type="checkbox" id="readCheck" name="read_check" onchange="toggleAccept()">
            <label for="readCheck">I have read and understood all the <strong>Terms &amp; Conditions</strong> of Culture of Internet and agree to abide by them.</label>
          </div>
          <button type="submit" name="accept_terms" class="btn-accept" id="acceptBtn" disabled>
            &#10003; Accept &amp; Continue to Dashboard
          </button>
        </form>
      <?php endif; ?>
    </div>
  </div>
</div>

<script>
const body      = document.getElementById('termsBody');
const hint      = document.getElementById('scrollHint');
const readCheck = document.getElementById('readCheck');
const acceptBtn = document.getElementById('acceptBtn');
let scrolledToBottom = false;

if (body) {
  body.addEventListener('scroll', () => {
    const atBottom = body.scrollTop + body.clientHeight >= body.scrollHeight - 30;
    if (atBottom && !scrolledToBottom) {
      scrolledToBottom = true;
      hint.classList.add('hidden');
    }
  });
}

function toggleAccept() {
  if (acceptBtn) acceptBtn.disabled = !readCheck.checked;
}
</script>
</body>
</html>