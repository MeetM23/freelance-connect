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
$message = '';
$error = '';

// Handle proposal deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_proposal'])) {
    $proposal_id = $_POST['proposal_id'];

    // Verify the proposal belongs to the current user
    try {
        $stmt = $pdo->prepare("SELECT id FROM proposals WHERE id = ? AND freelancer_id = ?");
        $stmt->execute([$proposal_id, $user_id]);

        if ($stmt->rowCount() > 0) {
            // Check if proposal has been accepted (has a deal)
            $stmt = $pdo->prepare("SELECT COUNT(*) as deal_count FROM deals WHERE proposal_id = ?");
            $stmt->execute([$proposal_id]);
            $deal_count = $stmt->fetch()['deal_count'];

            if ($deal_count > 0) {
                $error = "Cannot delete proposal. It has been accepted and is part of an active deal.";
            } else {
                // Delete the proposal
                $stmt = $pdo->prepare("DELETE FROM proposals WHERE id = ? AND freelancer_id = ?");
                $stmt->execute([$proposal_id, $user_id]);

                if ($stmt->rowCount() > 0) {
                    $message = "Proposal deleted successfully!";
                } else {
                    $error = "Failed to delete proposal.";
                }
            }
        } else {
            $error = "Proposal not found or you don't have permission to delete it.";
        }
    } catch (PDOException $e) {
        $error = "Error deleting proposal: " . $e->getMessage();
    }
}

// Get user's proposals
try {
    $stmt = $pdo->prepare("
        SELECT pr.*, 
               p.title as project_title,
               p.budget_min, p.budget_max,
               c.name as category_name,
               u.first_name, u.last_name,
               (SELECT COUNT(*) FROM deals WHERE proposal_id = pr.id) as deal_count
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
    <style>
        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
            border: none;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .delete-confirmation {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .delete-confirmation i {
            color: #f39c12;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="projects-container">
        <div class="projects-header">
            <h1 class="projects-title">My Proposals</h1>
            <p class="projects-subtitle">Track the status of your submitted proposals</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

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
                            <a href="freelancer-project-details.php?id=<?php echo $proposal['id']; ?>"
                                class="action-btn btn-outline">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                 
                            <?php if ($proposal['deal_count'] == 0): ?>
                                <form method="POST" style="display: inline;"
                                    onsubmit="return confirm('Are you sure you want to delete this proposal? This action cannot be undone.');">
                                    <input type="hidden" name="proposal_id" value="<?php echo $proposal['id']; ?>">
                                    <button type="submit" name="delete_proposal" class="action-btn btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            <?php else: ?>
                                <div class="delete-confirmation">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <span>Cannot delete - proposal accepted</span>
                                </div>
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