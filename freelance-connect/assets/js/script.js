// Mobile Navigation Toggle
const navToggle = document.getElementById('nav-toggle');
const navMenu = document.getElementById('nav-menu');

if (navToggle && navMenu) {
    navToggle.addEventListener('click', () => {
        navMenu.classList.toggle('active');
        
        // Animate hamburger menu
        const bars = navToggle.querySelectorAll('.bar');
        bars.forEach(bar => bar.classList.toggle('active'));
    });
}

// Close mobile menu when clicking on a link
const navLinks = document.querySelectorAll('.nav-link');
navLinks.forEach(link => {
    link.addEventListener('click', () => {
        navMenu.classList.remove('active');
        const bars = navToggle.querySelectorAll('.bar');
        bars.forEach(bar => bar.classList.remove('active'));
    });
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
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

// Search functionality
const searchForm = document.querySelector('.search-box');
const searchInput = document.querySelector('.search-input');

if (searchForm && searchInput) {
    searchForm.addEventListener('submit', function(e) {
        e.preventDefault();
        const query = searchInput.value.trim();
        if (query) {
            // You can implement search functionality here
            console.log('Searching for:', query);
            alert('Search functionality will be implemented in the next phase!');
        }
    });
}

// Animate stats on scroll
const statsSection = document.querySelector('.stats');
const statItems = document.querySelectorAll('.stat-item h3');

if (statsSection && statItems.length > 0) {
    const observerOptions = {
        threshold: 0.5,
        rootMargin: '0px 0px -100px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                statItems.forEach((stat, index) => {
                    setTimeout(() => {
                        animateNumber(stat);
                    }, index * 200);
                });
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    observer.observe(statsSection);
}

// Animate number counting
function animateNumber(element) {
    const target = parseInt(element.textContent);
    const duration = 2000;
    const step = target / (duration / 16);
    let current = 0;

    const timer = setInterval(() => {
        current += step;
        if (current >= target) {
            current = target;
            clearInterval(timer);
        }
        element.textContent = Math.floor(current).toLocaleString();
    }, 16);
}

// Add scroll effect to header
const header = document.querySelector('.header');
let lastScroll = 0;

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll <= 0) {
        header.classList.remove('scroll-up');
        return;
    }
    
    if (currentScroll > lastScroll && !header.classList.contains('scroll-down')) {
        // Scroll down
        header.classList.remove('scroll-up');
        header.classList.add('scroll-down');
    } else if (currentScroll < lastScroll && header.classList.contains('scroll-down')) {
        // Scroll up
        header.classList.remove('scroll-down');
        header.classList.add('scroll-up');
    }
    lastScroll = currentScroll;
});

// Add loading animation
window.addEventListener('load', () => {
    document.body.classList.add('loaded');
});

// Category card hover effects
const categoryCards = document.querySelectorAll('.category-card');

categoryCards.forEach(card => {
    card.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-10px) scale(1.02)';
    });
    
    card.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0) scale(1)';
    });
});

// Form validation for search
if (searchInput) {
    searchInput.addEventListener('input', function() {
        if (this.value.length > 0) {
            this.classList.add('has-content');
        } else {
            this.classList.remove('has-content');
        }
    });
}

// Add CSS for scroll effects
const style = document.createElement('style');
style.textContent = `
    .header {
        transition: transform 0.3s ease;
    }
    
    .header.scroll-down {
        transform: translateY(-100%);
    }
    
    .header.scroll-up {
        transform: translateY(0);
    }
    
    .bar.active:nth-child(1) {
        transform: rotate(-45deg) translate(-5px, 6px);
    }
    
    .bar.active:nth-child(2) {
        opacity: 0;
    }
    
    .bar.active:nth-child(3) {
        transform: rotate(45deg) translate(-5px, -6px);
    }
    
    .search-input.has-content {
        border-color: #14a800;
    }
    
    body.loaded {
        opacity: 1;
    }
    
    body {
        opacity: 0;
        transition: opacity 0.3s ease;
    }
`;

document.head.appendChild(style); 