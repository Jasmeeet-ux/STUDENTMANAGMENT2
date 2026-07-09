<?php
require_once __DIR__ . '/../db.php';
session_start();

if (isset($_SESSION['sub_admin_id'])) {
    header("Location: sub_admin_dashboard.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    if (!$username || !$password) {
        $error = "All fields are required.";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM sub_admins WHERE username = ?");
        $stmt->execute([$username]);
        $sub_admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$sub_admin) {
            $error = "Username not found.";
        } elseif (!password_verify($password, $sub_admin['password'])) {
            $error = "Incorrect password.";
        } else {
            $_SESSION['sub_admin_id']       = $sub_admin['id'];
            $_SESSION['sub_admin_name']     = $sub_admin['name'];
            $_SESSION['sub_admin_username'] = $sub_admin['username'];
            $_SESSION['sub_admin_batch_id'] = $sub_admin['batch_id'];
            header("Location: sub_admin_dashboard.php");
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sub Admin Login</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
*, *::before, *::after { margin:0; padding:0; box-sizing:border-box; }

body {
    font-family: 'DM Sans', sans-serif;
    min-height: 100vh;
    background: #1a0533;
    display: flex;
    align-items: center;
    justify-content: center;
    overflow: hidden;
    position: relative;
}

/* ── BACKGROUND SHAPES ── */
.bg {
    position: fixed;
    inset: 0;
    background: linear-gradient(135deg, #1a0533 0%, #2d1060 40%, #1a0533 100%);
    z-index: 0;
}

.shape {
    position: absolute;
    border-radius: 50%;
    opacity: 0.15;
    filter: blur(1px);
}
.shape-1 { width:220px; height:220px; border:2px solid #a855f7; top:8%; left:5%; border-radius:30% 70% 70% 30% / 30% 30% 70% 70%; }
.shape-2 { width:160px; height:160px; border:2px solid #7c3aed; bottom:15%; left:12%; border-radius:50%; }
.shape-3 { width:100px; height:100px; border:2px solid #c084fc; top:30%; left:25%; border-radius:20px; transform:rotate(45deg); }
.shape-4 { width:180px; height:180px; border:2px solid #9333ea; top:10%; right:8%; border-radius:50%; opacity:0.1; }
.shape-5 { width:80px; height:80px; background:#7c3aed; bottom:25%; right:5%; border-radius:50%; opacity:0.2; filter:blur(20px); }
.shape-6 { width:300px; height:300px; background:#581c87; top:50%; left:50%; transform:translate(-50%,-50%); border-radius:50%; opacity:0.15; filter:blur(60px); }

/* ── LAYOUT ── */
.container {
    position: relative;
    z-index: 1;
    width: 100%;
    max-width: 900px;
    min-height: 480px;
    display: flex;
    align-items: stretch;
    padding: 20px;
    gap: 0;
}

/* ── LEFT PANEL ── */
.left-panel {
    flex: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
    padding: 40px 50px 40px 20px;
    color: #fff;
}

.logo {
    display: flex;
    gap: 4px;
    margin-bottom: 48px;
}
.logo span {
    display: block;
    background: #fff;
    border-radius: 3px;
}
.logo span:first-child { width:14px; height:28px; }
.logo span:last-child  { width:14px; height:28px; margin-top:8px; }

.left-panel h1 {
    font-size: 52px;
    font-weight: 800;
    color: #fff;
    line-height: 1.1;
    margin-bottom: 16px;
    letter-spacing: -1px;
}

.left-panel .divider {
    width: 40px; height: 3px;
    background: #fff;
    border-radius: 2px;
    margin-bottom: 20px;
    opacity: 0.6;
}

.left-panel p {
    font-size: 13.5px;
    color: rgba(255,255,255,0.55);
    line-height: 1.7;
    max-width: 280px;
    margin-bottom: 32px;
}

.btn-learn {
    display: inline-flex;
    align-items: center;
    padding: 11px 26px;
    background: linear-gradient(135deg, #f97316, #ef4444);
    color: #fff;
    border-radius: 25px;
    font-size: 13px;
    font-weight: 700;
    text-decoration: none;
    width: fit-content;
    transition: opacity 0.2s, transform 0.2s;
}
.btn-learn:hover { opacity: 0.9; transform: translateY(-1px); }

/* ── RIGHT PANEL (GLASS CARD) ── */
.glass-card {
    width: 360px;
    flex-shrink: 0;
    background: rgba(255,255,255,0.07);
    backdrop-filter: blur(20px);
    -webkit-backdrop-filter: blur(20px);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 20px;
    padding: 44px 36px;
    display: flex;
    flex-direction: column;
    justify-content: center;
    box-shadow: 0 24px 60px rgba(0,0,0,0.4);
}

.glass-card h2 {
    font-size: 28px;
    font-weight: 800;
    color: #fff;
    text-align: center;
    margin-bottom: 6px;
    letter-spacing: -0.5px;
}

.underline-accent {
    width: 40px; height: 3px;
    background: linear-gradient(90deg, #f97316, #ef4444);
    border-radius: 2px;
    margin: 0 auto 30px;
}

/* Error */
.error-msg {
    background: rgba(239,68,68,0.15);
    border: 1px solid rgba(239,68,68,0.3);
    color: #fca5a5;
    font-size: 12.5px;
    padding: 9px 14px;
    border-radius: 8px;
    margin-bottom: 16px;
    text-align: center;
}

/* Fields */
.field {
    margin-bottom: 18px;
}
.field label {
    display: block;
    font-size: 12.5px;
    font-weight: 600;
    color: rgba(255,255,255,0.7);
    margin-bottom: 8px;
    letter-spacing: 0.3px;
}
.field input {
    width: 100%;
    padding: 12px 16px;
    background: rgba(255,255,255,0.08);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 10px;
    font-size: 14px;
    font-family: 'DM Sans', sans-serif;
    color: #fff;
    outline: none;
    transition: border-color 0.2s, background 0.2s;
}
.field input::placeholder { color: rgba(255,255,255,0.3); }
.field input:focus {
    border-color: rgba(168,85,247,0.6);
    background: rgba(255,255,255,0.12);
    box-shadow: 0 0 0 3px rgba(168,85,247,0.15);
}

/* Submit */
.btn-submit {
    width: 100%;
    padding: 13px;
    background: linear-gradient(135deg, #f97316, #ef4444);
    color: #fff;
    border: none;
    border-radius: 25px;
    font-size: 15px;
    font-weight: 700;
    font-family: 'DM Sans', sans-serif;
    cursor: pointer;
    margin-top: 8px;
    transition: opacity 0.2s, transform 0.15s;
    box-shadow: 0 6px 20px rgba(249,115,22,0.4);
    letter-spacing: 0.5px;
}
.btn-submit:hover { opacity: 0.92; transform: translateY(-1px); }

/* Social icons */
.social-row {
    display: flex;
    justify-content: center;
    gap: 18px;
    margin-top: 24px;
}
.social-row a {
    color: rgba(255,255,255,0.4);
    font-size: 18px;
    text-decoration: none;
    transition: color 0.2s;
    font-weight: 700;
}
.social-row a:hover { color: rgba(255,255,255,0.8); }

@media (max-width: 640px) {
    .container { flex-direction: column; align-items: center; padding: 30px 16px; }
    .left-panel { padding: 20px; text-align: center; align-items: center; }
    .left-panel h1 { font-size: 36px; }
    .glass-card { width: 100%; max-width: 360px; }
}
</style>
</head>
<body>

<div class="bg">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="shape shape-3"></div>
    <div class="shape shape-4"></div>
    <div class="shape shape-5"></div>
    <div class="shape shape-6"></div>
</div>

<div class="container">

    <!-- LEFT -->
    <div class="left-panel">
        <div class="logo">
            <span></span>
            <span></span>
        </div>
        <h1>Welcome!</h1>
        <div class="divider"></div>
        <p>Sub Admin Portal — Manage your batch students, mark attendance and track performance.</p>
        <a href="#" class="btn-learn">Learn More</a>
    </div>

    <!-- RIGHT GLASS CARD -->
    <div class="glass-card">
        <h2>Sign In</h2>
        <div class="underline-accent"></div>

        <?php if ($error): ?>
            <div class="error-msg">⚠️ <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="field">
                <label>User Name</label>
                <input type="text" name="username" placeholder="Enter username" required autocomplete="off">
            </div>
            <div class="field">
                <label>Password</label>
                <input type="password" name="password" placeholder="••••••••••" required>
            </div>
            <button type="submit" name="login" class="btn-submit">Submit</button>
        </form>

        <div class="social-row">
            <a href="#">f</a>
            <a href="#">in</a>
            <a href="#">p</a>
        </div>
    </div>

</div>

</body>
</html>