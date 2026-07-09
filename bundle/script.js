// Global JavaScript functionality

// Mobile menu toggle
document.addEventListener('DOMContentLoaded', function() {
    const hamburger = document.querySelector('.hamburger');
    const navMenu = document.querySelector('.nav-menu');

    if (hamburger && navMenu) {
        hamburger.addEventListener('click', function() {
            navMenu.classList.toggle('active');
        });
    }

    // Close mobile menu when clicking on links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', () => {
            navMenu.classList.remove('active');
        });
    });

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Form validation (excluding purchase forms)
    const forms = document.querySelectorAll('form:not([action*="purchase-handler"])');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            const requiredFields = form.querySelectorAll('[required]');
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    isValid = false;
                    field.style.borderColor = '#EF4444';
                    
                    // Remove error styling on input
                    field.addEventListener('input', function() {
                        this.style.borderColor = '#E5E7EB';
                    });
                }
            });

            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });

    // Progress bars animation
    const progressBars = document.querySelectorAll('.progress-fill');
    const animateProgressBars = () => {
        progressBars.forEach(bar => {
            const rect = bar.getBoundingClientRect();
            const isVisible = rect.top < window.innerHeight && rect.bottom >= 0;
            
            if (isVisible) {
                const width = bar.style.width || bar.getAttribute('data-width');
                bar.style.width = width;
            }
        });
    };

    // Run on load and scroll
    animateProgressBars();
    window.addEventListener('scroll', animateProgressBars);

    // Toast notifications
    window.showToast = function(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        
        // Toast styles
        toast.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 1rem 1.5rem;
            background: ${type === 'success' ? '#10B981' : type === 'error' ? '#EF4444' : '#3B82F6'};
            color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 10000;
            transform: translateX(400px);
            transition: transform 0.3s ease;
        `;
        
        document.body.appendChild(toast);
        
        // Animate in
        setTimeout(() => {
            toast.style.transform = 'translateX(0)';
        }, 100);
        
        // Remove after 3 seconds
        setTimeout(() => {
            toast.style.transform = 'translateX(400px)';
            setTimeout(() => {
                document.body.removeChild(toast);
            }, 300);
        }, 3000);
    };

    // Local storage helpers
    window.StorageHelper = {
        set: (key, value) => {
            localStorage.setItem(key, JSON.stringify(value));
        },
        get: (key) => {
            const item = localStorage.getItem(key);
            return item ? JSON.parse(item) : null;
        },
        remove: (key) => {
            localStorage.removeItem(key);
        }
    };

    // Mock user authentication
    window.AuthManager = {
        isLoggedIn: () => {
            return StorageHelper.get('user') !== null;
        },
        login: (userData) => {
            StorageHelper.set('user', userData);
            showToast('Login successful!', 'success');
        },
        logout: () => {
            StorageHelper.remove('user');
            showToast('Logged out successfully', 'info');
            window.location.href = 'index.html';
        },
        getUser: () => {
            return StorageHelper.get('user');
        }
    };

    // Course progress tracking
    window.CourseManager = {
        updateProgress: (courseId, lessonId, progress) => {
            const progressData = StorageHelper.get('courseProgress') || {};
            if (!progressData[courseId]) {
                progressData[courseId] = {};
            }
            progressData[courseId][lessonId] = progress;
            StorageHelper.set('courseProgress', progressData);
        },
        getProgress: (courseId) => {
            const progressData = StorageHelper.get('courseProgress') || {};
            return progressData[courseId] || {};
        }
    };

    // Video player functionality
    const videoPlayers = document.querySelectorAll('.video-player');
    videoPlayers.forEach(player => {
        const playBtn = player.parentElement.querySelector('.play-btn');
        const progressBar = player.parentElement.querySelector('.progress-filled');
        let isPlaying = false;
        let progress = 0;

        if (playBtn) {
            playBtn.addEventListener('click', () => {
                isPlaying = !isPlaying;
                playBtn.textContent = isPlaying ? 'Pause' : 'Play';
                
                if (isPlaying) {
                    // Simulate video progress
                    const interval = setInterval(() => {
                        if (!isPlaying) {
                            clearInterval(interval);
                            return;
                        }
                        
                        progress += 1;
                        if (progressBar) {
                            progressBar.style.width = `${Math.min(progress, 100)}%`;
                        }
                        
                        if (progress >= 100) {
                            clearInterval(interval);
                            playBtn.textContent = 'Replay';
                            isPlaying = false;
                            showToast('Video completed!', 'success');
                        }
                    }, 100);
                }
            });
        }
    });
});

// Utility functions
function formatCurrency(amount) {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(amount);
}

function formatDate(date) {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    }).format(new Date(date));
}

function generateId() {
    return Date.now().toString(36) + Math.random().toString(36).substr(2);
}

// Mock data
const mockCourses = [
    {
        id: 1,
        title: 'Web Development Bootcamp',
        description: 'Complete guide to modern web development',
        price: 99,
        rating: 4.8,
        reviews: 120,
        instructor: 'John Doe',
        duration: '40 hours',
        lessons: 25,
        image: 'https://images.pexels.com/photos/270348/pexels-photo-270348.jpeg'
    },
    {
        id: 2,
        title: 'Digital Marketing Mastery',
        description: 'Learn modern digital marketing strategies',
        price: 79,
        rating: 4.9,
        reviews: 95,
        instructor: 'Jane Smith',
        duration: '30 hours',
        lessons: 20,
        image: 'https://images.pexels.com/photos/265087/pexels-photo-265087.jpeg'
    },
    {
        id: 3,
        title: 'Data Science Fundamentals',
        description: 'Introduction to data science and analytics',
        price: 129,
        rating: 4.7,
        reviews: 87,
        instructor: 'Mike Johnson',
        duration: '50 hours',
        lessons: 30,
        image: 'https://images.pexels.com/photos/590020/pexels-photo-590020.jpg'
    }
];

const mockUsers = [
    { id: 1, name: 'John Doe', email: 'john@example.com', role: 'student', joinDate: '2024-01-15' },
    { id: 2, name: 'Jane Smith', email: 'jane@example.com', role: 'instructor', joinDate: '2024-01-10' },
    { id: 3, name: 'Mike Johnson', email: 'mike@example.com', role: 'student', joinDate: '2024-01-20' }
];