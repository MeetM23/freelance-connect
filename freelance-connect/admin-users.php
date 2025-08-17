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

// Handle user actions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action']) && isset($_POST['user_id'])) {
        $user_id = $_POST['user_id'];
        $action = $_POST['action'];

        try {
            switch ($action) {
                case 'suspend':
                    $stmt = $pdo->prepare("UPDATE users SET status = 'suspended' WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $message = "User suspended successfully.";
                    break;

                case 'activate':
                    $stmt = $pdo->prepare("UPDATE users SET status = 'active' WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $message = "User activated successfully.";
                    break;

                case 'ban':
                    $stmt = $pdo->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $message = "User banned successfully.";
                    break;

                case 'delete':
                    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->execute([$user_id]);
                    $message = "User deleted successfully.";
                    break;
            }
        } catch (PDOException $e) {
            $error = "Action failed: " . $e->getMessage();
        }
    }
}

// Get filter parameters
$filter_type = isset($_GET['type']) ? $_GET['type'] : '';
$filter_status = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? $_GET['search'] : '';

// Build query
$where_conditions = [];
$params = [];

if ($filter_type) {
    $where_conditions[] = "user_type = ?";
    $params[] = $filter_type;
}

if ($filter_status) {
    $where_conditions[] = "status = ?";
    $params[] = $filter_status;
}

if ($search) {
    $where_conditions[] = "(username LIKE ? OR first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
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

// Get users
try {
    $query = "SELECT * FROM users $where_clause ORDER BY created_at DESC";
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $users = $stmt->fetchAll();
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $users = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Management - Admin Panel</title>
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

        .users-table {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .table-header {
            background: #f8f9fa;
            padding: 1rem;
            border-bottom: 1px solid #dee2e6;
        }

        .table-header h3 {
            margin: 0;
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 1rem;
            text-align: left;
            border-bottom: 1px solid #dee2e6;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
        }

        .status-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
        }

        .status-suspended {
            background: #fff3cd;
            color: #856404;
        }

        .status-banned {
            background: #f8d7da;
            color: #721c24;
        }

        .user-type-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .type-client {
            background: #cce5ff;
            color: #004085;
        }

        .type-freelancer {
            background: #d1ecf1;
            color: #0c5460;
        }

        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.25rem 0.5rem;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            font-size: 0.8rem;
            text-decoration: none;
            display: inline-block;
        }

        .btn-suspend {
            background: #ffc107;
            color: #212529;
        }

        .btn-activate {
            background: #28a745;
            color: white;
        }

        .btn-ban {
            background: #dc3545;
            color: white;
        }

        .btn-delete {
            background: #6c757d;
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

        .no-users {
            padding: 2rem;
            text-align: center;
            color: #666;
        }
    </style>
</head>

<body>
    <div class="admin-header">
        <h1><i class="fas fa-users"></i> User Management</h1>
        <div class="admin-nav">
            <a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin-projects.php"><i class="fas fa-project-diagram"></i> Projects</a>
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
                    <label for="search">Search Users</label>
                    <input type="text" id="search" name="search" placeholder="Username, name, or email"
                        value="<?php echo htmlspecialchars($search); ?>">
                </div>

                <div class="form-group">
                    <label for="type">User Type</label>
                    <select id="type" name="type">
                        <option value="">All Types</option>
                        <option value="client" <?php echo $filter_type == 'client' ? 'selected' : ''; ?>>Client</option>
                        <option value="freelancer" <?php echo $filter_type == 'freelancer' ? 'selected' : ''; ?>>
                            Freelancer</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status</label>
                    <select id="status" name="status">
                        <option value="">All Status</option>
                        <option value="active" <?php echo $filter_status == 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="suspended" <?php echo $filter_status == 'suspended' ? 'selected' : ''; ?>>Suspended
                        </option>
                        <option value="banned" <?php echo $filter_status == 'banned' ? 'selected' : ''; ?>>Banned</option>
                    </select>
                </div>

                <button type="submit" class="filter-btn">
                    <i class="fas fa-search"></i> Filter
                </button>
            </form>
        </div>

        <div class="users-table">
            <div class="table-header">
                <h3>Users (<?php echo count($users); ?> found)</h3>
            </div>

            <?php if (empty($users)): ?>
                <div class="no-users">
                    <i class="fas fa-users" style="font-size: 3rem; color: #ccc; margin-bottom: 1rem;"></i>
                    <p>No users found matching your criteria.</p>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Type</th>
                            <th>Status</th>
                            <th>Location</th>
                            <th>Joined</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td>
                                    <div class="user-info">
                                        <div class="user-avatar">
                                            <?php echo strtoupper(substr($user['first_name'], 0, 1) . substr($user['last_name'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <div>
                                                <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                            </div>
                                            <div style="font-size: 0.8rem; color: #666;">
                                                @<?php echo htmlspecialchars($user['username']); ?>
                                            </div>
                                            <div style="font-size: 0.8rem; color: #666;">
                                                <?php echo htmlspecialchars($user['email']); ?>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="user-type-badge type-<?php echo $user['user_type']; ?>">
                                        <?php echo ucfirst($user['user_type']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo htmlspecialchars($user['location'] ?: 'Not specified'); ?></td>
                                <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <?php if ($user['status'] == 'active'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="suspend">
                                                <button type="submit" class="btn btn-suspend"
                                                    onclick="return confirm('Suspend this user?')">
                                                    <i class="fas fa-pause"></i> Suspend
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="activate">
                                                <button type="submit" class="btn btn-activate">
                                                    <i class="fas fa-play"></i> Activate
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php if ($user['status'] != 'banned'): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="ban">
                                                <button type="submit" class="btn btn-ban"
                                                    onclick="return confirm('Ban this user? This action cannot be undone.')">
                                                    <i class="fas fa-ban"></i> Ban
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="delete">
                                            <button type="submit" class="btn btn-delete"
                                                onclick="return confirm('Delete this user? This action cannot be undone.')">
                                                <i class="fas fa-trash"></i> Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>