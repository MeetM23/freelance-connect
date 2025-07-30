<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$deal_id = $_GET['id'] ?? '';

if (empty($deal_id)) {
    header("Location: dashboard.php");
    exit();
}

// Get deal information and verify access
try {
    $stmt = $pdo->prepare("
        SELECT d.*, 
               p.title as project_title,
               p.description as project_description,
               p.budget_min, p.budget_max,
               pr.bid_amount, pr.delivery_time,
               c.first_name as client_first_name, c.last_name as client_last_name,
               c.email as client_email, c.profile_image as client_image,
               f.first_name as freelancer_first_name, f.last_name as freelancer_last_name,
               f.email as freelancer_email, f.profile_image as freelancer_image
        FROM deals d
        LEFT JOIN proposals pr ON d.proposal_id = pr.id
        LEFT JOIN projects p ON pr.project_id = p.id
        LEFT JOIN users c ON d.client_id = c.id
        LEFT JOIN users f ON d.freelancer_id = f.id
        WHERE d.id = ? AND (d.client_id = ? OR d.freelancer_id = ?)
    ");
    $stmt->execute([$deal_id, $user_id, $user_id]);
    $deal = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$deal) {
        header("Location: dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    header("Location: dashboard.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['upload_file']) && $deal['status'] === 'ongoing') {
    if ($user_type === 'freelancer' && $user_id == $deal['freelancer_id']) {
        if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
            $allowed_types = ['zip', 'rar', 'docx', 'pdf', 'png', 'jpg', 'jpeg', 'gif'];
            $file_extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

            if (!in_array($file_extension, $allowed_types)) {
                $error_message = 'Invalid file type. Allowed: ZIP, RAR, DOCX, PDF, PNG, JPG, GIF';
            } elseif ($_FILES['file']['size'] > 10 * 1024 * 1024) { // 10MB limit
                $error_message = 'File too large. Maximum size is 10MB.';
            } else {
                $upload_dir = 'uploads/deals/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }

                $filename = 'deal_' . $deal_id . '_' . $user_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $filename;

                if (move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
                    try {
                        $stmt = $pdo->prepare("
                            INSERT INTO deal_files (deal_id, uploaded_by, file_name, file_path, uploaded_at) 
                            VALUES (?, ?, ?, ?, NOW())
                        ");
                        $stmt->execute([$deal_id, $user_id, $_FILES['file']['name'], $upload_path]);
                        $success_message = 'File uploaded successfully!';
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
        $error_message = 'Only freelancers can upload files.';
    }
}

// Handle status updates
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_status'])) {
    $new_status = $_POST['status'] ?? '';
    if (in_array($new_status, ['completed', 'cancelled'])) {
        if ($user_type === 'client' && $user_id == $deal['client_id']) {
            try {
                $stmt = $pdo->prepare("UPDATE deals SET status = ? WHERE id = ? AND client_id = ?");
                $stmt->execute([$new_status, $deal_id, $user_id]);
                $deal['status'] = $new_status;
                $success_message = 'Deal status updated successfully!';
            } catch (PDOException $e) {
                $error_message = 'Failed to update deal status.';
            }
        } else {
            $error_message = 'Only clients can update deal status.';
        }
    }
}

// Handle file deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete_file'])) {
    $file_id = $_POST['file_id'] ?? '';
    if (!empty($file_id)) {
        try {
            // Get file info
            $stmt = $pdo->prepare("SELECT * FROM deal_files WHERE id = ? AND deal_id = ?");
            $stmt->execute([$file_id, $deal_id]);
            $file = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($file && $file['uploaded_by'] == $user_id) {
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

// Calculate progress based on deal status and files
$progress = 0;
if ($deal['status'] === 'completed') {
    $progress = 100;
} elseif ($deal['status'] === 'cancelled') {
    $progress = 0;
} else {
    // Calculate progress based on files uploaded (simple logic)
    $progress = min(90, count($files) * 30); // 30% per file, max 90% until completed
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deal - <?php echo htmlspecialchars($deal['project_title']); ?> - Freelance Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/deal-page.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="deal-page-container">
        <!-- Deal Header -->
        <div class="deal-header">
            <h1 class="deal-title"><?php echo htmlspecialchars($deal['project_title']); ?></h1>
            <p class="deal-subtitle">
                Deal between
                <strong><?php echo htmlspecialchars($deal['client_first_name'] . ' ' . $deal['client_last_name']); ?></strong>
                and
                <strong><?php echo htmlspecialchars($deal['freelancer_first_name'] . ' ' . $deal['freelancer_last_name']); ?></strong>
            </p>

            <div class="deal-info">
                <div class="meta-item">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Budget: $<?php echo number_format($deal['bid_amount']); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-clock"></i>
                    <span>Delivery: <?php echo $deal['delivery_time']; ?> days</span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <span>Started: <?php echo date('M j, Y', strtotime($deal['created_at'])); ?></span>
                </div>
                <div class="meta-item">
                    <span class="deal-status status-<?php echo $deal['status']; ?>">
                        <?php echo ucfirst($deal['status']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if ($success_message): ?>
            <div class="message success-message"><?php echo htmlspecialchars($success_message); ?></div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="message error-message"><?php echo htmlspecialchars($error_message); ?></div>
        <?php endif; ?>

        <!-- Progress Section -->
        <div class="progress-section">
            <h2 class="progress-title">Project Progress</h2>
            <div class="progress-bar">
                <div class="progress-fill" style="width: <?php echo $progress; ?>%"></div>
            </div>
            <p class="progress-text"><?php echo $progress; ?>% Complete</p>
        </div>

        <!-- User Profiles Section -->
        <div class="users-section">
            <h2 class="users-title">Project Participants</h2>
            <div class="users-grid">
                <!-- Client Card -->
                <div class="user-card">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($deal['client_first_name'], 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <h4><?php echo htmlspecialchars($deal['client_first_name'] . ' ' . $deal['client_last_name']); ?>
                            </h4>
                            <p><?php echo htmlspecialchars($deal['client_email']); ?></p>
                            <span class="user-role">Client</span>
                        </div>
                    </div>
                </div>

                <!-- Freelancer Card -->
                <div class="user-card">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($deal['freelancer_first_name'], 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <h4><?php echo htmlspecialchars($deal['freelancer_first_name'] . ' ' . $deal['freelancer_last_name']); ?>
                            </h4>
                            <p><?php echo htmlspecialchars($deal['freelancer_email']); ?></p>
                            <span class="user-role">Freelancer</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Project Details Section -->
        <div class="project-details">
            <h2 class="project-title">Project Details</h2>
            <p class="project-description"><?php echo nl2br(htmlspecialchars($deal['project_description'])); ?></p>

            <div class="project-meta">
                <div class="meta-item">
                    <i class="fas fa-tag"></i>
                    <span>Project Budget: $<?php echo number_format($deal['budget_min']); ?> -
                        $<?php echo number_format($deal['budget_max']); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-file-alt"></i>
                    <span>Agreed Price: $<?php echo number_format($deal['bid_amount']); ?></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Timeline: <?php echo $deal['delivery_time']; ?> days</span>
                </div>
            </div>
        </div>

        <!-- File Upload Section (Freelancer Only) -->
        <?php if ($user_type === 'freelancer' && $user_id == $deal['freelancer_id'] && $deal['status'] === 'ongoing'): ?>
            <div class="files-section">
                <h2 class="files-title">Upload Deliverables</h2>
                <p class="files-subtitle">Upload your completed work files here</p>

                <form method="POST" action="" enctype="multipart/form-data" id="upload-form">
                    <input type="hidden" name="upload_file" value="1">

                    <div class="file-upload-area" id="upload-area">
                        <div class="file-upload-icon">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="file-upload-text">Drag and drop files here or click to browse</div>
                        <div class="file-upload-hint">Supported: ZIP, RAR, DOCX, PDF, PNG, JPG, GIF (Max 10MB)</div>
                        <input type="file" name="file" class="file-input" id="file-input"
                            accept=".zip,.rar,.docx,.pdf,.png,.jpg,.jpeg,.gif">
                        <button type="button" class="upload-btn" onclick="document.getElementById('file-input').click()">
                            Choose File
                        </button>
                    </div>

                    <button type="submit" class="upload-btn" id="submit-btn" style="display: none;">
                        <i class="fas fa-upload"></i> Upload File
                    </button>
                </form>
            </div>
        <?php endif; ?>

        <!-- Files List Section -->
        <div class="files-section">
            <h2 class="files-title">Project Files</h2>
            <div class="files-list">
                <?php if (empty($files)): ?>
                    <p style="color: #666; text-align: center; padding: 2rem;">No files uploaded yet.</p>
                <?php else: ?>
                    <?php foreach ($files as $file): ?>
                        <div class="file-item">
                            <div class="file-info">
                                <div class="file-icon">
                                    <i class="fas fa-file"></i>
                                </div>
                                <div class="file-details">
                                    <h5><?php echo htmlspecialchars($file['file_name']); ?></h5>
                                    <p>
                                        Uploaded by
                                        <?php echo htmlspecialchars($file['first_name'] . ' ' . $file['last_name']); ?>
                                        on <?php echo date('M j, Y g:i A', strtotime($file['uploaded_at'])); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="file-actions">
                                <a href="<?php echo htmlspecialchars($file['file_path']); ?>" class="action-btn btn-download"
                                    download="<?php echo htmlspecialchars($file['file_name']); ?>">
                                    <i class="fas fa-download"></i> Download
                                </a>

                                <?php if ($file['uploaded_by'] == $user_id && $deal['status'] === 'ongoing'): ?>
                                    <form method="POST" action="" style="display: inline;">
                                        <input type="hidden" name="delete_file" value="1">
                                        <input type="hidden" name="file_id" value="<?php echo $file['id']; ?>">
                                        <button type="submit" class="action-btn btn-delete"
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
        </div>

        <!-- Status Actions (Client Only) -->
        <?php if ($user_type === 'client' && $user_id == $deal['client_id'] && $deal['status'] === 'ongoing'): ?>
            <div class="status-actions">
                <h2 class="status-actions-title">Manage Deal Status</h2>
                <p class="status-actions-subtitle">Update the deal status based on project completion</p>

                <div class="status-buttons">
                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="update_status" value="1">
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="complete-btn"
                            onclick="return confirm('Mark this deal as completed? This action cannot be undone.')">
                            <i class="fas fa-check"></i> Mark as Completed
                        </button>
                    </form>

                    <form method="POST" action="" style="display: inline;">
                        <input type="hidden" name="update_status" value="1">
                        <input type="hidden" name="status" value="cancelled">
                        <button type="submit" class="cancel-btn"
                            onclick="return confirm('Cancel this deal? This action cannot be undone.')">
                            <i class="fas fa-times"></i> Cancel Deal
                        </button>
                    </form>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // File upload handling
        const fileInput = document.getElementById('file-input');
        const uploadArea = document.getElementById('upload-area');
        const submitBtn = document.getElementById('submit-btn');
        const uploadForm = document.getElementById('upload-form');

        if (fileInput) {
            fileInput.addEventListener('change', function () {
                if (this.files.length > 0) {
                    const file = this.files[0];
                    const fileName = file.name;

                    // Update upload area
                    uploadArea.innerHTML = `
                        <div class="file-upload-icon">
                            <i class="fas fa-file"></i>
                        </div>
                        <div class="file-upload-text">Selected: ${fileName}</div>
                        <div class="file-upload-hint">Click "Upload File" to submit</div>
                        <button type="button" class="upload-btn" onclick="document.getElementById('file-input').click()">
                            Choose Different File
                        </button>
                    `;

                    submitBtn.style.display = 'inline-block';
                }
            });

            // Drag and drop functionality
            uploadArea.addEventListener('dragover', function (e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', function (e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', function (e) {
                e.preventDefault();
                this.classList.remove('dragover');

                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    fileInput.dispatchEvent(new Event('change'));
                }
            });
        }

        // Auto-hide messages after 5 seconds
        setTimeout(function () {
            const messages = document.querySelectorAll('.message');
            messages.forEach(function (message) {
                message.style.opacity = '0';
                message.style.transition = 'opacity 0.5s ease';
                setTimeout(function () {
                    message.remove();
                }, 500);
            });
        }, 5000);
    </script>
</body>

</html>