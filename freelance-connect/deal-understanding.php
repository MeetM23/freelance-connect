<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];
$deal_id = $_GET['id'] ?? '';

if (empty($deal_id)) {
    header("Location: view-proposals.php");
    exit();
}

// Get deal information and verify access
try {
    $stmt = $pdo->prepare("
        SELECT d.*, 
               p.title as project_title,
               p.description as project_description,
               p.budget_min, p.budget_max,
               p.project_type, p.deadline,
               pr.bid_amount, pr.delivery_time, pr.cover_letter,
               pr.created_at as proposal_date,
               c.first_name as client_first_name, c.last_name as client_last_name,
               c.email as client_email,
               f.first_name as freelancer_first_name, f.last_name as freelancer_last_name,
               f.email as freelancer_email, f.skills as freelancer_skills,
               f.hourly_rate as freelancer_rate, f.profile_image as freelancer_image,
               f.created_at as freelancer_joined
        FROM deals d
        LEFT JOIN proposals pr ON d.proposal_id = pr.id
        LEFT JOIN projects p ON pr.project_id = p.id
        LEFT JOIN users c ON d.client_id = c.id
        LEFT JOIN users f ON d.freelancer_id = f.id
        WHERE d.id = ? AND d.client_id = ?
    ");
    $stmt->execute([$deal_id, $user_id]);
    $deal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$deal) {
        header("Location: view-proposals.php");
        exit();
    }

    // Get freelancer's completed projects count
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as completed_projects
        FROM deals 
        WHERE freelancer_id = ? AND status = 'completed'
    ");
    $stmt->execute([$deal['freelancer_id']]);
    $freelancer_stats = $stmt->fetch();

} catch (PDOException $e) {
    header("Location: view-proposals.php");
    exit();
}

// Handle deal confirmation
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['confirm_deal'])) {
    try {
        // Update deal status to confirmed
        $stmt = $pdo->prepare("UPDATE deals SET status = 'confirmed' WHERE id = ? AND client_id = ?");
        $stmt->execute([$deal_id, $user_id]);

        // Redirect to the main deal page
        header("Location: deal-page.php?id=" . $deal_id);
        exit();
    } catch (PDOException $e) {
        $error_message = "Failed to confirm deal. Please try again.";
    }
}

// Calculate dates
$proposal_date = new DateTime($deal['proposal_date']);
$delivery_date = clone $proposal_date;
$delivery_date->add(new DateInterval('P' . $deal['delivery_time'] . 'D'));
$project_deadline = new DateTime($deal['deadline']);

// Format freelancer skills
$skills = explode(', ', $deal['freelancer_skills']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deal Understanding - <?php echo htmlspecialchars($deal['project_title']); ?> - Freelance Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .deal-understanding-container {
            max-width: 1000px;
            margin: 120px auto 2rem;
            padding: 0 2rem;
        }

        .deal-header {
            background: linear-gradient(135deg, #14a800 0%, #118f00 100%);
            color: white;
            padding: 2rem;
            border-radius: 15px;
            text-align: center;
            margin-bottom: 2rem;
        }

        .deal-header h1 {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }

        .deal-header p {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .deal-content {
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

        .project-details {
            margin-bottom: 2rem;
        }

        .project-title {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 1rem;
        }

        .project-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 1.5rem;
        }

        .project-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .meta-item {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
        }

        .meta-label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .meta-value {
            font-size: 1.1rem;
            font-weight: 600;
            color: #333;
        }

        .proposal-details {
            margin-bottom: 2rem;
        }

        .proposal-content {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
        }

        .proposal-text {
            color: #666;
            line-height: 1.6;
            font-style: italic;
        }

        .freelancer-info {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        .freelancer-header {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .freelancer-avatar {
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

        .freelancer-details h4 {
            margin: 0;
            color: #333;
            font-size: 1.1rem;
        }

        .freelancer-details p {
            margin: 0.25rem 0 0;
            color: #666;
            font-size: 0.9rem;
        }

        .freelancer-stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .stat-item {
            text-align: center;
            padding: 1rem;
            background: white;
            border-radius: 8px;
        }

        .stat-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: #14a800;
        }

        .stat-label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
        }

        .skills-section {
            margin-bottom: 1rem;
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

        .deal-summary {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }

        .deal-summary h4 {
            color: #856404;
            margin-bottom: 1rem;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 0.5rem;
            padding: 0.5rem 0;
            border-bottom: 1px solid #ffeaa7;
        }

        .summary-item:last-child {
            border-bottom: none;
            font-weight: 600;
            font-size: 1.1rem;
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

        .warning-box {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
            text-align: center;
        }

        .info-box {
            background: #d1ecf1;
            color: #0c5460;
            border: 1px solid #bee5eb;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .deal-content {
                grid-template-columns: 1fr;
            }

            .project-meta {
                grid-template-columns: 1fr;
            }

            .freelancer-stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="deal-understanding-container">
        <!-- Deal Header -->
        <div class="deal-header">
            <h1><i class="fas fa-handshake"></i> Deal Understanding</h1>
            <p>Review the deal details before proceeding with the project</p>
        </div>

        <div class="deal-content">
            <!-- Main Content -->
            <div class="main-content">
                <!-- Project Details -->
                <div class="project-details">
                    <h2 class="section-title">Project Details</h2>
                    <h3 class="project-title"><?php echo htmlspecialchars($deal['project_title']); ?></h3>
                    <div class="project-description">
                        <?php echo nl2br(htmlspecialchars($deal['project_description'])); ?>
                    </div>

                    <div class="project-meta">
                        <div class="meta-item">
                            <div class="meta-label">Project Type</div>
                            <div class="meta-value"><?php echo ucfirst($deal['project_type']); ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Original Budget</div>
                            <div class="meta-value">$<?php echo number_format($deal['budget_min']); ?> -
                                $<?php echo number_format($deal['budget_max']); ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Project Deadline</div>
                            <div class="meta-value"><?php echo $project_deadline->format('M j, Y'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Proposal Details -->
                <div class="proposal-details">
                    <h2 class="section-title">Freelancer's Proposal</h2>
                    <div class="proposal-content">
                        <div class="proposal-text">
                            "<?php echo nl2br(htmlspecialchars($deal['cover_letter'])); ?>"
                        </div>
                    </div>

                    <div class="project-meta">
                        <div class="meta-item">
                            <div class="meta-label">Proposed Budget</div>
                            <div class="meta-value">$<?php echo number_format($deal['bid_amount']); ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Delivery Time</div>
                            <div class="meta-value"><?php echo $deal['delivery_time']; ?> days</div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Expected Delivery</div>
                            <div class="meta-value"><?php echo $delivery_date->format('M j, Y'); ?></div>
                        </div>
                        <div class="meta-item">
                            <div class="meta-label">Proposal Date</div>
                            <div class="meta-value"><?php echo $proposal_date->format('M j, Y'); ?></div>
                        </div>
                    </div>
                </div>

                <!-- Freelancer Information -->
                <div class="freelancer-info">
                    <h2 class="section-title">About the Freelancer</h2>
                    <div class="freelancer-header">
                        <div class="freelancer-avatar">
                            <?php echo strtoupper(substr($deal['freelancer_first_name'], 0, 1) . substr($deal['freelancer_last_name'], 0, 1)); ?>
                        </div>
                        <div class="freelancer-details">
                            <h4><?php echo htmlspecialchars($deal['freelancer_first_name'] . ' ' . $deal['freelancer_last_name']); ?>
                            </h4>
                            <p>Member since <?php echo date('M Y', strtotime($deal['freelancer_joined'])); ?></p>
                        </div>
                    </div>

                    <div class="freelancer-stats">
                        <div class="stat-item">
                            <div class="stat-number"><?php echo $freelancer_stats['completed_projects']; ?></div>
                            <div class="stat-label">Completed Projects</div>
                        </div>
                        <?php if (!empty($deal['freelancer_rate'])): ?>
                            <div class="stat-item">
                                <div class="stat-number">$<?php echo number_format($deal['freelancer_rate'], 2); ?></div>
                                <div class="stat-label">Hourly Rate</div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($deal['freelancer_skills'])): ?>
                        <div class="skills-section">
                            <strong>Skills:</strong>
                            <div class="skills-tags">
                                <?php foreach ($skills as $skill): ?>
                                    <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="sidebar">
                <!-- Deal Summary -->
                <div class="deal-summary">
                    <h4><i class="fas fa-file-contract"></i> Deal Summary</h4>
                    <div class="summary-item">
                        <span>Project:</span>
                        <span><?php echo htmlspecialchars($deal['project_title']); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Freelancer:</span>
                        <span><?php echo htmlspecialchars($deal['freelancer_first_name'] . ' ' . $deal['freelancer_last_name']); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Agreed Amount:</span>
                        <span>$<?php echo number_format($deal['bid_amount']); ?></span>
                    </div>
                    <div class="summary-item">
                        <span>Delivery Time:</span>
                        <span><?php echo $deal['delivery_time']; ?> days</span>
                    </div>
                    <div class="summary-item">
                        <span>Expected Delivery:</span>
                        <span><?php echo $delivery_date->format('M j, Y'); ?></span>
                    </div>
                </div>

                <!-- Important Information -->
                <div class="info-box">
                    <h4><i class="fas fa-info-circle"></i> Important Information</h4>
                    <ul style="margin: 0; padding-left: 1.5rem;">
                        <li>This deal is binding once confirmed</li>
                        <li>Payment will be processed upon completion</li>
                        <li>Communication will be through the platform</li>
                        <li>You can track progress in real-time</li>
                    </ul>
                </div>

                <!-- Action Buttons -->
                <div class="action-buttons">
                    <form method="POST" action="">
                        <button type="submit" name="confirm_deal" class="btn btn-primary">
                            <i class="fas fa-check"></i> Confirm Deal & Start Project
                        </button>
                    </form>

                    <a href="view-proposals.php" class="btn btn-outline">
                        <i class="fas fa-arrow-left"></i> Back to Proposals
                    </a>

                    <a href="client-dashboard.php" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Go to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>