<?php
require 'db.php';
require 'includes/email.php';

$signupError = '';
$signupSuccess = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = trim($_POST['firstName'] ?? '');
    $lastName = trim($_POST['lastName'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirmPassword'] ?? '';
    $referralCode = trim($_POST['referralCode'] ?? '');
    $terms = isset($_POST['terms']);
    $marketing = isset($_POST['marketing']) ? 1 : 0;

    // Validation
    if (empty($firstName) || empty($lastName) || empty($email) || empty($password)) {
        $signupError = 'Please fill in all required fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $signupError = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $signupError = 'Password must be at least 6 characters long.';
    } elseif ($password !== $confirmPassword) {
        $signupError = 'Passwords do not match.';
    } elseif (!$terms) {
        $signupError = 'You must accept the terms and conditions.';
    } elseif (!isDatabaseAvailable()) {
        header('Location: maintenance.php');
        exit;
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $signupError = 'An account with this email already exists.';
        } else {
            // Validate referral code
            $referrerId = null;
            if (!empty($referralCode)) {
                $stmt = $pdo->prepare("SELECT id FROM users WHERE referral_code = ?");
                $stmt->execute([$referralCode]);
                $referrerId = $stmt->fetchColumn();

                if (!$referrerId) {
                    $signupError = 'Invalid referral code.';
                }
            }

            if (!$signupError) {
                // Hash the password
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $name = $firstName . ' ' . $lastName;

                // Generate a unique referral code for the new user: 4 chars of name + 4-5 random numbers
                $nameForCode = str_replace(' ', '', $name);
                $namePart = strtoupper(substr($nameForCode, 0, 4));
                $randomPart = rand(1000, 99999);
                $newReferralCode = $namePart . $randomPart;

                // Start transaction to ensure data consistency
                $pdo->beginTransaction();

                try {
                    // Insert new user
                    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, referral_code, referred_by, role, created_at) VALUES (?, ?, ?, ?, ?, 'student', NOW())");
                    $stmt->execute([$name, $email, $hashedPassword, $newReferralCode, $referrerId]);
                    $userId = $pdo->lastInsertId();

                    // Create affiliate record for the new user
                    $stmt = $pdo->prepare("INSERT INTO affiliates (user_id, total_earnings, pending_payout) VALUES (?, 0.00, 0.00)");
                    $stmt->execute([$userId]);

                    // If user was referred, create referral record
                    if ($referrerId) {
                        $stmt = $pdo->prepare("INSERT INTO referrals (referrer_id, referred_user_id, commission, created_at) VALUES (?, ?, 0.00, NOW())");
                        $stmt->execute([$referrerId, $userId]);
                    }

                    // Commit transaction
                    $pdo->commit();

                    // Send welcome email
                    $welcomeBody = '
                    <html>
                    <body>
                        <h2>Welcome to Culture of Internet!</h2>
                        <p>Dear ' . htmlspecialchars($name) . ',</p>
                        <p>Thank you for signing up! Your account has been created successfully.</p>
                        <p><strong>User ID:</strong> ' . htmlspecialchars($email) . '</p>
                        <p>You can now log in and purchase bundles to access your dashboard.</p>
                        <img src="cid:logo_cid" alt="Company Logo" style="max-width: 150px;" />
                        <p>If you have any questions, please contact us.</p>
                        <p>Best regards,<br>Culture of Internet Team</p>
                    </body>
                    </html>';

                    $embeddedImages = [
                        'logo_cid' => 'https://cultureofinternet.com/images/coi-ligth.png'
                    ];

                    sendEmail($email, 'Welcome to Culture of Internet', $welcomeBody, '', [], $embeddedImages);

                    // Redirect to login page with signup success
                    header("Location: login.php?signup=1");
                    exit;
                } catch (Exception $e) {
                    // Rollback transaction on error
                    $pdo->rollback();
                    $signupError = 'Registration failed. Please try again.';
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
    <title>Sign Up</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Inter', sans-serif;
        }

        body {
            background: #f9fafc;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

        .signup-container {
            background: #fff;
            max-width: 380px;
            width: 100%;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            padding: 25px;
        }

        .signup-container h2 {
            text-align: center;
            font-size: 20px;
            margin-bottom: 20px;
            font-weight: bold;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            margin-bottom: 5px;
            color: #333;
        }

        .form-inline {
            display: flex;
            gap: 10px;
        }

        .form-group input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: 0.3s;
        }

        .form-group input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 2px rgba(37, 99, 235, 0.2);
            outline: none;
        }

        .password-field {
            position: relative;
        }

        .password-field input {
            padding-right: 50px;
        }

        .password-field span {
            position: absolute;
            right: 10px;
            top: 65%;
            transform: translateY(-50%);
            color: #007bff;
            font-size: 14px;
            cursor: pointer;
            user-select: none;
        }

        .terms {
            font-size: 12px;
            margin-bottom: 15px;
            color: #444;
        }

        .terms a {
            color: #007bff;
            text-decoration: none;
        }

        .signup-btn {
            width: 100%;
            padding: 12px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
            box-shadow: 0 4px 15px rgba(37, 99, 235, 0.3);
        }

        .signup-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(37, 99, 235, 0.4);
        }
    </style>
</head>

<body>

    <div class="signup-container">
        <h2>Finish signing up</h2>
        <?php if ($signupError): ?>
            <p style="color:red; text-align:center;"><?php echo htmlspecialchars($signupError); ?></p>
        <?php elseif ($signupSuccess): ?>
            <p style="color:green; text-align:center;"><?php echo htmlspecialchars($signupSuccess); ?></p>
        <?php endif; ?>

        <form id="signupForm" method="POST" action="">
            <div class="form-inline">
                <div class="form-group">
                    <label>First name</label>
                    <input type="text" name="firstName" required>
                </div>
                <div class="form-group">
                    <label>Last name</label>
                    <input type="text" name="lastName" required>
                </div>
            </div>

            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group password-field">
                <label>Password</label>
                <input type="password" name="password" id="password" required>
                <span id="togglePassword">Show</span>
            </div>

            <div class="form-group password-field">
                <label>Confirm Password</label>
                <input type="password" name="confirmPassword" required>
            </div>

            <div class="form-group">
                <label>Referral code (optional)</label>
                <input type="text" name="referralCode" id="referralCode" placeholder="Referral code">
                <div id="referralMessage" style="font-size: 12px; margin-top: 5px;"></div>
            </div>

            <p class="terms">
                <input type="checkbox" name="terms" required> By signing up, you agree with our
                <a href="#">Terms & conditions</a> and
                <a href="#">Privacy statement</a>.
            </p>

            <button type="submit" class="signup-btn">Sign up</button>
        </form>
    </div>

    <script>
        // Password show/hide
        const togglePassword = document.getElementById("togglePassword");
        const passwordField = document.getElementById("password");

        togglePassword.addEventListener("click", () => {
            if (passwordField.type === "password") {
                passwordField.type = "text";
                togglePassword.textContent = "Hide";
            } else {
                passwordField.type = "password";
                togglePassword.textContent = "Show";
            }
        });

        // Referral code validation
        const referralCodeInput = document.getElementById("referralCode");
        const referralMessage = document.getElementById("referralMessage");
        let referralTimeout;

        function checkReferralCode(code) {
            if (!code.trim()) {
                referralMessage.textContent = '';
                return;
            }

            fetch(`check-referral.php?referral_code=${encodeURIComponent(code)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.valid) {
                        referralMessage.textContent = `✓ Valid referral code by ${data.referrer_name}`;
                        referralMessage.style.color = 'green';
                    } else {
                        referralMessage.textContent = data.message;
                        referralMessage.style.color = 'red';
                    }
                })
                .catch(error => {
                    referralMessage.textContent = 'Error checking referral code.';
                    referralMessage.style.color = 'red';
                });
        }

        referralCodeInput.addEventListener("input", () => {
            clearTimeout(referralTimeout);
            referralTimeout = setTimeout(() => {
                checkReferralCode(referralCodeInput.value);
            }, 500); // Debounce for 500ms
        });

        referralCodeInput.addEventListener("blur", () => {
            checkReferralCode(referralCodeInput.value);
        });
    </script>

</body>

</html>