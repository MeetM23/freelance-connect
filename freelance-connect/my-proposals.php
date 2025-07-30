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

// Get user's proposals
try {
    $stmt = $pdo->prepare("
        SELECT pr.*, 
               p.title as project_title,
               p.budget_min, p.budget_max,
               c.name as category_name,
               u.first_name, u.last_name
        FROM proposals pr
        LEFT JOIN projects p ON pr.project_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.client_id = u.id
        WHERE pr.freelancer_id = ?
        ORDER BY pr.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $proposals = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $proposals = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Proposals - Freelance Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/projects.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="projects-container">
        <div class="projects-header">
            <h1 class="projects-title">My Proposals</h1>
            <p class="projects-subtitle">Track the status of your submitted proposals</p>
        </div>

        <?php if (empty($proposals)): ?>
            <div class="no-projects">
                <i class="fas fa-file-alt"></i>
                <h3>No Proposals Yet</h3>
                <p>You haven't submitted any proposals yet. Start browsing projects to find opportunities!</p>
                <a href="browse-projects.php" class="action-btn btn-primary">Browse Projects</a>
            </div>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($proposals as $proposal): ?>
                    <div class="project-card">
                        <div class="project-header">
                            <div>
                                <h3 class="project-title"><?php echo htmlspecialchars($proposal['project_title']); ?></h3>
                                <div class="skills-tags">
                                    <span class="skill-tag" style="background: <?php
                                    echo $proposal['status'] === 'accepted' ? '#d4edda' :
                                        ($proposal['status'] === 'rejected' ? '#f8d7da' : '#fff3cd');
                                    ?>; color: <?php
                                    echo $proposal['status'] === 'accepted' ? '#155724' :
                                        ($proposal['status'] === 'rejected' ? '#721c24' : '#856404');
                                    ?>;">
                                        <?php echo ucfirst($proposal['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="project-budget">
                                $<?php echo number_format($proposal['bid_amount']); ?>
                            </div>
                        </div>

                        <div class="project-details">
                            <div class="detail-item">
                                <i class="fas fa-user"></i>
                                <span>Client:
                                    <?php echo htmlspecialchars($proposal['first_name'] . ' ' . $proposal['last_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-tag"></i>
                                <span><?php echo htmlspecialchars($proposal['category_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-calendar"></i>
                                <span>Submitted <?php echo date('M j, Y', strtotime($proposal['created_at'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo $proposal['delivery_time']; ?> days delivery</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-dollar-sign"></i>
                                <span>Project Budget: $<?php echo number_format($proposal['budget_min']); ?> -
                                    $<?php echo number_format($proposal['budget_max']); ?></span>
                            </div>
                        </div>

                        <div class="project-actions">
                            <a href="view-proposal.php?id=<?php echo $proposal['id']; ?>" class="action-btn btn-outline">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <?php if ($proposal['status'] === 'pending'): ?>
                                <a href="edit-proposal.php?id=<?php echo $proposal['id']; ?>" class="action-btn btn-outline">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>