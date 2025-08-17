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

// Get admin data
try {
    $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $admin = $stmt->fetch();

    // Get login history
    $logStmt = $pdo->prepare("SELECT * FROM admin_login_history WHERE admin_id = ? ORDER BY login_time DESC LIMIT 10");
    $logStmt->execute([$_SESSION['admin_id']]);
    $loginHistory = $logStmt->fetchAll();

} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        try {
            switch ($action) {
                case 'update_profile':
                    $first_name = trim($_POST['first_name']);
                    $last_name = trim($_POST['last_name']);
                    $email = trim($_POST['email']);

                    if (empty($first_name) || empty($last_name) || empty($email)) {
                        $error = "All fields are required.";
                    } else {
                        $stmt = $pdo->prepare("UPDATE admins SET first_name = ?, last_name = ?, email = ? WHERE id = ?");
                        $stmt->execute([$first_name, $last_name, $email, $_SESSION['admin_id']]);

                        // Update session
                        $_SESSION['admin_name'] = $first_name . ' ' . $last_name;
                        $_SESSION['admin_email'] = $email;

                        $message = "Profile updated successfully.";

                        // Refresh admin data
                        $stmt = $pdo->prepare("SELECT * FROM admins WHERE id = ?");
                        $stmt->execute([$_SESSION['admin_id']]);
                        $admin = $stmt->fetch();
                    }
                    break;

                case 'change_password':
                    $current_password = $_POST['current_password'];
                    $new_password = $_POST['new_password'];
                    $confirm_password = $_POST['confirm_password'];

                    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                        $error = "All password fields are required.";
                    } elseif ($new_password !== $confirm_password) {
                        $error = "New passwords do not match.";
                    } elseif (strlen($new_password) < 6) {
                        $error = "New password must be at least 6 characters long.";
                    } elseif (!password_verify($current_password, $admin['password_hash'])) {
                        $error = "Current password is incorrect.";
                    } else {
                        $new_password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE admins SET password_hash = ? WHERE id = ?");
                        $stmt->execute([$new_password_hash, $_SESSION['admin_id']]);

                        $message = "Password changed successfully.";
                    }
                    break;
            }
        } catch (PDOException $e) {
            $error = "Action failed: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Settings - Freelance Connect</title>
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

        .settings-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
        }

        .settings-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background: #f8f9fa;
            padding: 1.5rem;
            border-bottom: 1px solid #dee2e6;
        }

        .card-header h3 {
            margin: 0;
            color: #333;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .card-body {
            padding: 1.5rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 500;
            transition: transform 0.2s ease;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
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

        .login-history {
            list-style: none;
            padding: 0;
        }

        .login-item {
            padding: 1rem 0;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .login-item:last-child {
            border-bottom: none;
        }

        .login-info {
            flex: 1;
        }

        .login-time {
            font-weight: 500;
            color: #333;
        }

        .login-details {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .login-status {
            background: #d4edda;
            color: #155724;
            padding: 0.25rem 0.5rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
        }

        .profile-details h4 {
            margin-bottom: 0.5rem;
            color: #333;
        }

        .profile-details p {
            color: #666;
            margin: 0;
        }

        @media (max-width: 768px) {
            .settings-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="admin-header">
        <h1><i class="fas fa-cog"></i> Admin Settings</h1>
        <div class="admin-nav">
            <a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="admin-users.php"><i class="fas fa-users"></i> Users</a>
            <a href="admin-projects.php"><i class="fas fa-project-diagram"></i> Projects</a>
            <a href="admin-proposals.php"><i class="fas fa-file-alt"></i> Proposals</a>
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

        <div class="settings-grid">
            <!-- Profile Settings -->
            <div class="settings-card">
                <div class="card-header">
                    <h3><i class="fas fa-user"></i> Profile Settings</h3>
                </div>
                <div class="card-body">
                    <div class="profile-info">
                        <div class="profile-avatar">
                            <?php echo strtoupper(substr($admin['first_name'], 0, 1) . substr($admin['last_name'], 0, 1)); ?>
                        </div>
                        <div class="profile-details">
                            <h4><?php echo htmlspecialchars($admin['first_name'] . ' ' . $admin['last_name']); ?></h4>
                            <p><?php echo htmlspecialchars($admin['email']); ?></p>
                            <p>Admin since <?php echo date('M j, Y', strtotime($admin['created_at'])); ?></p>
                        </div>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="action" value="update_profile">

                        <div class="form-group">
                            <label for="first_name">First Name</label>
                            <input type="text" id="first_name" name="first_name"
                                value="<?php echo htmlspecialchars($admin['first_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="last_name">Last Name</label>
                            <input type="text" id="last_name" name="last_name"
                                value="<?php echo htmlspecialchars($admin['last_name']); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" name="email"
                                value="<?php echo htmlspecialchars($admin['email']); ?>" required>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Update Profile
                        </button>
                    </form>
                </div>
            </div>

            <!-- Change Password -->
            <div class="settings-card">
                <div class="card-header">
                    <h3><i class="fas fa-lock"></i> Change Password</h3>
                </div>
                <div class="card-body">
                    <form method="POST">
                        <input type="hidden" name="action" value="change_password">

                        <div class="form-group">
                            <label for="current_password">Current Password</label>
                            <input type="password" id="current_password" name="current_password" required>
                        </div>

                        <div class="form-group">
                            <label for="new_password">New Password</label>
                            <input type="password" id="new_password" name="new_password" required>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" required>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-key"></i> Change Password
                        </button>
                    </form>
                </div>
            </div>

            <!-- Login History -->
            <div class="settings-card">
                <div class="card-header">
                    <h3><i class="fas fa-history"></i> Recent Login History</h3>
                </div>
                <div class="card-body">
                    <?php if (empty($loginHistory)): ?>
                        <p style="color: #666; text-align: center;">No login history available.</p>
                    <?php else: ?>
                        <ul class="login-history">
                            <?php foreach ($loginHistory as $login): ?>
                                <li class="login-item">
                                    <div class="login-info">
                                        <div class="login-time">
                                            <?php echo date('M j, Y g:i A', strtotime($login['login_time'])); ?>
                                        </div>
                                        <div class="login-details">
                                            IP: <?php echo htmlspecialchars($login['ip_address'] ?: 'Unknown'); ?>
                                        </div>
                                    </div>
                                    <span class="login-status">Success</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

            <!-- System Info -->
            <div class="settings-card">
                <div class="card-header">
                    <h3><i class="fas fa-info-circle"></i> System Information</h3>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label>PHP Version</label>
                        <input type="text" value="<?php echo PHP_VERSION; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Server Software</label>
                        <input type="text" value="<?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?>" readonly>
                    </div>

                    <div class="form-group">
                        <label>Database</label>
                        <input type="text" value="MySQL" readonly>
                    </div>

                    <div class="form-group">
                        <label>Last Login</label>
                        <input type="text"
                            value="<?php echo $admin['last_login'] ? date('M j, Y g:i A', strtotime($admin['last_login'])) : 'Never'; ?>"
                            readonly>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>

</html>