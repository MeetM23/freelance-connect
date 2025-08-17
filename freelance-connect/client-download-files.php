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

if (empty($deal_id)) {
    header("Location: view-proposals.php");
    exit();
}

// Get deal information and verify client access
try {
    $stmt = $pdo->prepare("
        SELECT d.*, 
               p.title as project_title,
               p.description as project_description,
               p.budget_min, p.budget_max,
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
    $deal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$deal) {
        header("Location: view-proposals.php");
        exit();
    }
} catch (PDOException $e) {
    header("Location: view-proposals.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle deal completion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['complete_deal'])) {
    $action = $_POST['action'] ?? '';
    if (in_array($action, ['completed', 'cancelled'])) {
        try {
            $stmt = $pdo->prepare("UPDATE deals SET status = ? WHERE id = ? AND client_id = ?");
            $stmt->execute([$action, $deal_id, $user_id]);
            $deal['status'] = $action;
            $success_message = 'Deal ' . $action . ' successfully!';
        } catch (PDOException $e) {
            $error_message = 'Failed to update deal status.';
        }
    }
}

// Get files for this deal
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

// Calculate progress
$progress = 0;
if ($deal['status'] === 'completed') {
    $progress = 100;
} elseif ($deal['status'] === 'cancelled') {
    $progress = 0;
} else {
    // Calculate progress based on files uploaded
    $progress = min(90, count($files) * 30); // 30% per file, max 90% until completed
    if (count($files) > 0) {
        $progress = max($progress, 30); // At least 30% if files are uploaded
    }
}

// Calculate time remaining
$time_remaining = '';
if (!empty($deal['delivery_time'])) {
    $created_date = new DateTime($deal['created_at']);
    $deadline = clone $created_date;
    $deadline->add(new DateInterval('P' . $deal['delivery_time'] . 'D'));
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
    <title>Download Files - <?php echo htmlspecialchars($deal['project_title']); ?> - Freelance Connect</title>
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

        .download-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .download-header {
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
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
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
            font-size: 1.8rem;
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
            transition: all 0.3s ease;
        }

        .file-item:hover {
            border-color: #667eea;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.1);
        }

        .file-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .file-icon {
            width: 50px;
            height: 50px;
            background: #667eea;
            color: white;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
        }

        .file-details h4 {
            margin-bottom: 0.25rem;
            color: #333;
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
            padding: 3rem;
            color: #666;
        }

        .no-files i {
            font-size: 4rem;
            margin-bottom: 1rem;
            opacity: 0.5;
            color: #667eea;
        }

        .completion-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .completion-actions {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .download-all-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            text-align: center;
        }

        .download-all-section h3 {
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .project-details {
                grid-template-columns: 1fr;
            }

            .progress-overview {
                grid-template-columns: 1fr;
            }

            .completion-actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="download-container">
        <!-- Project Header -->
        <div class="download-header">
            <h1 class="project-title"><?php echo htmlspecialchars($deal['project_title']); ?></h1>
            <p class="project-subtitle">Download Project Files & Complete Deal</p>

            <!-- Progress Overview -->
            <div class="progress-overview">
                <div class="progress-card">
                    <h3><?php echo $progress; ?>%</h3>
                    <p>Project Progress</p>
                </div>
                <div class="progress-card">
                    <h3><?php echo count($files); ?></h3>
                    <p>Files Available</p>
                </div>
                <div class="progress-card">
                    <h3>$<?php echo number_format($deal['bid_amount']); ?></h3>
                    <p>Project Budget</p>
                </div>
                <div class="progress-card">
                    <h3><?php echo $deal['delivery_time']; ?> days</h3>
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
                    <span class="detail-value"><?php echo htmlspecialchars($deal['project_title']); ?></span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="status-badge status-<?php echo $deal['status']; ?>">
                        <?php echo ucfirst($deal['status']); ?>
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Budget</span>
                    <span class="detail-value">$<?php echo number_format($deal['bid_amount']); ?></span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Delivery Time</span>
                    <span class="detail-value"><?php echo $deal['delivery_time']; ?> days</span>
                </div>

                <?php if (!empty($time_remaining)): ?>
                    <div class="detail-item">
                        <span class="detail-label">Time Remaining</span>
                        <span class="detail-value"><?php echo $time_remaining; ?></span>
                    </div>
                <?php endif; ?>

                <div class="detail-item">
                    <span class="detail-label">Created</span>
                    <span class="detail-value"><?php echo date('M j, Y', strtotime($deal['created_at'])); ?></span>
                </div>
            </div>

            <!-- Freelancer Information -->
            <div class="detail-section">
                <h3><i class="fas fa-user"></i> Freelancer Information</h3>

                <div class="freelancer-info">
                    <div class="freelancer-avatar">
                        <?php echo strtoupper(substr($deal['freelancer_first_name'], 0, 1) . substr($deal['freelancer_last_name'], 0, 1)); ?>
                    </div>
                    <div class="freelancer-details">
                        <h4><?php echo htmlspecialchars($deal['freelancer_first_name'] . ' ' . $deal['freelancer_last_name']); ?>
                        </h4>
                        <p><?php echo htmlspecialchars($deal['freelancer_email']); ?></p>
                        <?php if (!empty($deal['freelancer_rate'])): ?>
                            <p>Hourly Rate: $<?php echo number_format($deal['freelancer_rate'], 2); ?></p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!empty($deal['freelancer_skills'])): ?>
                    <h4>Skills:</h4>
                    <div class="skills-tags">
                        <?php
                        $skills = explode(', ', $deal['freelancer_skills']);
                        foreach ($skills as $skill): ?>
                            <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Download All Files Section -->
        <?php if (!empty($files)): ?>
            <div class="download-all-section">
                <h3><i class="fas fa-download"></i> Download All Project Files</h3>
                <p>All project deliverables are ready for download. Review the files and complete the deal when satisfied.
                </p>
                <div style="margin-top: 1rem;">
                    <a href="download-all-files.php?deal_id=<?php echo $deal_id; ?>" class="btn btn-primary"
                        style="background: white; color: #667eea;">
                        <i class="fas fa-download"></i> Download All Files (ZIP)
                    </a>
                </div>
            </div>
        <?php endif; ?>

        <!-- Project Files -->
        <div class="files-section">
            <h3><i class="fas fa-folder"></i> Project Deliverables</h3>

            <?php if (empty($files)): ?>
                <div class="no-files">
                    <i class="fas fa-folder-open"></i>
                    <h4>No files uploaded yet</h4>
                    <p>The freelancer hasn't uploaded any project files yet. Please wait for the deliverables.</p>
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

        <!-- Deal Completion Section -->
        <?php if ($deal['status'] === 'ongoing' && !empty($files)): ?>
            <div class="completion-section">
                <h3><i class="fas fa-check-circle"></i> Complete Deal</h3>
                <p>Review all the uploaded files. If you're satisfied with the deliverables, you can complete the deal.</p>

                <div class="completion-actions">
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="complete_deal" value="1">
                        <input type="hidden" name="action" value="completed">
                        <button type="submit" class="btn btn-success"
                            onclick="return confirm('Are you satisfied with the project deliverables? This will mark the deal as completed.')">
                            <i class="fas fa-check"></i> Mark as Completed
                        </button>
                    </form>

                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="complete_deal" value="1">
                        <input type="hidden" name="action" value="cancelled">
                        <button type="submit" class="btn btn-danger"
                            onclick="return confirm('Are you sure you want to cancel this deal? This action cannot be undone.')">
                            <i class="fas fa-times"></i> Cancel Deal
                        </button>
                    </form>
                </div>
            </div>
        <?php elseif ($deal['status'] === 'completed'): ?>
            <div class="completion-section">
                <h3><i class="fas fa-check-circle"></i> Deal Completed</h3>
                <p>This deal has been successfully completed. Thank you for using Freelance Connect!</p>
            </div>
        <?php elseif ($deal['status'] === 'cancelled'): ?>
            <div class="completion-section">
                <h3><i class="fas fa-times-circle"></i> Deal Cancelled</h3>
                <p>This deal has been cancelled.</p>
            </div>
        <?php endif; ?>

        <!-- Navigation -->
        <div class="detail-section">
            <h3><i class="fas fa-arrow-left"></i> Navigation</h3>
            <a href="view-proposals.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to Proposals
            </a>
     
     
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>

</html>