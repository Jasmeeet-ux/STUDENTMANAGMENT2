<?php
require_once __DIR__ . '/../db.php';
session_start();
if (!isset($_SESSION['admin_username'])) { header("Location: login.php"); exit; }

// Create sub_admins table if not exists
$pdo->exec("CREATE TABLE IF NOT EXISTS sub_admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");
$pdo->exec("CREATE TABLE IF NOT EXISTS sub_admin_batches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sub_admin_id INT NOT NULL,
    batch_id INT NOT NULL
)");

if (isset($_POST['add_sub_admin'])) {
    $name     = trim($_POST['name']);
    $username = trim($_POST['username']);
    $password = password_hash(trim($_POST['password']), PASSWORD_BCRYPT);
    $batches  = $_POST['batch_ids'] ?? [];

    $check = $pdo->prepare("SELECT id FROM sub_admins WHERE username=?");
    $check->execute([$username]);
    if ($check->fetchColumn()) {
        $error = "Username already exists!";
    } else {
        $pdo->prepare("INSERT INTO sub_admins (name,username,password) VALUES (?,?,?)")
            ->execute([$name,$username,$password]);
        $sub_id = $pdo->lastInsertId();
        foreach ($batches as $bid) {
            $pdo->prepare("INSERT INTO sub_admin_batches (sub_admin_id,batch_id) VALUES (?,?)")
                ->execute([$sub_id,(int)$bid]);
        }
        header("Location: sub_admins_list.php?created=1"); exit;
    }
}

$batches = $pdo->query("
    SELECT b.*, c.course_name FROM batches b
    LEFT JOIN courses c ON c.id=b.course_id
    ORDER BY b.batch_name ASC
")->fetchAll(PDO::FETCH_ASSOC);

function h($v){ return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8'); }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Add Sub Admin | Admin</title>
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
.topbar{margin-bottom:24px;}
.topbar h1{font-size:22px;font-weight:800;color:#0f172a;}
.error{background:#fee2e2;border:1px solid #fca5a5;color:#dc2626;padding:12px 18px;border-radius:10px;margin-bottom:18px;font-size:13.5px;font-weight:500;}
.form-wrap{max-width:560px;}
.card{background:#fff;border-radius:14px;padding:28px;border:1px solid #e2e8f0;box-shadow:0 1px 6px rgba(0,0,0,0.04);}
.card h2{font-size:15px;font-weight:800;color:#0f172a;margin-bottom:20px;padding-bottom:12px;border-bottom:2px solid #f1f5f9;}
.field{margin-bottom:16px;}
.field label{display:block;font-size:11px;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:6px;}
.field input{width:100%;padding:10px 13px;border:1.5px solid #e2e8f0;border-radius:9px;font-size:13.5px;font-family:'DM Sans',sans-serif;color:#0f172a;background:#fafafa;outline:none;transition:border-color 0.2s;}
.field input:focus{border-color:#2563eb;background:#fff;}
.batch-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:10px;margin-top:6px;}
.batch-check{display:flex;align-items:center;gap:8px;background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:9px;padding:10px 12px;cursor:pointer;transition:all 0.15s;}
.batch-check:hover{border-color:#2563eb;background:#eff6ff;}
.batch-check input[type=checkbox]{width:16px;height:16px;accent-color:#2563eb;cursor:pointer;}
.batch-check-label{font-size:13px;font-weight:600;color:#0f172a;cursor:pointer;}
.batch-check-sub{font-size:11px;color:#64748b;margin-top:1px;}
.btn-submit{width:100%;padding:12px;background:linear-gradient(135deg,#2563eb,#1d4ed8);color:#fff;border:none;border-radius:10px;font-size:14px;font-weight:700;font-family:'DM Sans',sans-serif;cursor:pointer;margin-top:8px;}
.btn-submit:hover{opacity:0.9;}
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
  <a href="view_batch.php"><span class="sb-icon">👁️</span>View Batch</a>
  <a href="courses.php"><span class="sb-icon">📚</span>Courses</a>
  <div class="sb-sec">Sub Admins</div>
  <a href="add_sub_admin.php" class="active"><span class="sb-icon">👤</span>Add Sub Admin</a>
  <a href="sub_admins_list.php"><span class="sb-icon">👥</span>Sub Admins List</a>
  <div class="sb-bottom"><a href="logout.php"><span class="sb-icon">🚪</span>Logout</a></div>
</div>

<div class="main">
  <div class="topbar"><h1>👤 Add Sub Admin</h1></div>
  <?php if(isset($error)): ?><div class="error">❌ <?= h($error) ?></div><?php endif; ?>

  <div class="form-wrap">
    <div class="card">
      <h2>➕ Create Sub Admin Account</h2>
      <form method="POST">
        <div class="field"><label>Full Name</label><input type="text" name="name" placeholder="e.g. Rahul Sharma" required></div>
        <div class="field"><label>Username</label><input type="text" name="username" placeholder="e.g. rahul123" required></div>
        <div class="field"><label>Password</label><input type="password" name="password" placeholder="Set a strong password" required></div>
        <div class="field">
          <label>Assign Batches</label>
          <?php if($batches): ?>
          <div class="batch-grid">
            <?php foreach($batches as $b): ?>
            <label class="batch-check">
              <input type="checkbox" name="batch_ids[]" value="<?= $b['id'] ?>">
              <div>
                <div class="batch-check-label"><?= h($b['batch_name']) ?></div>
                <?php if(!empty($b['course_name'])): ?>
                  <div class="batch-check-sub"><?= h($b['course_name']) ?></div>
                <?php endif; ?>
              </div>
            </label>
            <?php endforeach; ?>
          </div>
          <?php else: ?>
            <p style="font-size:13px;color:#94a3b8;">No batches available. Create batches first.</p>
          <?php endif; ?>
        </div>
        <button type="submit" name="add_sub_admin" class="btn-submit">👤 Create Sub Admin</button>
      </form>
    </div>
  </div>
</div>
</body>
</html>