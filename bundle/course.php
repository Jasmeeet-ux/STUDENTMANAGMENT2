<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ace the Interview - Master Your Next Job Interview</title>
    <link rel="stylesheet" href="Course.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>

    <?php include ('includes/header.php') ?>

    <!-- Course Banner -->
    <section class="hero-banner">
        <div class="container">
            <div class="hero-content">
                <div class="hero-left">
                    <div class="course-meta">
                        <span class="category">Career</span>
                        <span class="language">English</span>
                    </div>
                    <h1 class="course-title">Culture of Internet</h1>
                    <p class="course-subtitle">Master the art of interviewing and land your dream job with confidence
                    </p>
                    <div class="hero-stats">
                        <div class="stat">
                            <i class="fas fa-star"></i>
                            <span>4.8 (2,847 reviews)</span>
                        </div>
                        <div class="stat">
                            <i class="fas fa-users"></i>
                            <span>12,354 students</span>
                        </div>
                    </div>
                    <button class="cta-button" onclick="scrollToPricing()">
                        <i class="fas fa-shopping-cart"></i>
                        Buy Now
                    </button>
                </div>
                <div class="hero-right">
                    <div class="course-preview">
                        <div class="video">
                            <iframe width="560" height="315"
                                src="https://www.youtube.com/embed/E3FysUo1RZ8?si=MCHvDG35md45UKQI"
                                title="YouTube video player" frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                referrerpolicy="strict-origin-when-cross-origin" allowfullscreen=""></iframe>
                            <a href="https://www.youtube.com/@jobvacancyresult" target="_blank"> <img
                                    src="images/JVR.png" alt="img" height="100px"></a>
                        </div>
                        <div class="play-button">
                            <i class="fas fa-play"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Course Info Box -->
    <section class="course-info-box">
        <div class="container">
            <div class="info-cards">
                <div class="info-card">
                    <i class="fas fa-book-open"></i>
                    <div class="info-content">
                        <span class="info-label">Lessons</span>
                        <span class="info-value">31</span>
                    </div>
                </div>
                <div class="info-card">
                    <i class="fas fa-clock"></i>
                    <div class="info-content">
                        <span class="info-label">Duration</span>
                        <span class="info-value">2.3 hours</span>
                    </div>
                </div>
                <div class="info-card">
                    <i class="fas fa-signal"></i>
                    <div class="info-content">
                        <span class="info-label">Level</span>
                        <span class="info-value">Beginner</span>
                    </div>
                </div>
                <div class="info-card">
                    <i class="fas fa-certificate"></i>
                    <div class="info-content">
                        <span class="info-label">Certificate</span>
                        <span class="info-value">Yes</span>
                    </div>
                </div>
                <div class="info-card">
                    <i class="fas fa-user-tie"></i>
                    <div class="info-content">
                        <span class="info-label">Instructor</span>
                        <span class="info-value">Sarah Johnson</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="main-content">
        <div class="container">
            <div class="content-wrapper">
                <div class="content-left">
                    <!-- About Course Tab -->
                    <div class="tab-content active" id="about">
                        <h2>About This Course</h2>
                        <p>Transform your interview performance and land your dream job with this comprehensive
                            interview mastery course. Designed by industry experts with over 15 years of hiring
                            experience, this course will teach you everything you need to know to excel in any interview
                            situation.</p>

                        <h3>Who This Course Is For:</h3>
                        <ul>
                            <li>Job seekers preparing for their next career move</li>
                            <li>Recent graduates entering the job market</li>
                            <li>Professionals looking to advance their careers</li>
                            <li>Anyone who wants to improve their interview skills</li>
                        </ul>

                        <h3>What You'll Learn:</h3>
                        <div class="learning-outcomes">
                            <div class="outcome">
                                <i class="fas fa-check-circle"></i>
                                <span>Master the art of answering tough interview questions</span>
                            </div>
                            <div class="outcome">
                                <i class="fas fa-check-circle"></i>
                                <span>Build unshakeable confidence for any interview</span>
                            </div>
                            <div class="outcome">
                                <i class="fas fa-check-circle"></i>
                                <span>Create compelling resumes that get noticed</span>
                            </div>
                            <div class="outcome">
                                <i class="fas fa-check-circle"></i>
                                <span>Develop strong body language and communication skills</span>
                            </div>
                            <div class="outcome">
                                <i class="fas fa-check-circle"></i>
                                <span>Negotiate salary and benefits effectively</span>
                            </div>
                            <div class="outcome">
                                <i class="fas fa-check-circle"></i>
                                <span>Follow up professionally after interviews</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="sidebar">
                    <!-- Instructor Section -->
                    <div class="instructor-card">
                        <h3>Your Instructor</h3>
                        <div class="instructor-profile">
                            <img src="https://images.pexels.com/photos/3769021/pexels-photo-3769021.jpeg?auto=compress&cs=tinysrgb&w=200"
                                alt="Sarah Johnson" class="instructor-photo">
                            <div class="instructor-info">
                                <h4>Sarah Johnson</h4>
                                <p class="instructor-title">Senior HR Director & Career Coach</p>
                                <div class="instructor-stats">
                                    <div class="stat">
                                        <i class="fas fa-users"></i>
                                        <span>45K+ Students</span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-star"></i>
                                        <span>4.9 Rating</span>
                                    </div>
                                    <div class="stat">
                                        <i class="fas fa-book"></i>
                                        <span>12 Courses</span>
                                    </div>
                                </div>
                                <p class="instructor-bio">Sarah has 15+ years of experience in talent acquisition and
                                    has personally interviewed over 10,000 candidates. She's helped thousands of
                                    professionals land their dream jobs.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Certificate Info -->
                    <div class="certificate-card">
                        <h3>Certificate of Completion</h3>
                        <div class="certificate-preview">
                            <i class="fas fa-certificate"></i>
                            <div class="certificate-info">
                                <h4>MSME Certified E-Certificate</h4>
                                <p>Earn a professional certificate upon successful completion of the course</p>
                                <ul>
                                    <li>Add to your LinkedIn profile</li>
                                    <li>Include in your resume</li>
                                    <li>Share with employers</li>
                                    <li>Boost your career prospects</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Related Courses -->
    <section class="related-courses">
        <div class="container">
            <h2>You Might Also Like</h2>
            <div class="courses-grid">
                <div class="course-card">
                    <img src="https://images.pexels.com/photos/3760263/pexels-photo-3760263.jpeg?auto=compress&cs=tinysrgb&w=300"
                        alt="Resume Writing Course">
                    <div class="course-info">
                        <h3>Resume Writing Mastery</h3>
                        <p class="course-instructor">by John Smith</p>
                        <div class="course-rating">
                            <span class="stars">★★★★★</span>
                            <span>4.7 (1,234)</span>
                        </div>
                    </div>
                </div>

                <div class="course-card">
                    <img src="https://images.pexels.com/photos/3184465/pexels-photo-3184465.jpeg?auto=compress&cs=tinysrgb&w=300"
                        alt="LinkedIn Optimization">
                    <div class="course-info">
                        <h3>LinkedIn Profile Optimization</h3>
                        <p class="course-instructor">by Maria Garcia</p>
                        <div class="course-rating">
                            <span class="stars">★★★★★</span>
                            <span>4.6 (892)</span>
                        </div>
                    </div>
                </div>

                <div class="course-card">
                    <img src="https://images.pexels.com/photos/3184338/pexels-photo-3184338.jpeg?auto=compress&cs=tinysrgb&w=300"
                        alt="Salary Negotiation">
                    <div class="course-info">
                        <h3>Salary Negotiation Secrets</h3>
                        <p class="course-instructor">by Robert Lee</p>
                        <div class="course-rating">
                            <span class="stars">★★★★☆</span>
                            <span>4.5 (567)</span>
                        </div>
                    </div>
                </div>
            </div>
    </section>

    <!-- Reviews Section -->
    <section class="reviews-section">
        <div class="container">
            <h2>Student Reviews</h2>
            <div class="reviews-summary">
            <div class="rating-overview">
                <div class="rating-score">4.8</div>
                <div class="stars">
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                    <i class="fas fa-star"></i>
                </div>
                <div class="reviews-count">2,847 reviews</div>
            </div>
            <div class="rating-breakdown">
                <div class="rating-bar">
                    <span>5 stars</span>
                    <div class="bar">
                        <div class="fill" style="width: 78%"></div>
                    </div>
                    <span>78%</span>
                </div>
                <div class="rating-bar">
                    <span>4 stars</span>
                    <div class="bar">
                        <div class="fill" style="width: 15%"></div>
                    </div>
                    <span>15%</span>
                </div>
                <div class="rating-bar">
                    <span>3 stars</span>
                    <div class="bar">
                        <div class="fill" style="width: 5%"></div>
                    </div>
                    <span>5%</span>
                </div>
                <div class="rating-bar">
                    <span>2 stars</span>
                    <div class="bar">
                        <div class="fill" style="width: 1%"></div>
                    </div>
                    <span>1%</span>
                </div>
                <div class="rating-bar">
                    <span>1 star</span>
                    <div class="bar">
                        <div class="fill" style="width: 1%"></div>
                    </div>
                    <span>1%</span>
                </div>
            </div>
        </div>

        <div class="reviews-list">
            <div class="review">
                <div class="review-header">
                    <img src="https://images.pexels.com/photos/3769021/pexels-photo-3769021.jpeg?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop"
                        alt="Student" class="reviewer-photo">
                    <div class="reviewer-info">
                        <h4>Michael Chen</h4>
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="review-date">2 weeks ago</span>
                    </div>
                </div>
                <p class="review-text">This course completely transformed my interview skills. I went
                    from being nervous and unprepared to confident and articulate. Landed my dream job
                    within a month of completing the course!</p>
            </div>

            <div class="review">
                <div class="review-header">
                    <img src="https://images.pexels.com/photos/3763188/pexels-photo-3763188.jpeg?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop"
                        alt="Student" class="reviewer-photo">
                    <div class="reviewer-info">
                        <h4>Emily Rodriguez</h4>
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="review-date">1 month ago</span>
                    </div>
                </div>
                <p class="review-text">Sarah's teaching style is incredible. The mock interview sessions
                    were so helpful, and the body language tips made a huge difference. Highly
                    recommend!</p>
            </div>

            <div class="review">
                <div class="review-header">
                    <img src="https://images.pexels.com/photos/2379004/pexels-photo-2379004.jpeg?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop"
                        alt="Student" class="reviewer-photo">
                    <div class="reviewer-info">
                        <h4>David Thompson</h4>
                        <div class="stars">
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="fas fa-star"></i>
                            <i class="far fa-star"></i>
                        </div>
                        <span class="review-date">3 weeks ago</span>
                    </div>
                </div>
                <p class="review-text">Great content and practical tips. The salary negotiation section
                    was particularly valuable. Worth every penny!</p>
            </div>
        </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <h2>What Our Students Say</h2>
            <div class="testimonials-slider">
                <div class="testimonials-container">
                    <div class="testimonial">
                        <div class="testimonial-content">
                            <div class="testimonial-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="testimonial-text">"This course completely transformed my interview skills. I went from being nervous and unprepared to confident and articulate. Landed my dream job within a month!"</p>
                            <div class="testimonial-author">
                                <img src="https://images.pexels.com/photos/3769021/pexels-photo-3769021.jpeg?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop" alt="Sarah Chen" class="author-photo">
                                <div class="author-info">
                                    <h4>Sarah Chen</h4>
                                    <span class="author-title">Software Engineer</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial">
                        <div class="testimonial-content">
                            <div class="testimonial-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="testimonial-text">"Sarah's teaching style is incredible. The mock interview sessions were so helpful, and the body language tips made a huge difference. Highly recommend!"</p>
                            <div class="testimonial-author">
                                <img src="https://images.pexels.com/photos/3763188/pexels-photo-3763188.jpeg?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop" alt="Mike Rodriguez" class="author-photo">
                                <div class="author-info">
                                    <h4>Mike Rodriguez</h4>
                                    <span class="author-title">Marketing Manager</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial">
                        <div class="testimonial-content">
                            <div class="testimonial-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="testimonial-text">"Great content and practical tips. The salary negotiation section was particularly valuable. Worth every penny!"</p>
                            <div class="testimonial-author">
                                <img src="https://images.pexels.com/photos/2379004/pexels-photo-2379004.jpeg?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop" alt="Emma Thompson" class="author-photo">
                                <div class="author-info">
                                    <h4>Emma Thompson</h4>
                                    <span class="author-title">Product Designer</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="testimonial">
                        <div class="testimonial-content">
                            <div class="testimonial-stars">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="testimonial-text">"The course exceeded my expectations. The real-world examples and strategies helped me stand out in a competitive job market. Thank you!"</p>
                            <div class="testimonial-author">
                                <img src="https://images.pexels.com/photos/3769021/pexels-photo-3769021.jpeg?auto=compress&cs=tinysrgb&w=100&h=100&fit=crop" alt="Alex Johnson" class="author-photo">
                                <div class="author-info">
                                    <h4>Alex Johnson</h4>
                                    <span class="author-title">Data Analyst</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="slider-dots">
                    <span class="dot active" data-slide="0"></span>
                    <span class="dot" data-slide="1"></span>
                    <span class="dot" data-slide="2"></span>
                    <span class="dot" data-slide="3"></span>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="faq-section">
        <div class="container">
            <h2>Frequently Asked Questions</h2>
            <div class="faq-list">
                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(1)">
                        <span>How long do I have access to the course?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer" id="faq-1">
                        <p>You have lifetime access to the course! Once you purchase, you can watch the videos and
                            access all materials whenever you want, as many times as you need.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(2)">
                        <span>Do I get a certificate upon completion?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer" id="faq-2">
                        <p>Yes! You'll receive an MSME-certified digital certificate that you can add to your LinkedIn
                            profile, resume, and share with potential employers.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(3)">
                        <span>Who is this course designed for?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer" id="faq-3">
                        <p>This course is perfect for job seekers at any level - whether you're a recent graduate,
                            career changer, or experienced professional looking to improve your interview skills.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(4)">
                        <span>Can I get a refund if I'm not satisfied?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer" id="faq-4">
                        <p>Absolutely! We offer a 30-day money-back guarantee. If you're not completely satisfied with
                            the course, we'll refund your purchase in full.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(5)">
                        <span>Is there any support if I have questions?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer" id="faq-5">
                        <p>Yes! You can ask questions in the course discussion section and get responses from both the
                            instructor and fellow students. We're here to support your learning journey.</p>
                    </div>
                </div>

                <div class="faq-item">
                    <div class="faq-question" onclick="toggleFAQ(6)">
                        <span>Can I access the course on mobile devices?</span>
                        <i class="fas fa-chevron-down"></i>
                    </div>
                    <div class="faq-answer" id="faq-6">
                        <p>Yes! The course is fully optimized for mobile devices, tablets, and desktop computers. Learn
                            on-the-go with our responsive platform.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer CTA -->
    <section class="footer-cta" id="pricing">
        <div class="container">
            <div class="cta-content">
                <h2>Ready to Ace Your Next Interview?</h2>
                <p>Join thousands of successful professionals who transformed their careers</p>
                <div class="price-info">
                    <span class="original-price">$99</span>
                    <span class="current-price">$49</span>
                    <span class="discount">50% OFF</span>
                </div>
                <button class="cta-button large">
                    <i class="fas fa-shopping-cart"></i>
                    Enroll Now - $49
                </button>
                <div class="guarantee">
                    <i class="fas fa-shield-alt"></i>
                    <span>30-day money-back guarantee</span>
                </div>
            </div>
        </div>
    </section>

      <?php include ('includes\footer.php') ?>


    <script src="Course.js"></script>
</body>

</html>