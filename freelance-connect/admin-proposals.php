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

// Handle proposal actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && isset($_POST['proposal_id'])) {
        $proposal_id = $_POST['proposal_id'];
        $action = $_POST['action'];

        try {
            switch ($action) {
                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM proposals WHERE id = ?");
                    $stmt->execute([$proposal_id]);
                    $message = "Proposal deleted successfully.";
                    break;

                case 'reject':
                    $stmt = $pdo->prepare("UPDATE proposals SET status = 'rejected' WHERE id = ?");
                    $stmt->execute([$proposal_id]);
                    $message = "Proposal rejected successfully.";
                    break;

                case 'approve':
                    $stmt = $pdo->prepare("UPDATE proposals SET status = 'accepted' WHERE id = ?");
                    $stmt->execute([$proposal_id]);
                    $message = "Proposal approved successfully.";
                    break;
            }
        } catch (PDOException $e) {
            $error = "Action failed: " . $e->getMessage();
        }
    }
}

// Get filter parameters
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$filter_project = isset($_GET['project']) ? $_GET['project'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$where_conditions = [];
$params = [];

if ($filter_status) {
    $where_conditions[] = "pr.status = ?";
    $params[] = $filter_status;
}

if ($filter_project) {
    $where_conditions[] = "pr.project_id = ?";
    $params[] = $filter_project;
}

if ($search) {
    $where_conditions[] = "(p.title LIKE ? OR f.first_name LIKE ? OR f.last_name LIKE ? OR pr.cover_letter LIKE ?)";
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

// Get proposals
try {
    $query = "SELECT pr.*, p.title as project_title, p.budget_min, p.budget_max, 
                     f.first_name as freelancer_first, f.last_name as freelancer_last, f.username as freelancer_username,
                     c.first_name as client_first, c.last_name as client_last, c.username as client_username
              FROM proposals pr 
              JOIN projects p ON pr.project_id = p.id 
              JOIN users f ON pr.freelancer_id = f.id 
              JOIN users c ON p.client_id = c.id 
              $where_clause 
              ORDER BY pr.created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $proposals = $stmt->fetchAll();

    // Get projects for filter
    $projStmt = $pdo->query("SELECT id, title FROM projects ORDER BY title");
    $projects = $projStmt->fetchAll();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $proposals = [];
    $projects = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposal Management - Admin Panel</title>
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

        .proposals-grid {
            display: grid;
            gap: 1.5rem;
        }

        .proposal-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .proposal-header {
            padding: 1.5rem;
            border-bottom: 1px solid #dee2e6;
        }

        .proposal-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .proposal-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: 0.9rem;
            color: #666;
        }

        .proposal-body {
            padding: 1.5rem;
        }

        .proposal-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .proposal-details {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.5rem 0;
            border-bottom: 1px solid #eee;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 500;
            color: #333;
        }

        .detail-value {
            color: #666;
        }

        .cover-letter {
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 5px;
            border-left: 4px solid #667eea;
        }

        .cover-letter h4 {
            margin-bottom: 0.5rem;
            color: #333;
        }

        .cover-letter p {
            color: #555;
            line-height: 1.6;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-accepted {
            background: #d4edda;
            color: #155724;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .proposal-actions {
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

        .btn-reject {
            background: #ffc107;
            color: #212529;
        }

        .btn-approve {
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

        .no-proposals {
            padding: 2rem;
            text-align: center;
            color: #666;
        }

        .bid-amount {
            color: #28a745;
            font-weight: 600;
            font-size: 1.1rem;
        }

        @media (max-width: 768px) {
            .proposal-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="admin-header">
        <h1><i class="fas fa-file-alt"></i> Proposal Management</h1>
        <div class="admin-nav">
            <a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin-users.php"><i class="fas fa-users"></i> Users</a>
            <a href="admin-projects.php"><i class="fas fa-project-diagram"></i> Projects</a>
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
                    <label for="search">Search Proposals</label>
                    <input type="text" id="search" name="search"
                        placeholder="Project title, freelancer name, or cover letter"
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Status</option>
                        <option value="pending" <?php echo $filter_status == 'pending' ? 'selected' : ''; ?>>Pending
                        </option>
                        <option value="accepted" <?php echo $filter_status == 'accepted' ? 'selected' : ''; ?>>Accepted
                        </option>
                        <option value="rejected" <?php echo $filter_status == 'rejected' ? 'selected' : ''; ?>>Rejected
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="project">Project</label>
                    <select id="project" name="project">
                        <option value="">All Projects</option>
                        <?php foreach ($projects as $project): ?>
                            <option value="<?php echo $project['id']; ?>" <?php echo $filter_project == $project['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($project['title']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="filter-btn">
                    <i class="fas fa-search"></i> Filter
                </button>
            </form>
        </div>

        <div class="proposals-grid">
            <?php if (empty($proposals)): ?>
                <div class="no-proposals">
                    <i class="fas fa-file-alt" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <p>No proposals found matching your criteria.</p>
                </div>
            <?php else: ?>
                <?php foreach ($proposals as $proposal): ?>
                    <div class="proposal-card">
                        <div class="proposal-header">
                            <div class="proposal-title"><?php echo htmlspecialchars($proposal['project_title']); ?></div>
                            <div class="proposal-meta">
                                <span><i class="fas fa-user"></i>
                                    <?php echo htmlspecialchars($proposal['freelancer_first'] . ' ' . $proposal['freelancer_last']); ?>
                                    (@<?php echo htmlspecialchars($proposal['freelancer_username']); ?>)</span>
                                <span><i class="fas fa-user-tie"></i>
                                    <?php echo htmlspecialchars($proposal['client_first'] . ' ' . $proposal['client_last']); ?>
                                    (@<?php echo htmlspecialchars($proposal['client_username']); ?>)</span>
                                <span><i class="fas fa-calendar"></i>
                                    <?php echo date('M j, Y', strtotime($proposal['created_at'])); ?></span>
                                <span class="status-badge status-<?php echo $proposal['status']; ?>">
                                    <?php echo ucfirst($proposal['status']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="proposal-body">
                            <div class="proposal-content">
                                <div class="proposal-details">
                                    <div class="detail-item">
                                        <span class="detail-label">Bid Amount:</span>
                                        <span
                                            class="detail-value bid-amount">$<?php echo number_format($proposal['bid_amount']); ?></span>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-label">Delivery Time:</span>
                                        <span class="detail-value"><?php echo $proposal['delivery_time']; ?> days</span>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-label">Project Budget:</span>
                                        <span class="detail-value">$<?php echo number_format($proposal['budget_min']); ?> -
                                            $<?php echo number_format($proposal['budget_max']); ?></span>
                                    </div>

                                    <div class="detail-item">
                                        <span class="detail-label">Submitted:</span>
                                        <span
                                            class="detail-value"><?php echo date('M j, Y g:i A', strtotime($proposal['created_at'])); ?></span>
                                    </div>
                                </div>

                                <div class="cover-letter">
                                    <h4><i class="fas fa-envelope"></i> Cover Letter</h4>
                                    <p><?php echo nl2br(htmlspecialchars(substr($proposal['cover_letter'], 0, 300))); ?>
                                        <?php if (strlen($proposal['cover_letter']) > 300): ?>...<?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="proposal-actions">
                            <?php if ($proposal['status'] == 'pending'): ?>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="proposal_id" value="<?php echo $proposal['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-approve"
                                        onclick="return confirm('Approve this proposal?')">
                                        <i class="fas fa-check"></i> Approve
                                    </button>
                                </form>

                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="proposal_id" value="<?php echo $proposal['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-reject" onclick="return confirm('Reject this proposal?')">
                                        <i class="fas fa-times"></i> Reject
                                    </button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="proposal_id" value="<?php echo $proposal['id']; ?>">
                                <input type="hidden" name="action" value="delete">
                                <button type="submit" class="btn btn-delete"
                                    onclick="return confirm('Delete this proposal? This action cannot be undone.')">
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