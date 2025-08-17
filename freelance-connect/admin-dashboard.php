<?php
session_start();
require_once 'config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin-login.php');
    exit();
}

// Get platform statistics
try {
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total_users FROM users WHERE status = 'active'");
    $totalUsers = $stmt->fetch()['total_users'];

    // Total clients
    $stmt = $pdo->query("SELECT COUNT(*) as total_clients FROM users WHERE user_type = 'client' AND status = 'active'");
    $totalClients = $stmt->fetch()['total_clients'];

    // Total freelancers
    $stmt = $pdo->query("SELECT COUNT(*) as total_freelancers FROM users WHERE user_type = 'freelancer' AND status = 'active'");
    $totalFreelancers = $stmt->fetch()['total_freelancers'];

    // Total projects
    $stmt = $pdo->query("SELECT COUNT(*) as total_projects FROM projects");
    $totalProjects = $stmt->fetch()['total_projects'];

    // Active projects
    $stmt = $pdo->query("SELECT COUNT(*) as active_projects FROM projects WHERE status = 'open'");
    $activeProjects = $stmt->fetch()['active_projects'];

    // Total proposals
    $stmt = $pdo->query("SELECT COUNT(*) as total_proposals FROM proposals");
    $totalProposals = $stmt->fetch()['total_proposals'];

    // Pending proposals
    $stmt = $pdo->query("SELECT COUNT(*) as pending_proposals FROM proposals WHERE status = 'pending'");
    $pendingProposals = $stmt->fetch()['pending_proposals'];

    // Recent activity (last 10 users)
    $stmt = $pdo->query("SELECT username, first_name, last_name, user_type, created_at FROM users ORDER BY created_at DESC LIMIT 10");
    $recentUsers = $stmt->fetchAll();

    // Recent projects
    $stmt = $pdo->query("SELECT p.title, p.status, p.created_at, u.first_name, u.last_name FROM projects p JOIN users u ON p.client_id = u.id ORDER BY p.created_at DESC LIMIT 5");
    $recentProjects = $stmt->fetchAll();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Freelance Connect</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .admin-header h1 {
            font-size: 1.5rem;
        }

        .admin-nav {
            display: flex;
            gap: 1rem;
        }

        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background 0.3s ease;
        }

        .admin-nav a:hover {
            background: rgba(255, 255, 255, 0.2);
        }

        .admin-nav .logout {
            background: rgba(255, 255, 255, 0.1);
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .stat-card i {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #667eea;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #666;
            font-size: 0.9rem;
        }

        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .content-card {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .content-card h3 {
            margin-bottom: 1rem;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 0.5rem;
        }

        .activity-item {
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-info {
            flex: 1;
        }

        .activity-name {
            font-weight: 500;
            color: #333;
        }

        .activity-meta {
            font-size: 0.8rem;
            color: #666;
        }

        .activity-badge {
            background: #667eea;
            color: white;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.7rem;
            text-transform: uppercase;
        }

        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 2rem;
        }

        .action-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            text-decoration: none;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            transition: transform 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .action-btn:hover {
            transform: translateY(-2px);
            color: white;
        }

        @media (max-width: 768px) {
            .content-grid {
                grid-template-columns: 1fr;
            }

            .admin-nav {
                flex-direction: column;
                gap: 0.5rem;
            }
        }
    </style>
</head>

<body>
    <div class="admin-header">
        <h1><i class="fas fa-shield-alt"></i> Admin Dashboard</h1>
        <div class="admin-nav">
            <a href="admin-users.php"><i class="fas fa-users"></i> Users</a>
            <a href="admin-projects.php"><i class="fas fa-project-diagram"></i> Projects</a>
            <a href="admin-proposals.php"><i class="fas fa-file-alt"></i> Proposals</a>
            <a href="admin-settings.php"><i class="fas fa-cog"></i> Settings</a>
            <a href="admin-logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-number"><?php echo $totalUsers; ?></div>
                <div class="stat-label">Total Users</div>
            </div>

            <div class="stat-card">
                <i class="fas fa-user-tie"></i>
                <div class="stat-number"><?php echo $totalClients; ?></div>
                <div class="stat-label">Clients</div>
            </div>

            <div class="stat-card">
                <i class="fas fa-user-cog"></i>
                <div class="stat-number"><?php echo $totalFreelancers; ?></div>
                <div class="stat-label">Freelancers</div>
            </div>

            <div class="stat-card">
                <i class="fas fa-project-diagram"></i>
                <div class="stat-number"><?php echo $totalProjects; ?></div>
                <div class="stat-label">Total Projects</div>
            </div>

            <div class="stat-card">
                <i class="fas fa-folder-open"></i>
                <div class="stat-number"><?php echo $activeProjects; ?></div>
                <div class="stat-label">Active Projects</div>
            </div>

            <div class="stat-card">
                <i class="fas fa-file-alt"></i>
                <div class="stat-number"><?php echo $totalProposals; ?></div>
                <div class="stat-label">Total Proposals</div>
            </div>

            <div class="stat-card">
                <i class="fas fa-clock"></i>
                <div class="stat-number"><?php echo $pendingProposals; ?></div>
                <div class="stat-label">Pending Proposals</div>
            </div>
        </div>

        <div class="content-grid">
            <div class="content-card">
                <h3><i class="fas fa-user-plus"></i> Recent User Registrations</h3>
                <?php foreach ($recentUsers as $user): ?>
                    <div class="activity-item">
                        <div class="activity-info">
                            <div class="activity-name">
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                            <div class="activity-meta">@<?php echo htmlspecialchars($user['username']); ?> •
                                <?php echo date('M j, Y', strtotime($user['created_at'])); ?></div>
                        </div>
                        <span class="activity-badge"><?php echo ucfirst($user['user_type']); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="content-card">
                <h3><i class="fas fa-project-diagram"></i> Recent Projects</h3>
                <?php foreach ($recentProjects as $project): ?>
                    <div class="activity-item">
                        <div class="activity-info">
                            <div class="activity-name"><?php echo htmlspecialchars($project['title']); ?></div>
                            <div class="activity-meta">by
                                <?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?> •
                                <?php echo date('M j, Y', strtotime($project['created_at'])); ?></div>
                        </div>
                        <span
                            class="activity-badge"><?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?></span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="quick-actions">
            <a href="admin-users.php" class="action-btn">
                <i class="fas fa-users"></i> Manage Users
            </a>
            <a href="admin-projects.php" class="action-btn">
                <i class="fas fa-project-diagram"></i> Manage Projects
            </a>
            <a href="admin-proposals.php" class="action-btn">
                <i class="fas fa-file-alt"></i> Review Proposals
            </a>
            <a href="admin-settings.php" class="action-btn">
                <i class="fas fa-cog"></i> Admin Settings
            </a>
        </div>
    </div>
</body>

</html>