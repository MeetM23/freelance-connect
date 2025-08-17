<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Freelance Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .about-page {
            margin-top: 100px;
        }

        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4rem 0;
            text-align: center;
        }

        .hero-content {
            max-width: 800px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .hero-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }

        .hero-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .content-section {
            padding: 4rem 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
        }

        .section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #333;
            text-align: center;
            margin-bottom: 1rem;
        }

        .section-subtitle {
            font-size: 1.1rem;
            color: #666;
            text-align: center;
            max-width: 600px;
            margin: 0 auto 3rem;
        }

        .mission-vision {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin-bottom: 4rem;
        }

        .mission-card,
        .vision-card {
            background: white;
            padding: 2.5rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .card-icon {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #14a800 0%, #118f00 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
        }

        .card-icon i {
            font-size: 2rem;
            color: white;
        }

        .card-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        .card-description {
            color: #666;
            line-height: 1.6;
        }

        .stats-section {
            background: #f8f9fa;
            padding: 4rem 0;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 2rem;
        }

        .stat-card {
            text-align: center;
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .stat-number {
            font-size: 2.5rem;
            font-weight: 700;
            color: #14a800;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 1rem;
        }

        .values-section {
            padding: 4rem 0;
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
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #14a800;
        }

        .value-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        .value-description {
            color: #666;
            line-height: 1.6;
        }

        .team-section {
            background: #f8f9fa;
            padding: 4rem 0;
        }

        .team-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
        }

        .team-card {
            background: white;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .team-avatar {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
        }

        .team-avatar i {
            font-size: 2.5rem;
            color: white;
        }

        .team-name {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .team-role {
            color: #14a800;
            font-weight: 500;
            margin-bottom: 1rem;
        }

        .team-bio {
            color: #666;
            line-height: 1.6;
            font-size: 0.9rem;
        }

        .cta-section {
            padding: 4rem 0;
            text-align: center;
        }

        .cta-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }

        .cta-subtitle {
            font-size: 1.1rem;
            color: #666;
            margin-bottom: 2rem;
        }

        .cta-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
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

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2rem;
            }

            .mission-vision {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }

            .values-grid {
                grid-template-columns: 1fr;
            }

            .team-grid {
                grid-template-columns: 1fr;
            }

            .cta-buttons {
                flex-direction: column;
                align-items: center;
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

    <div class="about-page">
        <!-- Hero Section -->
        <section class="hero-section">
            <div class="hero-content">
                <h1 class="hero-title">About Freelance Connect</h1>
                <p class="hero-subtitle">
                    We're revolutionizing the way freelancers and clients connect, collaborate, and create amazing
                    projects together.
                    Our platform bridges the gap between talent and opportunity, making freelance work accessible,
                    secure, and rewarding.
                </p>
            </div>
        </section>

        <!-- Mission & Vision -->
        <section class="content-section">
            <div class="container">
                <h2 class="section-title">Our Mission & Vision</h2>
                <p class="section-subtitle">
                    We believe in empowering individuals to work on their own terms while helping businesses find the
                    perfect talent for their projects.
                </p>

                <div class="mission-vision">
                    <div class="mission-card">
                        <div class="card-icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                        <h3 class="card-title">Our Mission</h3>
                        <p class="card-description">
                            To create the world's most trusted and efficient freelance marketplace, where talented
                            professionals can showcase their skills
                            and businesses can find the perfect match for their projects. We're committed to building a
                            community that values quality,
                            transparency, and mutual success.
                        </p>
                    </div>

                    <div class="vision-card">
                        <div class="card-icon">
                            <i class="fas fa-eye"></i>
                        </div>
                        <h3 class="card-title">Our Vision</h3>
                        <p class="card-description">
                            To become the leading platform that transforms how work gets done globally. We envision a
                            future where geographical boundaries
                            don't limit opportunities, and where every skilled professional can find meaningful work
                            while every business can access
                            world-class talent.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Statistics -->
        <section class="stats-section">
            <div class="container">
                <h2 class="section-title">Platform Impact</h2>
                <p class="section-subtitle">
                    See how Freelance Connect is making a difference in the freelance economy
                </p>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-number">2,500+</div>
                        <div class="stat-label">Active Users</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">1,200+</div>
                        <div class="stat-label">Projects Posted</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">850+</div>
                        <div class="stat-label">Projects Completed</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-number">$2.5M+</div>
                        <div class="stat-label">Paid to Freelancers</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Our Values -->
        <section class="values-section">
            <div class="container">
                <h2 class="section-title">Our Core Values</h2>
                <p class="section-subtitle">
                    These principles guide everything we do and shape our community
                </p>

                <div class="values-grid">
                    <div class="value-card">
                        <h3 class="value-title">Quality First</h3>
                        <p class="value-description">
                            We maintain high standards for both freelancers and projects. Every member of our community
                            is committed to delivering
                            exceptional work and providing outstanding service.
                        </p>
                    </div>

                    <div class="value-card">
                        <h3 class="value-title">Trust & Transparency</h3>
                        <p class="value-description">
                            Building trust is at the heart of our platform. We provide transparent communication, secure
                            payment systems,
                            and clear project expectations to ensure successful collaborations.
                        </p>
                    </div>

                    <div class="value-card">
                        <h3 class="value-title">Innovation</h3>
                        <p class="value-description">
                            We continuously innovate our platform to provide the best tools and features for freelancers
                            and clients.
                            From advanced search to real-time communication, we're always improving.
                        </p>
                    </div>

                    <div class="value-card">
                        <h3 class="value-title">Community</h3>
                        <p class="value-description">
                            We believe in the power of community. Our platform fosters connections, knowledge sharing,
                            and mutual support
                            among freelancers and clients worldwide.
                        </p>
                    </div>

                    <div class="value-card">
                        <h3 class="value-title">Accessibility</h3>
                        <p class="value-description">
                            We make freelance opportunities accessible to everyone, regardless of location or
                            background.
                            Our platform breaks down barriers and creates equal opportunities for all.
                        </p>
                    </div>

                    <div class="value-card">
                        <h3 class="value-title">Growth</h3>
                        <p class="value-description">
                            We support the growth of both freelancers and businesses. Through skill development,
                            networking opportunities,
                            and project success, we help our community thrive and expand.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Team Section -->
        <section class="team-section">
            <div class="container">
                <h2 class="section-title">Meet Our Team</h2>
                <p class="section-subtitle">
                    The passionate individuals behind Freelance Connect
                </p>

                <div class="team-grid">
                    <div class="team-card">
                        <div class="team-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3 class="team-name">Sarah Johnson</h3>
                        <p class="team-role">CEO & Founder</p>
                        <p class="team-bio">
                            A former freelancer herself, Sarah founded Freelance Connect with a vision to create better
                            opportunities
                            for independent professionals worldwide.
                        </p>
                    </div>

                    <div class="team-card">
                        <div class="team-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3 class="team-name">Michael Chen</h3>
                        <p class="team-role">CTO</p>
                        <p class="team-bio">
                            Leading our technical team, Michael ensures our platform remains cutting-edge and provides
                            the best user experience possible.
                        </p>
                    </div>

                    <div class="team-card">
                        <div class="team-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3 class="team-name">Emily Rodriguez</h3>
                        <p class="team-role">Head of Community</p>
                        <p class="team-bio">
                            Emily works tirelessly to build and nurture our community, ensuring every member feels
                            supported and valued.
                        </p>
                    </div>

                    <div class="team-card">
                        <div class="team-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                        <h3 class="team-name">David Kim</h3>
                        <p class="team-role">Head of Operations</p>
                        <p class="team-bio">
                            David oversees our day-to-day operations, ensuring smooth project delivery and
                            customer satisfaction across the platform.
                        </p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section class="cta-section">
            <div class="container">
                <h2 class="cta-title">Join Our Community Today</h2>
                <p class="cta-subtitle">
                    Whether you're a talented freelancer looking for opportunities or a business seeking skilled
                    professionals,
                    Freelance Connect is here to help you succeed.
                </p>
                <div class="cta-buttons">
                    <a href="register.php" class="btn btn-primary">
                        <i class="fas fa-user-plus"></i> Get Started as Freelancer
                    </a>
                    <a href="register.php" class="btn btn-outline">
                        <i class="fas fa-briefcase"></i> Hire Talent
                    </a>
                </div>
            </div>
        </section>
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