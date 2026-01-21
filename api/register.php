<?php
require_once __DIR__ . '/../includes/api_utils.php';
require_once __DIR__ . '/../config/db.php';

handle_cors();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['error' => 'Invalid request method. Only POST is supported.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

$errors = [];
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$user_type = $input['user_type'] ?? '';

// --- Validation ---
if (empty($name)) $errors['name'] = 'Name is required';
if (empty($email)) $errors['email'] = 'Email is required';
elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors['email'] = 'Invalid email format';
if (empty($password)) $errors['password'] = 'Password is required';
elseif (strlen($password) < 6) $errors['password'] = 'Password must be at least 6 characters';
if (empty($user_type)) $errors['user_type'] = 'User type is required';
elseif (!in_array($user_type, ['freelancer', 'client'])) $errors['user_type'] = 'Invalid user type';

if (!empty($errors)) {
    send_json(['error' => 'Validation failed', 'details' => $errors], 422);
}

try {
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        send_json(['error' => 'An account with this email already exists.'], 409);
    }

    // Hash password
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // Split name
    $name_parts = explode(' ', $name, 2);
    $first_name = $name_parts[0];
    $last_name = $name_parts[1] ?? '';
    $username = strtolower(explode('@', $email)[0]);

    // Insert user
    $stmt = $pdo->prepare(
        "INSERT INTO users (username, email, password_hash, first_name, last_name, user_type) VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$username, $email, $password_hash, $first_name, $last_name, $user_type]);

    $user_id = $pdo->lastInsertId();

    send_json([
        'status' => 'success',
        'message' => 'Registration successful.',
        'user_id' => $user_id
    ], 201);
} catch (PDOException $e) {
    send_json(['error' => 'Database error during registration: ' . $e->getMessage()], 500);
}
