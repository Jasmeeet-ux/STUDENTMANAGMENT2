<?php
session_start();
require 'db.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - EduPlatform</title>
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
                <a href="affiliate.php" class="nav-link">Affiliate</a>
                <a href="contact.php" class="nav-link active">Contact</a>
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

    <!-- Contact Section -->
    <section class="hero">
        <div class="hero-container">
            <div class="hero-content">
                <h1>Contact Us</h1>
                <p>Get in touch with us for any inquiries or support.</p>
                <div class="contact-info">
                    <p><i class="fas fa-phone"></i> +91-81308-40080</p>
                    <p><i class="fas fa-envelope"></i> contactcultureofinternet@gmail.com</p>
                    <p><i class="fas fa-map-marker-alt"></i> Shop - 71, 2nd floor, Kingsway Camp, GTB Nagar New Delhi, India</p>
                </div>
                <!-- Basic Contact Form -->
                <form action="contact-handler.php" method="POST" class="mt-8">
                    <input type="text" name="name" placeholder="Your Name" required class="form-input">
                    <input type="email" name="email" placeholder="Your Email" required class="form-input">
                    <textarea name="message" placeholder="Your Message" required class="form-textarea"></textarea>
                    <button type="submit" class="btn btn-primary">Send Message</button>
                </form>
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
