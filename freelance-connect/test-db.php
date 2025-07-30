<?php
require_once 'config/db.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test basic connection
    echo "<p>✅ Database connection successful</p>";

    // Test if tables exist
    $tables = ['users', 'categories', 'projects', 'proposals'];

    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "<p>✅ Table '$table' exists</p>";
        } else {
            echo "<p>❌ Table '$table' does not exist</p>";
        }
    }

    // Test categories table
    $stmt = $pdo->query("SELECT * FROM categories");
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Categories in database:</h3>";
    echo "<ul>";
    foreach ($categories as $category) {
        echo "<li>ID: {$category['id']} - {$category['name']}</li>";
    }
    echo "</ul>";

    // Test users table
    $stmt = $pdo->query("SELECT COUNT(*) as count FROM users");
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    echo "<p>✅ Users table has {$result['count']} records</p>";

    // Test projects table structure
    $stmt = $pdo->query("DESCRIBE projects");
    $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo "<h3>Projects table structure:</h3>";
    echo "<ul>";
    foreach ($columns as $column) {
        echo "<li>{$column['Field']} - {$column['Type']} - Null: {$column['Null']}</li>";
    }
    echo "</ul>";

} catch (PDOException $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}
?>