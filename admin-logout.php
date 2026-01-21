<?php
session_start();

// Clear all admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_name']);
unset($_SESSION['admin_email']);

// Destroy the session
session_destroy();

// Redirect to admin login page
header('Location: admin-login.php');
exit();
?>