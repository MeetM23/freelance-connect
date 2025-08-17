<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Categories - Freelance Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .categories-page {
            margin-top: 100px;
            padding: 2rem 0;
        }

        .categories-header {
            text-align: center;
            margin-bottom: 3rem;
        }

        .categories-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }

        .categories-subtitle {
            font-size: 1.1rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
        }

        .categories-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 2rem;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .category-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #f0f0f0;
        }

        .category-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }

        .category-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1.5rem;
        }

        .category-icon i {
            font-size: 2rem;
            color: white;
        }

        .category-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        .category-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .category-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.2rem;
            font-weight: 600;
            color: #14a800;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
        }

        .category-actions {
            display: flex;
            gap: 1rem;
        }

        .btn-category {
            flex: 1;
            padding: 0.75rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #14a800;
            color: white;
        }

        .btn-primary:hover {
            background: #118f00;
            transform: translateY(-2px);
        }

        .btn-outline {
            border: 2px solid #14a800;
            color: #14a800;
            background: transparent;
        }

        .btn-outline:hover {
            background: #14a800;
            color: white;
            transform: translateY(-2px);
        }

        .popular-skills {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .skills-title {
            font-size: 0.9rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .skills-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .skill-tag {
            background: #e8f5e8;
            color: #14a800;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .categories-grid {
                grid-template-columns: 1fr;
                padding: 0 1rem;
            }

            .categories-title {
                font-size: 2rem;
            }

            .category-actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <header class="header">
        <nav class="navbar">
            <div class="nav-container">
                <div class="nav-logo">
                    <a href="index.php">
                        <i class="fas fa-handshake"></i>
                        <span>Freelance Connect</span>
                    </a>
                </div>

                <div class="nav-menu" id="nav-menu">
                    <ul class="nav-list">
                        <li class="nav-item">
                            <a href="login.php" class="nav-link">Find Work</a>
                        </li>
                        <li class="nav-item">
                            <a href="login.php" class="nav-link">Hire Talent</a>
                        </li>
                        <li class="nav-item">
                            <a href="categories.php" class="nav-link">Categories</a>
                        </li>
                        <li class="nav-item">
                            <a href="about.php" class="nav-link">About</a>
                        </li>
                    </ul>
                </div>

                <div class="nav-buttons">
                    <a href="login.php" class="btn btn-outline">Log In</a>
                    <a href="register.php" class="btn btn-primary">Sign Up</a>
                </div>

                <div class="nav-toggle" id="nav-toggle">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </nav>
    </header>

    <div class="categories-page">
        <div class="categories-header">
            <h1 class="categories-title">Browse by Category</h1>
            <p class="categories-subtitle">Find the perfect project in your area of expertise. Each category offers
                unique opportunities for freelancers and clients.</p>
        </div>

        <div class="categories-grid">
            <!-- Web Development -->
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-code"></i>
                </div>

                <h3 class="category-title">Web Development</h3>
                <p class="category-description">
                    Build custom websites, web applications, and e-commerce solutions. From simple landing pages to
                    complex web platforms.
                </p>

         

                <div class="popular-skills">
                    <div class="skills-title">Popular Skills:</div>
                    <div class="skills-tags">
                        <span class="skill-tag">PHP</span>
                        <span class="skill-tag">JavaScript</span>
                        <span class="skill-tag">React</span>
                        <span class="skill-tag">Node.js</span>
                        <span class="skill-tag">HTML/CSS</span>
                    </div>
                </div>
            </div>

            <!-- Mobile Development -->
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-mobile-alt"></i>
                </div>

                <h3 class="category-title">Mobile Development</h3>
                <p class="category-description">
                    Create iOS and Android applications with modern UI/UX design. Native and cross-platform development.
                </p>


                <div class="popular-skills">
                    <div class="skills-title">Popular Skills:</div>
                    <div class="skills-tags">
                        <span class="skill-tag">iOS</span>
                        <span class="skill-tag">Android</span>
                        <span class="skill-tag">React Native</span>
                        <span class="skill-tag">Flutter</span>
                        <span class="skill-tag">Swift</span>
                    </div>
                </div>
            </div>

            <!-- Design & Creative -->
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-palette"></i>
                </div>

                <h3 class="category-title">Design & Creative</h3>
                <p class="category-description">
                    Design logos, branding materials, and creative visual content. Graphic design and creative services.
                </p>


                <div class="popular-skills">
                    <div class="skills-title">Popular Skills:</div>
                    <div class="skills-tags">
                        <span class="skill-tag">Photoshop</span>
                        <span class="skill-tag">Illustrator</span>
                        <span class="skill-tag">UI/UX</span>
                        <span class="skill-tag">Logo Design</span>
                        <span class="skill-tag">Branding</span>
                    </div>
                </div>
            </div>

            <!-- Digital Marketing -->
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-chart-line"></i>
                </div>

                <h3 class="category-title">Digital Marketing</h3>
                <p class="category-description">
                    SEO optimization, social media marketing, and content strategy. Drive traffic and increase
                    conversions.
                </p>

                <div class="popular-skills">
                    <div class="skills-title">Popular Skills:</div>
                    <div class="skills-tags">
                        <span class="skill-tag">SEO</span>
                        <span class="skill-tag">Social Media</span>
                        <span class="skill-tag">Google Ads</span>
                        <span class="skill-tag">Content Marketing</span>
                        <span class="skill-tag">Analytics</span>
                    </div>
                </div>
            </div>

            <!-- Writing & Translation -->
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-pen-fancy"></i>
                </div>

                <h3 class="category-title">Writing & Translation</h3>
                <p class="category-description">
                    Content writing, copywriting, and translation services. Professional writing for all industries.
                </p>

   

                <div class="popular-skills">
                    <div class="skills-title">Popular Skills:</div>
                    <div class="skills-tags">
                        <span class="skill-tag">Content Writing</span>
                        <span class="skill-tag">Copywriting</span>
                        <span class="skill-tag">Translation</span>
                        <span class="skill-tag">Editing</span>
                        <span class="skill-tag">SEO Writing</span>
                    </div>
                </div>
            </div>

            <!-- Video & Animation -->
            <div class="category-card">
                <div class="category-icon">
                    <i class="fas fa-video"></i>
                </div>

                <h3 class="category-title">Video & Animation</h3>
                <p class="category-description">
                    Video editing, motion graphics, and animation. Create engaging visual content.
                </p>

          
                <div class="popular-skills">
                    <div class="skills-title">Popular Skills:</div>
                    <div class="skills-tags">
                        <span class="skill-tag">Video Editing</span>
                        <span class="skill-tag">Motion Graphics</span>
                        <span class="skill-tag">Animation</span>
                        <span class="skill-tag">After Effects</span>
                        <span class="skill-tag">Premiere Pro</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer">
        <div class="footer-container">
            <div class="footer-content">
                <div class="footer-section">
                    <h3>Freelance Connect</h3>
                    <p>Connecting talented freelancers with amazing opportunities worldwide.</p>
                </div>
                <div class="footer-section">
                    <h4>Quick Links</h4>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="categories.php">Categories</a></li>
                        <li><a href="about.php">About</a></li>
                        <li><a href="register.php">Sign Up</a></li>
                    </ul>
                </div>
                <div class="footer-section">
                    <h4>Contact</h4>
                    <p>Email: info@freelanceconnect.com</p>
                    <p>Phone: +1 (555) 123-4567</p>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; 2024 Freelance Connect. All rights reserved.</p>
            </div>
        </div>
    </footer>
</body>

</html>