<?php
session_start();

require 'db.php';
require 'includes/email.php';

$message = ""; // for success/error messages

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $userId = $stmt->fetchColumn();

    if ($userId) {
        $resetToken = bin2hex(random_bytes(16));
        $stmt = $pdo->prepare("UPDATE users
            SET reset_token = ?, reset_token_expiry = DATE_ADD(NOW(), INTERVAL 1 HOUR)
            WHERE id = ?");
        $stmt->execute([$resetToken, $userId]);

        $resetLink = "http://localhost/bundle/reset-password.php?token=$resetToken";

        $body = "Click <a href='$resetLink'>here</a> to reset your password.
                 This link will expire in 1 hour.";

        if (sendEmail($email, 'Password Reset Request', $body)) {
            $message = "<div class='alert success'>Password reset link has been sent to your Gmail inbox.</div>";
        } else {
            $message = "<div class='alert error'>Message could not be sent. Please try again later.</div>";
        }
    } else {
        $message = "<div class='alert error'>Email not found.</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>
  <style>
    /* Basic Reset */
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }



    body {
      font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', 'Roboto', 'Oxygen',
        'Ubuntu', 'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue',
        sans-serif;
      -webkit-font-smoothing: antialiased;
      -moz-osx-font-smoothing: grayscale;
      background-color: #ffffff;
      background-image:
        linear-gradient(rgba(0, 0, 0, 0.1) 1px, transparent 1px),
        linear-gradient(90deg, rgba(0, 0, 0, 0.1) 1px, transparent 1px);
      background-size: 20px 20px;
      background-color: #f5f3f0;
      background-image:
        radial-gradient(circle at 20% 30%, rgba(190, 134, 82, 0.08) 0%, transparent 45%),
        radial-gradient(circle at 80% 70%, rgba(235, 190, 129, 0.06) 0%, transparent 35%),
        repeating-linear-gradient(45deg, rgba(190, 134, 82, 0.015) 0%, rgba(190, 134, 82, 0.015) 1px, transparent 1px, transparent 12px),
        linear-gradient(180deg, #f5f3f0 0%, #eae7e2 100%);
      background-attachment: fixed;
      display: flex;
      justify-content: center;
      align-items: center;
      min-height: 100vh;
      padding: 0.5rem;
    }



    .fp-container {
      background: white;
      padding: 2.5rem;
      border-radius: 12px;
      box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
      width: 100%;
      max-width: 500px;
      text-align: center;
      animation: fadeIn 1s ease-in;
    }

    @keyframes fadeIn {
      from {
        opacity: 0;
        transform: translateY(20px);
      }

      to {
        opacity: 1;
        transform: translateY(0);
      }
    }

    .fp-icon {
      background: #be8652;
      color: white;
      width: 50px;
      height: 50px;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      margin: 0 auto 1rem;
      font-size: 24px;
    }

    .fp-text {
      font-size: 2rem;
      margin-bottom: 0.5rem;
      color: #222;
    }

    .fp-sub-text {
      font-size: 0.9rem;
      color: #555;
      margin-bottom: 1.5rem;
    }

    .fp-input {
      width: 100%;
      padding: 14px;
      border: 1px solid #ddd;
      border-radius: 8px;
      outline: none;
      margin-bottom: 1.5rem;
      font-size: 0.95rem;
      transition: border-color 0.3s ease;
    }

    .fp-input:focus {
      border-color: #be8652;
      outline: none;
    }

    .fp-input::placeholder {
      color: #999;
      opacity: 1;
    }

    .fp-btns {
      display: flex;
      gap: 10px;
      justify-content: center;
    }

    .fp-btn {
      flex: 1;
      padding: 14px;
      border: none;
      border-radius: 8px;
      cursor: pointer;
      font-size: 1rem;
      transition: background-color 0.3s ease, transform 0.1s ease;
      font-weight: 500;
    }

    .fp-btn:focus {
      outline: 2px solid #be8652;
      outline-offset: 2px;
    }

    .fp-btn:hover {
      transform: translateY(-1px) scale(1.02);
      box-shadow: 0 4px 12px rgba(190, 134, 82, 0.3);
    }

    .fp-btn.fp-cancel {
      background: rgba(235, 190, 129, 0.3);
      color: #be8652;
      border: 1px solid #be8652;
    }

    .fp-btn.fp-cancel:hover {
      background: rgba(235, 190, 129, 0.5);
    }

    .fp-btn.fp-reset {
      background: #EBBE81;
      color: #000000;
    }

    .fp-btn.fp-reset:hover {
      background: #be8652;
      color: white;
    }

    .fp-link {
      margin-top: 1rem;
    }

    .fp-link a {
      color: #be8652;
      text-decoration: none;
      font-size: 0.9rem;
      transition: color 0.3s ease;
    }

    .fp-link a:hover {
      text-decoration: underline;
      color: #a07646;
    }

    .alert {
      padding: 12px;
      margin-bottom: 15px;
      border-radius: 8px;
      font-size: 0.9rem;
      text-align: left;
    }
    .alert.success {
      background: #e9f7ef;
      color: #2e7d32;
      border: 1px solid #c8e6c9;
    }
    .alert.error {
      background: #ffebee;
      color: #c62828;
      border: 1px solid #ef9a9a;
    }

    /* Professional Responsive Design */
    @media (max-width: 768px) {
      body {
        padding: 1rem;
      }

      .fp-container {
        width: calc(100% - 2rem);
        /* max-width: none; */
        margin: 0;
        /* padding: 1.5rem; */
      }

      

      .fp-sub-text {
        font-size: 0.9rem;
      }

      .fp-input {
        padding: 10px;
        font-size: 0.9rem;
      }

      .fp-btns {
        gap: 8px;
      }

      .fp-btn {
        padding: 10px;
        font-size: 0.95rem;
      }

      /* .fp-icon {
        width: 35px;
        height: 35px;
        font-size: 18px;
      } */
    }

    @media (max-width: 480px) {
      .fp-container {
        padding: 1rem;
        border-radius: 8px;
      }

      .fp-text {
        font-size: 1.8rem;
        /* font-size: 1.25rem; */
        /* margin-bottom: 0.4rem; */
      }

      .fp-sub-text {
        font-size: 0.85rem;
        margin-bottom: 1.2rem;
      }

      fp-input {
        padding: 10px;
        font-size: 0.9rem;
        margin-bottom: 1.2rem;
      }

      .fp-btns {
        flex-direction: column;
        gap: 8px;
      }

      .fp-btn {
        padding: 12px;
        font-size: 1rem;
      }

      /* .fp-icon {
        width: 32px;
        height: 32px;
        font-size: 16px;
        margin-bottom: 0.8rem;
      } */
    }
  </style>
</head>

<body>
  <?php include('includes/header.php') ?>
  <div class="fp-container">
    <div class="fp-icon">?</div>
    <h2 class="fp-text">Forgot password?</h2>
    <p class="fp-sub-text">No worries, we'll send you reset instructions.</p>
    <!-- Show message here -->
    <?php if (!empty($message)) echo $message; ?>

    <form method="POST" action="">
      <input class="fp-input" type="email" name="email" placeholder="e.g. john.doe@gmail.com" required>
      <div class="fp-btns">
        <button type="button" class="fp-btn fp-cancel" onclick="goBack()">Cancel</button>
        <button type="submit" class="fp-btn fp-reset">Reset password</button>
      </div>
    </form>
    <div class="fp-link">
      <a href="login-Sign-Up1.php">Back to Login</a>
    </div>
  </div>

  <script>
    // Handle cancel button
    function goBack() {
      window.location.href = "login-Sign-Up1.php";
    }
  </script>
</body>

</html>