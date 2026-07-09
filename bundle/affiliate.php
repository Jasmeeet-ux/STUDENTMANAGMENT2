<?php
session_start();
require 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Affiliate Program - EduPlatform</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>EduPlatform</h2>
            </div>
            <div class="nav-menu">
                <a href="index.php" class="nav-link">Home</a>
                <a href="courses.php" class="nav-link">Courses</a>
                <a href="about.php" class="nav-link">About</a>
                <a href="pricing.php" class="nav-link">Pricing</a>
                <a href="affiliate.php" class="nav-link active">Affiliate</a>
                <a href="contact.php" class="nav-link">Contact</a>
                <?php if (isLoggedIn()): ?>
                    <a href="user-dashboard/dashboard.php" class="btn btn-primary">Dashboard</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-outline">Login</a>
                    <a href="signup.php" class="btn btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Affiliate Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1>Join Our Affiliate Program</h1>
                <p>Earn money by promoting our courses. Share your referral link and get commissions on every sale.</p>
                <div class="hero-buttons">
                    <?php if (isLoggedIn()): ?>
                        <a href="user-dashboard/affiliate.php" class="btn btn-primary btn-large">View Affiliate Dashboard</a>
                    <?php else: ?>
                        <a href="signup.php" class="btn btn-primary btn-large">Join Now</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-white border-t border-gray-200">
        <div class="max-w-7xl mx-auto px-6 py-10 grid md:grid-cols-3 gap-8">
            <div>
                <h2 class="text-lg font-semibold">Culture of Internet</h2>
                <p class="text-sm text-gray-600 mt-2">Empowering Your Journey in Professional Courses.</p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-amber-600 mb-4">Contact Us</h3>
                <p class="text-sm text-gray-700">contactcultureofinternet@gmail.com</p>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-amber-600 mb-4">Company</h3>
                <ul class="space-y-2 text-sm">
                    <li><a href="contact.php" class="text-gray-600 hover:text-amber-600 transition">Contact</a></li>
                    <li><a href="about.php" class="text-gray-600 hover:text-amber-600 transition">About</a></li>
                </ul>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>
</body>
</html>
