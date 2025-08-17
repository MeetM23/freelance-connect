<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in and is a freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$project_id = $_GET['id'] ?? '';

if (empty($project_id)) {
    header("Location: browse-projects.php");
    exit();
}

// Get project details
try {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               c.name as category_name,
               u.first_name, u.last_name, u.email as client_email,
               u.profile_image as client_image, u.created_at as client_joined,
               COUNT(pr.id) as proposal_count,
               AVG(pr.bid_amount) as avg_bid_amount
        FROM projects p 
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.client_id = u.id
        LEFT JOIN proposals pr ON p.id = pr.project_id
        WHERE p.id = ? AND p.status = 'open'
        AND p.id NOT IN (
            SELECT DISTINCT pr2.project_id 
            FROM proposals pr2 
            INNER JOIN deals d ON pr2.id = d.proposal_id 
            WHERE d.status IN ('ongoing', 'confirmed', 'completed')
        )
        GROUP BY p.id
    ");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        header("Location: browse-projects.php");
        exit();
    }

    // Check if freelancer already submitted a proposal
    $stmt = $pdo->prepare("SELECT id FROM proposals WHERE project_id = ? AND freelancer_id = ?");
    $stmt->execute([$project_id, $user_id]);
    $existing_proposal = $stmt->fetch();

    // Get client's other projects
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total_projects, 
               COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_projects
        FROM projects 
        WHERE client_id = ? AND id != ?
    ");
    $stmt->execute([$project['client_id'], $project_id]);
    $client_stats = $stmt->fetch();

    // Get recent proposals for this project
    $stmt = $pdo->prepare("
        SELECT pr.bid_amount, pr.delivery_time, pr.created_at,
               u.first_name, u.last_name, u.profile_image
        FROM proposals pr
        LEFT JOIN users u ON pr.freelancer_id = u.id
        WHERE pr.project_id = ?
        ORDER BY pr.created_at DESC
        LIMIT 5
    ");
    $stmt->execute([$project_id]);
    $recent_proposals = $stmt->fetchAll();

} catch (PDOException $e) {
    header("Location: browse-projects.php");
    exit();
}

// Calculate project duration
$created_date = new DateTime($project['created_at']);
$now = new DateTime();
$days_ago = $now->diff($created_date)->days;

// Format skills
$skills = explode(', ', $project['skills_required']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($project['title']); ?> - Freelance Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/projects.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .project-detail-container {
            max-width: 1200px;
            margin: 120px auto 2rem;
            padding: 0 2rem;
        }

        .project-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .project-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }

        .project-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 2rem;
            margin-bottom: 1.5rem;
            padding: 1rem;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .meta-item i {
            color: #14a800;
            font-size: 1.1rem;
        }

        .budget-range {
            font-size: 1.2rem;
            font-weight: 600;
            color: #14a800;
        }

        .project-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
        }

        .main-content {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .sidebar {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            height: fit-content;
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
            border-bottom: 2px solid #14a800;
            padding-bottom: 0.5rem;
        }

        .project-description {
            color: #666;
            line-height: 1.8;
            margin-bottom: 2rem;
        }

        .skills-section {
            margin-bottom: 2rem;
        }

        .skills-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .skill-tag {
            background: #e8f5e8;
            color: #14a800;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .client-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .client-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .client-avatar {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: 600;
        }

        .client-details h4 {
            margin: 0;
            color: #333;
            font-size: 1.1rem;
        }

        .client-details p {
            margin: 0.25rem 0 0;
            color: #666;
            font-size: 0.9rem;
        }

        .client-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 8px;
        }

        .stat-number {
            font-size: 1.5rem;
            font-weight: 700;
            color: #14a800;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
        }

        .proposal-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .proposal-info h4 {
            color: #856404;
            margin-bottom: 1rem;
        }

        .proposal-stats {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
        }

        .proposal-stat {
            text-align: center;
        }

        .proposal-stat .number {
            font-size: 1.2rem;
            font-weight: 600;
            color: #856404;
        }

        .proposal-stat .label {
            font-size: 0.8rem;
            color: #856404;
        }

        .recent-proposals {
            margin-bottom: 2rem;
        }

        .proposal-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }

        .proposal-item:last-child {
            border-bottom: none;
        }

        .proposal-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 0.9rem;
            font-weight: 600;
        }

        .proposal-details {
            flex: 1;
        }

        .proposal-name {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.25rem;
        }

        .proposal-meta {
            font-size: 0.8rem;
            color: #666;
        }

        .proposal-bid {
            font-weight: 600;
            color: #14a800;
        }

        .action-buttons {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .btn {
            padding: 1rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 1rem;
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

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-disabled {
            background: #e9ecef;
            color: #6c757d;
            cursor: not-allowed;
        }

        .btn-disabled:hover {
            transform: none;
        }

        .warning-message {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        @media (max-width: 768px) {
            .project-content {
                grid-template-columns: 1fr;
            }

            .project-meta {
                flex-direction: column;
                gap: 1rem;
            }

            .client-stats {
                grid-template-columns: 1fr;
            }

            .proposal-stats {
                flex-direction: column;
                gap: 1rem;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="project-detail-container">
        <!-- Project Header -->
        <div class="project-header">
            <h1 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h1>

            <div class="project-meta">
                <div class="meta-item">
                    <i class="fas fa-tag"></i>
                    <span><?php echo htmlspecialchars($project['category_name']); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-clock"></i>
                    <span><?php echo ucfirst($project['project_type']); ?> Project</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <span>Posted <?php echo $days_ago; ?> days ago</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-file-alt"></i>
                    <span><?php echo $project['proposal_count']; ?> proposals</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-dollar-sign"></i>
                    <span class="budget-range">$<?php echo number_format($project['budget_min']); ?> -
                        $<?php echo number_format($project['budget_max']); ?></span>
                </div>
            </div>
        </div>

        <div class="project-content">
            <!-- Main Content -->
            <div class="main-content">
                <h2 class="section-title">Project Description</h2>
                <div class="project-description">
                    <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                </div>

                <div class="skills-section">
                    <h3 class="section-title">Required Skills</h3>
                    <div class="skills-tags">
                        <?php foreach ($skills as $skill): ?>
                            <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <div class="client-info">
                    <h3 class="section-title">About the Client</h3>
                    <div class="client-header">
                        <div class="client-avatar">
                            <?php echo strtoupper(substr($project['first_name'], 0, 1) . substr($project['last_name'], 0, 1)); ?>
                        </div>
                        <div class="client-details">
                            <h4><?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?>
                            </h4>
                            <p>Member since <?php echo date('M Y', strtotime($project['client_joined'])); ?></p>
                        </div>
                    </div>
                    <div class="client-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $client_stats['total_projects']; ?></div>
                            <div class="stat-label">Total Projects</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $client_stats['completed_projects']; ?></div>
                            <div class="stat-label">Completed</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <div class="proposal-info">
                    <h4><i class="fas fa-info-circle"></i> Project Overview</h4>
                    <div class="proposal-stats">
                        <div class="proposal-stat">
                            <div class="number"><?php echo $project['proposal_count']; ?></div>
                            <div class="label">Proposals</div>
                        </div>
                        <div class="proposal-stat">
                            <div class="number">$<?php echo number_format($project['avg_bid_amount'] ?? 0); ?></div>
                            <div class="label">Avg Bid</div>
                        </div>
                        <div class="proposal-stat">
                            <div class="number"><?php echo $days_ago; ?></div>
                            <div class="label">Days Ago</div>
                        </div>
                    </div>
                </div>

                <?php if ($existing_proposal): ?>
                    <div class="warning-message">
                        <i class="fas fa-exclamation-triangle"></i>
                        You have already submitted a proposal for this project.
                    </div>
                    <div class="action-buttons">
                        <a href="my-proposals.php" class="btn btn-secondary">
                            <i class="fas fa-file-alt"></i> View My Proposal
                        </a>
                        <a href="browse-projects.php" class="btn btn-outline">
                            <i class="fas fa-search"></i> Browse More Projects
                        </a>
                    </div>
                <?php else: ?>
                    <div class="action-buttons">
                        <a href="submit-proposal.php?project_id=<?php echo $project_id; ?>" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Proposal
                        </a>
                        <a href="browse-projects.php" class="btn btn-outline">
                            <i class="fas fa-search"></i> Browse More Projects
                        </a>
                    </div>
                <?php endif; ?>

                <?php if (!empty($recent_proposals)): ?>
                    <div class="recent-proposals">
                        <h3 class="section-title">Recent Proposals</h3>
                        <?php foreach ($recent_proposals as $proposal): ?>
                            <div class="proposal-item">
                                <div class="proposal-avatar">
                                    <?php echo strtoupper(substr($proposal['first_name'], 0, 1) . substr($proposal['last_name'], 0, 1)); ?>
                                </div>
                                <div class="proposal-details">
                                    <div class="proposal-name">
                                        <?php echo htmlspecialchars($proposal['first_name'] . ' ' . $proposal['last_name']); ?>
                                    </div>
                                    <div class="proposal-meta">
                                        <?php echo $proposal['delivery_time']; ?> days delivery
                                    </div>
                                </div>
                                <div class="proposal-bid">
                                    $<?php echo number_format($proposal['bid_amount']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>