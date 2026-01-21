<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in and is a client
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'client') {
    header("Location: login.php");
    exit();
}

// Debug: Check session data
if (isset($_GET['debug'])) {
    echo "<pre>Session data: ";
    print_r($_SESSION);
    echo "</pre>";
}

$errors = [];
$success = '';
$form_data = [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Debug: Log form data
    if (isset($_GET['debug'])) {
        echo "<pre>Form data: ";
        print_r($_POST);
        echo "</pre>";
    }

    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $budget = trim($_POST['budget'] ?? '');
    $budget_type = $_POST['budget_type'] ?? '';
    $skills_required = $_POST['skills_required'] ?? [];
    $deadline = $_POST['deadline'] ?? '';
    $project_type = $_POST['project_type'] ?? '';
    $experience_level = $_POST['experience_level'] ?? '';

    // Validation
    if (empty($title)) {
        $errors['title'] = 'Project title is required';
    } elseif (strlen($title) < 10) {
        $errors['title'] = 'Project title must be at least 10 characters long';
    } elseif (strlen($title) > 255) {
        $errors['title'] = 'Project title must be less than 255 characters';
    }

    if (empty($description)) {
        $errors['description'] = 'Project description is required';
    } elseif (strlen($description) < 50) {
        $errors['description'] = 'Project description must be at least 50 characters long';
    }

    if (empty($budget)) {
        $errors['budget'] = 'Budget is required';
    } elseif (!is_numeric($budget) || $budget <= 0) {
        $errors['budget'] = 'Please enter a valid budget amount';
    }

    if (empty($budget_type)) {
        $errors['budget_type'] = 'Please select budget type';
    }

    if (empty($skills_required)) {
        $errors['skills_required'] = 'At least one skill is required';
    }

    if (empty($deadline)) {
        $errors['deadline'] = 'Project deadline is required';
    } else {
        $deadline_date = new DateTime($deadline);
        $today = new DateTime();
        if ($deadline_date <= $today) {
            $errors['deadline'] = 'Deadline must be in the future';
        }
    }

    if (empty($project_type)) {
        $errors['project_type'] = 'Please select project type';
    }

    if (empty($experience_level)) {
        $errors['experience_level'] = 'Please select experience level';
    }

    // If no errors, proceed with project creation
    if (empty($errors)) {
        try {
            // Map project_type to category_id
            $category_mapping = [
                'web-development' => 1,
                'mobile-development' => 2,
                'design' => 3,
                'marketing' => 4,
                'writing' => 5,
                'other' => 1 // Default to Web Development for 'other'
            ];

            $category_id = $category_mapping[$project_type] ?? 1;

            // Insert project into database
            $stmt = $pdo->prepare("INSERT INTO projects (client_id, category_id, title, description, budget_min, budget_max, project_type, skills_required, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'open')");

            // For fixed projects, set both min and max to the same value
            $budget_min = $budget_max = $budget;

            // Convert skills array to string
            $skills_string = implode(', ', $skills_required);

            $stmt->execute([
                $_SESSION['user_id'],
                $category_id,
                $title,
                $description,
                $budget_min,
                $budget_max,
                $project_type,
                $skills_string
            ]);

            $success = 'Project posted successfully! Freelancers can now view and apply for your project.';

            // Clear form data
            $form_data = [];

        } catch (PDOException $e) {
            // Log the error for debugging
            error_log("Project posting error: " . $e->getMessage());
            $errors['general'] = 'Failed to post project. Please try again. Error: ' . $e->getMessage();
        }
    } else {
        // Store form data for re-population
        $form_data = [
            'title' => $title,
            'description' => $description,
            'budget' => $budget,
            'budget_type' => $budget_type,
            'skills_required' => $skills_required,
            'deadline' => $deadline,
            'project_type' => $project_type,
            'experience_level' => $experience_level
        ];
    }
}

// Predefined skills list
$available_skills = [
    'PHP',
    'JavaScript',
    'React',
    'Node.js',
    'Python',
    'Java',
    'C#',
    'C++',
    'HTML/CSS',
    'WordPress',
    'Laravel',
    'Django',
    'Angular',
    'Vue.js',
    'MySQL',
    'PostgreSQL',
    'MongoDB',
    'AWS',
    'Docker',
    'Git',
    'UI/UX Design',
    'Graphic Design',
    'Logo Design',
    'Illustration',
    'Content Writing',
    'Copywriting',
    'SEO',
    'Digital Marketing',
    'Video Editing',
    'Animation',
    '3D Modeling',
    'Mobile Development'
];
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post New Project - Freelance Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/project-form.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="project-form-container">
        <div class="project-form-header">
            <h1 class="project-form-title">Post a New Project</h1>
            <p class="project-form-subtitle">Tell freelancers about your project and get the best talent</p>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form class="project-form" method="POST" action="">
            <!-- Project Details Section -->
            <div class="form-section">
                <h3><i class="fas fa-info-circle"></i> Project Details</h3>

                <div class="form-group">
                    <label for="title" class="required-field">Project Title</label>
                    <input type="text" id="title" name="title"
                        value="<?php echo htmlspecialchars($form_data['title'] ?? ''); ?>"
                        class="<?php echo isset($errors['title']) ? 'error' : ''; ?>"
                        placeholder="e.g., Build a responsive e-commerce website" required>
                    <?php if (isset($errors['title'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['title']); ?></div>
                    <?php endif; ?>
                    <div class="help-text">Be specific about what you need. A clear title helps freelancers understand
                        your project.</div>
                </div>

                <div class="form-group">
                    <label for="description" class="required-field">Project Description</label>
                    <textarea id="description" name="description"
                        class="<?php echo isset($errors['description']) ? 'error' : ''; ?>"
                        placeholder="Describe your project in detail. Include requirements, goals, and any specific features you need."
                        required><?php echo htmlspecialchars($form_data['description'] ?? ''); ?></textarea>
                    <?php if (isset($errors['description'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['description']); ?></div>
                    <?php endif; ?>
                    <div class="help-text">The more details you provide, the better proposals you'll receive.</div>
                </div>
            </div>

            <!-- Budget & Timeline Section -->
            <div class="form-section">
                <h3><i class="fas fa-dollar-sign"></i> Budget & Timeline</h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="budget" class="required-field">Budget Amount</label>
                        <div class="budget-group">
                            <input type="number" id="budget" name="budget"
                                value="<?php echo htmlspecialchars($form_data['budget'] ?? ''); ?>"
                                class="<?php echo isset($errors['budget']) ? 'error' : ''; ?>" placeholder="500" min="1"
                                step="0.01" required>
                            <select name="budget_type"
                                class="<?php echo isset($errors['budget_type']) ? 'error' : ''; ?>">
                                <option value="">Type</option>
                                <option value="fixed" <?php echo ($form_data['budget_type'] ?? '') === 'fixed' ? 'selected' : ''; ?>>Fixed Price</option>
                                <option value="hourly" <?php echo ($form_data['budget_type'] ?? '') === 'hourly' ? 'selected' : ''; ?>>Hourly Rate</option>
                            </select>
                        </div>
                        <?php if (isset($errors['budget'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['budget']); ?></div>
                        <?php endif; ?>
                        <?php if (isset($errors['budget_type'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['budget_type']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="deadline" class="required-field">Project Deadline</label>
                        <input type="date" id="deadline" name="deadline"
                            value="<?php echo htmlspecialchars($form_data['deadline'] ?? ''); ?>"
                            class="<?php echo isset($errors['deadline']) ? 'error' : ''; ?>"
                            min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                        <?php if (isset($errors['deadline'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['deadline']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Skills & Requirements Section -->
            <div class="form-section">
                <h3><i class="fas fa-tools"></i> Skills & Requirements</h3>

                <div class="form-group">
                    <label class="required-field">Skills Required</label>
                    <div class="skills-tags" id="skills-tags">
                        <?php if (!empty($form_data['skills_required'])): ?>
                            <?php foreach ($form_data['skills_required'] as $skill): ?>
                                <div class="skill-tag">
                                    <span><?php echo htmlspecialchars($skill); ?></span>
                                    <button type="button" class="remove-skill" onclick="removeSkill(this)">×</button>
                                    <input type="hidden" name="skills_required[]"
                                        value="<?php echo htmlspecialchars($skill); ?>">
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    <div class="add-skill-group">
                        <input type="text" id="skill-input" placeholder="Add a skill (e.g., PHP, React, Design)">
                        <button type="button" class="add-skill-btn" onclick="addSkill()">Add Skill</button>
                    </div>
                    <?php if (isset($errors['skills_required'])): ?>
                        <div class="error-message"><?php echo htmlspecialchars($errors['skills_required']); ?></div>
                    <?php endif; ?>
                    <div class="help-text">Add the key skills needed for your project. This helps freelancers find your
                        project.</div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="project_type" class="required-field">Project Type</label>
                        <select id="project_type" name="project_type"
                            class="<?php echo isset($errors['project_type']) ? 'error' : ''; ?>" required>
                            <option value="">Select project type</option>
                            <option value="web-development" <?php echo ($form_data['project_type'] ?? '') === 'web-development' ? 'selected' : ''; ?>>Web Development</option>
                            <option value="mobile-development" <?php echo ($form_data['project_type'] ?? '') === 'mobile-development' ? 'selected' : ''; ?>>Mobile Development</option>
                            <option value="design" <?php echo ($form_data['project_type'] ?? '') === 'design' ? 'selected' : ''; ?>>Design & Creative</option>
                            <option value="writing" <?php echo ($form_data['project_type'] ?? '') === 'writing' ? 'selected' : ''; ?>>Writing & Translation</option>
                            <option value="marketing" <?php echo ($form_data['project_type'] ?? '') === 'marketing' ? 'selected' : ''; ?>>Digital Marketing</option>
                            <option value="other" <?php echo ($form_data['project_type'] ?? '') === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                        <?php if (isset($errors['project_type'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['project_type']); ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="form-group">
                        <label for="experience_level" class="required-field">Experience Level</label>
                        <select id="experience_level" name="experience_level"
                            class="<?php echo isset($errors['experience_level']) ? 'error' : ''; ?>" required>
                            <option value="">Select experience level</option>
                            <option value="entry" <?php echo ($form_data['experience_level'] ?? '') === 'entry' ? 'selected' : ''; ?>>Entry Level</option>
                            <option value="intermediate" <?php echo ($form_data['experience_level'] ?? '') === 'intermediate' ? 'selected' : ''; ?>>Intermediate</option>
                            <option value="expert" <?php echo ($form_data['experience_level'] ?? '') === 'expert' ? 'selected' : ''; ?>>Expert</option>
                        </select>
                        <?php if (isset($errors['experience_level'])): ?>
                            <div class="error-message"><?php echo htmlspecialchars($errors['experience_level']); ?></div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="submit-btn">
                    <span id="submit-text">Post Project</span>
                    <span id="submit-loading" class="loading" style="display: none;"></span>
                </button>
                <a href="client-dashboard.php" class="cancel-btn">Cancel</a>
            </div>
        </form>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Skills management
        function addSkill() {
            const skillInput = document.getElementById('skill-input');
            const skill = skillInput.value.trim();

            if (skill && skill.length > 0) {
                const skillsTags = document.getElementById('skills-tags');
                const skillTag = document.createElement('div');
                skillTag.className = 'skill-tag';
                skillTag.innerHTML = `
                    <span>${skill}</span>
                    <button type="button" class="remove-skill" onclick="removeSkill(this)">×</button>
                    <input type="hidden" name="skills_required[]" value="${skill}">
                `;
                skillsTags.appendChild(skillTag);
                skillInput.value = '';
            }
        }

        function removeSkill(button) {
            button.parentElement.remove();
        }

        // Allow Enter key to add skills
        document.getElementById('skill-input').addEventListener('keypress', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addSkill();
            }
        });

        // Form submission loading state
        document.querySelector('.project-form').addEventListener('submit', function () {
            const submitBtn = document.querySelector('.submit-btn');
            const submitText = document.getElementById('submit-text');
            const submitLoading = document.getElementById('submit-loading');

            submitBtn.disabled = true;
            submitText.style.display = 'none';
            submitLoading.style.display = 'inline-block';
        });

        // Auto-focus on title field
        document.getElementById('title').focus();
    </script>
</body>
</html>