<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit();
}

$user_id = $_SESSION['user_id'];
$deal_id = $_POST['deal_id'] ?? '';
$message_text = trim($_POST['message'] ?? '');

if (empty($deal_id) || empty($message_text)) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit();
}

// Verify user has access to this deal
try {
    $stmt = $pdo->prepare("SELECT id FROM deals WHERE id = ? AND (client_id = ? OR freelancer_id = ?)");
    $stmt->execute([$deal_id, $user_id, $user_id]);

    if ($stmt->rowCount() == 0) {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit();
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Database error']);
    exit();
}

// Handle file upload
$file_path = '';
if (isset($_FILES['file']) && $_FILES['file']['error'] == 0) {
    $allowed_types = ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png', 'gif'];
    $file_extension = strtolower(pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION));

    if (!in_array($file_extension, $allowed_types)) {
        echo json_encode(['success' => false, 'error' => 'Invalid file type']);
        exit();
    }

    if ($_FILES['file']['size'] > 10 * 1024 * 1024) { // 10MB limit
        echo json_encode(['success' => false, 'error' => 'File too large (max 10MB)']);
        exit();
    }

    $upload_dir = 'uploads/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $filename = 'message_' . $deal_id . '_' . $user_id . '_' . time() . '.' . $file_extension;
    $upload_path = $upload_dir . $filename;

    if (!move_uploaded_file($_FILES['file']['tmp_name'], $upload_path)) {
        echo json_encode(['success' => false, 'error' => 'Failed to upload file']);
        exit();
    }

    $file_path = $upload_path;
}

// Insert message into database
try {
    $stmt = $pdo->prepare("
        INSERT INTO messages (deal_id, sender_id, message, file, seen, created_at) 
        VALUES (?, ?, ?, ?, 0, NOW())
    ");

    $stmt->execute([$deal_id, $user_id, $message_text, $file_path]);

    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to save message']);
}
?>