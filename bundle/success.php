<?php
session_start();
require 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login-Sign-Up1.php');
    exit;
}

// optional: grab the last purchase record for display
$userId = $_SESSION['user_id'];
$stmt = $pdo->prepare("
    SELECT p.id, c.title, p.amount, p.purchased_at
    FROM purchases p
    JOIN courses c ON c.id = p.course_id
    WHERE p.user_id = ?
    ORDER BY p.purchased_at DESC
    LIMIT 1
");
$stmt->execute([$userId]);
$purchase = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Payment Successful - Culture of Internet</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<script src="https://cdn.tailwindcss.com"></script>
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
<style>
.fade-in {animation: fadeIn 0.6s ease forwards;}
@keyframes fadeIn {from {opacity:0; transform:translateY(20px);} to {opacity:1; transform:translateY(0);} }
</style>
</head>
<body class="bg-gray-50 text-gray-800">
<?php include('includes/header.php'); ?>

<div class="flex flex-col items-center justify-center min-h-screen px-4 text-center">
    <div class="bg-white rounded-xl shadow-lg p-8 max-w-md w-full fade-in">
        <div class="text-green-500 text-6xl mb-4"><i class="fas fa-check-circle"></i></div>
        <h1 class="text-2xl font-bold mb-2">Payment Successful</h1>
        <p class="text-gray-600 mb-6">Thank you for your purchase! Your course has been added to your account.</p>

        <?php if ($purchase): ?>
        <div class="bg-gray-100 rounded-lg p-4 mb-6 text-left">
            <p><strong>Course:</strong> <?= htmlspecialchars($purchase['title']) ?></p>
            <p><strong>Amount:</strong> ₹<?= number_format($purchase['amount'], 2) ?></p>
            <p><strong>Date:</strong> <?= htmlspecialchars($purchase['purchased_at']) ?></p>
            <p><strong>Order ID:</strong> #<?= str_pad($purchase['id'], 6, '0', STR_PAD_LEFT) ?></p>
        </div>
        <?php endif; ?>

        <a href="user-dashboard/dashboard.php" class="inline-block bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-md transition">Go to Dashboard</a>

        <p class="text-gray-500 text-sm mt-4" id="redirect-text">Redirecting you automatically in <span id="countdown">5</span> seconds...</p>
    </div>
</div>

<script>
let seconds = 5;
const countdown = document.getElementById('countdown');
const interval = setInterval(() => {
    seconds--;
    countdown.textContent = seconds;
    if (seconds <= 0) {
        clearInterval(interval);
        window.location.href = "user-dashboard/dashboard.php";
    }
}, 1000);
</script>

<?php include('includes/footer.php'); ?>
</body>
</html>
