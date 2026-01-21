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
$deal_id = $_GET['deal_id'] ?? '';

if (empty($deal_id)) {
    echo json_encode(['success' => false, 'error' => 'Missing deal ID']);
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

// Mark messages as seen
try {
    $stmt = $pdo->prepare("
        UPDATE messages 
        SET seen = 1 
        WHERE deal_id = ? AND sender_id != ?
    ");
    $stmt->execute([$deal_id, $user_id]);
} catch (PDOException $e) {
    // Handle error silently
}

// Get messages
try {
    $stmt = $pdo->prepare("
        SELECT m.*, u.first_name, u.last_name, u.user_type
        FROM messages m
        LEFT JOIN users u ON m.sender_id = u.id
        WHERE m.deal_id = ?
        ORDER BY m.created_at ASC
    ");
    $stmt->execute([$deal_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(['success' => true, 'messages' => $messages]);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'error' => 'Failed to load messages']);
}
?>