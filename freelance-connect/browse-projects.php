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

// Get filter parameters
$search = trim($_GET['search'] ?? '');
$category = $_GET['category'] ?? '';
$budget_min = $_GET['budget_min'] ?? '';
$budget_max = $_GET['budget_max'] ?? '';
$project_type = $_GET['project_type'] ?? '';

// Build query with filters
$where_conditions = ["p.status = 'open'"];
$params = [];

if (!empty($search)) {
    $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.skills_required LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($category)) {
    $where_conditions[] = "p.category_id = ?";
    $params[] = $category;
}

if (!empty($budget_min)) {
    $where_conditions[] = "p.budget_max >= ?";
    $params[] = $budget_min;
}

if (!empty($budget_max)) {
    $where_conditions[] = "p.budget_min <= ?";
    $params[] = $budget_max;
}

if (!empty($project_type)) {
    $where_conditions[] = "p.project_type = ?";
    $params[] = $project_type;
}

$where_clause = implode(' AND ', $where_conditions);

// Get projects
try {
    $query = "
        SELECT p.*, 
               c.name as category_name,
               u.first_name, u.last_name,
               COUNT(pr.id) as proposal_count
        FROM projects p 
        LEFT JOIN categories c ON p.category_id = c.id
        LEFT JOIN users u ON p.client_id = u.id
        LEFT JOIN proposals pr ON p.id = pr.project_id
        WHERE $where_clause
        GROUP BY p.id 
        ORDER BY p.created_at DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $projects = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $projects = [];
}

// Get categories for filter
try {
    $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $categories = [];
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Projects - Freelance Connect</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/projects.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include 'includes/header.php'; ?>

    <div class="projects-container">
        <div class="projects-header">
            <h1 class="projects-title">Browse Projects</h1>
            <p class="projects-subtitle">Find the perfect project that matches your skills and interests</p>
        </div>

        <!-- Search and Filter Section -->
        <div class="search-filters">
            <form method="GET" action="">
                <div class="filter-row">
                    <div class="filter-group">
                        <label for="search">Search Projects</label>
                        <input type="text" id="search" name="search" value="<?php echo htmlspecialchars($search); ?>"
                            placeholder="Search by title, description, or skills">
                    </div>

                    <div class="filter-group">
                        <label for="category">Category</label>
                        <select id="category" name="category">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" <?php echo $category == $cat['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="project_type">Project Type</label>
                        <select id="project_type" name="project_type">
                            <option value="">All Types</option>
                            <option value="fixed" <?php echo $project_type === 'fixed' ? 'selected' : ''; ?>>Fixed Price
                            </option>
                            <option value="hourly" <?php echo $project_type === 'hourly' ? 'selected' : ''; ?>>Hourly Rate
                            </option>
                        </select>
                    </div>
                </div>

                <div class="filter-row">
                    <div class="filter-group">
                        <label for="budget_min">Min Budget ($)</label>
                        <input type="number" id="budget_min" name="budget_min"
                            value="<?php echo htmlspecialchars($budget_min); ?>" placeholder="0" min="0">
                    </div>

                    <div class="filter-group">
                        <label for="budget_max">Max Budget ($)</label>
                        <input type="number" id="budget_max" name="budget_max"
                            value="<?php echo htmlspecialchars($budget_max); ?>" placeholder="10000" min="0">
                    </div>

                    <div class="filter-group" style="display: flex; align-items: end;">
                        <button type="submit" class="search-btn">
                            <i class="fas fa-search"></i> Search Projects
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Projects Grid -->
        <?php if (empty($projects)): ?>
            <div class="no-projects">
                <i class="fas fa-search"></i>
                <h3>No Projects Found</h3>
                <p>Try adjusting your search criteria or check back later for new projects.</p>
                <a href="browse-projects.php" class="action-btn btn-primary">Clear Filters</a>
            </div>
        <?php else: ?>
            <div class="projects-grid">
                <?php foreach ($projects as $project): ?>
                    <div class="project-card">
                        <div class="project-header">
                            <div>
                                <h3 class="project-title"><?php echo htmlspecialchars($project['title']); ?></h3>
                                <div class="skills-tags">
                                    <?php
                                    $skills = explode(', ', $project['skills_required']);
                                    foreach (array_slice($skills, 0, 4) as $skill): ?>
                                        <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                    <?php endforeach; ?>
                                    <?php if (count($skills) > 4): ?>
                                        <span class="skill-tag">+<?php echo count($skills) - 4; ?> more</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div class="project-budget">
                                $<?php echo number_format($project['budget_min']); ?> -
                                $<?php echo number_format($project['budget_max']); ?>
                            </div>
                        </div>

                        <p class="project-description">
                            <?php echo htmlspecialchars(substr($project['description'], 0, 200)) . (strlen($project['description']) > 200 ? '...' : ''); ?>
                        </p>

                        <div class="project-details">
                            <div class="detail-item">
                                <i class="fas fa-user"></i>
                                <span><?php echo htmlspecialchars($project['first_name'] . ' ' . $project['last_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-tag"></i>
                                <span><?php echo htmlspecialchars($project['category_name']); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-calendar"></i>
                                <span>Posted <?php echo date('M j, Y', strtotime($project['created_at'])); ?></span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-file-alt"></i>
                                <span><?php echo $project['proposal_count']; ?> proposals</span>
                            </div>
                            <div class="detail-item">
                                <i class="fas fa-clock"></i>
                                <span><?php echo ucfirst($project['project_type']); ?> Project</span>
                            </div>
                        </div>

                        <div class="project-actions">
                            <a href="submit-proposal.php?project_id=<?php echo $project['id']; ?>"
                                class="action-btn btn-primary">
                                <i class="fas fa-paper-plane"></i> Submit Proposal
                            </a>
                            <a href="view-project.php?id=<?php echo $project['id']; ?>" class="action-btn btn-outline">
                                <i class="fas fa-eye"></i> View Details
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>

    <script>
        // Auto-submit form when filters change
        document.querySelectorAll('.search-filters select, .search-filters input[type="number"]').forEach(element => {
            element.addEventListener('change', function () {
                // Don't auto-submit for search input to allow typing
                if (this.type !== 'text') {
                    this.closest('form').submit();
                }
            });
        });

        // Add debounced search for text input
        let searchTimeout;
        document.getElementById('search').addEventListener('input', function () {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                this.closest('form').submit();
            }, 1000); // Submit after 1 second of no typing
        });
    </script>
</body>

</html>