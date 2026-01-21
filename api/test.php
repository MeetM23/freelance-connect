<?php
require_once __DIR__ . '/../includes/api_utils.php';
require_once __DIR__ . '/../config/db.php';

handle_cors();

try {
    // Test DB connection
    $stmt = $pdo->query("SELECT 1");
    $result = $stmt->fetch();

    send_json([
        'status' => 'success',
        'message' => 'API is working and DB is connected',
        'data' => $result,
        'env' => [
            'host' => getenv('DB_HOST') ? 'set' : 'not set',
            'user' => getenv('DB_USER') ? 'set' : 'not set'
        ]
    ]);
} catch (Exception $e) {
    send_json(['error' => $e->getMessage()], 500);
}
?>