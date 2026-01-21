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
$message = '';
$error = '';

// Handle proposal deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_proposal'])) {
    $proposal_id = $_POST['proposal_id'];

    // Verify the proposal belongs to a project owned by the current user
    try {
        $stmt = $pdo->prepare("
            SELECT pr.id FROM proposals pr 
            JOIN projects p ON pr.project_id = p.id 
            WHERE pr.id = ? AND p.client_id = ?
        ");
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
                $stmt = $pdo->prepare("DELETE FROM proposals WHERE id = ?");
                $stmt->execute([$proposal_id]);

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

// Handle proposal actions (accept/reject)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $proposal_id = $_POST['proposal_id'] ?? '';
    $action = $_POST['action'] ?? '';

    if (!empty($proposal_id) && in_array($action, ['accept', 'reject'])) {
        try {
            // Update proposal status
            $stmt = $pdo->prepare("UPDATE proposals SET status = ? WHERE id = ? AND project_id IN (SELECT id FROM projects WHERE client_id = ?)");
            $status = ($action === 'accept') ? 'accepted' : 'rejected';
            $stmt->execute([$status, $proposal_id, $user_id]);

            if ($action === 'accept') {
                // Create a new deal
                $stmt = $pdo->prepare("
                    INSERT INTO deals (proposal_id, client_id, freelancer_id, status, created_at) 
                    SELECT ?, ?, freelancer_id, 'ongoing', NOW() 
                    FROM proposals WHERE id = ?
                ");
                $stmt->execute([$proposal_id, $user_id, $proposal_id]);

                $deal_id = $pdo->lastInsertId();

                // Redirect to deal page
                header("Location: deal-page-simple.php?id=" . $deal_id);
                exit();
            }

            $success_message = "Proposal " . $action . "ed successfully!";
        } catch (PDOException $e) {
            $error_message = "Failed to " . $action . " proposal. Please try again.";
        }
    }
}

// Get filter parameters
$project_filter = $_GET['project'] ?? '';

// Get client's projects for filter
try {
    $stmt = $pdo->prepare("SELECT id, title FROM projects WHERE client_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $projects = [];
}

// Build query for proposals
$where_conditions = ["p.client_id = ?"];
$params = [$user_id];

if (!empty($project_filter)) {
    $where_conditions[] = "pr.project_id = ?";
    $params[] = $project_filter;
}

$where_clause = implode(' AND ', $where_conditions);

// Get proposals
try {
    $query = "
        SELECT pr.*, 
               p.title as project_title,
               p.budget_min, p.budget_max,
               c.name as category_name,
               u.first_name, u.last_name,
               u.email as freelancer_email,
               u.skills as freelancer_skills,
               u.hourly_rate as freelancer_rate,
               (SELECT COUNT(*) FROM deals WHERE proposal_id = pr.id) as deal_count
        FROM proposals pr
        LEFT JOIN projects p ON pr.project_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON pr.freelancer_id = u.id
        WHERE $where_clause
        ORDER BY pr.created_at DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
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
    <title>View Proposals - Freelance Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/proposals.css">
    <link rel="stylesheet" href="style.css">
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

    <div class="proposals-container">
        <div class="proposals-header">
            <h1 class="proposals-title">View Proposals</h1>
            <p class="proposals-subtitle">Review and manage proposals submitted by freelancers</p>
        </div>

        <?php if ($message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (isset($success_message)): ?>
            <div class="success-message"
                style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if (isset($error_message)): ?>
            <div class="error-message"
                style="background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Project Filter -->
        <div class="project-filter">
            <form method="GET" action="">
                <div class="filter-group">
                    <label for="project">Filter by Project</label>
                    <select id="project" name="project" onchange="this.form.submit()">
                        <option value="">All Projects</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>" <?php echo $project_filter == $project['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($project['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>

        <!-- Proposals Grid -->
        <?php if (empty($proposals)): ?>
            <div class="no-proposals">
                <i class="fas fa-file-alt"></i>
                <h3>No Proposals Yet</h3>
                <p>You haven't received any proposals yet. Make sure your projects are visible and attractive to
                    freelancers.</p>
                <a href="post-project.php" class="action-btn btn-primary">Post New Project</a>
            </div>
        <?php else: ?>
            <div class="proposals-grid">
                <?php foreach ($proposals as $proposal): ?>
                    <div class="proposal-card">
                        <div class="proposal-header">
                            <div>
                                <h3 class="proposal-title"><?php echo htmlspecialchars($proposal['project_title']); ?></h3>
                                <div class="skills-tags">
                                    <span class="proposal-status status-<?php echo $proposal['status']; ?>">
                                        <?php echo ucfirst($proposal['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="project-budget">
                                $<?php echo number_format($proposal['bid_amount']); ?>
                            </div>
                        </div>

                        <div class="proposal-content" id="content-<?php echo $proposal['id']; ?>">
                            <?php echo nl2br(htmlspecialchars($proposal['cover_letter'])); ?>
                        </div>

                        <?php if (strlen($proposal['cover_letter']) > 200): ?>
                            <button class="read-more-btn" onclick="toggleContent(<?php echo $proposal['id']; ?>)">
                                Read More
                            </button>
                        <?php endif; ?>

                        <div class="proposal-details">
                            <div class="detail-item">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($proposal['first_name'] . ' ' . $proposal['last_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-envelope"></i>
                                <span><?php echo htmlspecialchars($proposal['freelancer_email']); ?></span>
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
                            <?php if (!empty($proposal['freelancer_rate'])): ?>
                                <div class="detail-item">
                                    <i class="fas fa-hourglass-half"></i>
                                    <span>Hourly Rate: $<?php echo number_format($proposal['freelancer_rate'], 2); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($proposal['freelancer_skills'])): ?>
                            <div style="margin-bottom: 1.5rem;">
                                <strong>Freelancer Skills:</strong>
                                <div class="skills-tags" style="margin-top: 0.5rem;">
                                    <?php
                                    $skills = explode(', ', $proposal['freelancer_skills']);
                                    foreach ($skills as $skill): ?>
                                        <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <div class="proposal-actions">
                            <?php if ($proposal['status'] === 'pending'): ?>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="proposal_id" value="<?php echo $proposal['id']; ?>">
                                    <input type="hidden" name="action" value="accept">
                                    <button type="submit" class="action-btn btn-accept"
                                        onclick="return confirm('Are you sure you want to accept this proposal? This will create a deal and redirect you to the chat page.')">
                                        <i class="fas fa-check"></i> Accept Proposal
                                    </button>
                                </form>

                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="proposal_id" value="<?php echo $proposal['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="action-btn btn-reject"
                                        onclick="return confirm('Are you sure you want to reject this proposal?')">
                                        <i class="fas fa-times"></i> Reject Proposal
                                    </button>
                                </form>
                            <?php elseif ($proposal['status'] === 'accepted'): ?>
                                <a href="client-project-progress.php?proposal_id=<?php echo $proposal['id']; ?>"
                                    class="action-btn btn-outline">
                                    <i class="fas fa-chart-line"></i> View Progress
                                </a>
                            <?php endif; ?>

                            <a href="view-freelancer-profile.php?id=<?php echo $proposal['freelancer_id']; ?>"
                                class="action-btn btn-secondary">
                                <i class="fas fa-user"></i> View Profile
                            </a>

                            <?php if ($proposal['deal_count'] == 0): ?>
                                <form method="POST" action="" style="display: inline;"
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

    <script>
        function toggleContent(proposalId) {
            const content = document.getElementById('content-' + proposalId);
            const button = content.nextElementSibling;

            if (content.classList.contains('expanded')) {
                content.classList.remove('expanded');
                button.textContent = 'Read More';
            } else {
                content.classList.add('expanded');
                button.textContent = 'Read Less';
            }
        }

        // Auto-submit form when project filter changes
        document.getElementById('project').addEventListener('change', function () {
            this.form.submit();
        });
    </script>
</body>

</html>