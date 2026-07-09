<?php
require_once "db.php"; // Your PDO + session functions

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// If admin is already logged in, redirect to dashboard
if (isset($_SESSION['admin_token']) && validateAdminSession($pdo)) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $email    = trim($_POST["email"]);
    $password = trim($_POST["password"]);

    // Fetch admin user
    $stmt = $pdo->prepare("
        SELECT id, name, email, password, role 
        FROM users 
        WHERE email = ? LIMIT 1
    ");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && strtolower($user["role"]) === "admin") {

        if (password_verify($password, $user["password"])) {

            // Create DB session (with device + IP)
            createAdminSession($pdo, $user["id"]);

            // Save admin name (for top-right initial)
            $_SESSION['admin_name'] = $user['name'];

            // Redirect
            header("Location: dashboard.php");
            exit;

        } else {
            $error = "Incorrect password!";
        }

    } else {
        $error = "No admin account found with this email!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Login</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">

<div class="bg-white w-full max-w-md p-6 rounded shadow">
    <h2 class="text-2xl font-bold mb-4">Admin Login</h2>

    <?php if ($error): ?>
        <div class="bg-red-100 text-red-700 p-2 rounded mb-3">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">
        <label class="block mb-2">Email</label>
        <input type="email" name="email" required class="w-full p-2 border mb-3 rounded">

        <label class="block mb-2">Password</label>
        <input type="password" name="password" required class="w-full p-2 border mb-4 rounded">

        <button class="w-full bg-indigo-600 text-white p-2 rounded">
            Login
        </button>
    </form>
</div>

</body>
</html>
