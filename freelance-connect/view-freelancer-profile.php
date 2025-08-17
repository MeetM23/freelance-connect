<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header("Location: login.php");
    exit();
}

// Get freelancer ID from URL
$freelancer_id = $_GET['id'] ?? '';

if (empty($freelancer_id)) {
    header("Location: view-proposals.php");
    exit();
}

// Get freelancer details
try {
    $stmt = $pdo->prepare("
        SELECT id, username, email, first_name, last_name, bio, skills, hourly_rate, location, profile_image, created_at
        FROM users 
        WHERE id = ? AND user_type = 'freelancer'
    ");
    $stmt->execute([$freelancer_id]);
    $freelancer = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$freelancer) {
        header("Location: view-proposals.php");
        exit();
    }
} catch (PDOException $e) {
    header("Location: view-proposals.php");
    exit();
}

// Get freelancer's completed projects count
try {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as completed_count
        FROM proposals p
        JOIN projects pr ON p.project_id = pr.id
        WHERE p.freelancer_id = ? AND p.status = 'accepted'
    ");
    $stmt->execute([$freelancer_id]);
    $completedProjects = $stmt->fetch(PDO::FETCH_ASSOC)['completed_count'];
} catch (PDOException $e) {
    $completedProjects = 0;
}

// Get freelancer's recent proposals
try {
    $stmt = $pdo->prepare("
        SELECT p.title, pr.status, pr.created_at, pr.bid_amount
        FROM proposals pr
        JOIN projects p ON pr.project_id = p.id
        WHERE pr.freelancer_id = ?
        ORDER BY pr.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$freelancer_id]);
    $recentProposals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $recentProposals = [];
}

// Get freelancer's skills as array
$skills = !empty($freelancer['skills']) ? explode(', ', $freelancer['skills']) : [];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($freelancer['first_name'] . ' ' . $freelancer['last_name']); ?> - Freelancer
        Profile</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .profile-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            gap: 2rem;
        }

        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: bold;
            flex-shrink: 0;
        }

        .profile-info h1 {
            margin: 0 0 0.5rem 0;
            color: #333;
            font-size: 2rem;
        }

        .profile-meta {
            color: #666;
            margin-bottom: 1rem;
        }

        .profile-meta span {
            margin-right: 1rem;
        }

        .profile-stats {
            display: flex;
            gap: 2rem;
            margin-top: 1rem;
        }

        .stat-item {
            text-align: center;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: bold;
            color: #667eea;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #666;
        }

        .profile-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .main-content {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .sidebar {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }

        .section-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .bio-text {
            line-height: 1.8;
            color: #555;
            margin-bottom: 2rem;
        }

        .skills-section {
            margin-bottom: 2rem;
        }

        .skills-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .skill-tag {
            background: #f0f4ff;
            color: #667eea;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .contact-info {
            margin-bottom: 2rem;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
            color: #555;
        }

        .contact-item i {
            color: #667eea;
            width: 20px;
        }

        .hourly-rate {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem;
            border-radius: 10px;
            text-align: center;
            margin-bottom: 2rem;
        }

        .rate-amount {
            font-size: 2rem;
            font-weight: bold;
        }

        .rate-label {
            font-size: 0.9rem;
            opacity: 0.9;
        }

        .recent-proposals {
            margin-bottom: 2rem;
        }

        .proposal-item {
            padding: 1rem;
            border: 1px solid #eee;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .proposal-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .proposal-meta {
            font-size: 0.9rem;
            color: #666;
        }

        .proposal-status {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
            margin-left: 0.5rem;
        }

        .status-accepted {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: 500;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            transition: transform 0.2s ease;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .back-link {
            margin-bottom: 2rem;
        }

        .back-link a {
            color: #667eea;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            font-weight: 500;
        }

        .back-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .profile-header {
                flex-direction: column;
                text-align: center;
            }

            .profile-content {
                grid-template-columns: 1fr;
            }

            .profile-stats {
                justify-content: center;
            }

            .action-buttons {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="profile-container">
        <div class="back-link">
            <a href="view-proposals.php">
                <i class="fas fa-arrow-left"></i> Back to Proposals
            </a>
        </div>

        <div class="profile-header">
            <div class="profile-avatar">
                <?php echo strtoupper(substr($freelancer['first_name'], 0, 1) . substr($freelancer['last_name'], 0, 1)); ?>
            </div>

            <div class="profile-info">
                <h1><?php echo htmlspecialchars($freelancer['first_name'] . ' ' . $freelancer['last_name']); ?></h1>
                <div class="profile-meta">
                    <span><i class="fas fa-user"></i> @<?php echo htmlspecialchars($freelancer['username']); ?></span>
                    <?php if (!empty($freelancer['location'])): ?>
                        <span><i class="fas fa-map-marker-alt"></i>
                            <?php echo htmlspecialchars($freelancer['location']); ?></span>
                    <?php endif; ?>
                    <span><i class="fas fa-calendar"></i> Member since
                        <?php echo date('M Y', strtotime($freelancer['created_at'])); ?></span>
                </div>

                <div class="profile-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?php echo $completedProjects; ?></div>
                        <div class="stat-label">Completed Projects</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?php echo count($recentProposals); ?></div>
                        <div class="stat-label">Recent Proposals</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="profile-content">
            <div class="main-content">
                <?php if (!empty($freelancer['bio'])): ?>
                    <div class="section-title">
                        <i class="fas fa-user-circle"></i> About
                    </div>
                    <div class="bio-text">
                        <?php echo nl2br(htmlspecialchars($freelancer['bio'])); ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($skills)): ?>
                    <div class="skills-section">
                        <div class="section-title">
                            <i class="fas fa-tools"></i> Skills & Expertise
                        </div>
                        <div class="skills-grid">
                            <?php foreach ($skills as $skill): ?>
                                <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (!empty($recentProposals)): ?>
                    <div class="recent-proposals">
                        <div class="section-title">
                            <i class="fas fa-file-alt"></i> Recent Proposals
                        </div>
                        <?php foreach ($recentProposals as $proposal): ?>
                            <div class="proposal-item">
                                <div class="proposal-title">
                                    <?php echo htmlspecialchars($proposal['title']); ?>
                                    <span class="proposal-status status-<?php echo $proposal['status']; ?>">
                                        <?php echo ucfirst($proposal['status']); ?>
                                    </span>
                                </div>
                                <div class="proposal-meta">
                                    <span><i class="fas fa-dollar-sign"></i>
                                        $<?php echo number_format($proposal['bid_amount']); ?></span>
                                    <span><i class="fas fa-calendar"></i>
                                        <?php echo date('M j, Y', strtotime($proposal['created_at'])); ?></span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="sidebar">
                <?php if (!empty($freelancer['hourly_rate'])): ?>
                    <div class="hourly-rate">
                        <div class="rate-amount">$<?php echo number_format($freelancer['hourly_rate'], 2); ?></div>
                        <div class="rate-label">per hour</div>
                    </div>
                <?php endif; ?>

                <div class="contact-info">
                    <div class="section-title">
                        <i class="fas fa-envelope"></i> Contact Information
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <span><?php echo htmlspecialchars($freelancer['email']); ?></span>
                    </div>
                    <?php if (!empty($freelancer['location'])): ?>
                        <div class="contact-item">
                            <i class="fas fa-map-marker-alt"></i>
                            <span><?php echo htmlspecialchars($freelancer['location']); ?></span>
                        </div>
                    <?php endif; ?>
                    <div class="contact-item">
                        <i class="fas fa-calendar"></i>
                        <span>Joined <?php echo date('M j, Y', strtotime($freelancer['created_at'])); ?></span>
                    </div>
                </div>

                <div class="action-buttons">
             
                    <a href="view-proposals.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Proposals
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>