<?php
// Debug script for admin login issues
// Run this to check what's wrong

echo "<h2>Admin Login Debug</h2>";

// 1. Check database connection
echo "<h3>1. Database Connection Test</h3>";
try {
    require_once 'config/db.php';
    echo "✅ Database connection successful<br>";
} catch (Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "<br>";
    exit;
}

// 2. Check if admins table exists
echo "<h3>2. Check if admins table exists</h3>";
try {
    $stmt = $pdo->query("SHOW TABLES LIKE 'admins'");
    $tableExists = $stmt->rowCount() > 0;

    if ($tableExists) {
        echo "✅ Admins table exists<br>";
    } else {
        echo "❌ Admins table does not exist<br>";
        echo "You need to run the admin_schema.sql file first<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error checking table: " . $e->getMessage() . "<br>";
}

// 3. Check admin records
echo "<h3>3. Check admin records</h3>";
try {
    $stmt = $pdo->query("SELECT id, username, email, first_name, last_name, is_active FROM admins");
    $admins = $stmt->fetchAll();

    if (count($admins) > 0) {
        echo "✅ Found " . count($admins) . " admin record(s):<br>";
        foreach ($admins as $admin) {
            echo "- ID: {$admin['id']}, Username: {$admin['username']}, Email: {$admin['email']}, Active: " . ($admin['is_active'] ? 'Yes' : 'No') . "<br>";
        }
    } else {
        echo "❌ No admin records found<br>";
        echo "You need to insert an admin user<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error checking admin records: " . $e->getMessage() . "<br>";
}

// 4. Test password verification
echo "<h3>4. Test password verification</h3>";
try {
    $stmt = $pdo->prepare("SELECT password_hash FROM admins WHERE email = ?");
    $stmt->execute(['admin@freelanceconnect.com']);
    $admin = $stmt->fetch();

    if ($admin) {
        $testPassword = 'admin123';
        $hash = $admin['password_hash'];

        echo "Found admin with hash: " . substr($hash, 0, 20) . "...<br>";

        if (password_verify($testPassword, $hash)) {
            echo "✅ Password verification successful<br>";
        } else {
            echo "❌ Password verification failed<br>";
            echo "The password hash is incorrect<br>";
        }
    } else {
        echo "❌ No admin found with email: admin@freelanceconnect.com<br>";
    }
} catch (PDOException $e) {
    echo "❌ Error testing password: " . $e->getMessage() . "<br>";
}

// 5. Generate new password hash
echo "<h3>5. Generate new password hash</h3>";
$newPassword = 'admin123';
$newHash = password_hash($newPassword, PASSWORD_DEFAULT);
echo "New hash for 'admin123': " . $newHash . "<br>";

// 6. SQL to fix the issue
echo "<h3>6. SQL to fix the issue</h3>";
echo "<pre>";
echo "-- Run this SQL in your database:\n\n";
echo "-- First, check if admin exists\n";
echo "SELECT * FROM admins WHERE email = 'admin@freelanceconnect.com';\n\n";
echo "-- If admin exists, update password\n";
echo "UPDATE admins SET password_hash = '" . $newHash . "' WHERE email = 'admin@freelanceconnect.com';\n\n";
echo "-- If no admin exists, create one\n";
echo "INSERT INTO admins (username, email, password_hash, first_name, last_name) VALUES ";
echo "('admin', 'admin@freelanceconnect.com', '" . $newHash . "', 'Admin', 'User');\n";
echo "</pre>";

echo "<h3>7. Test Login</h3>";
echo "<form method='POST' action='admin-login.php'>";
echo "<input type='email' name='email' value='admin@freelanceconnect.com' placeholder='Email'><br>";
echo "<input type='password' name='password' value='admin123' placeholder='Password'><br>";
echo "<button type='submit'>Test Login</button>";
echo "</form>";
?>