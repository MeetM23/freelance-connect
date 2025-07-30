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

// Get user's projects
try {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               COUNT(pr.id) as proposal_count,
               c.name as category_name
        FROM projects p 
        LEFT JOIN proposals pr ON p.id = pr.project_id
        LEFT JOIN categories c ON p.category_id = c.id
        WHERE p.client_id = ? 
        GROUP BY p.id 
        ORDER BY p.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $projects = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Projects - Freelance Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .projects-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
            margin-top: 100px;
        }

        .projects-header {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .projects-title {
            font-size: 1.8rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .projects-subtitle {
            color: #666;
        }

        .post-project-btn {
            background: #14a800;
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: background 0.3s ease;
        }

        .post-project-btn:hover {
            background: #118f00;
        }

        .projects-grid {
            display: grid;
            gap: 1.5rem;
        }

        .project-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            border-left: 4px solid #14a800;
        }

        .project-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1rem;
        }

        .project-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .project-status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-open {
            background: #d4edda;
            color: #155724;
        }

        .status-in-progress {
            background: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .project-description {
            color: #666;
            margin-bottom: 1rem;
            line-height: 1.6;
        }

        .project-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666;
            font-size: 0.9rem;
        }

        .detail-item i {
            color: #14a800;
            width: 16px;
        }

        .project-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 0.5rem 1rem;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.9rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #14a800;
            color: white;
        }

        .btn-primary:hover {
            background: #118f00;
        }

        .btn-outline {
            border: 2px solid #14a800;
            color: #14a800;
            background: transparent;
        }

        .btn-outline:hover {
            background: #14a800;
            color: white;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .no-projects {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .no-projects i {
            font-size: 3rem;
            color: #ccc;
            margin-bottom: 1rem;
        }

        .no-projects h3 {
            color: #666;
            margin-bottom: 1rem;
        }

        .no-projects p {
            color: #999;
            margin-bottom: 2rem;
        }

        .skills-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }

        .skill-tag {
            background: #f8f9fa;
            color: #666;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            border: 1px solid #e9ecef;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="projects-container">
        <div class="projects-header">
            <div>
                <h1 class="projects-title">My Projects</h1>
                <p class="projects-subtitle">Manage your posted projects and view proposals</p>
            </div>
            <a href="post-project.php" class="post-project-btn">
                <i class="fas fa-plus"></i> Post New Project
            </a>
        </div>

        <?php if (empty($projects)): ?>
            <div class="no-projects">
                <i class="fas fa-folder-open"></i>
                <h3>No Projects Yet</h3>
                <p>You haven't posted any projects yet. Start by creating your first project to find talented freelancers.
                </p>
                <a href="post-project.php" class="action-btn btn-primary">
                    <i class="fas fa-plus"></i> Post Your First Project
                </a>
            </div>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                    <div class="project-card">
                        <div class="project-header">
                            <div>
                                <h3 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                                <div class="skills-tags">
                                    <?php
                                    $skills = explode(', ', $project['skills_required']);
                                    foreach (array_slice($skills, 0, 3) as $skill): ?>
                                        <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($skills) > 3): ?>
                                        <span class="skill-tag">+<?php echo count($skills) - 3; ?> more</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <span class="project-status status-<?php echo $project['status']; ?>">
                                <?php echo ucfirst($project['status']); ?>
                            </span>
                        </div>

                        <p class="project-description">
                            <?php echo htmlspecialchars(substr($project['description'], 0, 150)) . (strlen($project['description']) > 150 ? '...' : ''); ?>
                        </p>

                        <div class="project-details">
                            <div class="detail-item">
                                <i class="fas fa-dollar-sign"></i>
                                <span>$<?php echo number_format($project['budget_min']); ?> -
                                    $<?php echo number_format($project['budget_max']); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-calendar"></i>
                                <span>Posted <?php echo date('M j, Y', strtotime($project['created_at'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-users"></i>
                                <span><?php echo $project['proposal_count']; ?> proposals</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-tag"></i>
                                <span><?php echo ucfirst(str_replace('-', ' ', $project['project_type'] ?? 'N/A')); ?></span>
                            </div>
                        </div>

                        <div class="project-actions">
                            <a href="view-project.php?id=<?php echo $project['id']; ?>" class="action-btn btn-primary">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                            <a href="view-proposals.php?project_id=<?php echo $project['id']; ?>"
                                class="action-btn btn-outline">
                                <i class="fas fa-file-alt"></i> View Proposals (<?php echo $project['proposal_count']; ?>)
                            </a>
                            <?php if ($project['status'] === 'open'): ?>
                                <a href="edit-project.php?id=<?php echo $project['id']; ?>" class="action-btn btn-secondary">
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