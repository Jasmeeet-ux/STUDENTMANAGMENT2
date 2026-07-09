// Tab functionality
function showTab(tabId) {
    // Remove active class from all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => button.classList.remove('active'));
    
    // Hide all tab contents
    const tabContents = document.querySelectorAll('.tab-content');
    tabContents.forEach(content => content.classList.remove('active'));
    
    // Show selected tab content
    document.getElementById(tabId).classList.add('active');
    
    // Add active class to clicked button
    event.target.classList.add('active');
}



// FAQ toggle functionality
function toggleFAQ(faqNumber) {
    const faqItem = document.querySelector(`[onclick="toggleFAQ(${faqNumber})"]`).closest('.faq-item');
    const answer = document.getElementById(`faq-${faqNumber}`);
    const isActive = faqItem.classList.contains('active');

    // Close all FAQ items first
    document.querySelectorAll('.faq-item').forEach(item => {
        item.classList.remove('active');
    });

    document.querySelectorAll('.faq-answer').forEach(ans => {
        ans.classList.remove('show');
        ans.style.display = 'none';
    });

    // If this FAQ wasn't active, open it
    if (!isActive) {
        faqItem.classList.add('active');
        answer.style.display = 'block';
        setTimeout(() => {
            answer.classList.add('show');
        }, 10);
    }
}

// Smooth scroll to pricing section
function scrollToPricing() {
    document.getElementById('pricing').scrollIntoView({
        behavior: 'smooth',
        block: 'center'
    });
}

// Intersection Observer for animations
function initScrollAnimations() {
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    // Observe elements that should animate on scroll
    const animatedElements = document.querySelectorAll('.info-card, .course-card, .review, .faq-item');
    animatedElements.forEach(el => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
        observer.observe(el);
    });
}

// Entrance animations for staggered reveals
function initEntranceAnimations() {
    // Stagger animations for info-cards
    const infoCards = document.querySelectorAll('.info-card');
    infoCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.animationDelay = `${index * 0.1}s`;
            card.classList.add('animate-entrance');
        }, 100);
    });

    // Stagger animations for course-cards
    const courseCards = document.querySelectorAll('.course-card');
    courseCards.forEach((card, index) => {
        setTimeout(() => {
            card.style.animationDelay = `${index * 0.15}s`;
            card.classList.add('animate-entrance');
        }, 200);
    });

    // Stagger animations for FAQ items
    const faqItems = document.querySelectorAll('.faq-item');
    faqItems.forEach((item, index) => {
        setTimeout(() => {
            item.style.animationDelay = `${index * 0.1}s`;
            item.classList.add('animate-entrance');
        }, 300);
    });
}

// Progress bar animation for rating breakdown
function animateRatingBars() {
    const bars = document.querySelectorAll('.fill');
    bars.forEach((bar, index) => {
        const targetWidth = bar.style.width;
        bar.style.width = '0%';

        setTimeout(() => {
            bar.style.transition = 'width 1.2s cubic-bezier(0.25, 0.46, 0.45, 0.94)';
            bar.style.width = targetWidth;
        }, 500 + index * 200); // Stagger the bars
    });
}

// Course preview play button functionality
function initVideoPreview() {
    const playButton = document.querySelector('.play-button');
    if (playButton) {
        playButton.addEventListener('click', () => {
            // Add a subtle animation
            playButton.style.transform = 'translate(-50%, -50%) scale(0.9)';
            setTimeout(() => {
                playButton.style.transform = 'translate(-50%, -50%) scale(1.1)';
            }, 150);
            setTimeout(() => {
                // Start the video autoplay
                const iframe = document.querySelector('.video iframe');
                if (iframe && !iframe.src.includes('autoplay=1')) {
                    iframe.src += '&autoplay=1';
                }
                playButton.style.transform = 'translate(-50%, -50%) scale(1)';
                // Hide the play button to reveal the video
                playButton.style.display = 'none';
            }, 300);
        });
    }
}

// CTA button click tracking
function initCTATracking() {
    const ctaButtons = document.querySelectorAll('.cta-button');
    ctaButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            // Add ripple effect
            const ripple = document.createElement('span');
            const rect = button.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.cssText = `
                position: absolute;
                width: ${size}px;
                height: ${size}px;
                left: ${x}px;
                top: ${y}px;
                background: rgba(255, 255, 255, 0.3);
                border-radius: 50%;
                transform: scale(0);
                animation: ripple 0.6s linear;
                pointer-events: none;
            `;
            
            button.style.position = 'relative';
            button.style.overflow = 'hidden';
            button.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });
}

// Add ripple animation CSS
function addRippleStyles() {
    const style = document.createElement('style');
    style.textContent = `
        @keyframes ripple {
            to {
                transform: scale(2);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);
}

// Course card hover effects
function initCourseCardEffects() {
    const courseCards = document.querySelectorAll('.course-card');
    courseCards.forEach(card => {
        card.addEventListener('mouseenter', () => {
            card.style.transform = 'translateY(-8px) scale(1.02)';
        });
        
        card.addEventListener('mouseleave', () => {
            card.style.transform = 'translateY(0) scale(1)';
        });
    });
}

// Navbar scroll effect (if you want to add a fixed navbar later)
function initNavbarScroll() {
    let lastScrollY = window.scrollY;
    
    window.addEventListener('scroll', () => {
        const currentScrollY = window.scrollY;
        // You can add navbar show/hide logic here if needed
        lastScrollY = currentScrollY;
    });
}

// Testimonials slider functionality
function initTestimonialsSlider() {
    const slider = document.querySelector('.testimonials-container');
    const dots = document.querySelectorAll('.dot');
    let currentSlide = 0;
    let autoSlideInterval;
    const totalSlides = 4;
    const slideInterval = 5000; // 5 seconds

    function showSlide(index) {
        if (index >= totalSlides) currentSlide = 0;
        if (index < 0) currentSlide = totalSlides - 1;

        slider.style.transform = `translateX(-${currentSlide * 25}%)`;

        // Update dots
        dots.forEach((dot, i) => {
            dot.classList.toggle('active', i === currentSlide);
        });
    }

    function nextSlide() {
        currentSlide++;
        showSlide(currentSlide);
    }

    function startAutoSlide() {
        autoSlideInterval = setInterval(nextSlide, slideInterval);
    }

    function stopAutoSlide() {
        clearInterval(autoSlideInterval);
    }

    // Dot click handlers
    dots.forEach((dot, index) => {
        dot.addEventListener('click', () => {
            currentSlide = index;
            showSlide(currentSlide);
            stopAutoSlide();
            startAutoSlide(); // Restart auto slide
        });
    });

    // Pause on hover
    const sliderContainer = document.querySelector('.testimonials-slider');
    sliderContainer.addEventListener('mouseenter', stopAutoSlide);
    sliderContainer.addEventListener('mouseleave', startAutoSlide);

    // Start auto sliding
    startAutoSlide();
}

// Initialize all functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initScrollAnimations();
    initEntranceAnimations();
    initVideoPreview();
    initCTATracking();
    initCourseCardEffects();
    addRippleStyles();
    initNavbarScroll();
    initTestimonialsSlider();

    // Animate rating bars after a delay
    setTimeout(animateRatingBars, 1000);

    // Add loading states
    const images = document.querySelectorAll('img');
    images.forEach(img => {
        if (!img.complete) {
            img.classList.add('loading');
            img.addEventListener('load', () => {
                img.classList.remove('loading');
            });
        }
    });
});

// Keyboard navigation for accessibility
document.addEventListener('keydown', (e) => {
    // Tab navigation for custom elements
    if (e.key === 'Enter' || e.key === ' ') {
        const activeElement = document.activeElement;
        
        if (activeElement.classList.contains('tab-button')) {
            e.preventDefault();
            activeElement.click();
        }
        
        if (activeElement.classList.contains('faq-question')) {
            e.preventDefault();
            activeElement.click();
        }
        

    }
});

// Performance optimization: Lazy loading for images
function initLazyLoading() {
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    if (img.dataset.src) {
                        img.src = img.dataset.src;
                        img.removeAttribute('data-src');
                        imageObserver.unobserve(img);
                    }
                }
            });
        });
        
        document.querySelectorAll('img[data-src]').forEach(img => {
            imageObserver.observe(img);
        });
    }
}

// Error handling for failed image loads
document.addEventListener('error', (e) => {
    if (e.target.tagName === 'IMG') {
        e.target.style.display = 'none';
        console.log('Image failed to load:', e.target.src);
    }
}, true);

// Add smooth transitions to all interactive elements
function initSmoothTransitions() {
    const style = document.createElement('style');
    style.textContent = `
        * {
            transition: color 0.3s ease, background-color 0.3s ease, border-color 0.3s ease, transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .reduced-motion * {
            transition: none !important;
            animation: none !important;
        }
    `;
    document.head.appendChild(style);
    
    // Respect user's motion preferences
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        document.body.classList.add('reduced-motion');
    }
}

// Call smooth transitions on load
document.addEventListener('DOMContentLoaded', initSmoothTransitions);

// Add focus trap for modals (if you add them later)
function trapFocus(element) {
    const focusableElements = element.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );
    const firstElement = focusableElements[0];
    const lastElement = focusableElements[focusableElements.length - 1];
    
    element.addEventListener('keydown', (e) => {
        if (e.key === 'Tab') {
            if (e.shiftKey) {
                if (document.activeElement === firstElement) {
                    lastElement.focus();
                    e.preventDefault();
                }
            } else {
                if (document.activeElement === lastElement) {
                    firstElement.focus();
                    e.preventDefault();
                }
            }
        }
        
        if (e.key === 'Escape') {
            // Close modal logic would go here
        }
    });
}

// Console easter egg for developers
console.log(`
🎓 Course Landing Page
Built with vanilla JavaScript, CSS Grid, and lots of ❤️

Features:
✅ Responsive Design
✅ Smooth Animations  
✅ Accessibility Features
✅ Performance Optimized
✅ SEO Friendly

Want to customize? Check out the modular CSS and JS structure!
`);