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
    <title>Courses - EduPlatform</title>
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
                <a href="courses" class="nav-link active">Courses</a>
                <a href="about" class="nav-link">About</a>
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
            <h1>Our Courses</h1>
            <p>Discover our comprehensive collection of courses designed to advance your skills</p>
        </div>
    </header>

    <!-- Course Filters -->
    <section class="course-filters">
        <div class="container">
            <div class="filter-bar">
                <div class="filter-group">
                    <label for="category">Category:</label>
                    <select id="category" class="form-select">
                        <option value="">All Categories</option>
                        <option value="web-development">Web Development</option>
                        <option value="marketing">Digital Marketing</option>
                        <option value="data-science">Data Science</option>
                        <option value="design">Design</option>
                        <option value="business">Business</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="level">Level:</label>
                    <select id="level" class="form-select">
                        <option value="">All Levels</option>
                        <option value="beginner">Beginner</option>
                        <option value="intermediate">Intermediate</option>
                        <option value="advanced">Advanced</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label for="price">Price:</label>
                    <select id="price" class="form-select">
                        <option value="">All Prices</option>
                        <option value="free">Free</option>
                        <option value="paid">Paid</option>
                        <option value="0-50">$0-$50</option>
                        <option value="50-100">$50-$100</option>
                        <option value="100+">$100+</option>
                    </select>
                </div>
                <div class="search-group">
                    <input type="text" id="search" class="form-input" placeholder="Search courses...">
                </div>
            </div>
        </div>
    </section>

    <!-- Courses Grid -->
    <section class="page-content">
        <div class="container">
            <div class="courses-grid" id="coursesGrid">
                <!-- Courses will be loaded dynamically -->
            </div>
            
            <!-- Pagination -->
            <div class="pagination">
                <button class="btn btn-outline" id="prevPage">Previous</button>
                <span id="pageInfo">Page 1 of 3</span>
                <button class="btn btn-outline" id="nextPage">Next</button>
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
    <script>
        // Course catalog functionality
        let currentPage = 1;
        const coursesPerPage = 9;
        let filteredCourses = [...mockCourses];

        // Extended mock courses data
        const allCourses = [
            ...mockCourses,
            {
                id: 4,
                title: 'Python Programming',
                description: 'Learn Python from scratch',
                price: 89,
                rating: 4.6,
                reviews: 156,
                instructor: 'Sarah Wilson',
                duration: '35 hours',
                lessons: 22,
                category: 'programming',
                level: 'beginner',
                image: 'https://images.pexels.com/photos/1181671/pexels-photo-1181671.jpeg'
            },
            {
                id: 5,
                title: 'UI/UX Design Fundamentals',
                description: 'Master the principles of user interface design',
                price: 109,
                rating: 4.8,
                reviews: 203,
                instructor: 'Alex Chen',
                duration: '28 hours',
                lessons: 18,
                category: 'design',
                level: 'intermediate',
                image: 'https://images.pexels.com/photos/196644/pexels-photo-196644.jpeg'
            },
            {
                id: 6,
                title: 'Machine Learning Basics',
                description: 'Introduction to machine learning concepts',
                price: 149,
                rating: 4.7,
                reviews: 89,
                instructor: 'David Kim',
                duration: '45 hours',
                lessons: 35,
                category: 'data-science',
                level: 'advanced',
                image: 'https://images.pexels.com/photos/8386440/pexels-photo-8386440.jpeg'
            }
        ];

        function renderCourses() {
            const grid = document.getElementById('coursesGrid');
            const startIndex = (currentPage - 1) * coursesPerPage;
            const endIndex = startIndex + coursesPerPage;
            const coursesToShow = filteredCourses.slice(startIndex, endIndex);

            grid.innerHTML = coursesToShow.map(course => `
                <div class="course-card">
                    <div class="course-image" style="background-image: linear-gradient(135deg, #667eea 0%, #764ba2 100%)"></div>
                    <div class="course-content">
                        <h3>${course.title}</h3>
                        <p>${course.description}</p>
                        <div class="course-instructor">
                            <small>By ${course.instructor}</small>
                        </div>
                        <div class="course-details">
                            <span class="duration">📺 ${course.duration}</span>
                            <span class="lessons">📚 ${course.lessons} lessons</span>
                        </div>
                        <div class="course-meta">
                            <span class="price">${formatCurrency(course.price)}</span>
                            <span class="rating">⭐ ${course.rating} (${course.reviews} reviews)</span>
                        </div>
                        <a href="course-detail?id=${course.id}" class="btn btn-primary">View Course</a>
                    </div>
                </div>
            `).join('');

            updatePagination();
        }

        function updatePagination() {
            const totalPages = Math.ceil(filteredCourses.length / coursesPerPage);
            document.getElementById('pageInfo').textContent = `Page ${currentPage} of ${totalPages}`;
            document.getElementById('prevPage').disabled = currentPage === 1;
            document.getElementById('nextPage').disabled = currentPage === totalPages;
        }

        function filterCourses() {
            const category = document.getElementById('category').value;
            const level = document.getElementById('level').value;
            const priceRange = document.getElementById('price').value;
            const searchTerm = document.getElementById('search').value.toLowerCase();

            filteredCourses = allCourses.filter(course => {
                const matchesCategory = !category || course.category === category;
                const matchesLevel = !level || course.level === level;
                const matchesPrice = !priceRange || 
                    (priceRange === 'free' && course.price === 0) ||
                    (priceRange === 'paid' && course.price > 0) ||
                    (priceRange === '0-50' && course.price >= 0 && course.price <= 50) ||
                    (priceRange === '50-100' && course.price > 50 && course.price <= 100) ||
                    (priceRange === '100+' && course.price > 100);
                const matchesSearch = !searchTerm || 
                    course.title.toLowerCase().includes(searchTerm) ||
                    course.description.toLowerCase().includes(searchTerm);

                return matchesCategory && matchesLevel && matchesPrice && matchesSearch;
            });

            currentPage = 1;
            renderCourses();
        }

        // Event listeners
        document.getElementById('category').addEventListener('change', filterCourses);
        document.getElementById('level').addEventListener('change', filterCourses);
        document.getElementById('price').addEventListener('change', filterCourses);
        document.getElementById('search').addEventListener('input', filterCourses);

        document.getElementById('prevPage').addEventListener('click', () => {
            if (currentPage > 1) {
                currentPage--;
                renderCourses();
            }
        });

        document.getElementById('nextPage').addEventListener('click', () => {
            const totalPages = Math.ceil(filteredCourses.length / coursesPerPage);
            if (currentPage < totalPages) {
                currentPage++;
                renderCourses();
            }
        });

        // Initialize
        filteredCourses = allCourses;
        renderCourses();
    </script>

    <style>
        .course-filters {
            background: #F9FAFB;
            padding: 2rem 0;
        }

        .filter-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .filter-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }

        .search-group {
            grid-column: span 1;
        }

        .course-instructor {
            margin-bottom: 1rem;
            color: #6B7280;
        }

        .course-details {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            font-size: 0.9rem;
            color: #6B7280;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 2rem;
            margin-top: 3rem;
        }

        @media (max-width: 768px) {
            .filter-bar {
                grid-template-columns: 1fr;
            }
            
            .pagination {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</body>
</html>