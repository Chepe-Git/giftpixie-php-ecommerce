<?php
session_start();

// Unset all admin session variables
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_logged_in']);
unset($_SESSION['admin_role']);

// Keep user session if they were also logged in as user
// But unset role to prevent automatic admin access
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    unset($_SESSION['role']);
}

// Redirect to admin login
header("Location: login.php");
exit();
?>