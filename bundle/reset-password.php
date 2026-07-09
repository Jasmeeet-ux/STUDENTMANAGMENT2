<?php
session_start();

require 'db.php'; 

$token = $_POST['token'] ?? ($_GET['token'] ?? null);
$message = '';
$error = '';

if (!$pdo) {
    $error = "Database connection failed. Please try again later.";
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $newPassword = $_POST['newPassword'] ?? null;

    $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
    $stmt->execute([$token]);
    $userId = $stmt->fetchColumn();

    if ($userId && $newPassword) {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        $updateStmt = $pdo->prepare("UPDATE users 
            SET password = ?, reset_token = NULL, reset_token_expiry = NULL 
            WHERE id = ?");
        $updateStmt->execute([$hashedPassword, $userId]);

        $message = "Password has been reset successfully. You can now <a href='login.php'>login</a>.";
    } else {
        $error = "Invalid or expired token. Please request a new password reset link.";
    }
} else {
    if (empty($token)) {
        $error = "No reset token provided.";
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE reset_token = ? AND reset_token_expiry > NOW()");
        $stmt->execute([$token]);
        if (!$stmt->fetchColumn()) {
            $error = "Invalid or expired token. Please request a new password reset link.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Reset Password</title>
    <style>
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
        .reset-body {
            background-color: #be86522c;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .reset-container {
            background: #ffffff;
            padding: 2.5rem 3rem;
            border-radius: 12px;
            width: 405px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            text-align: center;
            transition: box-shadow 0.3s ease;
        }

        .reset-container:hover {
            box-shadow: 0 12px 36px rgba(0, 0, 0, 0.18);
        }

        .reset-logo {
            margin-bottom: 1.5rem;
        }

        .reset-h1 {
            font-weight: 700;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #1a1a1a;
        }

        .reset-description {
            font-size: 1rem;
            color: #666;
            margin-bottom: 2rem;
        }

        .reset-label {
            display: block;
            text-align: left;
            font-size: 1rem;
            margin-bottom: 0.5rem;
            color: #333;
        }

        .reset-input-password {
            width: 100%;
            padding: 0.65rem 1rem;
            font-size: 1.1rem;
            border: 1.5px solid #ccc;
            border-radius: 6px;
            margin-bottom: 1.25rem;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        .reset-input-password:focus {
            border-color: #be8652;
            outline: none;
            box-shadow: 0 0 6px rgba(190, 134, 82, 0.4);
        }

        .reset-password-requirements {
            text-align: left;
            font-size: 0.9rem;
            color: #444;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 0.5rem 0.75rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .reset-password-requirements .reset-checkmark {
            color: #0a8a0a;
            font-weight: bold;
            font-size: 1.2rem;
            line-height: 1;
        }

        .reset-button {
            width: 100%;
            background-color: #be8652;
            border: none;
            color: white;
            font-size: 1.1rem;
            font-weight: 700;
            padding: 0.85rem;
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
            box-shadow: 0 4px 12px rgba(190, 134, 82, 0.4);
        }

        .reset-button:disabled {
            background-color: #d4b08a;
            cursor: not-allowed;
            box-shadow: none;
        }

        .reset-button:hover:not(:disabled) {
            background-color: #a07642;
            box-shadow: 0 6px 18px rgba(160, 118, 66, 0.6);
        }
        .alert {margin-bottom:1rem;padding:.8rem;border-radius:6px;font-size:.95rem;}
        .alert.error {background:#ffebee;color:#c62828;}
        .alert.success {background:#e8f5e9;color:#2e7d32;}
    </style>
</head>
<body class="reset-body">
    <?php include ('includes/header.php') ?>

    <div class="reset-container" role="main" aria-label="Change Your Password">
        <h1 class="reset-h1">Change Your Password</h1>
        <p class="reset-description">Enter a new password below to change your password.</p>
        <?php if ($error): ?>
            <div class="alert error"><?php echo $error; ?></div>
            <p><a href="forgot-password.php">Request a new link</a></p>
        <?php elseif ($message): ?>
            <div class="alert success"><?php echo $message; ?></div>
        <?php else: ?>
        <form id="resetForm" method="POST" action="" novalidate>
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

            <label class="reset-label" for="newPassword">New password*</label>
            <input type="password" class="reset-input-password" id="newPassword" name="newPassword" required aria-describedby="passwordHelp" />

            <label class="reset-label" for="confirmPassword">Re-enter new password*</label>
            <input type="password" class="reset-input-password" id="confirmPassword" name="confirmPassword" required />

            <div class="reset-password-requirements" id="passwordHelp" style="display:none;">
                <span class="reset-checkmark" aria-hidden="true">✔</span>
                <span>At least 10 characters in length</span>
            </div>

            <button type="submit" class="reset-button" id="submitBtn" disabled>Reset password</button>
        </form>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const newPasswordInput = document.getElementById('newPassword');
            const confirmPasswordInput = document.getElementById('confirmPassword');
            const submitBtn = document.getElementById('submitBtn');
            const form = document.getElementById('resetForm');

            function validatePasswords() {
                const newPassword = newPasswordInput.value;
                const confirmPassword = confirmPasswordInput.value;
                const passwordsMatch = newPassword === confirmPassword && confirmPassword.length > 0;
                submitBtn.disabled = !passwordsMatch;
            }

            newPasswordInput?.addEventListener('input', validatePasswords);
            confirmPasswordInput?.addEventListener('input', validatePasswords);

            // Let the form submit normally so the server can update the password.
            // Client-side only enables/disables the submit button for UX.
        });
    </script>
</body>
</html>