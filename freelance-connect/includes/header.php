<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Freelance Connect - Find the Perfect Freelance Services</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <?php if ($_SESSION['user_type'] === 'client'): ?>
                            <a href="client-dashboard.php" class="btn btn-outline">Dashboard</a>
                            <a href="client-profile.php" class="btn btn-outline">Profile</a>
                        <?php elseif ($_SESSION['user_type'] === 'freelancer'): ?>
                            <a href="freelancer-dashboard.php" class="btn btn-outline">Dashboard</a>
                            <a href="freelancer-profile.php" class="btn btn-outline">Profile</a>
                        <?php else: ?>
                            <a href="dashboard.php" class="btn btn-outline">Dashboard</a>
                        <?php endif; ?>
                        <a href="logout.php" class="btn btn-primary">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="btn btn-outline">Log In</a>
                        <a href="register.php" class="btn btn-primary">Sign Up</a>
                    <?php endif; ?>
                </div>

                <div class="nav-toggle" id="nav-toggle">
                    <span class="bar"></span>
                    <span class="bar"></span>
                    <span class="bar"></span>
                </div>
            </div>
        </nav>
    </header>