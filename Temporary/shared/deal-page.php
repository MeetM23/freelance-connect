<?php
session_start();
require_once '../config/db.php';

// Access control: Only logged-in users
if (!isset($_SESSION['user_id'])) {
    header("Location: ../login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];
$deal_id = $_GET['id'] ?? '';

if (empty($deal_id)) {
    header("Location: ../dashboard.php");
    exit();
}

// Fetch deal and verify access
try {
    $stmt = $pdo->prepare("
        SELECT d.*, 
               p.title as project_title, p.description as project_description, p.budget_min, p.budget_max, p.deadline,
               c.first_name as client_first_name, c.last_name as client_last_name, c.email as client_email, c.profile_image as client_image,
               f.first_name as freelancer_first_name, f.last_name as freelancer_last_name, f.email as freelancer_email, f.profile_image as freelancer_image
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
        header("Location: ../dashboard.php");
        exit();
    }
} catch (PDOException $e) {
    header("Location: ../dashboard.php");
    exit();
}

// File upload (freelancer, ongoing only)
$upload_error = '';
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['upload_file']) &&
    $user_id == $deal['freelancer_id'] &&
    $deal['status'] === 'ongoing'
) {
    if (isset($_FILES['deliverable']) && $_FILES['deliverable']['error'] === UPLOAD_ERR_OK) {
        $allowed_ext = ['zip', 'rar', 'docx', 'pdf', 'png', 'jpg', 'jpeg'];
        $max_size = 10 * 1024 * 1024; // 10MB
        $file = $_FILES['deliverable'];
        $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if (!in_array($ext, $allowed_ext)) {
            $upload_error = 'Invalid file type.';
        } elseif ($file['size'] > $max_size) {
            $upload_error = 'File too large (max 10MB).';
        } else {
            $new_name = 'deal_' . $deal_id . '_' . $user_id . '_' . time() . '.' . $ext;
            $target_dir = '../uploads/deals/';
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            $target_path = $target_dir . $new_name;
            if (move_uploaded_file($file['tmp_name'], $target_path)) {
                $stmt = $pdo->prepare("INSERT INTO deal_files (deal_id, uploaded_by, file_name, file_path) VALUES (?, ?, ?, ?)");
                $stmt->execute([$deal_id, $user_id, $file['name'], 'uploads/deals/' . $new_name]);
            } else {
                $upload_error = 'Failed to upload file.';
            }
        }
    } else {
        $upload_error = 'No file selected or upload error.';
    }
}

// Mark as completed (client, ongoing only)
if (
    $_SERVER['REQUEST_METHOD'] === 'POST' &&
    isset($_POST['mark_completed']) &&
    $user_id == $deal['client_id'] &&
    $deal['status'] === 'ongoing'
) {
    $stmt = $pdo->prepare("UPDATE deals SET status = 'completed' WHERE id = ?");
    $stmt->execute([$deal_id]);
    $deal['status'] = 'completed';
}

// Get uploaded files
$stmt = $pdo->prepare("SELECT * FROM deal_files WHERE deal_id = ? ORDER BY uploaded_at DESC");
$stmt->execute([$deal_id]);
$files = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Counterpart info
if ($user_id == $deal['client_id']) {
    $counterpart_label = 'Freelancer';
    $counterpart_name = $deal['freelancer_first_name'] . ' ' . $deal['freelancer_last_name'];
    $counterpart_email = $deal['freelancer_email'];
    $counterpart_image = $deal['freelancer_image'];
} else {
    $counterpart_label = 'Client';
    $counterpart_name = $deal['client_first_name'] . ' ' . $deal['client_last_name'];
    $counterpart_email = $deal['client_email'];
    $counterpart_image = $deal['client_image'];
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Deal Page - Freelance Connect</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .deal-container {
            max-width: 800px;
            margin: 40px auto;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.07);
            padding: 2rem;
        }

        .deal-header {
            border-bottom: 1px solid #eee;
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
        }

        .deal-summary span {
            margin-right: 2rem;
            font-size: 1rem;
        }

        .deal-counterpart {
            display: flex;
            align-items: center;
            margin-top: 1rem;
        }

        .profile-img {
            width: 48px;
            height: 48px;
            border-radius: 50%;
            object-fit: cover;
            margin-right: 1rem;
            border: 1px solid #ddd;
        }

        .deal-files-section,
        .deal-upload-section,
        .deal-complete-section {
            margin-top: 2rem;
        }

        .deal-files-list {
            list-style: none;
            padding: 0;
        }

        .deal-files-list li {
            margin-bottom: 0.5rem;
        }

        .file-meta {
            color: #888;
            font-size: 0.9em;
            margin-left: 0.5rem;
        }

        .error-message {
            color: #b30000;
            background: #ffeaea;
            padding: 0.5rem 1rem;
            border-radius: 6px;
            margin-bottom: 1rem;
        }

        .deal-readonly-message {
            margin-top: 2rem;
            color: #155724;
            background: #d4edda;
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            font-weight: 600;
        }

        .action-btn {
            background: #14a800;
            color: #fff;
            border: none;
            padding: 0.5rem 1.2rem;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 0.5rem;
        }

        .action-btn.btn-accept {
            background: #007bff;
        }

        .action-btn:disabled {
            background: #ccc;
            cursor: not-allowed;
        }
    </style>
</head>

<body>
    <?php include '../includes/header.php'; ?>
    <div class="deal-container">
        <div class="deal-header">
            <h1><?php echo htmlspecialchars($deal['project_title']); ?></h1>
            <div class="deal-summary">
                <span><strong>Status:</strong> <span
                        class="deal-status status-<?php echo $deal['status']; ?>"><?php echo ucfirst($deal['status']); ?></span></span>
                <span><strong>Budget:</strong> $<?php echo number_format($deal['budget_min']); ?> -
                    $<?php echo number_format($deal['budget_max']); ?></span>
                <span><strong>Deadline:</strong> <?php echo htmlspecialchars($deal['deadline']); ?></span>
            </div>
            <div style="margin-top:1rem; color:#666; font-size:1.05em;">
                <strong>Project Description:</strong><br>
                <?php echo nl2br(htmlspecialchars($deal['project_description'])); ?>
            </div>
            <div class="deal-counterpart">
                <img src="../assets/images/<?php echo htmlspecialchars($counterpart_image ?? 'default.png'); ?>"
                    alt="Profile" class="profile-img">
                <span><strong><?php echo htmlspecialchars($counterpart_label); ?>:</strong>
                    <?php echo htmlspecialchars($counterpart_name); ?>
                    (<?php echo htmlspecialchars($counterpart_email); ?>)</span>
            </div>
        </div>
        <div class="deal-files-section">
            <h2>Uploaded Files</h2>
            <?php if (empty($files)): ?>
                <p>No files uploaded yet.</p>
            <?php else: ?>
                <ul class="deal-files-list">
                    <?php foreach ($files as $file): ?>
                        <li>
                            <a href="../<?php echo htmlspecialchars($file['file_path']); ?>"
                                download><?php echo htmlspecialchars($file['file_name']); ?></a>
                            <span class="file-meta">Uploaded by
                                <?php echo $file['uploaded_by'] == $deal['freelancer_id'] ? 'Freelancer' : 'Client'; ?> on
                                <?php echo date('M j, Y H:i', strtotime($file['uploaded_at'])); ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
        <?php if ($user_id == $deal['freelancer_id'] && $deal['status'] === 'ongoing'): ?>
            <div class="deal-upload-section">
                <h2>Upload Deliverable</h2>
                <?php if ($upload_error): ?>
                    <div class="error-message"><?php echo htmlspecialchars($upload_error); ?></div><?php endif; ?>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="deliverable" required accept=".zip,.rar,.docx,.pdf,.png,.jpg,.jpeg">
                    <button type="submit" name="upload_file" class="action-btn">Upload</button>
                </form>
            </div>
        <?php endif; ?>
        <?php if ($user_id == $deal['client_id'] && $deal['status'] === 'ongoing'): ?>
            <div class="deal-complete-section">
                <form method="POST">
                    <button type="submit" name="mark_completed" class="action-btn btn-accept"
                        onclick="return confirm('Mark this deal as completed?')">Mark as Completed</button>
                </form>
            </div>
        <?php endif; ?>
        <?php if ($deal['status'] === 'completed'): ?>
            <div class="deal-readonly-message">
                <strong>This deal is completed. No further uploads or changes allowed.</strong>
            </div>
        <?php endif; ?>
    </div>
    <?php include '../includes/footer.php'; ?>
</body>

</html>