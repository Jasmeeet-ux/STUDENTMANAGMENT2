<?php
require_once '../db.php';
session_start();

$error = "";
$show_forgot = isset($_GET['forgot']) && $_GET['forgot'] == '1';
$forgot_msg = "";
$show_terms_popup = false;

try {
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS terms_accepted TINYINT(1) NOT NULL DEFAULT 0");
} catch(Exception $e) {}

if(isset($_POST['forgot_submit'])) {
    $forgot_email = trim($_POST['forgot_email']);
    if($forgot_email == "") {
        $forgot_msg = "error:Please enter your email address.";
    } else {
        $forgot_msg = "success:Password reset link sent to " . htmlspecialchars($forgot_email);
    }
    $show_forgot = true;
}

if(isset($_POST['accept_terms']) && isset($_SESSION['pending_student_id'])) {
    $pdo->prepare("UPDATE users SET terms_accepted = 1 WHERE id = ?")
        ->execute([$_SESSION['pending_student_id']]);
    $_SESSION['student_id']   = $_SESSION['pending_student_id'];
    $_SESSION['student_name'] = $_SESSION['pending_student_name'] ?? '';
    $_SESSION['mode']         = $_SESSION['pending_mode'] ?? 'online';
    unset($_SESSION['pending_student_id'], $_SESSION['pending_student_name'], $_SESSION['pending_mode']);
    header("Location: student_dashboard.php");
    exit;
}

if(isset($_POST['login'])) {
    $reg_no   = trim($_POST['reg_no']);
    $password = trim($_POST['password']);
    $mode     = trim($_POST['mode']);

    if($reg_no == "" || $password == "" || $mode == "") {
        $error = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE reg_no = ?");
        $stmt->execute([$reg_no]);
        $student = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$student) {
            $error = "Registration number not found.";
        } else {
            $password_valid = false;
            // Check for hashed password OR plain text fallback
            if (password_verify($password, $student['password'])) {
                $password_valid = true;
            } elseif ($student['password'] === $password) {
                $password_valid = true;
            }

            if (!$password_valid) {
                $error = "Incorrect password.";
            } else {
            $_SESSION['student_name'] = $student['name'];
            $_SESSION['mode']         = $mode;
            if ((int)($student['terms_accepted'] ?? 0) === 0) {
                $_SESSION['pending_student_id']   = $student['id'];
                $_SESSION['pending_student_name'] = $student['name'];
                $_SESSION['pending_mode']         = $mode;
                $show_terms_popup = true;
            } else {
                $_SESSION['student_id'] = $student['id'];
                header("Location: student_dashboard.php");
                exit;
            }
        }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Student Login — Culture of Internet</title>
<link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700;800;900&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

body {
  min-height: 100vh;
  display: flex;
  align-items: center;
  justify-content: center;
  font-family: 'Nunito', sans-serif;
  /* Dark navy-gold themed background */
  background: #1a2535;
  background-image:
    radial-gradient(ellipse at 10% 10%, rgba(195,155,95,0.18) 0%, transparent 45%),
    radial-gradient(ellipse at 90% 90%, rgba(195,155,95,0.12) 0%, transparent 45%),
    radial-gradient(ellipse at 80% 10%, rgba(42,62,90,0.6) 0%, transparent 50%),
    radial-gradient(ellipse at 20% 90%, rgba(42,62,90,0.5) 0%, transparent 50%);
  padding: 20px;
  overflow: hidden;
}

/* Background floating blobs like reference */
body::before {
  content: '';
  position: fixed;
  width: 500px; height: 500px;
  border-radius: 50%;
  background: rgba(195,155,95,0.06);
  top: -150px; left: -150px;
  pointer-events: none;
}
body::after {
  content: '';
  position: fixed;
  width: 400px; height: 400px;
  border-radius: 50%;
  background: rgba(195,155,95,0.05);
  bottom: -120px; right: -120px;
  pointer-events: none;
}

/* ── MAIN CARD ── */
.card {
  position: relative;
  display: flex;
  width: 100%;
  max-width: 900px;
  min-height: 540px;
  border-radius: 30px;
  box-shadow: 0 40px 100px rgba(0,0,0,0.5), 0 0 0 1px rgba(195,155,95,0.1);
  overflow: hidden;
  animation: cardIn 0.7s cubic-bezier(.22,1,.36,1) both;
  z-index: 1;
}
@keyframes cardIn {
  from { opacity:0; transform:translateY(36px) scale(0.96); }
  to   { opacity:1; transform:none; }
}

/* ── LEFT PANEL — White curved, like reference ── */
.panel-left {
  width: 45%;
  background: #ffffff;
  position: relative;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: space-between;
  padding: 32px 28px 28px;
  overflow: hidden;
  /* Curved right edge like reference design */
  clip-path: ellipse(100% 100% at 0% 50%);
}

/* subtle circle decorations like reference */
.deco-circle {
  position: absolute;
  border-radius: 50%;
  background: rgba(195,155,95,0.08);
  pointer-events: none;
}
.deco-circle.c1 { width:220px; height:220px; bottom:-60px; right:-60px; }
.deco-circle.c2 { width:140px; height:140px; bottom:60px; right:20px; background:rgba(195,155,95,0.05); }
.deco-circle.c3 { width:80px;  height:80px;  top:40px; right:30px; background:rgba(195,155,95,0.07); }

/* logo top-left corner */
.logo-corner {
  display: flex;
  align-items: center;
  gap: 10px;
  z-index: 2;
  position: relative;
}
.logo-corner img {
  width: 46px;
  height: 46px;
  object-fit: contain;
  border-radius: 50%;
}
.logo-corner-text {
  display: flex;
  flex-direction: column;
}
.logo-corner-text strong {
  font-size: 13px;
  font-weight: 900;
  color: #1e2d40;
  line-height: 1.2;
  letter-spacing: 0.2px;
}
.logo-corner-text span {
  font-size: 10px;
  color: #c39b5f;
  font-weight: 700;
  letter-spacing: 0.5px;
  text-transform: uppercase;
}

/* Center illustration area */
.illus-area {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1;
  padding-top: 40px;
}

/* SVG illustration — books/graduation themed, gold+navy */
.illus-svg {
  width: 82%;
  max-width: 300px;
  filter: drop-shadow(0 12px 28px rgba(30,45,64,0.15));
}

/* tagline bottom */
.left-footer {
  font-size: 11px;
  color: #94a3b8;
  font-weight: 600;
  z-index: 2;
  position: relative;
}

/* ── RIGHT PANEL — Dark, login form ── */
.panel-right {
  flex: 1;
  background: #1e2d40;
  background-image:
    radial-gradient(ellipse at 80% 20%, rgba(195,155,95,0.1) 0%, transparent 55%),
    radial-gradient(ellipse at 20% 80%, rgba(195,155,95,0.07) 0%, transparent 55%);
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 48px 44px;
}

.form-title {
  font-size: 28px;
  font-weight: 900;
  color: #fff;
  margin-bottom: 6px;
  text-align: center;
  letter-spacing: -0.3px;
}
.form-sub {
  font-size: 13px;
  color: rgba(255,255,255,0.45);
  text-align: center;
  margin-bottom: 28px;
  font-weight: 600;
}

.form-wrap { width: 100%; max-width: 300px; }

.error-msg {
  background: rgba(239,68,68,0.15);
  border: 1px solid rgba(239,68,68,0.3);
  color: #fca5a5;
  font-size: 13px;
  padding: 10px 14px;
  border-radius: 10px;
  margin-bottom: 16px;
  text-align: center;
  font-weight: 600;
}

.field { margin-bottom: 14px; }
.field label {
  display: block;
  font-size: 11.5px;
  font-weight: 800;
  color: rgba(255,255,255,0.55);
  margin-bottom: 6px;
  letter-spacing: 0.8px;
  text-transform: uppercase;
}
.field input,
.field select {
  width: 100%;
  padding: 11px 16px;
  border: 1.5px solid rgba(255,255,255,0.1);
  border-radius: 10px;
  font-size: 14px;
  font-family: 'Nunito', sans-serif;
  color: #fff;
  background: rgba(255,255,255,0.07);
  outline: none;
  transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
}
.field input::placeholder { color: rgba(255,255,255,0.3); }
.field input:focus,
.field select:focus {
  border-color: #c39b5f;
  background: rgba(195,155,95,0.08);
  box-shadow: 0 0 0 3px rgba(195,155,95,0.15);
}
.field select {
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24'%3E%3Cpath d='M7 10l5 5 5-5' stroke='rgba(255,255,255,0.4)' stroke-width='2' fill='none' stroke-linecap='round'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 18px;
  cursor: pointer;
  background-color: rgba(255,255,255,0.07);
}
.field select option { background: #1e2d40; color: #fff; }

.row-inline {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 20px;
}
.remember {
  display: flex;
  align-items: center;
  gap: 6px;
  font-size: 12px;
  color: rgba(255,255,255,0.45);
  cursor: pointer;
  font-weight: 600;
}
.remember input[type=checkbox] { accent-color: #c39b5f; }
.forgot-link {
  font-size: 12px;
  color: #c39b5f;
  text-decoration: none;
  font-weight: 800;
  transition: color 0.2s;
}
.forgot-link:hover { color: #e8c07a; }

.btn-login {
  width: 100%;
  padding: 13px;
  background: linear-gradient(135deg, #c39b5f 0%, #d4ad6f 50%, #b8893e 100%);
  color: #1e2d40;
  font-size: 15px;
  font-weight: 900;
  font-family: 'Nunito', sans-serif;
  border: none;
  border-radius: 10px;
  cursor: pointer;
  transition: transform 0.15s, box-shadow 0.2s;
  box-shadow: 0 4px 20px rgba(195,155,95,0.4);
  letter-spacing: 0.3px;
}
.btn-login:hover {
  transform: translateY(-2px);
  box-shadow: 0 8px 28px rgba(195,155,95,0.5);
}

.mode-tabs{display:flex;gap:8px;margin-bottom:20px;}
.mode-tab{flex:1;padding:10px;border:1.5px solid #2a3f58;border-radius:9px;background:#1e2d40;color:rgba(255,255,255,0.45);font-size:13px;font-weight:600;cursor:pointer;font-family:'Nunito',sans-serif;transition:all 0.18s;}
.mode-tab.active{background:linear-gradient(135deg,#c39b5f,#d4af72);color:#1e2d40;border-color:#c39b5f;}
.mode-tab:hover:not(.active){border-color:#c39b5f;color:rgba(195,155,95,0.8);}

/* ── MODALS ── */
.modal-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,0.5);
  backdrop-filter: blur(6px);
  z-index: 100; align-items: center; justify-content: center; padding: 20px;
}
.modal-overlay.active { display: flex; }
.modal {
  background: #1e2d40;
  border: 1px solid rgba(195,155,95,0.2);
  border-radius: 20px; padding: 36px;
  width: 100%; max-width: 380px;
  box-shadow: 0 24px 60px rgba(0,0,0,0.4);
  animation: slideUp 0.3s cubic-bezier(.22,1,.36,1) both;
}
@keyframes slideUp {
  from { opacity:0; transform:translateY(30px); }
  to   { opacity:1; transform:none; }
}
.modal h3 { font-size:20px; font-weight:900; color:#fff; margin-bottom:6px; }
.modal p.modal-sub { font-size:13px; color:rgba(255,255,255,0.45); margin-bottom:22px; }
.modal-actions { display:flex; gap:10px; margin-top:8px; }
.btn-send {
  flex:1; padding:11px;
  background: linear-gradient(135deg,#c39b5f,#b8893e);
  color:#1e2d40; border:none; border-radius:10px;
  font-size:14px; font-weight:800; font-family:'Nunito',sans-serif; cursor:pointer;
}
.btn-cancel {
  flex:1; padding:11px;
  background: rgba(255,255,255,0.08); color:rgba(255,255,255,0.6);
  border: 1px solid rgba(255,255,255,0.1); border-radius:10px;
  font-size:14px; font-weight:700; font-family:'Nunito',sans-serif; cursor:pointer;
  text-align:center; text-decoration:none; display:flex; align-items:center; justify-content:center;
}
.success-msg {
  background: rgba(34,197,94,0.15); border:1px solid rgba(34,197,94,0.3);
  color:#86efac; font-size:13px; padding:9px 14px; border-radius:8px;
  margin-bottom:14px; text-align:center;
}

/* ── TERMS POPUP ── */
.terms-overlay {
  display: none; position: fixed; inset: 0;
  background: rgba(0,0,0,0.65);
  backdrop-filter: blur(6px);
  z-index: 200; align-items: center; justify-content: center; padding: 20px;
}
.terms-overlay.active { display: flex; }
.terms-popup {
  background: #fff; border-radius: 22px;
  width: 100%; max-width: 560px; max-height: 88vh;
  box-shadow: 0 30px 80px rgba(0,0,0,0.4);
  animation: slideUp 0.35s cubic-bezier(.22,1,.36,1) both;
  display: flex; flex-direction: column; overflow: hidden;
}
.terms-head {
  padding: 20px 26px 16px; border-bottom: 1px solid #e2e8f0;
  display: flex; align-items: center; gap: 12px; flex-shrink: 0;
  background: #1e2d40;
}
.terms-head .t-icon {
  width:42px; height:42px; border-radius:11px;
  background: linear-gradient(135deg,#c39b5f,#b8893e);
  display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0;
}
.terms-head h3 { font-size:17px; font-weight:900; color:#fff; margin-bottom:2px; }
.terms-head p  { font-size:12px; color:rgba(255,255,255,0.5); font-weight:600; }
.terms-body { flex:1; overflow-y:auto; padding:20px 26px; }
.terms-body::-webkit-scrollbar { width:4px; }
.terms-body::-webkit-scrollbar-thumb { background:#cbd5e1; border-radius:4px; }
.t-section { margin-bottom:16px; }
.t-section h4 { font-size:13px; font-weight:800; color:#0f172a; margin-bottom:6px; display:flex; align-items:center; gap:7px; }
.t-num { width:20px; height:20px; border-radius:50%; background:#1e2d40; color:#fff; font-size:10px; font-weight:900; display:inline-flex; align-items:center; justify-content:center; flex-shrink:0; }
.t-section p { font-size:12.5px; color:#475569; line-height:1.7; }
.t-section ul { padding-left:16px; margin-top:4px; }
.t-section ul li { font-size:12.5px; color:#475569; line-height:1.7; margin-bottom:2px; }
.t-highlight { background:#fff7ed; border-left:3px solid #c39b5f; padding:8px 12px; border-radius:0 6px 6px 0; margin-top:6px; font-size:12px; color:#92400e; font-weight:600; }
.scroll-hint { text-align:center; font-size:11px; color:#94a3b8; font-weight:700; padding:8px 16px; background:#f8fafc; border-top:1px solid #f1f5f9; flex-shrink:0; transition:opacity 0.4s; }
.terms-footer { padding:14px 26px 18px; border-top:1px solid #e2e8f0; background:#f8fafc; flex-shrink:0; }
.must-read { font-size:11.5px; color:#c39b5f; font-weight:700; text-align:center; margin-bottom:10px; transition:opacity 0.3s; }
.terms-check-row { display:flex; align-items:flex-start; gap:9px; margin-bottom:13px; cursor:pointer; }
.terms-check-row input[type=checkbox] { width:15px; height:15px; margin-top:3px; accent-color:#1e2d40; cursor:pointer; flex-shrink:0; }
.terms-check-row span { font-size:12.5px; color:#334155; line-height:1.5; font-weight:600; }
.terms-check-row span strong { color:#0f172a; }
.btn-accept {
  width:100%; padding:12px;
  background: linear-gradient(135deg,#1e2d40,#2d4a6e); color:#fff;
  font-size:14px; font-weight:800; font-family:'Nunito',sans-serif;
  border:none; border-radius:10px; cursor:pointer;
  box-shadow:0 4px 14px rgba(30,45,64,0.3);
  transition:opacity 0.15s, transform 0.15s;
}
.btn-accept:hover:not(:disabled) { transform:translateY(-1px); }
.btn-accept:disabled { opacity:0.38; cursor:not-allowed; }

/* ── RESPONSIVE ── */
@media (max-width: 720px) {
  body { padding: 12px; }
  .card { flex-direction: column; max-width: 460px; min-height: unset; border-radius: 22px; }
  .panel-left {
    width: 100%;
    clip-path: none;
    border-radius: 0;
    padding: 24px 24px 0;
    min-height: unset;
    align-items: center;
  }
  .illus-area { position: relative; padding-top: 0; margin: 16px 0; }
  .illus-svg { width: 55%; max-width: 200px; }
  .left-footer { display: none; }
  .logo-corner { justify-content: center; }
  .panel-right { padding: 28px 28px 36px; }
  .form-title { font-size: 22px; }
}

@media (max-width: 420px) {
  .card { border-radius: 18px; }
  .illus-svg { width: 50%; max-width: 160px; }
  .panel-right { padding: 22px 20px 28px; }
  .form-title { font-size: 20px; }
  .terms-popup { border-radius: 16px; }
}
</style>
</head>
<body>

<div class="card">

  <!-- ══ LEFT PANEL ══ -->
  <div class="panel-left">
    <div class="deco-circle c1"></div>
    <div class="deco-circle c2"></div>
    <div class="deco-circle c3"></div>

    <!-- Logo top-left corner -->
    <div class="logo-corner">
      <img src="../imgs/COI logo.png" alt="COI Logo">
      <div class="logo-corner-text">
        <strong>CULTURE OF<br>INTERNET</strong>
        <span>Student Portal</span>
      </div>
    </div>

    <!-- Center Illustration — gold/navy themed study scene -->
    <div class="illus-area">
      <svg class="illus-svg" viewBox="0 0 320 300" fill="none" xmlns="http://www.w3.org/2000/svg">
        <!-- Ground / base circle glow -->
        <ellipse cx="160" cy="260" rx="120" ry="22" fill="rgba(195,155,95,0.12)"/>

        <!-- Big open book center -->
        <g transform="translate(70, 130)">
          <!-- left page -->
          <path d="M90 0 Q45 8 0 20 L0 110 Q45 100 90 95 Z" fill="#f0e8d5" stroke="#d4b87a" stroke-width="1.5"/>
          <!-- right page -->
          <path d="M90 0 Q135 8 180 20 L180 110 Q135 100 90 95 Z" fill="#e8dcc8" stroke="#c9a85c" stroke-width="1.5"/>
          <!-- spine -->
          <line x1="90" y1="0" x2="90" y2="95" stroke="#b8893e" stroke-width="3"/>
          <!-- left page lines -->
          <line x1="20" y1="38" x2="78" y2="34" stroke="#c9a85c" stroke-width="1.5" opacity="0.6"/>
          <line x1="18" y1="50" x2="76" y2="46" stroke="#c9a85c" stroke-width="1.5" opacity="0.6"/>
          <line x1="16" y1="62" x2="74" y2="58" stroke="#c9a85c" stroke-width="1.5" opacity="0.6"/>
          <line x1="14" y1="74" x2="72" y2="70" stroke="#c9a85c" stroke-width="1.5" opacity="0.6"/>
          <!-- right page lines -->
          <line x1="102" y1="34" x2="160" y2="38" stroke="#b8893e" stroke-width="1.5" opacity="0.6"/>
          <line x1="104" y1="46" x2="162" y2="50" stroke="#b8893e" stroke-width="1.5" opacity="0.6"/>
          <line x1="106" y1="58" x2="164" y2="62" stroke="#b8893e" stroke-width="1.5" opacity="0.6"/>
          <line x1="108" y1="70" x2="166" y2="74" stroke="#b8893e" stroke-width="1.5" opacity="0.6"/>
        </g>

        <!-- Stacked books left -->
        <rect x="30" y="210" width="70" height="14" rx="3" fill="#1e2d40" opacity="0.85"/>
        <rect x="34" y="197" width="62" height="14" rx="3" fill="#2d4a6e" opacity="0.85"/>
        <rect x="38" y="184" width="54" height="14" rx="3" fill="#c39b5f" opacity="0.9"/>

        <!-- Stacked books right -->
        <rect x="220" y="210" width="70" height="14" rx="3" fill="#c39b5f" opacity="0.85"/>
        <rect x="224" y="197" width="62" height="14" rx="3" fill="#1e2d40" opacity="0.85"/>
        <rect x="228" y="184" width="54" height="14" rx="3" fill="#2d4a6e" opacity="0.85"/>

        <!-- Graduation cap floating top center -->
        <g transform="translate(130, 30)">
          <!-- cap board -->
          <rect x="-30" y="18" width="60" height="8" rx="3" fill="#1e2d40"/>
          <!-- cap top -->
          <polygon points="0,0 -38,18 38,18" fill="#1e2d40"/>
          <!-- tassel -->
          <line x1="28" y1="18" x2="34" y2="38" stroke="#c39b5f" stroke-width="2"/>
          <circle cx="34" cy="40" r="4" fill="#c39b5f"/>
          <!-- gold trim on cap -->
          <rect x="-30" y="18" width="60" height="3" rx="1" fill="#c39b5f" opacity="0.7"/>
        </g>

        <!-- Pencil floating right -->
        <g transform="translate(270, 80) rotate(-30)">
          <rect x="-4" y="-30" width="8" height="50" rx="2" fill="#f0e8d5" stroke="#d4b87a" stroke-width="1"/>
          <polygon points="-4,20 4,20 0,32" fill="#c39b5f"/>
          <rect x="-4" y="-30" width="8" height="8" rx="1" fill="#1e2d40"/>
        </g>

        <!-- Star sparkles -->
        <circle cx="60"  cy="60"  r="3" fill="#c39b5f" opacity="0.7"/>
        <circle cx="260" cy="50"  r="2" fill="#c39b5f" opacity="0.5"/>
        <circle cx="280" cy="160" r="3" fill="#c39b5f" opacity="0.6"/>
        <circle cx="40"  cy="150" r="2" fill="#c39b5f" opacity="0.5"/>

        <!-- COI "C" monogram watermark center top -->
        <text x="160" y="118" text-anchor="middle" font-family="Nunito,sans-serif" font-size="13" font-weight="900" fill="#c39b5f" opacity="0.35" letter-spacing="2">LEARN · GROW</text>
      </svg>
    </div>

    <div class="left-footer">© Culture of Internet · Student Portal</div>
  </div>

  <!-- ══ RIGHT PANEL ══ -->
  <div class="panel-right">
    <div class="form-title">Student Login</div>
    <div class="form-sub">Access your learning dashboard</div>

    <div class="form-wrap">
      <?php if($error): ?>
        <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <form method="POST" action="" id="loginForm">
        <!-- MODE TABS -->
        <div class="mode-tabs">
          <button type="button" class="mode-tab active" onclick="switchMode('offline')">📴 Offline</button>
          <button type="button" class="mode-tab" onclick="switchMode('online')">🌐 Online</button>
        </div>

        <!-- OFFLINE/ONLINE FIELDS -->
        <div id="loginFields">
          <div class="field">
            <label>Registration No.</label>
            <input type="text" name="reg_no" placeholder="e.g. DMGD-0226-1441" autocomplete="off">
          </div>
          <div class="field">
            <label>Password</label>
            <input type="password" name="password" placeholder="Enter your password">
          </div>
          <input type="hidden" name="mode" value="offline" id="modeInput">
          <div class="row-inline">
            <label class="remember"><input type="checkbox" name="remember"> Remember me</label>
            <a href="?forgot=1" class="forgot-link">Forgot Password?</a>
          </div>
          <button type="submit" name="login" class="btn-login">Login →</button>
        </div>

      </form>

    </div>
  </div>
</div>

<!-- FORGOT MODAL -->
<div class="modal-overlay <?php echo $show_forgot ? 'active' : ''; ?>" id="forgotModal">
  <div class="modal">
    <h3>Reset Password</h3>
    <p class="modal-sub">Enter your registered email and we'll send a reset link.</p>
    <?php if($forgot_msg): ?>
      <?php $parts = explode(':', $forgot_msg, 2); $msgType = $parts[0]; $msgText = $parts[1] ?? ''; ?>
      <div class="<?= $msgType === 'success' ? 'success-msg' : 'error-msg' ?>"><?= htmlspecialchars($msgText) ?></div>
    <?php endif; ?>
    <form method="POST" action="?forgot=1">
      <div class="field">
        <label>Email Address</label>
        <input type="email" name="forgot_email" placeholder="your@email.com" required>
      </div>
      <div class="modal-actions">
        <button type="submit" name="forgot_submit" class="btn-send">Send Link</button>
        <a href="?" class="btn-cancel">Cancel</a>
      </div>
    </form>
  </div>
</div>

<!-- TERMS POPUP -->
<div class="terms-overlay <?php echo $show_terms_popup ? 'active' : ''; ?>" id="termsOverlay">
  <div class="terms-popup">
    <div class="terms-head">
      <div class="t-icon">📋</div>
      <div>
        <h3>Terms &amp; Conditions</h3>
        <p>Culture of Internet — Read carefully before continuing</p>
      </div>
    </div>
    <div class="terms-body" id="termsBody">
      <div class="t-section"><h4><span class="t-num">1</span> Code of Conduct</h4><p>Any indiscipline, bullying, misuse of institute rules, irregular attendance, or spreading false or negative content about the institute may result in <strong>immediate termination without refund</strong>.</p><div class="t-highlight">Management decision will be final and binding.</div></div>
      <div class="t-section"><h4><span class="t-num">2</span> Fees &amp; EMI Policy</h4><ul><li>EMI must be paid within <strong>7 days</strong> of due date.</li><li>EMI date changes require written proof + <strong>Rs.1,000 charge</strong>.</li><li>Fee details &amp; discounts are <strong>strictly confidential</strong>.</li><li>Disclosing fee/discount = <strong>immediate cancellation of discount</strong>.</li></ul></div>
      <div class="t-section"><h4><span class="t-num">3</span> Attendance &amp; Performance</h4><ul><li>Minimum <strong>85% Physical Attendance</strong> mandatory.</li><li>Minimum <strong>85% Homework Submission</strong> compulsory.</li><li>Late beyond <strong>15 minutes</strong> = marked absent.</li></ul><div class="t-highlight">Failure = No backup classes, No internship, No job assistance, No certificate.</div></div>
      <div class="t-section"><h4><span class="t-num">4</span> Course Duration &amp; Leave</h4><ul><li>Max leave: <strong>15 days</strong> (6-month) | <strong>30 days</strong> (12-month).</li><li>Exceeding leave = No backup classes.</li><li>Continuing after end date = <strong>Rs.100/day fine</strong>.</li></ul></div>
      <div class="t-section"><h4><span class="t-num">5</span> Internship &amp; Placement</h4><ul><li>We provide <strong>job assistance, not job guarantee</strong>.</li><li>3-month unpaid internship mandatory for placement.</li><li>No internship completion = No placement support.</li></ul></div>
      <div class="t-section"><h4><span class="t-num">6</span> Online Classes &amp; Portal</h4><ul><li>6-month: <strong>10 free online classes</strong> | 12-month: <strong>20 free</strong>.</li><li>Portal access for <strong>60 days after course completion</strong>.</li><li>Recorded classes for revision only — <strong>sharing prohibited</strong>.</li></ul></div>
      <div class="t-section"><h4><span class="t-num">7</span> Communication Policy</h4><ul><li>WhatsApp: <strong>+91 8130840080</strong></li><li>Email: <strong>contactcultureofinternet@gmail.com</strong></li></ul><div class="t-highlight">Institute not responsible for promises made outside official channels.</div></div>
      <div class="t-section"><h4><span class="t-num">8</span> Fees &amp; Refund Policy</h4><ul><li>Registration &amp; admission fees are <strong>non-refundable</strong>.</li><li>Course fees are <strong>non-transferable and non-refundable</strong>.</li></ul></div>
      <div class="t-section"><h4><span class="t-num">9</span> Certification Requirements</h4><ul><li>Maintain <strong>85% criteria</strong>.</li><li>Complete internship (if applicable).</li><li>Submit portfolio and testimonial.</li></ul><div class="t-highlight">Without fulfilling all conditions, certificate will NOT be issued.</div></div>
      <div class="t-section"><h4><span class="t-num">10</span> General Policy</h4><ul><li>GST (<strong>18%</strong>) applicable as per government rules.</li><li>Management reserves the right to update policies.</li></ul><div class="t-highlight">By enrolling, the student confirms they have read and agreed to ALL terms.</div></div>
    </div>
    <div class="scroll-hint" id="scrollHint">↓ Scroll down to read all terms</div>
    <div class="terms-footer">
      <div class="must-read" id="mustRead">Please scroll and read all terms first</div>
      <form method="POST" action="">
        <label class="terms-check-row">
          <input type="checkbox" id="termsCheck" disabled>
          <span>I have read and agree to the <strong>Terms &amp; Conditions</strong> of Culture of Internet.</span>
        </label>
        <button type="submit" name="accept_terms" class="btn-accept" id="acceptBtn" disabled>
          Accept — Continue to Dashboard
        </button>
      </form>
    </div>
  </div>
</div>

<script>
  document.querySelectorAll('.forgot-link').forEach(link => {
    link.addEventListener('click', e => {
      e.preventDefault();
      document.getElementById('forgotModal').classList.add('active');
    });
  });
  document.getElementById('forgotModal').addEventListener('click', function(e) {
    if (e.target === this) window.location.href = '?';
  });

  const termsBody  = document.getElementById('termsBody');
  const scrollHint = document.getElementById('scrollHint');
  const mustRead   = document.getElementById('mustRead');
  const termsCheck = document.getElementById('termsCheck');
  const acceptBtn  = document.getElementById('acceptBtn');
  let scrolledFull = false;

  if (termsBody) {
    termsBody.addEventListener('scroll', () => {
      const atBottom = termsBody.scrollTop + termsBody.clientHeight >= termsBody.scrollHeight - 30;
      if (atBottom && !scrolledFull) {
        scrolledFull = true;
        scrollHint.style.opacity = '0';
        mustRead.style.opacity   = '0';
        setTimeout(() => {
          scrollHint.style.display = 'none';
          mustRead.style.display   = 'none';
        }, 400);
        termsCheck.disabled = false;
      }
    });
  }
  if (termsCheck) {
    termsCheck.addEventListener('change', () => {
      acceptBtn.disabled = !termsCheck.checked;
    });
  }
</script>
<script>
function switchMode(mode) {
  const tabs      = document.querySelectorAll('.mode-tab');
  const modeInput = document.getElementById('modeInput');

  if (mode === 'offline') {
    modeInput.value = 'offline';
    tabs[0].classList.add('active');
    tabs[1].classList.remove('active');
  } else {
    modeInput.value = 'online';
    tabs[0].classList.remove('active');
    tabs[1].classList.add('active');
  }
}
</script>
</body>
</html>