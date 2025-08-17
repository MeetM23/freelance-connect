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

// Get deal ID from URL parameter
$deal_id = $_GET['id'] ?? '';
$proposal_id = $_GET['proposal_id'] ?? '';

if (empty($deal_id) && empty($proposal_id)) {
    header("Location: my-projects.php");
    exit();
}

// Get deal information and verify client access
try {
    if (!empty($deal_id)) {
        $stmt = $pdo->prepare("
            SELECT d.*, 
                   p.title as project_title,
                   p.description as project_description,
                   p.budget_min, p.budget_max,
                   p.skills_required,
                   pr.bid_amount, pr.delivery_time, pr.cover_letter,
                   c.first_name as client_first_name, c.last_name as client_last_name,
                   c.email as client_email, c.profile_image as client_image,
                   f.first_name as freelancer_first_name, f.last_name as freelancer_last_name,
                   f.email as freelancer_email, f.profile_image as freelancer_image,
                   f.skills as freelancer_skills, f.hourly_rate as freelancer_rate
            FROM deals d
            LEFT JOIN proposals pr ON d.proposal_id = pr.id
            LEFT JOIN projects p ON pr.project_id = p.id
            LEFT JOIN users c ON d.client_id = c.id
            LEFT JOIN users f ON d.freelancer_id = f.id
            WHERE d.id = ? AND d.client_id = ?
        ");
        $stmt->execute([$deal_id, $user_id]);
    } else {
        // If no deal_id, get from proposal_id
        $stmt = $pdo->prepare("
            SELECT pr.id as proposal_id,
                   pr.bid_amount, pr.delivery_time, pr.cover_letter, pr.status as proposal_status,
                   p.id as project_id, p.title as project_title,
                   p.description as project_description,
                   p.budget_min, p.budget_max,
                   p.skills_required,
                   c.first_name as client_first_name, c.last_name as client_last_name,
                   c.email as client_email, c.profile_image as client_image,
                   f.first_name as freelancer_first_name, f.last_name as freelancer_last_name,
                   f.email as freelancer_email, f.profile_image as freelancer_image,
                   f.skills as freelancer_skills, f.hourly_rate as freelancer_rate
            FROM proposals pr
            LEFT JOIN projects p ON pr.project_id = p.id
            LEFT JOIN users c ON p.client_id = c.id
            LEFT JOIN users f ON pr.freelancer_id = f.id
            WHERE pr.id = ? AND p.client_id = ?
        ");
        $stmt->execute([$proposal_id, $user_id]);
    }

    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        header("Location: my-projects.php");
        exit();
    }

    // If we have a proposal but no deal, check if there's a deal
    if (!empty($proposal_id) && empty($deal_id)) {
        $stmt = $pdo->prepare("SELECT id FROM deals WHERE proposal_id = ?");
        $stmt->execute([$proposal_id]);
        $deal = $stmt->fetch();
        if ($deal) {
            header("Location: client-project-progress.php?id=" . $deal['id']);
            exit();
        }
    }

} catch (PDOException $e) {
    header("Location: my-projects.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle status updates (only for deals)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status']) && !empty($deal_id)) {
    $new_status = $_POST['status'] ?? '';
    if (in_array($new_status, ['completed', 'cancelled'])) {
        try {
            $stmt = $pdo->prepare("UPDATE deals SET status = ? WHERE id = ? AND client_id = ?");
            $stmt->execute([$new_status, $deal_id, $user_id]);
            $project['status'] = $new_status;
            $success_message = 'Project status updated successfully!';
        } catch (PDOException $e) {
            $error_message = 'Failed to update project status.';
        }
    }
}

// Get files for this project (if it's a deal)
$files = [];
if (!empty($deal_id)) {
    try {
        $stmt = $pdo->prepare("
            SELECT df.*, u.first_name, u.last_name, u.user_type
            FROM deal_files df
            LEFT JOIN users u ON df.uploaded_by = u.id
            WHERE df.deal_id = ?
            ORDER BY df.uploaded_at DESC
        ");
        $stmt->execute([$deal_id]);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $files = [];
    }
}

// Calculate progress
$progress = 0;
$status = $project['status'] ?? $project['proposal_status'] ?? 'pending';

if ($status === 'completed') {
    $progress = 100;
} elseif ($status === 'cancelled') {
    $progress = 0;
} elseif ($status === 'accepted' || $status === 'ongoing') {
    // Calculate progress based on files uploaded and time elapsed
    $progress = min(90, count($files) * 25); // 25% per file, max 90% until completed
    if (count($files) > 0) {
        $progress = max($progress, 30); // At least 30% if files are uploaded
    }
} else {
    $progress = 10; // Basic progress for accepted proposals
}

// Calculate time remaining
$time_remaining = '';
if (!empty($project['delivery_time'])) {
    $created_date = new DateTime($project['created_at'] ?? 'now');
    $deadline = clone $created_date;
    $deadline->add(new DateInterval('P' . $project['delivery_time'] . 'D'));
    $now = new DateTime();

    if ($now < $deadline) {
        $interval = $now->diff($deadline);
        $time_remaining = $interval->days . ' days remaining';
    } else {
        $time_remaining = 'Deadline passed';
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Progress - <?php echo htmlspecialchars($project['project_title']); ?> - Freelance Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: #f8f9fa;
            color: #333;
            line-height: 1.6;
        }

        .progress-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .progress-header {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .project-title {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .project-subtitle {
            color: #666;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }

        .progress-overview {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .progress-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 10px;
            text-align: center;
        }

        .progress-card h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .progress-card p {
            opacity: 0.9;
            font-size: 0.9rem;
        }

        .progress-bar-container {
            background: #e9ecef;
            border-radius: 10px;
            height: 20px;
            overflow: hidden;
            margin: 1rem 0;
        }

        .progress-bar {
            background: linear-gradient(90deg, #28a745, #20c997);
            height: 100%;
            border-radius: 10px;
            transition: width 0.3s ease;
            position: relative;
        }

        .progress-text {
            text-align: center;
            font-weight: 600;
            color: #333;
            margin-top: 0.5rem;
        }

        .project-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .detail-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .detail-section h3 {
            color: #333;
            margin-bottom: 1rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.75rem 0;
            border-bottom: 1px solid #eee;
        }

        .detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 500;
            color: #666;
        }

        .detail-value {
            font-weight: 600;
            color: #333;
        }

        .freelancer-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .freelancer-avatar {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: #667eea;
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: bold;
        }

        .freelancer-details h4 {
            margin-bottom: 0.25rem;
        }

        .freelancer-details p {
            color: #666;
            font-size: 0.9rem;
        }

        .skills-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 1rem;
        }

        .skill-tag {
            background: #e9ecef;
            color: #495057;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .files-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .file-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            margin-bottom: 1rem;
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .file-icon {
            width: 40px;
            height: 40px;
            background: #667eea;
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .file-details h4 {
            margin-bottom: 0.25rem;
        }

        .file-details p {
            color: #666;
            font-size: 0.8rem;
        }

        .file-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: #667eea;
            color: white;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-ongoing {
            background: #fff3cd;
            color: #856404;
        }

        .status-completed {
            background: #d4edda;
            color: #155724;
        }

        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
        }

        .status-pending {
            background: #cce5ff;
            color: #004085;
        }

        .status-accepted {
            background: #d1ecf1;
            color: #0c5460;
        }

        .message {
            padding: 1rem;
            border-radius: 8px;
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

        .no-files {
            text-align: center;
            padding: 2rem;
            color: #666;
        }

        .no-files i {
            font-size: 3rem;
            margin-bottom: 1rem;
            opacity: 0.5;
        }

        @media (max-width: 768px) {
            .project-details {
                grid-template-columns: 1fr;
            }

            .progress-overview {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="progress-container">
        <!-- Project Header -->
        <div class="progress-header">
            <h1 class="project-title"><?php echo htmlspecialchars($project['project_title']); ?></h1>
            <p class="project-subtitle">Project Progress & Deliverables</p>

            <!-- Progress Overview -->
            <div class="progress-overview">
                <div class="progress-card">
                    <h3><?php echo $progress; ?>%</h3>
                    <p>Project Progress</p>
                </div>
                <div class="progress-card">
                    <h3><?php echo count($files); ?></h3>
                    <p>Files Delivered</p>
                </div>
                <div class="progress-card">
                    <h3>$<?php echo number_format($project['bid_amount']); ?></h3>
                    <p>Project Budget</p>
                </div>
                <div class="progress-card">
                    <h3><?php echo $project['delivery_time']; ?> days</h3>
                    <p>Delivery Timeline</p>
                </div>
            </div>

            <!-- Progress Bar -->
            <div class="progress-bar-container">
                <div class="progress-bar" style="width: <?php echo $progress; ?>%"></div>
            </div>
            <div class="progress-text"><?php echo $progress; ?>% Complete</div>
        </div>

        <?php if ($success_message): ?>
            <div class="message success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="message error">
                <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Project Details -->
        <div class="project-details">
            <!-- Project Information -->
            <div class="detail-section">
                <h3><i class="fas fa-info-circle"></i> Project Information</h3>

                <div class="detail-item">
                    <span class="detail-label">Project Title</span>
                    <span class="detail-value"><?php echo htmlspecialchars($project['project_title']); ?></span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="status-badge status-<?php echo $status; ?>">
                        <?php echo ucfirst($status); ?>
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Budget</span>
                    <span class="detail-value">$<?php echo number_format($project['bid_amount']); ?></span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Delivery Time</span>
                    <span class="detail-value"><?php echo $project['delivery_time']; ?> days</span>
                </div>

                <?php if (!empty($time_remaining)): ?>
                    <div class="detail-item">
                        <span class="detail-label">Time Remaining</span>
                        <span class="detail-value"><?php echo $time_remaining; ?></span>
                    </div>
                <?php endif; ?>

                <div class="detail-item">
                    <span class="detail-label">Created</span>
                    <span
                        class="detail-value"><?php echo date('M j, Y', strtotime($project['created_at'] ?? 'now')); ?></span>
                </div>
            </div>

            <!-- Freelancer Information -->
            <div class="detail-section">
                <h3><i class="fas fa-user"></i> Freelancer Information</h3>

                <div class="freelancer-info">
                    <div class="freelancer-avatar">
                        <?php echo strtoupper(substr($project['freelancer_first_name'], 0, 1) . substr($project['freelancer_last_name'], 0, 1)); ?>
                    </div>
                    <div class="freelancer-details">
                        <h4><?php echo htmlspecialchars($project['freelancer_first_name'] . ' ' . $project['freelancer_last_name']); ?>
                        </h4>
                        <p><?php echo htmlspecialchars($project['freelancer_email']); ?></p>
                        <?php if (!empty($project['freelancer_rate'])): ?>
                            <p>Hourly Rate: $<?php echo number_format($project['freelancer_rate'], 2); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($project['freelancer_skills'])): ?>
                    <h4>Skills:</h4>
                    <div class="skills-tags">
                        <?php
                        $skills = explode(', ', $project['freelancer_skills']);
                        foreach ($skills as $skill): ?>
                            <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Project Description -->
        <div class="detail-section">
            <h3><i class="fas fa-file-alt"></i> Project Description</h3>
            <p><?php echo nl2br(htmlspecialchars($project['project_description'])); ?></p>
        </div>

        <!-- Freelancer's Proposal -->
        <div class="detail-section">
            <h3><i class="fas fa-comment"></i> Freelancer's Proposal</h3>
            <p><?php echo nl2br(htmlspecialchars($project['cover_letter'])); ?></p>
        </div>

        <!-- Project Files -->
        <div class="files-section">
            <h3><i class="fas fa-folder"></i> Project Deliverables</h3>

            <?php if (empty($files)): ?>
                <div class="no-files">
                    <i class="fas fa-folder-open"></i>
                    <h4>No files uploaded yet</h4>
                    <p>The freelancer hasn't uploaded any project files yet.</p>
                </div>
            <?php else: ?>
                <?php foreach ($files as $file): ?>
                    <div class="file-item">
                        <div class="file-info">
                            <div class="file-icon">
                                <i class="fas fa-file"></i>
                            </div>
                            <div class="file-details">
                                <h4><?php echo htmlspecialchars($file['file_name']); ?></h4>
                                <p>Uploaded by <?php echo htmlspecialchars($file['first_name'] . ' ' . $file['last_name']); ?>
                                    on <?php echo date('M j, Y g:i A', strtotime($file['uploaded_at'])); ?></p>
                            </div>
                        </div>
                        <div class="file-actions">
                            <a href="<?php echo htmlspecialchars($file['file_path']); ?>" download class="btn btn-primary">
                                <i class="fas fa-download"></i> Download
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Project Actions -->
        <?php if (!empty($deal_id) && $status === 'ongoing'): ?>
            <div class="detail-section">
                <h3><i class="fas fa-cogs"></i> Project Actions</h3>

                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="status" value="completed">
                    <button type="submit" class="btn btn-success"
                        onclick="return confirm('Are you satisfied with the project? This will mark it as completed.')">
                        <i class="fas fa-check"></i> Mark as Completed
                    </button>
                </form>

                <form method="POST" action="" style="display: inline;">
                    <input type="hidden" name="update_status" value="1">
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="btn btn-danger"
                        onclick="return confirm('Are you sure you want to cancel this project? This action cannot be undone.')">
                        <i class="fas fa-times"></i> Cancel Project
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Navigation -->
        <div class="detail-section">
            <h3><i class="fas fa-arrow-left"></i> Navigation</h3>
            <a href="view-proposals.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Proposals
            </a>
     
            <?php if (!empty($deal_id)): ?>
        
                <a href="client-download-files.php?id=<?php echo $deal_id; ?>" class="btn btn-success">
                    <i class="fas fa-download"></i> Download Files
                </a>
            <?php endif; ?>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>