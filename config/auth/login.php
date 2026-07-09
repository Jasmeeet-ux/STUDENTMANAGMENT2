<?php
require_once '../db.php';
session_start();

$error = "";

if (isset($_POST['login'])) {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
}
if (isset($_POST['login'])) {

    if ($username == "" || $password == "") {
        $error = "All fields are required";
    } else {

        $stmt = $pdo->prepare("SELECT * FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            $error = "Admin not found";
        } else {
            if (!password_verify($password, $admin['password'])) {
    $error = "Wrong password";
} else {

    $_SESSION['admin_id'] = $admin['id'];
    $_SESSION['admin_username'] = $admin['username'];

    header("Location: dashboard.php");
    exit;
}

        
        }
    }
}


?>


<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login</title>

<style>
*{
    margin:0;
    padding:0;
    box-sizing:border-box;
    font-family: 'Segoe UI', sans-serif;
}

body{
    height:100vh;
    background:#f4f4f4;
}

/* top purple area */
.header{
    height:45vh;
    background:linear-gradient(135deg,#7b2ff7,#9f5cff);
    border-bottom-left-radius:60px;
    border-bottom-right-radius:60px;
    display:flex;
    align-items:center;
    justify-content:center;
    color:white;
    text-align:center;
}

.header h1{
    font-size:28px;
}

.header p{
    margin-top:8px;
    opacity:0.9;
}

/* login card */
.login-card{
    width:350px;
    background:#fff;
    margin:-120px auto 0;
    padding:30px;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.15);
}

.login-card h2{
    text-align:center;
    margin-bottom:20px;
    color:#333;
}

.input-group{
    margin-bottom:15px;
}

.input-group label{
    display:block;
    font-size:14px;
    margin-bottom:5px;
    color:#555;
}

.input-group input{
    width:100%;
    padding:10px;
    border:1px solid #ccc;
    border-radius:6px;
    outline:none;
}

.input-group input:focus{
    border-color:#7b2ff7;
}

.login-btn{
    width:100%;
    padding:10px;
    background:#7b2ff7;
    border:none;
    color:white;
    font-size:16px;
    border-radius:6px;
    cursor:pointer;
}

.login-btn:hover{
    background:#6926d9;
}

.footer-text{
    text-align:center;
    margin-top:15px;
    font-size:13px;
    color:#777;
}
</style>
</head>

<body>

<div class="header">
    <div>
        <h1>LOGO</h1>
        <p>Hello 👋 Welcome!</p>
    </div>
</div>

<div class="login-card">
    <h2>Login</h2>
    <?php if ($error): ?>
    <p style="color:red; text-align:center; margin-bottom:10px;">
        <?= $error ?>
    </p>
<?php endif; ?>


    <form method="POST">
        <div class="input-group">
            <label>Admin Username</label>
            <input type="text" name="username" required>
        </div>

        <div class="input-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <button class="login-btn" name="login">Login</button>
    </form>

    <div class="footer-text">
        Admin Panel Access Only
    </div>
</div>

</body>
</html>
