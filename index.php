<?php
session_start();
// Include database configuration
require_once 'config/db.php';
?>

<?php include 'includes/header.php'; ?>

<!-- Hero Section -->
<section class="hero">
    <div class="hero-container">
        <h1>Find the Perfect Freelance Services</h1>
        <p>Connect with talented freelancers and get your projects done with excellence</p>

        <div class="search-container">
            <form class="search-box">
                <input type="text" class="search-input" placeholder="What service are you looking for today?" required>
                <button type="submit" class="search-btn">
                    <i class="fas fa-search"></i> Search
                </button>
            </form>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="categories" id="categories">
    <div class="categories-container">
        <h2 class="section-title">Popular Categories</h2>

        <div class="categories-grid">
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-code"></i>
                </div>
                <h3>Web Development</h3>
                <p>Custom websites, web apps, and e-commerce solutions</p>
                <a href="#" class="category-link">Browse Projects <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>
                <h3>Mobile Development</h3>
                <p>iOS and Android apps with modern UI/UX design</p>
                <a href="#" class="category-link">Browse Projects <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-palette"></i>
                </div>
                <h3>Design & Creative</h3>
                <p>Logo design, branding, and creative visual content</p>
                <a href="#" class="category-link">Browse Projects <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <h3>Digital Marketing</h3>
                <p>SEO, social media marketing, and content strategy</p>
                <a href="#" class="category-link">Browse Projects <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-pen-fancy"></i>
                </div>
                <h3>Writing & Translation</h3>
                <p>Content writing, copywriting, and translation services</p>
                <a href="#" class="category-link">Browse Projects <i class="fas fa-arrow-right"></i></a>
            </div>

            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-video"></i>
                </div>
                <h3>Video & Animation</h3>
                <p>Video editing, motion graphics, and animation</p>
                <a href="#" class="category-link">Browse Projects <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </div>
</section>

<!-- Trusted By Section -->
<section class="trusted-by">
    <div class="trusted-container">
        <h3 class="trusted-title">Trusted by leading companies worldwide</h3>

        <div class="companies-grid">
            <div class="company-logo">Microsoft</div>
            <div class="company-logo">Google</div>
            <div class="company-logo">Amazon</div>
            <div class="company-logo">Apple</div>
            <div class="company-logo">Netflix</div>
            <div class="company-logo">Spotify</div>
        </div>
    </div>
</section>

<!-- Stats Section -->
<section class="stats">
    <div class="stats-container">
        <div class="stats-grid">
            <div class="stat-item">
                <h3>2.5M+</h3>
                <p>Active Freelancers</p>
            </div>

            <div class="stat-item">
                <h3>500K+</h3>
                <p>Projects Completed</p>
            </div>

            <div class="stat-item">
                <h3>95%</h3>
                <p>Client Satisfaction</p>
            </div>

            <div class="stat-item">
                <h3>$2B+</h3>
                <p>Paid to Freelancers</p>
            </div>
        </div>
    </div>
</section>

<!-- How It Works Section -->
<section class="how-it-works" id="about">
    <div class="categories-container">
        <h2 class="section-title">How Freelance Connect Works</h2>

        <div class="categories-grid">
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-user-plus"></i>
                </div>
                <h3>1. Create Your Profile</h3>
                <p>Sign up and build your professional profile showcasing your skills and experience</p>
            </div>

            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3>2. Find Perfect Projects</h3>
                <p>Browse thousands of projects and find the ones that match your expertise</p>
            </div>

            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <h3>3. Connect & Collaborate</h3>
                <p>Submit proposals, communicate with clients, and deliver exceptional work</p>
            </div>
        </div>
    </div>
</section>

<!-- CTA Section -->
<section class="cta">
    <div class="hero-container">
        <h2>Ready to Start Your Freelance Journey?</h2>
        <p>Join thousands of successful freelancers and clients on Freelance Connect</p>
        <div class="cta-buttons">
            <a href="register.php" class="btn btn-primary">Get Started as Freelancer</a>
            <a href="register.php" class="btn btn-outline">Hire Talent</a>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>