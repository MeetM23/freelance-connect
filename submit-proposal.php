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
$project_id = $_GET['project_id'] ?? '';

if (empty($project_id)) {
    header("Location: browse-projects.php");
    exit();
}

$errors = [];
$success = '';

// Get project details
try {
    $stmt = $pdo->prepare("
        SELECT p.*, 
               c.name as category_name,
               u.first_name, u.last_name
        FROM projects p 
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.client_id = u.id
        WHERE p.id = ? AND p.status = 'open'
    ");
    $stmt->execute([$project_id]);
    $project = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$project) {
        header("Location: browse-projects.php");
        exit();
    }
} catch (PDOException $e) {
    header("Location: browse-projects.php");
    exit();
}

// Check if freelancer already submitted a proposal
try {
    $stmt = $pdo->prepare("SELECT id FROM proposals WHERE project_id = ? AND freelancer_id = ?");
    $stmt->execute([$project_id, $user_id]);
    if ($stmt->rowCount() > 0) {
        $errors['general'] = 'You have already submitted a proposal for this project.';
    }
} catch (PDOException $e) {
    // Continue with form
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && empty($errors)) {
    $message = trim($_POST['message'] ?? '');
    $proposed_budget = trim($_POST['proposed_budget'] ?? '');
    $delivery_time = trim($_POST['delivery_time'] ?? '');

    // Validation
    if (empty($message)) {
        $errors['message'] = 'Proposal message is required';
    } elseif (strlen($message) < 50) {
        $errors['message'] = 'Proposal message must be at least 50 characters long';
    }

    if (empty($proposed_budget)) {
        $errors['proposed_budget'] = 'Proposed budget is required';
    } elseif (!is_numeric($proposed_budget) || $proposed_budget <= 0) {
        $errors['proposed_budget'] = 'Please enter a valid budget amount';
    }

    if (empty($delivery_time)) {
        $errors['delivery_time'] = 'Delivery time is required';
    } elseif (!is_numeric($delivery_time) || $delivery_time <= 0) {
        $errors['delivery_time'] = 'Please enter a valid delivery time';
    }

    // Handle file upload
    $attachment_path = '';
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] == 0) {
        $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'];
        $file_extension = strtolower(pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION));

        if (!in_array($file_extension, $allowed_types)) {
            $errors['attachment'] = 'Only PDF, DOC, DOCX, JPG, JPEG, and PNG files are allowed';
        } elseif ($_FILES['attachment']['size'] > 5 * 1024 * 1024) { // 5MB limit
            $errors['attachment'] = 'File size must be less than 5MB';
        } else {
            $upload_dir = 'uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $filename = 'proposal_' . $project_id . '_' . $user_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $upload_path)) {
                $attachment_path = $upload_path;
            } else {
                $errors['attachment'] = 'Failed to upload file. Please try again.';
            }
        }
    }

    // If no errors, submit proposal
    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO proposals (project_id, freelancer_id, cover_letter, bid_amount, delivery_time, status, created_at) 
                VALUES (?, ?, ?, ?, ?, 'pending', NOW())
            ");

            $stmt->execute([
                $project_id,
                $user_id,
                $message,
                $proposed_budget,
                $delivery_time
            ]);

            $success = 'Proposal submitted successfully! The client will review your proposal and get back to you.';

        } catch (PDOException $e) {
            $errors['general'] = 'Failed to submit proposal. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Submit Proposal - Freelance Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/projects.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="proposal-container">
        <div class="proposal-header">
            <h1 class="proposal-title">Submit Proposal</h1>
            <p class="proposal-subtitle">Present your best offer for this project</p>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Project Information -->
        <div class="project-info">
            <h3>Project Details</h3>
            <div class="project-info-details">
                <div class="detail-item">
                    <i class="fas fa-briefcase"></i>
                    <span><strong><?php echo htmlspecialchars($project['title']); ?></strong></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-user"></i>
                    <span>Client:
                        <?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-dollar-sign"></i>
                    <span>Budget: $<?php echo number_format($project['budget_min']); ?> -
                        $<?php echo number_format($project['budget_max']); ?></span>
                </div>
                <div class="detail-item">
                    <i class="fas fa-tag"></i>
                    <span>Category: <?php echo htmlspecialchars($project['category_name']); ?></span>
                </div>
            </div>
            <div style="margin-top: 1rem;">
                <strong>Project Description:</strong>
                <p style="color: #666; margin-top: 0.5rem;">
                    <?php echo nl2br(htmlspecialchars($project['description'])); ?>
                </p>
            </div>
            <div style="margin-top: 1rem;">
                <strong>Required Skills:</strong>
                <div class="skills-tags" style="margin-top: 0.5rem;">
                    <?php
                    $skills = explode(', ', $project['skills_required']);
                    foreach ($skills as $skill): ?>
                        <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if (empty($success)): ?>
            <form class="proposal-form" method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="message" class="required-field">Proposal Message</label>
                    <textarea id="message" name="message" class="<?php echo isset($errors['message']) ? 'error' : ''; ?>"
                        placeholder="Introduce yourself, explain your approach to this project, and why you're the best fit. Be specific about how you'll deliver the project requirements."
                        required><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
                    <?php if (isset($errors['message'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['message']); ?></div>
                    <?php endif; ?>
                    <div class="help-text">Write a compelling proposal that shows your understanding of the project and your
                        expertise.</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="proposed_budget" class="required-field">Your Proposed Budget ($)</label>
                        <input type="number" id="proposed_budget" name="proposed_budget"
                            value="<?php echo htmlspecialchars($_POST['proposed_budget'] ?? ''); ?>"
                            class="<?php echo isset($errors['proposed_budget']) ? 'error' : ''; ?>"
                            placeholder="Enter your proposed budget" min="1" step="0.01" required>
                        <?php if (isset($errors['proposed_budget'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['proposed_budget']); ?></div>
                        <?php endif; ?>
                        <div class="help-text">Client's budget range: $<?php echo number_format($project['budget_min']); ?>
                            - $<?php echo number_format($project['budget_max']); ?></div>
                    </div>

                    <div class="form-group">
                        <label for="delivery_time" class="required-field">Delivery Time (Days)</label>
                        <input type="number" id="delivery_time" name="delivery_time"
                            value="<?php echo htmlspecialchars($_POST['delivery_time'] ?? ''); ?>"
                            class="<?php echo isset($errors['delivery_time']) ? 'error' : ''; ?>"
                            placeholder="How many days to complete" min="1" required>
                        <?php if (isset($errors['delivery_time'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['delivery_time']); ?></div>
                        <?php endif; ?>
                        <div class="help-text">Estimate how many days you need to complete this project.</div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="attachment">Attachment (Optional)</label>
                    <div class="file-upload">
                        <input type="file" id="attachment" name="attachment" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                        <label for="attachment" class="file-upload-label">
                            <i class="fas fa-cloud-upload-alt"></i>
                            <span>Click to upload resume, portfolio, or relevant files</span>
                        </label>
                    </div>
                    <?php if (isset($errors['attachment'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['attachment']); ?></div>
                    <?php endif; ?>
                    <div class="help-text">Upload your resume, portfolio, or any relevant files (PDF, DOC, DOCX, JPG, PNG -
                        max 5MB)</div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="submit-btn">
                        <span id="submit-text">Submit Proposal</span>
                        <span id="submit-loading" class="loading" style="display: none;"></span>
                    </button>
                    <a href="browse-projects.php" class="cancel-btn">Cancel</a>
                </div>
            </form>
        <?php else: ?>
            <div class="form-actions">
                <a href="browse-projects.php" class="action-btn btn-primary">Browse More Projects</a>
                <a href="my-proposals.php" class="action-btn btn-outline">View My Proposals</a>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // File upload preview
        document.getElementById('attachment').addEventListener('change', function () {
            const file = this.files[0];
            const label = this.nextElementSibling;

            if (file) {
                label.innerHTML = `<i class="fas fa-file"></i><span>${file.name}</span>`;
                label.style.borderColor = '#14a800';
                label.style.background = '#f0f8f0';
                label.style.color = '#14a800';
            } else {
                label.innerHTML = `<i class="fas fa-cloud-upload-alt"></i><span>Click to upload resume, portfolio, or relevant files</span>`;
                label.style.borderColor = '#e1e5e9';
                label.style.background = '#f8f9fa';
                label.style.color = '#666';
            }
        });

        // Form submission loading state
        document.querySelector('.proposal-form').addEventListener('submit', function () {
            const submitBtn = document.querySelector('.submit-btn');
            const submitText = document.getElementById('submit-text');
            const submitLoading = document.getElementById('submit-loading');

            submitBtn.disabled = true;
            submitText.style.display = 'none';
            submitLoading.style.display = 'inline-block';
        });

        // Auto-focus on message field
        document.getElementById('message').focus();
    </script>
</body>

</html>