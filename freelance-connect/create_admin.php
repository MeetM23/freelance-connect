<?php
// Script to create/update admin account
// Run this once to set up the admin account

require_once 'config/db.php';

echo "<h2>Creating/Updating Admin Account</h2>";

try {
    // Check if admins table exists
    $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
    if ($stmt->rowCount() == 0) {
        echo "❌ Admins table does not exist. Please run admin_schema.sql first.<br>";
        exit;
    }

    echo "✅ Admins table exists<br>";

    // Check if admin already exists
    $stmt = $pdo->prepare("SELECT id FROM admins WHERE email = ?");
    $stmt->execute(['admin@freelanceconnect.com']);
    $existingAdmin = $stmt->fetch();

    // Create password hash
    $password = 'admin123';
    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    if ($existingAdmin) {
        // Update existing admin
        $stmt = $pdo->prepare("UPDATE admins SET password_hash = ?, first_name = ?, last_name = ?, username = ?, is_active = 1 WHERE email = ?");
        $stmt->execute([$passwordHash, 'Admin', 'User', 'admin', 'admin@freelanceconnect.com']);
        echo "✅ Admin account updated successfully<br>";
    } else {
        // Create new admin
        $stmt = $pdo->prepare("INSERT INTO admins (username, email, password_hash, first_name, last_name, is_active) VALUES (?, ?, ?, ?, ?, 1)");
        $stmt->execute(['admin', 'admin@freelanceconnect.com', $passwordHash, 'Admin', 'User']);
        echo "✅ Admin account created successfully<br>";
    }

    echo "<br><strong>Login Credentials:</strong><br>";
    echo "Email: admin@freelanceconnect.com<br>";
    echo "Password: admin123<br>";
    echo "Username: admin<br>";

    echo "<br><a href='admin-login.php'>Go to Admin Login</a>";

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage() . "<br>";
}
?>