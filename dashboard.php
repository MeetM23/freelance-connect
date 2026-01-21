<?php
session_start();
require_once 'config/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Redirect based on user type
if ($_SESSION['user_type'] === 'freelancer') {
    header("Location: freelancer-dashboard.php");
} else {
    header("Location: client-dashboard.php");
}
exit();
?>