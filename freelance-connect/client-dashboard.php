<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header("Location: login.php");
    exit();
}

// Get user data
$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get client statistics
try {
    // Active projects (deals with status 'ongoing')
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as active_projects 
        FROM deals 
        WHERE client_id = ? AND status = 'ongoing'
    ");
    $stmt->execute([$user_id]);
    $activeProjects = $stmt->fetch()['active_projects'];

    // Completed projects (deals with status 'completed')
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as completed_projects 
        FROM deals 
        WHERE client_id = ? AND status = 'completed'
    ");
    $stmt->execute([$user_id]);
    $completedProjects = $stmt->fetch()['completed_projects'];

    // Total spent (sum of bid amounts from completed deals)
    $stmt = $pdo->prepare("
        SELECT COALESCE(SUM(d.bid_amount), 0) as total_spent 
        FROM deals d
        WHERE d.client_id = ? AND d.status = 'completed'
    ");
    $stmt->execute([$user_id]);
    $totalSpent = $stmt->fetch()['total_spent'];

    // Hired freelancers (count of unique freelancers from deals)
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT freelancer_id) as hired_freelancers 
        FROM deals 
        WHERE client_id = ? AND status IN ('ongoing', 'completed')
    ");
    $stmt->execute([$user_id]);
    $hiredFreelancers = $stmt->fetch()['hired_freelancers'];

} catch (PDOException $e) {
    // Set default values if there's an error
    $activeProjects = 0;
    $completedProjects = 0;
    $totalSpent = 0;
    $hiredFreelancers = 0;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Client Dashboard - Freelance Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .dashboard-header {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .welcome-message {
            font-size: 1.5rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .stat-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
            color: #667eea;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #14a800;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .logout-btn {
            background: #dc3545;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            text-decoration: none;
            font-size: 0.9rem;
            transition: background 0.3s ease;
        }

        .logout-btn:hover {
            background: #c82333;
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="dashboard-container" style="margin-top: 100px;">
        <div class="dashboard-header">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1 class="welcome-message">Welcome back, <?php echo htmlspecialchars($user_name); ?>!</h1>
                    <p style="color: #666;">Client Dashboard</p>
                </div>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>

        <div class="dashboard-stats">
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-tasks"></i>
                </div>
                <div class="stat-number"><?php echo $activeProjects; ?></div>
                <div class="stat-label">Active Projects</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-number"><?php echo $completedProjects; ?></div>
                <div class="stat-label">Completed Projects</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-dollar-sign"></i>
                </div>
                <div class="stat-number">$<?php echo number_format($totalSpent); ?></div>
                <div class="stat-label">Total Spent</div>
            </div>
            <div class="stat-card">
                <div class="stat-icon">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo $hiredFreelancers; ?></div>
                <div class="stat-label">Hired Freelancers</div>
            </div>
        </div>

        <div style="background: white; padding: 2rem; border-radius: 12px; box-shadow: 0 5px 15px rgba(0,0,0,0.1);">
            <h2 style="margin-bottom: 1rem; color: #333;">Quick Actions</h2>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="post-project.php" class="btn btn-primary" style="display: inline-block;">
                    <i class="fas fa-plus"></i> Post New Project
                </a>
                <a href="my-projects.php" class="btn btn-outline" style="display: inline-block;">
                    <i class="fas fa-folder"></i> View My Projects
                </a>
                <a href="view-proposals.php" class="btn btn-outline" style="display: inline-block;">
                    <i class="fas fa-file-alt"></i> View Proposals
                </a>
           
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>