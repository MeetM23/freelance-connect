<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in and is a freelancer
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'freelancer') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_name = $_SESSION['user_name'];

// Get proposal ID from URL parameter
$proposal_id = $_GET['id'] ?? '';

if (empty($proposal_id)) {
    header("Location: my-proposals.php");
    exit();
}

// Get proposal and project information
try {
    $stmt = $pdo->prepare("
        SELECT pr.*, 
               p.title as project_title,
               p.description as project_description,
               p.budget_min, p.budget_max,
               p.skills_required,
               c.name as category_name,
               cl.first_name as client_first_name, cl.last_name as client_last_name,
               cl.email as client_email, cl.profile_image as client_image
        FROM proposals pr
        LEFT JOIN projects p ON pr.project_id = p.id
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users cl ON p.client_id = cl.id
        WHERE pr.id = ? AND pr.freelancer_id = ?
    ");
    $stmt->execute([$proposal_id, $user_id]);
    $proposal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$proposal) {
        header("Location: my-proposals.php");
        exit();
    }

    // Check if there's a deal for this proposal
    $stmt = $pdo->prepare("SELECT id, status FROM deals WHERE proposal_id = ?");
    $stmt->execute([$proposal_id]);
    $deal = $stmt->fetch(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    header("Location: my-proposals.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_file'])) {
    if ($deal && $deal['status'] === 'ongoing') {
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $allowed_types = ['zip', 'rar', 'docx', 'pdf', 'png', 'jpg', 'jpeg', 'gif', 'txt', 'xlsx', 'pptx'];
            $file_extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

            if (!in_array($file_extension, $allowed_types)) {
                $error_message = 'Invalid file type. Allowed: ZIP, RAR, DOCX, PDF, PNG, JPG, GIF, TXT, XLSX, PPTX';
            } elseif ($_FILES['file']['size'] > 50 * 1024 * 1024) { // 50MB limit
                $error_message = 'File too large. Maximum size is 50MB.';
            } else {
                $upload_dir = 'uploads/deals/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $filename = 'project_' . $deal['id'] . '_' . $user_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO deal_files (deal_id, uploaded_by, file_name, file_path, uploaded_at) 
                            VALUES (?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$deal['id'], $user_id, $_FILES['file']['name'], $upload_path]);
                        $success_message = 'Project file uploaded successfully!';
                    } catch (PDOException $e) {
                        $error_message = 'Failed to save file information.';
                    }
                } else {
                    $error_message = 'Failed to upload file. Please try again.';
                }
            }
        } else {
            $error_message = 'Please select a file to upload.';
        }
    } else {
        $error_message = 'You can only upload files for ongoing projects.';
    }
}

// Handle file deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_file'])) {
    $file_id = $_POST['file_id'] ?? '';
    if (!empty($file_id) && $deal && $deal['status'] === 'ongoing') {
        try {
            // Get file info
            $stmt = $pdo->prepare("SELECT * FROM deal_files WHERE id = ? AND deal_id = ? AND uploaded_by = ?");
            $stmt->execute([$file_id, $deal['id'], $user_id]);
            $file = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($file) {
                // Delete physical file
                if (file_exists($file['file_path'])) {
                    unlink($file['file_path']);
                }

                // Delete database record
                $stmt = $pdo->prepare("DELETE FROM deal_files WHERE id = ?");
                $stmt->execute([$file_id]);

                $success_message = 'File deleted successfully!';
            } else {
                $error_message = 'You can only delete your own files.';
            }
        } catch (PDOException $e) {
            $error_message = 'Failed to delete file.';
        }
    }
}

// Get files for this project (if it's a deal)
$files = [];
if ($deal) {
    try {
        $stmt = $pdo->prepare("
            SELECT df.*, u.first_name, u.last_name, u.user_type
            FROM deal_files df
            LEFT JOIN users u ON df.uploaded_by = u.id
            WHERE df.deal_id = ?
            ORDER BY df.uploaded_at DESC
        ");
        $stmt->execute([$deal['id']]);
        $files = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $files = [];
    }
}

// Calculate progress
$progress = 0;
if ($deal) {
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
} else {
    $progress = 10; // Basic progress for accepted proposals
}

// Calculate time remaining
$time_remaining = '';
if (!empty($proposal['delivery_time'])) {
    $created_date = new DateTime($proposal['created_at']);
    $deadline = clone $created_date;
    $deadline->add(new DateInterval('P' . $proposal['delivery_time'] . 'D'));
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
    <title>Project Details - <?php echo htmlspecialchars($proposal['project_title']); ?> - Freelance Connect</title>
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

        .project-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .project-header {
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

        .client-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .client-avatar {
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

        .client-details h4 {
            margin-bottom: 0.25rem;
        }

        .client-details p {
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

        .upload-section {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .upload-area {
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 2rem;
            text-align: center;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }

        .upload-area:hover {
            border-color: #5a6fd8;
            background: #f8f9ff;
        }

        .upload-area.dragover {
            border-color: #28a745;
            background: #f8fff9;
        }

        .upload-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }

        .file-input {
            display: none;
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

        .status-pending {
            background: #cce5ff;
            color: #004085;
        }

        .status-accepted {
            background: #d1ecf1;
            color: #0c5460;
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

    <div class="project-container">
        <!-- Project Header -->
        <div class="project-header">
            <h1 class="project-title"><?php echo htmlspecialchars($proposal['project_title']); ?></h1>
            <p class="project-subtitle">Project Details & Deliverables</p>

            <!-- Progress Overview -->
            <div class="progress-overview">
                <div class="progress-card">
                    <h3><?php echo $progress; ?>%</h3>
                    <p>Project Progress</p>
                </div>
                <div class="progress-card">
                    <h3><?php echo count($files); ?></h3>
                    <p>Files Submitted</p>
                </div>
                <div class="progress-card">
                    <h3>$<?php echo number_format($proposal['bid_amount']); ?></h3>
                    <p>Your Bid</p>
                </div>
                <div class="progress-card">
                    <h3><?php echo $proposal['delivery_time']; ?> days</h3>
                    <p>Delivery Time</p>
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
                    <span class="detail-value"><?php echo htmlspecialchars($proposal['project_title']); ?></span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Status</span>
                    <span class="status-badge status-<?php echo $deal ? $deal['status'] : $proposal['status']; ?>">
                        <?php echo ucfirst($deal ? $deal['status'] : $proposal['status']); ?>
                    </span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Your Bid</span>
                    <span class="detail-value">$<?php echo number_format($proposal['bid_amount']); ?></span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Delivery Time</span>
                    <span class="detail-value"><?php echo $proposal['delivery_time']; ?> days</span>
                </div>

                <?php if (!empty($time_remaining)): ?>
                    <div class="detail-item">
                        <span class="detail-label">Time Remaining</span>
                        <span class="detail-value"><?php echo $time_remaining; ?></span>
                    </div>
                <?php endif; ?>

                <div class="detail-item">
                    <span class="detail-label">Category</span>
                    <span class="detail-value"><?php echo htmlspecialchars($proposal['category_name']); ?></span>
                </div>

                <div class="detail-item">
                    <span class="detail-label">Submitted</span>
                    <span class="detail-value"><?php echo date('M j, Y', strtotime($proposal['created_at'])); ?></span>
                </div>
            </div>

            <!-- Client Information -->
            <div class="detail-section">
                <h3><i class="fas fa-user"></i> Client Information</h3>

                <div class="client-info">
                    <div class="client-avatar">
                        <?php echo strtoupper(substr($proposal['client_first_name'], 0, 1) . substr($proposal['client_last_name'], 0, 1)); ?>
                    </div>
                    <div class="client-details">
                        <h4><?php echo htmlspecialchars($proposal['client_first_name'] . ' ' . $proposal['client_last_name']); ?>
                        </h4>
                        <p><?php echo htmlspecialchars($proposal['client_email']); ?></p>
                    </div>
                </div>

                <?php if (!empty($proposal['skills_required'])): ?>
                    <h4>Required Skills:</h4>
                    <div class="skills-tags">
                        <?php
                        $skills = explode(', ', $proposal['skills_required']);
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
            <p><?php echo nl2br(htmlspecialchars($proposal['project_description'])); ?></p>
        </div>

        <!-- Your Proposal -->
        <div class="detail-section">
            <h3><i class="fas fa-comment"></i> Your Proposal</h3>
            <p><?php echo nl2br(htmlspecialchars($proposal['cover_letter'])); ?></p>
        </div>

        <!-- File Upload Section (only for ongoing deals) -->
        <?php if ($deal && $deal['status'] === 'ongoing'): ?>
            <div class="upload-section">
                <h3><i class="fas fa-upload"></i> Submit Project Files</h3>

                <form method="POST" action="" enctype="multipart/form-data">
                    <input type="hidden" name="upload_file" value="1">

                    <div class="upload-area" id="uploadArea">
                        <div class="upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <h4>Drag & Drop files here or click to browse</h4>
                        <p>Supported formats: ZIP, RAR, DOCX, PDF, PNG, JPG, GIF, TXT, XLSX, PPTX (Max: 50MB)</p>
                        <input type="file" name="file" id="fileInput" class="file-input"
                            accept=".zip,.rar,.docx,.pdf,.png,.jpg,.jpeg,.gif,.txt,.xlsx,.pptx">
                        <button type="button" class="btn btn-primary"
                            onclick="document.getElementById('fileInput').click()">
                            <i class="fas fa-folder-open"></i> Choose File
                        </button>
                    </div>

                    <button type="submit" class="btn btn-success" style="width: 100%;">
                        <i class="fas fa-upload"></i> Upload File
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Submitted Files -->
        <div class="files-section">
            <h3><i class="fas fa-folder"></i> Submitted Files</h3>

            <?php if (empty($files)): ?>
                <div class="no-files">
                    <i class="fas fa-folder-open"></i>
                    <h4>No files submitted yet</h4>
                    <p>Upload your project deliverables here.</p>
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
                                <p>Uploaded on <?php echo date('M j, Y g:i A', strtotime($file['uploaded_at'])); ?></p>
                            </div>
                        </div>
                        <div class="file-actions">
                            <?php if ($deal && $deal['status'] === 'ongoing' && $file['uploaded_by'] == $user_id): ?>
                                <form method="POST" action="" style="display: inline;">
                                    <input type="hidden" name="delete_file" value="1">
                                    <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                    <button type="submit" class="btn btn-danger"
                                        onclick="return confirm('Are you sure you want to delete this file?')">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Navigation -->
        <div class="detail-section">
            <h3><i class="fas fa-arrow-left"></i> Navigation</h3>
            <a href="my-proposals.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i> Back to My Proposals
            </a>
          
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Drag and drop functionality
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');

        if (uploadArea && fileInput) {
            uploadArea.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadArea.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', () => {
                uploadArea.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadArea.classList.remove('dragover');
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                }
            });

            fileInput.addEventListener('change', () => {
                if (fileInput.files.length > 0) {
                    const fileName = fileInput.files[0].name;
                    // Update only the content, not the entire HTML
                    const uploadIcon = uploadArea.querySelector('.upload-icon');
                    const uploadTitle = uploadArea.querySelector('h4');
                    const uploadDesc = uploadArea.querySelector('p');
                    const chooseButton = uploadArea.querySelector('button');

                    if (uploadIcon) uploadIcon.innerHTML = '<i class="fas fa-file"></i>';
                    if (uploadTitle) uploadTitle.textContent = `Selected File: ${fileName}`;
                    if (uploadDesc) uploadDesc.textContent = 'Click "Upload File" to submit';
                    if (chooseButton) chooseButton.innerHTML = '<i class="fas fa-folder-open"></i> Choose Different File';
                }
            });
        }
    </script>
</body>

</html>