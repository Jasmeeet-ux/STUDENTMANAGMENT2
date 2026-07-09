<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

require 'db.php';           // NOW gives $pdo + $dbAvailable
require 'includes/email.php';

// ===================== INTENDED PURCHASE HANDLER =====================
if (isset($_GET['course_id']) && isset($_GET['price'])) {
    $_SESSION['intended_purchase'] = [
        'course_id' => $_GET['course_id'],
        'price'     => $_GET['price']
    ];
}

// If already logged in → go directly where needed
if (isset($_SESSION['user_id'])) {

    if (!empty($_SESSION['intended_purchase'])) {
        $int = $_SESSION['intended_purchase'];
        unset($_SESSION['intended_purchase']);

        header("Location: payment.php?course_id=" . urlencode($int['course_id']) .
               "&price=" . urlencode($int['price']));
        exit;
    }

    header("Location: user-dashboard/dashboard.php");
    exit();
}


// =============================== ERROR HANDLERS ===============================
$signupError = "";
$loginError  = "";
$signupSuccess = "";

if (!isDatabaseAvailable()) {
    header("Location: maintenance.php");
    exit;
}


// ============================================================================
//                               FORM HANDLING
// ============================================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $mode = $_POST['mode'] ?? 'signup';

    // ========================================================================
    //                                SIGNUP
    // ========================================================================
    if ($mode === 'signup') {

        $firstName    = trim($_POST['firstName'] ?? '');
        $lastName     = trim($_POST['lastName'] ?? '');
        $email        = trim($_POST['email'] ?? '');
        $password     = $_POST['password'] ?? '';
        $referralCode = trim($_POST['referralCode'] ?? '');
        $terms        = isset($_POST['terms']);

        if (empty($referralCode)) {
            $signupError = "A valid referral code is required.";
        } elseif (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
            $signupError = "All fields are required.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $signupError = "Invalid email address.";
        } elseif (strlen($password) < 6) {
            $signupError = "Password must be at least 6 characters.";
        } elseif (!$terms) {
            $signupError = "You must accept the terms and conditions.";
        }

        if (!$signupError) {

            // -------------------------------------------------------------
            // CHECK IF EMAIL EXISTS
            // -------------------------------------------------------------
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $signupError = "An account with this email already exists.";
            }
        }

        if (!$signupError) {

            // -------------------------------------------------------------
            // VALIDATE REFERRAL CODE
            // -------------------------------------------------------------
            $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ?");
            $stmt->execute([$referralCode]);
            $referrerId = $stmt->fetchColumn();

            if (!$referrerId) {
                $signupError = "Invalid or expired referral code.";
            }
        }


        if (!$signupError) {

            // -------------------------------------------------------------
            // CREATE USER
            // -------------------------------------------------------------
            $pdo->beginTransaction();

            try {
                $name           = $firstName . ' ' . $lastName;
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

                // generate user referral code
                $namePart   = strtoupper(substr(str_replace(" ", "", $name), 0, 4));
                $randomPart = rand(1000, 99999);
                $newReferralCode = $namePart . $randomPart;

                // Insert user
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, email, password, referral_code, referred_by, role, created_at)
                    VALUES (?, ?, ?, ?, ?, 'student', NOW())
                ");
                $stmt->execute([$name, $email, $hashedPassword, $newReferralCode, $referrerId]);

                $userId = $pdo->lastInsertId();

                // Create affiliate + referral entry
                $pdo->prepare("INSERT INTO affiliates (user_id, total_earnings, pending_payout) VALUES (?,0,0)")
                    ->execute([$userId]);

                $pdo->prepare("INSERT INTO referrals (referrer_id, referred_user_id, commission, created_at)
                               VALUES (?, ?, 0, NOW())")
                    ->execute([$referrerId, $userId]);

                $pdo->commit();

                // Create session
                $_SESSION['user_id'] = $userId;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;

                // Send welcome email
                $welcomeBody = "
                <html><body>
                <h2>Welcome to Culture of Internet!</h2>
                <p>Your account has been created successfully.</p>
                </body></html>";

                sendEmail($email, "Welcome to Culture of Internet", $welcomeBody);


                // Redirect to intended purchase
                if (!empty($_SESSION['intended_purchase'])) {
                    $int = $_SESSION['intended_purchase'];
                    unset($_SESSION['intended_purchase']);

                    header("Location: payment.php?course_id=" . urlencode($int['course_id']) .
                           "&price=" . urlencode($int['price']));
                    exit;
                }

                header("Location: pricings.php");
                exit;

            } catch (Exception $e) {
                $pdo->rollBack();
                $signupError = "Registration failed. Please try again.";
            }
        }
    }


    // ========================================================================
    //                                 LOGIN
    // ========================================================================
    else if ($mode === 'login') {

        $email    = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $loginError = "Please fill in all fields.";
        }

        if (!$loginError) {

            $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['password'])) {
                $loginError = "Invalid email or password.";
            } else {

                // Admin safeguard
                if ($user['role'] === 'admin') {
                    $loginError = "Admins must log in through admin login.";
                } else {

                    // Create session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_name'] = $user['name'];
                    $_SESSION['user_email'] = $user['email'];

                    // Continue intended purchase
                    if (!empty($_SESSION['intended_purchase'])) {
                        $int = $_SESSION['intended_purchase'];
                        unset($_SESSION['intended_purchase']);

                        header("Location: payment.php?course_id=" . urlencode($int['course_id']) .
                               "&price=" . urlencode($int['price']));
                        exit;
                    }

                    header("Location: pricings.php");
                    exit;
                }
            }
        }
    }
}
?>

<!doctype html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <link rel="icon" type="png" href="images/favicon_coi.png" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login & Sign Up</title>
    <link rel="stylesheet" href="css/login-Sign-Up.css" />
</head>

<style>
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
            'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue',
            sans-serif;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        min-height: 100vh;
        background-color: #ffffff;
        background-image: 
            linear-gradient(rgba(0, 0, 0, 0.1) 1px, transparent 1px),
            linear-gradient(90deg, rgba(0, 0, 0, 0.1) 1px, transparent 1px);
        background-size: 20px 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #f5f3f0;
        background-image:
            radial-gradient(circle at 20% 30%, rgba(190, 134, 82, 0.08) 0%, transparent 45%),
            radial-gradient(circle at 80% 70%, rgba(235, 190, 129, 0.06) 0%, transparent 35%),
            repeating-linear-gradient(45deg, rgba(190, 134, 82, 0.015) 0%, rgba(190, 134, 82, 0.015) 1px, transparent 1px, transparent 12px),
            linear-gradient(180deg, #f5f3f0 0%, #eae7e2 100%);
        background-attachment: fixed;
        color: #2d2b29;
    }

    .ls-container {
        margin-top: 7rem;
        display: flex;
        width: 100%;
        max-width: 1100px;
        min-height: 568px;
        background: rgba(255, 255, 255, 0.85);
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 25px 60px rgba(190, 134, 82, 0.15), 0 0 0 1px rgba(190, 134, 82, 0.08);
        animation: fadeIn 0.6s ease-out;
        /* margin: 84px auto; */
        backdrop-filter: blur(10px);
        -webkit-backdrop-filter: blur(10px);
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }

        to {
            opacity: 1;
            transform: scale(1);
        }
    }

    .ls-left-section {
        flex: 1;
        /* background:#6b5139; */
        background: linear-gradient(135deg, #000000 0%, #be8652 100%);
        /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
        padding: 20px;
        display: flex;
        flex-direction: column;
        position: relative;
        overflow: hidden;
    }

    /* .left-section::before {
  content: '';
  position: absolute;
  top: -50%;
  left: -50%;
  width: 200%;
  height: 200%;
  background: radial-gradient(circle, rgba(255, 255, 255, 0.1) 0%, transparent 70%);
  animation: rotate 20s linear infinite;
} */

    @keyframes rotate {
        from {
            transform: rotate(0deg);
        }

        to {
            transform: rotate(360deg);
        }
    }

    /* 
    .ls-left-section::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        right: 0;
        height: 60%;
        background: linear-gradient(to top, rgba(0, 0, 0, 0.3) 0%, transparent 100%);
    } */

    /* .logo {
  position: relative;
  z-index: 1;
  margin-bottom: 20px;
  animation: slideDown 0.6s ease-out 0.2s both;
} */

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* .back-btn {
  position: relative;
  z-index: 1;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  background: rgba(255, 255, 255, 0.15);
  border: 1px solid rgba(255, 255, 255, 0.2);
  color: white;
  padding: 10px 20px;
  border-radius: 10px;
  font-size: 14px;
  cursor: pointer;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  backdrop-filter: blur(10px);
  width: fit-content;
  animation: slideDown 0.6s ease-out 0.3s both;
}

.back-btn:hover {
  background: rgba(255, 255, 255, 0.25);
  transform: translateX(-4px);
  box-shadow: 0 4px 15px rgba(255, 255, 255, 0.2);
} */

    .ls-slider {
        position: relative;
        z-index: 1;
        flex: 1;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        margin-top: auto;
        animation: slideUp 0.6s ease-out 0.4s both;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .ls-quotes-container {
        position: relative;
        min-height: 200px;
    }

    .ls-quote {
        position: absolute;
        width: 100%;
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.5s ease, transform 0.5s ease;
    }

    .ls-quote.ls-active {
        opacity: 1;
        transform: translateY(0);
    }

    .ls-quote-icon {
        margin-bottom: 20px;
        animation: float 3s ease-in-out infinite;
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0);
        }

        50% {
            transform: translateY(-10px);
        }
    }

    .ls-quotes-container h2 {
        color: white;
        font-size: 38px;
        font-weight: 600;
        line-height: 1.3;
        margin-bottom: 12px;
        letter-spacing: -0.5px;
    }

    .ls-quote-subtitle {
        color: rgba(255, 255, 255, 0.8);
        font-size: 16px;
        margin-top: 12px;
        font-weight: 300;
    }

    .ls-slider-dots {
        display: flex;
        gap: 10px;
        margin-top: -20px;
    }

    .ls-dot {
        width: 36px;
        height: 4px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 2px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        cursor: pointer;
    }

    .ls-dot:hover {
        background: rgba(255, 255, 255, 0.5);
    }

    .ls-dot.ls-active {
        background: white;
        width: 48px;
    }

    .ls-right-section {
        flex: 1;
        background: rgba(255, 255, 255, 0.95);
        padding: 20px 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow-y: auto;
        position: relative;
        box-shadow: -1px 0 0 rgba(190, 134, 82, 0.1);
    }

    .ls-right-section::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        width: 1px;
        height: 100%;
        background: linear-gradient(to bottom,
                rgba(190, 134, 82, 0) 0%,
                rgba(190, 134, 82, 0.1) 30%,
                rgba(190, 134, 82, 0.1) 70%,
                rgba(190, 134, 82, 0) 100%);
    }

    .ls-form-container {
        width: 100%;
        max-width: 750px;
        /* max-width: 400px; */
        animation: slideIn 0.6s ease-out 0.2s both;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateX(30px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    #ls-form-title {
        color: rgb(0, 0, 0);
        font-size: 32px;
        font-weight: 600;
        margin-bottom: 12px;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .ls-toggle-text {
        color: #000000;
        font-size: 14px;
        margin-bottom: 32px;
        transition: all 0.3s ease;
    }

    .ls-toggle-text a {
        color: #be8652;
        text-decoration: underline;
        margin-left: 4px;
        cursor: pointer;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .ls-toggle-text a:hover {
        color: #764ba2;
        text-decoration: underline;
    }

    #ls-auth-form {
        display: flex;
        flex-direction: column;
        gap: 18px;
    }

    .ls-name-fields {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
        opacity: 1;
        max-height: 500px;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .ls-name-fields.ls-hiding {
        opacity: 0;
        max-height: 0;
        margin: 0;
        transform: translateY(-10px);
    }

    .ls-referral-field {
        opacity: 1;
        max-height: 500px;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .ls-referral-field.ls-hiding {
        opacity: 0;
        max-height: 0;
        margin: 0;
        transform: translateY(-10px);
    }

    input[type="text"],
    input[type="email"],
    input[type="password"] {
        width: 100%;
        padding: 15px 18px;
        background: rgba(82, 82, 82, 0.05);
        border: 1.5px solid rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        color: rgb(0, 0, 0);
        font-size: 14px;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    input[type="text"]::placeholder,
    input[type="email"]::placeholder,
    input[type="password"]::placeholder {
        color: #6b7280;
    }

    input[type="text"]:focus,
    input[type="email"]:focus,
    input[type="password"]:focus {
        outline: none;
        border-color: #363636;
        background: rgba(108, 108, 108, 0.08);
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    input[type="text"]:hover,
    input[type="email"]:hover,
    input[type="password"]:hover {
        border-color: rgba(255, 255, 255, 0.2);
        border-radius: 10px;
    }

    .ls-password-field {
        position: relative;
    }

    .ls-toggle-password {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #6b7280;
        cursor: pointer;
        padding: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        border-radius: 6px;
    }

    .ls-toggle-password:hover {
        color: white;
        background: rgba(255, 255, 255, 0.05);
    }

    .ls-forgot-password {
        text-align: right;
        opacity: 0;
        max-height: 0;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        transform: translateY(-10px);
    }

    .ls-forgot-password.ls-showing {
        opacity: 1;
        max-height: 50px;
        transform: translateY(0);
    }

    .ls-forgot-password a {
        color: #be8652;
        font-size: 14px;
        text-decoration: underline;
        transition: color 0.3s ease;
        font-weight: 500;
    }

    .ls-forgot-password a:hover {
        color: #764ba2;
        text-decoration: underline;
    }

    .ls-terms-checkbox {
        display: flex;
        align-items: flex-start;
        gap: 10px;
        opacity: 1;
        max-height: 500px;
        overflow: hidden;
        transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .ls-terms-checkbox.ls-hiding {
        opacity: 0;
        max-height: 0;
        margin: 0;
        transform: translateY(-10px);
    }

    .ls-terms-checkbox input[type="checkbox"] {
        margin-top: 2px;
        width: 20px;
        height: 20px;
        cursor: pointer;
        accent-color: #667eea;
        transition: transform 0.2s ease;
    }

    .ls-terms-checkbox input[type="checkbox"]:hover {
        transform: scale(1.1);
    }

    .ls-terms-checkbox label {
        color: #000000;
        font-size: 14px;
        line-height: 1.5;
    }

    .ls-terms-checkbox label a {
        color: #be8652;
        text-decoration: underline;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .ls-terms-checkbox label a:hover {
        color: #764ba2;
        text-decoration: underline;
    }

    .ls-submit-btn {
        width: 55%;
        /* width: 100%; */
        padding: 15px;
        background: linear-gradient(135deg, #000000 0%, #be8652 100%);
        /* background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); */
        border: none;
        border-radius: 10px;
        color: white;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        margin-top: 10px;
        box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3);
        margin-left: auto;
        margin-right: auto;
    }

    .ls-submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
    }

    .ls-submit-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 10px rgba(102, 126, 234, 0.3);
    }

    @media (max-width: 968px) {
        .ls-container {
            flex-direction: column;
            max-width: 970px;
            /* max-width: 500px; */
            /* max-width: 500px; */
            margin-top: 0px;
        }

        .ls-left-section {
            min-height: 240px;
            /* min-height: 400px; */
            /* min-height: 180px; */
            /* min-height: 350px; */
            padding: 40px 30px;
        }

        .ls-quotes-container h2 {
            font-size: 30px;

        }

        .ls-right-section {
            padding: 40px 30px;
        }

        #ls-form-title {
            font-size: 28px;
        }

        .ls-slider-dots {
            margin-top: -170px;
            /* margin-top: -130px; */
            margin-bottom: -10px;
        }

        .ls-left-img {
            margin-top: -100px;
            display: none;

        }

        .ls-quote-icon {
            display: none;
        }

        .ls-quotes-container h2 {
            margin-top: -70px;
        }

        .black-div {
            height: 80px;
            background: linear-gradient(to bottom,
                    rgba(0, 0, 0, 0.95) 0%,
                    rgba(10, 9, 8, 0.88) 100%);
        }

    }

    @media (max-width: 640px) {
        /* body { */
            /* padding: 10px; */
        /* } */



        .ls-left-section {
            /* min-height: 160px; */
            padding: 30px 24px;
        }

        .ls-quotes-container h2 {
            font-size: 26px;
        }

        .ls-quote-subtitle {
            font-size: 14px;
        }

        .ls-right-section {
            padding: 30px 20px;
        }

        .ls-form-container {
            max-width: 100%;
        }

        #ls-form-title {
            font-size: 26px;
        }

        .ls-name-fields {
            grid-template-columns: 1fr;
        }

        .ls-back-btn {
            font-size: 13px;
            padding: 8px 16px;
        }

        .ls-slider-dots {
            margin-top: -175px;
            /* margin-top: -50px; */
        }
    }

    @media (max-width: 400px) {
        .ls-left-section {
            min-height: 220px;
            /* min-height: 400px; */
            /* min-height: 121px; */
            padding: 24px 20px;
        }

        .ls-quotes-container h2 {
            font-size: 22px;
        }

        .ls-right-section {
            padding: 24px 16px;
        }

        #ls-form-title {
            font-size: 24px;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            padding: 13px 16px;
            font-size: 13px;
        }

        .ls-submit-btn {
            padding: 13px;
            font-size: 15px;
        }


        .ls-slider-dots {
            margin-top: -193px;
        }



    }

    .ls-left-img {
        /* align-items: center; */
        height: 80%;
        width: 100%;
        /* width: 60%; */
        margin-bottom: -180px;
        margin-left: auto;
        margin-right: auto;
    }

    .black-div {
        width: 100%;
        height: 120px;
        background: linear-gradient(to bottom,
                rgba(0, 0, 0, 0.92) 0%,
                rgba(10, 9, 8, 0.85) 100%);
        position: relative;
        overflow: hidden;
    }

    .black-div::after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 100%;
        height: 1px;
        background: linear-gradient(to right,
                rgba(190, 134, 82, 0) 0%,
                rgba(190, 134, 82, 0.15) 50%,
                rgba(190, 134, 82, 0) 100%);
    }
</style>

<body>
    <?php include('includes/header.php') ?>

    <div class="ls-container">
        <div class="ls-left-section">
            <img src="images/gMMelbR6U7.gif" alt="animation" class="ls-left-img">

            <div class="ls-slider">
                <div class="ls-quotes-container">
                    <div class="ls-quote ls-active">
                        <svg class="ls-quote-icon" width="40" height="40" viewBox="0 0 24 24" fill="none">
                            <path d="M10 8C8.34315 8 7 9.34315 7 11V12H10C11.1046 12 12 12.8954 12 14V17C12 18.1046 11.1046 19 10 19H7C5.89543 19 5 18.1046 5 17V11C5 8.23858 7.23858 6 10 6V8Z" fill="rgba(255,255,255,0.3)" />
                            <path d="M19 8C17.3431 8 16 9.34315 16 11V12H19C20.1046 12 21 12.8954 21 14V17C21 18.1046 20.1046 19 19 19H16C14.8954 19 14 18.1046 14 17V11C14 8.23858 16.2386 6 19 6V8Z" fill="rgba(255,255,255,0.3)" />
                        </svg>
                        <h2>Capturing Moments, Creating Memories</h2>
                    </div>
                </div>
                <div class="ls-slider-dots">
                    <span class="ls-dot"></span>
                    <span class="ls-dot"></span>
                    <span class="ls-dot ls-active"></span>
                </div>
            </div>
        </div>

        <div class="ls-right-section">
            <div class="ls-form-container">

                <?php
                // Determine initial mode: if login error, stay in login mode; else default to signup
                $initialMode = 'signup';
                if (!empty($loginError)) {
                    $initialMode = 'login';
                }
                ?>
                <h1 id="ls-form-title"><?php echo $initialMode === 'login' ? 'Welcome back' : 'Create an account'; ?></h1>
                <p class="ls-toggle-text">
                    <span id="ls-toggle-question"><?php echo $initialMode === 'login' ? "Don't have an account?" : 'Already have an account?'; ?></span>
                    <a href="#" id="ls-toggle-link"><?php echo $initialMode === 'login' ? 'Sign up' : 'Log in'; ?></a>
                </p>

                <?php if (!empty($signupError)): ?>
                    <div class="error-message" style="color: red; margin-bottom: 10px;"><?php echo htmlspecialchars($signupError); ?></div>
                <?php endif; ?>
                <?php if (!empty($signupSuccess)): ?>
                    <div class="success-message" style="color: green; margin-bottom: 10px;"><?php echo htmlspecialchars($signupSuccess); ?></div>
                <?php endif; ?>
                <?php if (!empty($loginError)): ?>
                    <div class="error-message" style="color: red; margin-bottom: 10px;"><?php echo htmlspecialchars($loginError); ?></div>
                <?php endif; ?>

                <form id="ls-auth-form" method="POST" action="">
                    <input type="hidden" name="mode" id="mode" value="<?php echo $initialMode; ?>" />
                    <div class="ls-name-fields" id="ls-name-fields">
                        <input type="text" name="firstName" id="ls-first-name" placeholder="First name" required />
                        <input type="text" name="lastName" id="ls-last-name" placeholder="Last name" required />
                    </div>

                    <input type="email" name="email" id="ls-email" placeholder="Email" required />

                    <div class="ls-password-field">
                        <input type="password" name="password" id="ls-password" placeholder="Enter your password" required />
                        <button type="button" class="ls-toggle-password" id="ls-toggle-password">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
                                <path d="M12 5C5.63636 5 2 12 2 12C2 12 5.63636 19 12 19C18.3636 19 22 12 22 12C22 12 18.3636 5 12 5Z" stroke="currentColor" stroke-width="2" />
                                <circle cx="12" cy="12" r="3" stroke="currentColor" stroke-width="2" />
                            </svg>
                        </button>
                    </div>

                    <div class="ls-forgot-password" id="ls-forgot-password">
                        <a href="forgot-password.php">Forgot password?</a>
                    </div>

                    <div class="ls-referral-field" id="ls-referral-field">
                        <input type="text" name="referralCode" id="ls-referral-code" placeholder="Referral code (optional)" />
                    </div>

                    <div class="ls-terms-checkbox" id="ls-terms-checkbox">
                        <input type="checkbox" name="terms" id="ls-terms" required />
                        <label for="ls-terms">
                            I agree to the <a href="terms-and-conditions.php" target="_blank">Terms & Conditions</a>
                        </label>
                    </div>

                    <button type="submit" class="ls-submit-btn" id="ls-submit-btn">Create account</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Set initial mode from PHP
            let isLoginMode = <?php echo $initialMode === 'login' ? 'true' : 'false'; ?>;

            const formTitle = document.getElementById('ls-form-title');
            const toggleQuestion = document.getElementById('ls-toggle-question');
            const toggleLink = document.getElementById('ls-toggle-link');
            const nameFields = document.getElementById('ls-name-fields');
            const referralField = document.getElementById('ls-referral-field');
            const termsCheckbox = document.getElementById('ls-terms-checkbox');
            const forgotPassword = document.getElementById('ls-forgot-password');
            const submitBtn = document.getElementById('ls-submit-btn');
            const authForm = document.getElementById('ls-auth-form');
            const togglePasswordBtn = document.getElementById('ls-toggle-password');
            const passwordInput = document.getElementById('ls-password');

            function updateFormMode() {
                const modeInput = document.getElementById('mode');
                if (isLoginMode) {
                    nameFields.style.display = 'none';
                    referralField.style.display = 'none';
                    termsCheckbox.style.display = 'none';
                    forgotPassword.classList.add('ls-showing');
                    formTitle.textContent = 'Welcome back';
                    toggleQuestion.textContent = "Don't have an account?";
                    toggleLink.textContent = 'Sign up';
                    submitBtn.textContent = 'Log in';
                    modeInput.value = 'login';
                    document.getElementById('ls-first-name').removeAttribute('required');
                    document.getElementById('ls-last-name').removeAttribute('required');
                    document.getElementById('ls-terms').removeAttribute('required');
                } else {
                    nameFields.style.display = 'flex';
                    referralField.style.display = 'block';
                    termsCheckbox.style.display = 'block';
                    forgotPassword.classList.remove('ls-showing');
                    formTitle.textContent = 'Create an account';
                    toggleQuestion.textContent = 'Already have an account?';
                    toggleLink.textContent = 'Log in';
                    submitBtn.textContent = 'Create account';
                    modeInput.value = 'signup';
                    document.getElementById('ls-first-name').setAttribute('required', '');
                    document.getElementById('ls-last-name').setAttribute('required', '');
                    document.getElementById('ls-terms').setAttribute('required', '');
                }
            }

            // Set initial state on page load
            updateFormMode();

            // toggle login/signup
            toggleLink.addEventListener('click', (e) => {
                e.preventDefault();
                isLoginMode = !isLoginMode;
                updateFormMode();
            });

            // toggle password visibility
            togglePasswordBtn.addEventListener('click', () => {
                const type = passwordInput.type === 'password' ? 'text' : 'password';
                passwordInput.type = type;
            });
        });
    </script>
</body>

</html>