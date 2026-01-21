<?php
session_start();
require_once 'config/db.php';

$errors = [];
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $user_type = $_POST['user_type'] ?? '';

    // Validation
    if (empty($name)) {
        $errors['name'] = 'Name is required';
    } elseif (strlen($name) < 2) {
        $errors['name'] = 'Name must be at least 2 characters long';
    }

    if (empty($email)) {
        $errors['email'] = 'Email is required';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Please enter a valid email address';
    } else {
        // Check if email already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        if ($stmt->rowCount() > 0) {
            $errors['email'] = 'Email already exists. Please use a different email.';
        }
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    } elseif (strlen($password) < 6) {
        $errors['password'] = 'Password must be at least 6 characters long';
    }

    if (empty($confirm_password)) {
        $errors['confirm_password'] = 'Please confirm your password';
    } elseif ($password !== $confirm_password) {
        $errors['confirm_password'] = 'Passwords do not match';
    }

    if (empty($user_type)) {
        $errors['user_type'] = 'Please select your user type';
    } elseif (!in_array($user_type, ['freelancer', 'client'])) {
        $errors['user_type'] = 'Invalid user type selected';
    }

    // If no errors, proceed with registration
    if (empty($errors)) {
        try {
            // Hash password
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            // Insert user into database
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash, first_name, last_name, user_type) VALUES (?, ?, ?, ?, ?, ?)");

            // Split name into first and last name
            $name_parts = explode(' ', $name, 2);
            $first_name = $name_parts[0];
            $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

            // Create username from email
            $username = strtolower(explode('@', $email)[0]);

            $stmt->execute([$username, $email, $password_hash, $first_name, $last_name, $user_type]);

            $success = 'Registration successful! You can now log in.';

            // Clear form data
            $name = $email = $password = $confirm_password = $user_type = '';

        } catch (PDOException $e) {
            $errors['general'] = 'Registration failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Freelance Connect</title>
    <link rel="stylesheet" href="assets/css/auth.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <div class="auth-container">
        <div class="auth-header">
            <div class="auth-logo">
                <i class="fas fa-handshake"></i>
                <span>Freelance Connect</span>
            </div>
            <h1 class="auth-title">Create Your Account</h1>
            <p class="auth-subtitle">Join thousands of freelancers and clients</p>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="">
            <div class="form-group">
                <label for="name">Full Name *</label>
                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($name ?? ''); ?>"
                    class="<?php echo isset($errors['name']) ? 'error' : ''; ?>" placeholder="Enter your full name"
                    required>
                <?php if (isset($errors['name'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($errors['name']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email">Email Address *</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email ?? ''); ?>"
                    class="<?php echo isset($errors['email']) ? 'error' : ''; ?>" placeholder="Enter your email address"
                    required>
                <?php if (isset($errors['email'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($errors['email']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password">Password *</label>
                <div class="password-toggle">
                    <input type="password" id="password" name="password"
                        class="<?php echo isset($errors['password']) ? 'error' : ''; ?>"
                        placeholder="Create a password (min 6 characters)" required>
                    <button type="button" class="password-toggle-btn" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($errors['password']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password *</label>
                <div class="password-toggle">
                    <input type="password" id="confirm_password" name="confirm_password"
                        class="<?php echo isset($errors['confirm_password']) ? 'error' : ''; ?>"
                        placeholder="Confirm your password" required>
                    <button type="button" class="password-toggle-btn" onclick="togglePassword('confirm_password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <?php if (isset($errors['confirm_password'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($errors['confirm_password']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label>I want to *</label>
                <div class="user-type-group">
                    <div class="user-type-option">
                        <input type="radio" id="freelancer" name="user_type" value="freelancer" <?php echo ($user_type ?? '') === 'freelancer' ? 'checked' : ''; ?>>
                        <label for="freelancer">
                            <i class="fas fa-user-tie"></i><br>
                            Work as a Freelancer
                        </label>
                    </div>
                    <div class="user-type-option">
                        <input type="radio" id="client" name="user_type" value="client" <?php echo ($user_type ?? '') === 'client' ? 'checked' : ''; ?>>
                        <label for="client">
                            <i class="fas fa-briefcase"></i><br>
                            Hire Talent
                        </label>
                    </div>
                </div>
                <?php if (isset($errors['user_type'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($errors['user_type']); ?></div>
                <?php endif; ?>
            </div>

            <button type="submit" class="submit-btn">
                <span id="submit-text">Create Account</span>
                <span id="submit-loading" class="loading" style="display: none;"></span>
            </button>
        </form>

        <div class="auth-footer">
            <p>Already have an account? <a href="login.php">Sign In</a></p>
            <p><a href="index.php">← Back to Homepage</a></p>
        </div>
    </div>

    <script>
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const button = field.nextElementSibling;
            const icon = button.querySelector('i');

            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        // Form submission loading state
        document.querySelector('.auth-form').addEventListener('submit', function () {
            const submitBtn = document.querySelector('.submit-btn');
            const submitText = document.getElementById('submit-text');
            const submitLoading = document.getElementById('submit-loading');

            submitBtn.disabled = true;
            submitText.style.display = 'none';
            submitLoading.style.display = 'inline-block';
        });

        // Real-time password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function () {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            const errorDiv = this.parentElement.nextElementSibling;

            if (confirmPassword && password !== confirmPassword) {
                this.classList.add('error');
                if (!errorDiv || !errorDiv.classList.contains('error-message')) {
                    const newErrorDiv = document.createElement('div');
                    newErrorDiv.className = 'error-message';
                    newErrorDiv.textContent = 'Passwords do not match';
                    this.parentElement.parentElement.appendChild(newErrorDiv);
                }
            } else {
                this.classList.remove('error');
                if (errorDiv && errorDiv.classList.contains('error-message')) {
                    errorDiv.remove();
                }
            }
        });
    </script>
</body>

</html>