<?php
// Database configuration
$host = "localhost";
$user = "root";
$password = "";
$dbname = "freelance_connect";

// Create connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $user, $password);
    // Set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
?>