<?php
session_start();
require_once 'config/db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: admin-login.php');
    exit();
}

$message = '';
$error = '';

// Handle project actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && isset($_POST['project_id'])) {
        $project_id = $_POST['project_id'];
        $action = $_POST['action'];

        try {
            switch ($action) {
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM projects WHERE id = ?");
                    $stmt->execute([$project_id]);
                    $message = "Project deleted successfully.";
                    break;

                case 'disable':
                    $stmt = $pdo->prepare("UPDATE projects SET status = 'cancelled' WHERE id = ?");
                    $stmt->execute([$project_id]);
                    $message = "Project disabled successfully.";
                    break;

                case 'activate':
                    $stmt = $pdo->prepare("UPDATE projects SET status = 'open' WHERE id = ?");
                    $stmt->execute([$project_id]);
                    $message = "Project activated successfully.";
                    break;
            }
        } catch (PDOException $e) {
            $error = "Action failed: " . $e->getMessage();
        }
    }
}

// Get filter parameters
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_category = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$where_conditions = [];
$params = [];

if ($filter_status) {
    $where_conditions[] = "p.status = ?";
    $params[] = $filter_status;
}

if ($filter_category) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $filter_category;
}

if ($search) {
    $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

$where_clause = '';
if (!empty($where_conditions)) {
    $where_clause = 'WHERE ' . implode(' AND ', $where_conditions);
}

// Get projects
try {
    $query = "SELECT p.*, u.first_name, u.last_name, u.username, c.name as category_name 
              FROM projects p 
              JOIN users u ON p.client_id = u.id 
              JOIN categories c ON p.category_id = c.id 
              $where_clause 
              ORDER BY p.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $projects = $stmt->fetchAll();

    // Get categories for filter
    $catStmt = $pdo->query("SELECT id, name FROM categories ORDER BY name");
    $categories = $catStmt->fetchAll();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $projects = [];
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management - Admin Panel</title>
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

        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .filter-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
        }

        .form-group {
            display: flex;
            flex-direction: column;
        }

        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        .form-group select,
        .form-group input {
            padding: 0.5rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 0.9rem;
        }

        .filter-btn {
            background: #667eea;
            color: white;
            border: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 500;
        }

        .projects-grid {
            display: grid;
            gap: 1.5rem;
        }

        .project-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .project-header {
            padding: 1.5rem;
            border-bottom: 1px solid #dee2e6;
        }

        .project-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .project-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: 0.9rem;
            color: #666;
        }

        .project-body {
            padding: 1.5rem;
        }

        .project-description {
            color: #555;
            line-height: 1.6;
            margin-bottom: 1rem;
        }

        .project-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .detail-item {
            display: flex;
            flex-direction: column;
        }

        .detail-label {
            font-size: 0.8rem;
            color: #666;
            text-transform: uppercase;
            font-weight: 500;
        }

        .detail-value {
            font-weight: 500;
            color: #333;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-open {
            background: #d4edda;
            color: #155724;
        }

        .status-in_progress {
            background: #cce5ff;
            color: #004085;
        }

        .status-completed {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .project-actions {
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            display: flex;
            gap: 0.5rem;
            justify-content: flex-end;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.9rem;
            text-decoration: none;
            display: inline-block;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-disable {
            background: #ffc107;
            color: #212529;
        }

        .btn-activate {
            background: #28a745;
            color: white;
        }

        .message {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .no-projects {
            padding: 2rem;
            text-align: center;
            color: #666;
        }

        .budget-range {
            color: #667eea;
            font-weight: 600;
        }
    </style>
</head>

<body>
    <div class="admin-header">
        <h1><i class="fas fa-project-diagram"></i> Project Management</h1>
        <div class="admin-nav">
            <a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin-users.php"><i class="fas fa-users"></i> Users</a>
            <a href="admin-proposals.php"><i class="fas fa-file-alt"></i> Proposals</a>
            <a href="admin-settings.php"><i class="fas fa-cog"></i> Settings</a>
            <a href="admin-logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </div>

    <div class="container">
        <?php if ($message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="message error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <div class="filters">
            <form method="GET" class="filter-form">
                <div class="form-group">
                    <label for="search">Search Projects</label>
                    <input type="text" id="search" name="search"
                        placeholder="Project title, description, or client name"
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Status</option>
                        <option value="open" <?php echo $filter_status == 'open' ? 'selected' : ''; ?>>Open</option>
                        <option value="in_progress" <?php echo $filter_status == 'in_progress' ? 'selected' : ''; ?>>In
                            Progress</option>
                        <option value="completed" <?php echo $filter_status == 'completed' ? 'selected' : ''; ?>>Completed
                        </option>
                        <option value="cancelled" <?php echo $filter_status == 'cancelled' ? 'selected' : ''; ?>>Cancelled
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="category">Category</label>
                    <select id="category" name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categories as $category): ?>
                            <option value="<?php echo $category['id']; ?>" <?php echo $filter_category == $category['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="filter-btn">
                    <i class="fas fa-search"></i> Filter
                </button>
            </form>
        </div>

        <div class="projects-grid">
            <?php if (empty($projects)): ?>
                <div class="no-projects">
                    <i class="fas fa-project-diagram" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <p>No projects found matching your criteria.</p>
                </div>
            <?php else: ?>
                <?php foreach ($projects as $project): ?>
                    <div class="project-card">
                        <div class="project-header">
                            <div class="project-title"><?php echo htmlspecialchars($project['title']); ?></div>
                            <div class="project-meta">
                                <span><i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?>
                                    (@<?php echo htmlspecialchars($project['username']); ?>)</span>
                                <span><i class="fas fa-tag"></i>
                                    <?php echo htmlspecialchars($project['category_name']); ?></span>
                                <span><i class="fas fa-calendar"></i>
                                    <?php echo date('M j, Y', strtotime($project['created_at'])); ?></span>
                                <span class="status-badge status-<?php echo $project['status']; ?>">
                                    <?php echo ucfirst(str_replace('_', ' ', $project['status'])); ?>
                                </span>
                            </div>
                        </div>

                        <div class="project-body">
                            <div class="project-description">
                                <?php echo nl2br(htmlspecialchars(substr($project['description'], 0, 200))); ?>
                                <?php if (strlen($project['description']) > 200): ?>...<?php endif; ?>
                            </div>

                            <div class="project-details">
                                <div class="detail-item">
                                    <span class="detail-label">Budget Range</span>
                                    <span class="detail-value budget-range">
                                        $<?php echo number_format($project['budget_min']); ?> -
                                        $<?php echo number_format($project['budget_max']); ?>
                                    </span>
                                </div>

                                <div class="detail-item">
                                    <span class="detail-label">Project Type</span>
                                    <span class="detail-value"><?php echo ucfirst($project['project_type']); ?></span>
                                </div>

                                <div class="detail-item">
                                    <span class="detail-label">Skills Required</span>
                                    <span
                                        class="detail-value"><?php echo htmlspecialchars($project['skills_required'] ?: 'Not specified'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="project-actions">
                            <?php if ($project['status'] == 'open'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                    <input type="hidden" name="action" value="disable">
                                    <button type="submit" class="btn btn-disable" onclick="return confirm('Disable this project?')">
                                        <i class="fas fa-pause"></i> Disable
                                    </button>
                                </form>
                            <?php elseif ($project['status'] == 'cancelled'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                    <input type="hidden" name="action" value="activate">
                                    <button type="submit" class="btn btn-activate">
                                        <i class="fas fa-play"></i> Activate
                                    </button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="project_id" value="<?php echo $project['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-delete"
                                    onclick="return confirm('Delete this project? This action cannot be undone.')">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>