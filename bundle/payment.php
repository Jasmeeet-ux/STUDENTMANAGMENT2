<?php
// payment.php
session_start();
require 'db.php';
require 'includes/email.php';
require 'includes/invoice.php'; // you already use this

// Helpers used in your app: isDatabaseAvailable(), getCurrentUserId(), getCurrentUser()
// If they exist, great. If not, this file uses session user_id directly.

if (!function_exists('isDatabaseAvailable')) {
    function isDatabaseAvailable() { global $pdo; return isset($pdo) && $pdo; }
}
if (!function_exists('getCurrentUserId')) {
    function getCurrentUserId() { return $_SESSION['user_id'] ?? null; }
}
if (!function_exists('getCurrentUser')) {
    function getCurrentUser() {
        return [
            'id' => $_SESSION['user_id'] ?? null,
            'name' => $_SESSION['user_name'] ?? '',
            'email' => $_SESSION['user_email'] ?? ''
        ];
    }
}

// ensure DB available
if (!isDatabaseAvailable()) {
    header('Location: maintenance.php');
    exit;
}

// ensure logged in
if (!isset($_SESSION['user_id'])) {
    // Keep intended purchase saved (purchase-handler already does this, but safe)
    if (isset($_GET['course_id'])) {
        $_SESSION['intended_purchase'] = ['course_id' => (int)$_GET['course_id']];
    }
    header('Location: login-Sign-Up1.php');
    exit;
}

$userId = getCurrentUserId();
$user = getCurrentUser();

// get course_id from GET (we will load trusted price from DB)
$courseId = isset($_GET['course_id']) ? (int)$_GET['course_id'] : null;
if (!$courseId) {
    header('Location: pricings.php');
    exit;
}

// fetch course (title + price)
try {
    $stmt = $pdo->prepare("SELECT id, title, price FROM courses WHERE id = ?");
    $stmt->execute([$courseId]);
    $course = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log('payment.php: DB error fetching course: ' . $e->getMessage());
    header('Location: pricings.php');
    exit;
}

if (!$course) {
    header('Location: pricings.php');
    exit;
}

$coursePrice = (float)$course['price'];

// check if user already purchased this course
try {
    $stmt = $pdo->prepare("SELECT id FROM purchases WHERE user_id = ? AND course_id = ? AND status = 'completed'");
    $stmt->execute([$userId, $courseId]);
    $already = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($already) {
        // already purchased - send user to their courses
        header('Location: user-dashboard/my-courses.php');
        exit;
    }
} catch (Exception $e) {
    error_log('payment.php: error checking previous purchases: ' . $e->getMessage());
    // allow them to continue, but log error
}

// If this is the confirm POST, simulate payment and record in DB
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_payment'])) {
    try {
           $pdo->beginTransaction();

        // Insert purchase
        $purchaseStmt = $pdo->prepare("INSERT INTO purchases (user_id, course_id, amount, status, purchased_at) VALUES (?, ?, ?, 'completed', NOW())");
        $purchaseStmt->execute([$userId, $courseId, $coursePrice]);
        $purchaseId = $pdo->lastInsertId();

        // Insert transactions record
        $transStmt = $pdo->prepare("INSERT INTO transactions (user_id, amount, currency, gateway, status, created_at) VALUES (?, ?, 'INR', ?, 'completed', NOW())");
        $transStmt->execute([$userId, $coursePrice, 'MockGateway']);
        $transactionId = $pdo->lastInsertId();

        // Commission: check if the buyer was referred
        $stmt = $pdo->prepare("SELECT referred_by FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $referredBy = $stmt->fetchColumn();
        if ($referredBy) {
            // 25% commission
            $commission = round($coursePrice * 0.25, 2);

            // Insert into referrals table to track the commission
            $refIns = $pdo->prepare("INSERT INTO referrals (referrer_id, referred_user_id, commission, created_at) VALUES (?, ?, ?, NOW())");
            $refIns->execute([$referredBy, $userId, $commission]);

            // Update affiliates table - increment both total_earnings and pending_payout
            $affUp = $pdo->prepare("UPDATE affiliates SET total_earnings = COALESCE(total_earnings,0) + ?, pending_payout = COALESCE(pending_payout,0) + ? WHERE user_id = ?");
            $affUp->execute([$commission, $commission, $referredBy]);
        }

        $pdo->commit();

        // Generate invoice HTML and email
        $invoiceNumber = 'INV-' . str_pad($purchaseId, 6, '0', STR_PAD_LEFT);
        $purchaseDate = date('Y-m-d H:i:s');
        $purchaseDetails = [
            [
                'title' => $course['title'],
                'price' => $coursePrice,
                'quantity' => 1,
                'total' => $coursePrice
            ]
        ];
        $invoiceHTML = generateInvoiceHTML($user['name'], $user['email'], $purchaseDetails, $invoiceNumber, $purchaseDate);

        $emailBody = "
        <html><body>
            <h2>Purchase Confirmation</h2>
            <p>Dear " . htmlspecialchars($user['name']) . ",</p>
            <p>Thank you for purchasing <strong>" . htmlspecialchars($course['title']) . "</strong>.</p>
            <p><strong>Amount:</strong> ₹" . number_format($coursePrice, 2) . "</p>
            " . $invoiceHTML . "
            <p>Best regards,<br>Culture of Internet Team</p>
        </body></html>";

        // send email (use embedded images if you like)
        sendEmail($user['email'], 'Purchase Confirmation - ' . $course['title'], $emailBody);

        // clear intended purchase
        if (isset($_SESSION['intended_purchase'])) {
            unset($_SESSION['intended_purchase']);
        }

        // redirect to dashboard after success
header('Location: success.php');
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        error_log('payment.php: payment processing failed: ' . $e->getMessage());
        $message = 'Payment failed. Please try again later.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Payment - Culture of Internet</title>
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
<?php include('includes/header.php'); ?>

<main class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Complete Your Purchase</h1>

    <?php if (!empty($message)): ?>
        <div class="mb-4 text-red-600"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <div class="bg-white shadow rounded p-6">
        <h2 class="text-lg font-semibold"><?= htmlspecialchars($course['title']) ?></h2>
        <p class="mt-2">Price: <strong>₹<?= number_format($coursePrice, 2) ?></strong></p>

        <p class="mt-4 text-sm text-gray-600">This is a mock checkout for testing. No real payment will be processed.</p>

        <form method="POST" class="mt-6">
            <button type="submit" name="confirm_payment" class="px-6 py-3 bg-blue-600 text-white rounded">Confirm Payment ₹<?= number_format($coursePrice, 2) ?></button>
        </form>
    </div>
</main>

<?php include('includes/footer.php'); ?>
</body>
</html>
