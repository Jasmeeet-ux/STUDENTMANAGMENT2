<?php
session_start();
require 'db.php';
$loggedIn = isLoggedIn();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - EduPlatform</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-logo">
                <h2>EduPlatform</h2>
            </div>
            <div class="nav-menu">
                <a href="index" class="nav-link">Home</a>
                <a href="courses" class="nav-link">Courses</a>
                <a href="about" class="nav-link active">About</a>
                <a href="pricing" class="nav-link">Pricing</a>
                <a href="affiliate" class="nav-link">Affiliate</a>
                <a href="contact" class="nav-link">Contact</a>
                <?php if ($loggedIn): ?>
                    <a href="user-dashboard/dashboard.php" class="btn btn-primary">Dashboard</a>
                <?php else: ?>
                    <a href="login" class="btn btn-outline">Login</a>
                    <a href="signup" class="btn btn-primary">Sign Up</a>
                <?php endif; ?>
            </div>
            <div class="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Page Header -->
    <header class="page-header">
        <div class="container">
            <h1>About EduPlatform</h1>
            <p>Empowering learners worldwide with quality education and earning opportunities</p>
        </div>
    </header>

    <!-- About Content -->
    <section class="page-content">
        <div class="container">
            <!-- Mission Section -->
            <div class="about-section">
                <div class="about-content">
                    <h2>Our Mission</h2>
                    <p>At EduPlatform, we believe that education should be accessible, engaging, and rewarding. Our mission is to democratize learning by providing high-quality courses from industry experts while creating opportunities for learners to earn through our innovative affiliate program.</p>
                    <p>We're committed to bridging the gap between traditional education and the rapidly evolving demands of the modern workforce. Through our comprehensive platform, we empower individuals to acquire new skills, advance their careers, and build sustainable income streams.</p>
                </div>
                <div class="about-image">
                    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); height: 300px; border-radius: 15px; display: flex; align-items: center; justify-content: center; color: white; font-size: 1.2rem;">
                        Our Vision in Action
                    </div>
                </div>
            </div>

            <!-- Stats Section -->
            <div class="stats-section">
                <h2 style="text-align: center; margin-bottom: 3rem;">Our Impact</h2>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">10,000+</div>
                        <div class="stat-label">Active Students</div>
                        <p>Learners from around the world trust our platform</p>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">500+</div>
                        <div class="stat-label">Expert Courses</div>
                        <p>Comprehensive curriculum across multiple domains</p>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">50+</div>
                        <div class="stat-label">Industry Experts</div>
                        <p>Instructors with real-world experience</p>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">95%</div>
                        <div class="stat-label">Success Rate</div>
                        <p>Students who complete courses and achieve their goals</p>
                    </div>
                </div>
            </div>

            <!-- Values Section -->
            <div class="values-section">
                <h2 style="text-align: center; margin-bottom: 3rem;">Our Values</h2>
                <div class="values-grid">
                    <div class="value-card">
                        <div class="value-icon">🎯</div>
                        <h3>Excellence</h3>
                        <p>We maintain the highest standards in course quality, instructor expertise, and student support to ensure exceptional learning experiences.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">🌍</div>
                        <h3>Accessibility</h3>
                        <p>Learning should be available to everyone. We make our courses affordable and accessible across different devices and locations.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">🤝</div>
                        <h3>Community</h3>
                        <p>We foster a supportive learning community where students and instructors can connect, collaborate, and grow together.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">💡</div>
                        <h3>Innovation</h3>
                        <p>We continuously evolve our platform with cutting-edge technology to enhance the learning experience and outcomes.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">💰</div>
                        <h3>Empowerment</h3>
                        <p>Beyond education, we provide opportunities for learners to monetize their knowledge and build sustainable income streams.</p>
                    </div>
                    <div class="value-card">
                        <div class="value-icon">🔒</div>
                        <h3>Trust</h3>
                        <p>We prioritize data security, transparent pricing, and honest communication to build lasting relationships with our community.</p>
                    </div>
                </div>
            </div>

            <!-- Team Section -->
            <div class="team-section">
                <h2 style="text-align: center; margin-bottom: 3rem;">Meet Our Team</h2>
                <div class="team-grid">
                    <div class="team-member">
                        <div class="member-avatar">
                            <div class="avatar-placeholder">JS</div>
                        </div>
                        <h3>John Smith</h3>
                        <p class="member-role">CEO & Co-Founder</p>
                        <p>Former Google engineer with 15+ years in tech education. Passionate about making learning accessible to everyone.</p>
                    </div>
                    <div class="team-member">
                        <div class="member-avatar">
                            <div class="avatar-placeholder">SJ</div>
                        </div>
                        <h3>Sarah Johnson</h3>
                        <p class="member-role">CTO & Co-Founder</p>
                        <p>Full-stack developer and former Microsoft architect. Leads our technology vision and platform development.</p>
                    </div>
                    <div class="team-member">
                        <div class="member-avatar">
                            <div class="avatar-placeholder">MD</div>
                        </div>
                        <h3>Michael Davis</h3>
                        <p class="member-role">Head of Education</p>
                        <p>Former university professor with expertise in curriculum design and instructional technology.</p>
                    </div>
                    <div class="team-member">
                        <div class="member-avatar">
                            <div class="avatar-placeholder">EW</div>
                        </div>
                        <h3>Emily Wang</h3>
                        <p class="member-role">Head of Marketing</p>
                        <p>Digital marketing expert with proven track record in growing educational platforms and communities.</p>
                    </div>
                </div>
            </div>

            <!-- Timeline Section -->
            <div class="timeline-section">
                <h2 style="text-align: center; margin-bottom: 3rem;">Our Journey</h2>
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-date">2020</div>
                        <div class="timeline-content">
                            <h3>The Beginning</h3>
                            <p>Founded EduPlatform with a vision to revolutionize online education and create earning opportunities for learners.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-date">2021</div>
                        <div class="timeline-content">
                            <h3>First 1,000 Students</h3>
                            <p>Launched our first courses and reached our milestone of 1,000 active students within the first year.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-date">2022</div>
                        <div class="timeline-content">
                            <h3>Affiliate Program Launch</h3>
                            <p>Introduced our innovative affiliate program, enabling students to earn while they learn and share knowledge.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-date">2023</div>
                        <div class="timeline-content">
                            <h3>Global Expansion</h3>
                            <p>Expanded internationally with courses in multiple languages and partnerships with global organizations.</p>
                        </div>
                    </div>
                    <div class="timeline-item">
                        <div class="timeline-date">2024</div>
                        <div class="timeline-content">
                            <h3>10K+ Students Strong</h3>
                            <p>Celebrating over 10,000 active learners and launching advanced features like AI-powered learning paths.</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- CTA Section -->
            <div class="cta-section">
                <div class="cta-content">
                    <h2>Join Our Mission</h2>
                    <p>Be part of our growing community of learners, instructors, and affiliates. Together, we're building the future of education.</p>
                    <div class="cta-buttons">
                        <a href="signup" class="btn btn-primary btn-large">Start Learning Today</a>
                        <a href="contact" class="btn btn-outline btn-large">Get in Touch</a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>EduPlatform</h3>
                    <p>Empowering learners worldwide with quality education and earning opportunities.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <a href="courses">Courses</a>
                    <a href="about">About Us</a>
                    <a href="pricing">Pricing</a>
                    <a href="affiliate">Affiliate Program</a>
                </div>
                <div class="footer-section">
                    <h4>Support</h4>
                    <a href="contact">Contact Us</a>
                    <a href="privacy">Privacy Policy</a>
                    <a href="terms">Terms & Conditions</a>
                </div>
                <div class="footer-section">
                    <h4>Connect</h4>
                    <p>Follow us on social media</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 EduPlatform. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="script.js"></script>

    <style>
        .about-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: center;
            margin-bottom: 5rem;
        }

        .about-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1.5rem;
            color: #1F2937;
        }

        .about-content p {
            font-size: 1.1rem;
            line-height: 1.8;
            color: #6B7280;
            margin-bottom: 1.5rem;
        }

        .stats-section {
            background: #F9FAFB;
            padding: 4rem 0;
            margin: 5rem 0;
            border-radius: 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .stat-item {
            text-align: center;
            padding: 2rem;
        }

        .stat-number {
            font-size: 3rem;
            font-weight: 700;
            background: linear-gradient(135deg, #3B82F6, #8B5CF6);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1F2937;
            margin-bottom: 1rem;
        }

        .stat-item p {
            color: #6B7280;
        }

        .values-section {
            margin: 5rem 0;
        }

        .values-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2rem;
        }

        .value-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .value-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .value-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
        }

        .value-card h3 {
            margin-bottom: 1rem;
            color: #1F2937;
        }

        .team-section {
            margin: 5rem 0;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .team-member {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }

        .member-avatar {
            width: 100px;
            height: 100px;
            margin: 0 auto 1rem;
            border-radius: 50%;
            background: linear-gradient(135deg, #3B82F6, #8B5CF6);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .avatar-placeholder {
            color: white;
            font-size: 2rem;
            font-weight: 700;
        }

        .member-role {
            color: #3B82F6;
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .timeline-section {
            margin: 5rem 0;
        }

        .timeline {
            position: relative;
            max-width: 800px;
            margin: 0 auto;
        }

        .timeline::before {
            content: '';
            position: absolute;
            left: 50%;
            top: 0;
            bottom: 0;
            width: 2px;
            background: linear-gradient(135deg, #3B82F6, #8B5CF6);
            transform: translateX(-50%);
        }

        .timeline-item {
            position: relative;
            margin-bottom: 3rem;
            display: flex;
            align-items: center;
        }

        .timeline-item:nth-child(odd) {
            flex-direction: row;
        }

        .timeline-item:nth-child(even) {
            flex-direction: row-reverse;
        }

        .timeline-date {
            background: linear-gradient(135deg, #3B82F6, #8B5CF6);
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-weight: 600;
            position: absolute;
            left: 50%;
            transform: translateX(-50%);
            z-index: 2;
        }

        .timeline-content {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            width: 45%;
            margin-top: 2rem;
        }

        .timeline-item:nth-child(odd) .timeline-content {
            margin-right: auto;
        }

        .timeline-item:nth-child(even) .timeline-content {
            margin-left: auto;
        }

        .cta-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 2rem;
            border-radius: 20px;
            text-align: center;
            margin: 5rem 0;
        }

        .cta-content h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
        }

        .cta-content p {
            font-size: 1.2rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        @media (max-width: 768px) {
            .about-section {
                grid-template-columns: 1fr;
            }
            
            .timeline::before {
                left: 20px;
            }
            
            .timeline-item {
                flex-direction: column !important;
                align-items: flex-start;
                padding-left: 50px;
            }
            
            .timeline-date {
                left: 20px;
                transform: translateX(-50%);
            }
            
            .timeline-content {
                width: 100%;
                margin: 2rem 0 0 0 !important;
            }
            
            .cta-buttons {
                flex-direction: column;
                align-items: center;
            }
        }
    </style>
</body>
</html>