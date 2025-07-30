<?php
session_start();
require_once 'config/db.php';

$errors = [];

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    $redirect_url = $_SESSION['user_type'] === 'freelancer' ? 'freelancer-dashboard.php' : 'client-dashboard.php';
    header("Location: $redirect_url");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Validation
    if (empty($email)) {
        $errors['email'] = 'Email is required';
    }

    if (empty($password)) {
        $errors['password'] = 'Password is required';
    }

    // If no validation errors, attempt login
    if (empty($errors)) {
        try {
            // Get user by email
            $stmt = $pdo->prepare("SELECT id, email, password_hash, first_name, last_name, user_type FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password_hash'])) {
                // Login successful
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_name'] = $user['first_name'] . ' ' . $user['last_name'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['login_time'] = time();

                // Redirect based on user type
                $redirect_url = $user['user_type'] === 'freelancer' ? 'freelancer-dashboard.php' : 'client-dashboard.php';
                header("Location: $redirect_url");
                exit();
            } else {
                $errors['general'] = 'Invalid email or password';
            }
        } catch (PDOException $e) {
            $errors['general'] = 'Login failed. Please try again.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Freelance Connect</title>
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
            <h1 class="auth-title">Welcome Back</h1>
            <p class="auth-subtitle">Sign in to your account</p>
        </div>

        <?php if (!empty($errors['general'])): ?>
            <div class="error-message"><?php echo htmlspecialchars($errors['general']); ?></div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="">
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
                        placeholder="Enter your password" required>
                    <button type="button" class="password-toggle-btn" onclick="togglePassword('password')">
                        <i class="fas fa-eye"></i>
                    </button>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <div class="error-message"><?php echo htmlspecialchars($errors['password']); ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <label style="margin-bottom: 0;">
                        <input type="checkbox" name="remember" style="margin-right: 0.5rem;">
                        Remember me
                    </label>
                    <a href="forgot-password.php" style="color: #14a800; text-decoration: none; font-size: 0.9rem;">
                        Forgot Password?
                    </a>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <span id="submit-text">Sign In</span>
                <span id="submit-loading" class="loading" style="display: none;"></span>
            </button>
        </form>

        <div class="auth-footer">
            <p>Don't have an account? <a href="register.php">Sign Up</a></p>
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

        // Auto-focus on email field
        document.getElementById('email').focus();
    </script>
</body>

</html>