// Mobile Menu Toggle
const mobileMenuToggle = document.getElementById('nav-toggle');
const navMenu = document.getElementById('nav-menu');

if (mobileMenuToggle && navMenu) {
    mobileMenuToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
    });
}

// Smooth Scrolling
function scrollToSection(sectionId) {
    const section = document.getElementById(sectionId);
    if (section) {
        section.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }
}

// Header Scroll Effect
window.addEventListener('scroll', () => {
    const header = document.getElementById('header');
    if (window.scrollY > 60) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// FAQ Toggle
function toggleFAQ(element) {
    const faqItem = element.parentElement;
    const isActive = faqItem.classList.contains("active");

    // Close all other FAQs before opening the clicked one
    document.querySelectorAll(".faqq-item").forEach(item => {
        item.classList.remove("active");
        const question = item.querySelector(".faqq-question");
        const answer = item.querySelector(".faqq-answer");
        if (question && answer) {
            question.setAttribute("aria-expanded", "false");
            answer.hidden = true;
        }
    });

    // Toggle the clicked FAQ
    if (!isActive) {
        faqItem.classList.add("active");
        const question = faqItem.querySelector(".faqq-question");
        const answer = faqItem.querySelector(".faqq-answer");
        if (question && answer) {
            question.setAttribute("aria-expanded", "true");
            answer.hidden = false;
        }
    }
}

// Auto Slider for Reviews with dynamic speed and touch support
document.addEventListener('DOMContentLoaded', () => {
    const reviewsSection = document.querySelector('.reviews-section');
    let autoScrollAnimationFrame;
    let isDragging = false;
    let startX;
    let scrollLeft;

    function startAutoScroll() {
        if (!reviewsSection) return;

        function animate() {
            if (!isDragging) {
                const scrollLeftPos = reviewsSection.scrollLeft;
                const scrollWidth = reviewsSection.scrollWidth;
                const clientWidth = reviewsSection.clientWidth;

                // Dynamic scroll speed based on screen width
                let scrollAmount = clientWidth * 0.0015; // 0.15% of visible width per frame

                if (window.innerWidth < 768) {
                    scrollAmount = clientWidth * 0.0025; // slower on small screens
                }

                if (scrollLeftPos + clientWidth >= scrollWidth - 1) {
                    // Reset to start smoothly
                    reviewsSection.scrollTo({ left: 0, behavior: 'auto' });
                } else {
                    reviewsSection.scrollBy({ left: scrollAmount, behavior: 'auto' });
                }
            }
            autoScrollAnimationFrame = requestAnimationFrame(animate);
        }
        animate();
    }

    function stopAutoScroll() {
        if (autoScrollAnimationFrame) {
            cancelAnimationFrame(autoScrollAnimationFrame);
        }
    }

    // Touch and mouse drag support for mobile and desktop
    if (reviewsSection) {
        reviewsSection.addEventListener('mouseenter', stopAutoScroll);
        reviewsSection.addEventListener('mouseleave', startAutoScroll);

        // Touch events
        reviewsSection.addEventListener('touchstart', (e) => {
            isDragging = true;
            startX = e.touches[0].pageX - reviewsSection.offsetLeft;
            scrollLeft = reviewsSection.scrollLeft;
            stopAutoScroll();
        });

        reviewsSection.addEventListener('touchmove', (e) => {
            if (!isDragging) return;
            const x = e.touches[0].pageX - reviewsSection.offsetLeft;
            const walk = (startX - x); // scroll-fast
            reviewsSection.scrollLeft = scrollLeft + walk;
        });

        reviewsSection.addEventListener('touchend', () => {
            isDragging = false;
            startAutoScroll();
        });

        // Mouse events for drag support on desktop
        reviewsSection.addEventListener('mousedown', (e) => {
            isDragging = true;
            startX = e.pageX - reviewsSection.offsetLeft;
            scrollLeft = reviewsSection.scrollLeft;
            stopAutoScroll();
            reviewsSection.classList.add('dragging');
        });

        reviewsSection.addEventListener('mousemove', (e) => {
            if (!isDragging) return;
            e.preventDefault();
            const x = e.pageX - reviewsSection.offsetLeft;
            const walk = (startX - x);
            reviewsSection.scrollLeft = scrollLeft + walk;
        });

        reviewsSection.addEventListener('mouseup', () => {
            isDragging = false;
            startAutoScroll();
            reviewsSection.classList.remove('dragging');
        });

        reviewsSection.addEventListener('mouseleave', () => {
            if (isDragging) {
                isDragging = false;
                startAutoScroll();
                reviewsSection.classList.remove('dragging');
            }
        });
    }

    startAutoScroll();
});

// Copy Referral Link
function copyReferralLink() {
    const referralInput = document.getElementById('referral-link-input');
    if (referralInput) {
        referralInput.select();
        referralInput.setSelectionRange(0, 99999); // For mobile devices
        navigator.clipboard.writeText(referralInput.value).then(() => {
            alert('Referral link copied to clipboard!');
        }).catch(() => {
            alert('Failed to copy referral link. Please copy manually.');
        });
    }
}

// Counter Animation
function animateCounter(element, target, duration = 2000) {
    let start = 0;
    const increment = target / (duration / 16);

    const timer = setInterval(() => {
        start += increment;
        if (start >= target) {
            element.textContent = target + (target === 95 ? '%' : '+');
            clearInterval(timer);
        } else {
            element.textContent = Math.floor(start) + (target === 95 ? '%' : '+');
        }
    }, 16);
}

// Intersection Observer for counter animation
const observerOptions = {
    threshold: 0.5,
    rootMargin: '0px 0px -100px 0px'
};

const counterObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const target = parseInt(entry.target.getAttribute('data-target'));
            animateCounter(entry.target, target);
            counterObserver.unobserve(entry.target);
        }
    });
}, observerOptions);

// Observe all counter elements
document.querySelectorAll('.stat-number, .impact-number').forEach(counter => {
    counterObserver.observe(counter);
});

// Testimonial Slider
class TestimonialSlider {
    constructor() {
        this.currentSlide = 0;
        this.slides = document.querySelectorAll('.testimonial-slide');
        this.dots = document.querySelectorAll('.dot');
        this.prevBtn = document.getElementById('prev-btn');
        this.nextBtn = document.getElementById('next-btn');
        this.autoSlideInterval = null;

        this.init();
    }

    init() {
        // Add event listeners
        this.prevBtn.addEventListener('click', () => this.prevSlide());
        this.nextBtn.addEventListener('click', () => this.nextSlide());

        // Add dot click listeners
        this.dots.forEach((dot, index) => {
            dot.addEventListener('click', () => this.goToSlide(index));
        });

        // Start auto-slide
        this.startAutoSlide();

        // Pause auto-slide on hover
        const slider = document.querySelector('.testimonial-slider');
        slider.addEventListener('mouseenter', () => this.stopAutoSlide());
        slider.addEventListener('mouseleave', () => this.startAutoSlide());
    }

    showSlide(index) {
        // Hide all slides
        this.slides.forEach(slide => slide.classList.remove('active'));
        this.dots.forEach(dot => dot.classList.remove('active'));

        // Show current slide
        this.slides[index].classList.add('active');
        this.dots[index].classList.add('active');

        this.currentSlide = index;
    }

    nextSlide() {
        const nextIndex = (this.currentSlide + 1) % this.slides.length;
        this.showSlide(nextIndex);
    }

    prevSlide() {
        const prevIndex = (this.currentSlide - 1 + this.slides.length) % this.slides.length;
        this.showSlide(prevIndex);
    }

    goToSlide(index) {
        this.showSlide(index);
    }

    startAutoSlide() {
        this.autoSlideInterval = setInterval(() => {
            this.nextSlide();
        }, 5000); // Change slide every 5 seconds
    }

    stopAutoSlide() {
        if (this.autoSlideInterval) {
            clearInterval(this.autoSlideInterval);
            this.autoSlideInterval = null;
        }
    }
}

// Initialize testimonial slider when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    new TestimonialSlider();
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            const targetPosition = target.offsetTop - 20;

            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// Scroll reveal animation
const revealElements = document.querySelectorAll('.stat-card, .course-card, .step, .benefit-card, .impact-stat, .mentor-card');

const revealObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('fade-in-up');
            revealObserver.unobserve(entry.target);
        }
    });
}, {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
});

revealElements.forEach(element => {
    revealObserver.observe(element);
});

// Add loading animation to buttons
document.querySelectorAll('.btn').forEach(button => {
    button.addEventListener('click', function(e) {
        // Add ripple effect
        const ripple = document.createElement('span');
        const rect = this.getBoundingClientRect();
        const size = Math.max(rect.width, rect.height);
        const x = e.clientX - rect.left - size / 2;
        const y = e.clientY - rect.top - size / 2;

        ripple.style.width = ripple.style.height = size + 'px';
        ripple.style.left = x + 'px';
        ripple.style.top = y + 'px';
        ripple.classList.add('ripple');

        this.appendChild(ripple);

        setTimeout(() => {
            ripple.remove();
        }, 600);
    });
});

// Add ripple effect CSS
const style = document.createElement('style');
style.textContent = `
    .btn {
        position: relative;
        overflow: hidden;
    }

    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s linear;
        pointer-events: none;
    }

    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// Form validation (if forms are added later)
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Lazy loading for images
const imageObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            const img = entry.target;
            img.src = img.dataset.src;
            img.classList.remove('lazy');
            imageObserver.unobserve(img);
        }
    });
});

document.querySelectorAll('img[data-src]').forEach(img => {
    imageObserver.observe(img);
});

// Performance optimization: Debounce scroll events
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Optimized scroll handler
const optimizedScrollHandler = debounce(() => {
    const scrolled = window.scrollY > 100;
    header.classList.toggle('scrolled', scrolled);
}, 10);

window.addEventListener('scroll', optimizedScrollHandler);

// Add keyboard navigation for testimonial slider
document.addEventListener('keydown', (e) => {
    const slider = document.querySelector('.testimonial-slider');
    if (slider && slider.matches(':hover')) {
        if (e.key === 'ArrowLeft') {
            document.getElementById('prev-btn').click();
        } else if (e.key === 'ArrowRight') {
            document.getElementById('next-btn').click();
        }
    }
});

function toggleFAQ(element) {
    const faqItem = element.parentElement;
    const isActive = faqItem.classList.contains("active");

    // Close all other FAQs before opening the clicked one
    document.querySelectorAll(".faq-item").forEach(item => {
        item.classList.remove("active");
        const question = item.querySelector(".faq-question");
        const answer = item.querySelector(".faq-answer");
        if (question && answer) {
            question.setAttribute("aria-expanded", "false");
            answer.hidden = true;
        }
    });

    // Toggle the clicked FAQ
    if (!isActive) {
        faqItem.classList.add("active");
        const question = faqItem.querySelector(".faq-question");
        const answer = faqItem.querySelector(".faq-answer");
        if (question && answer) {
            question.setAttribute("aria-expanded", "true");
            answer.hidden = false;
        }
    }
}



// Add FAQ items to reveal observer for scroll animations
const faqItems = document.querySelectorAll('.faq-item');
faqItems.forEach(item => {
    revealObserver.observe(item);
});

// Initialize all animations and interactions when page loads
window.addEventListener('load', () => {
    // Add loaded class to body for CSS animations
    document.body.classList.add('loaded');

    // Trigger any initial animations
    const heroElements = document.querySelectorAll('.hero-text > *');
    heroElements.forEach((element, index) => {
        setTimeout(() => {
            element.classList.add('fade-in-up');
        }, index * 200);
    });
});
