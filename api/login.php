<?php
require_once __DIR__ . '/../includes/api_utils.php';
require_once __DIR__ . '/../config/db.php';

handle_cors();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(['error' => 'Invalid request method. Only POST is supported.'], 405);
}

$input = json_decode(file_get_contents('php://input'), true);

$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';

if (empty($email) || empty($password)) {
    send_json(['error' => 'Email and password are required.'], 400);
}

try {
    $stmt = $pdo->prepare("SELECT id, email, password_hash, first_name, last_name, user_type FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password_hash'])) {
        // Remove sensitive data
        unset($user['password_hash']);
        
        // In a real app, you would generate a JWT token here.
        // For now, we return the user object which the frontend can store.
        send_json([
            'status' => 'success',
            'message' => 'Login successful',
            'user' => $user
        ]);
    } else {
        send_json(['error' => 'Invalid email or password.'], 401);
    }

} catch (PDOException $e) {
    send_json(['error' => 'Database error during login: ' . $e->getMessage()], 500);
}
?>