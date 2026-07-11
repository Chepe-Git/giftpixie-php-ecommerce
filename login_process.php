<?php
session_start();
require_once 'db.php';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    
    // Basic validation
    if (empty($username) || empty($password)) {
        header("Location: login.php?error=Please fill in all fields");
        exit();
    }
    
    // Check if user exists with username or email
    $query = "SELECT id, username, email, password, first_name, last_name, role FROM users 
              WHERE username = ? OR email = ?";
    
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    if ($row = mysqli_fetch_assoc($result)) {
        if (password_verify($password, $row['password'])) {
            session_regenerate_id(true);

            // Login successful - set session variables
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['email'] = $row['email'];
            $_SESSION['first_name'] = $row['first_name'];
            $_SESSION['last_name'] = $row['last_name'];
            $_SESSION['role'] = $row['role'];
            
            // Redirect based on role
            if ($row['role'] === 'admin') {
                $_SESSION['admin_id'] = $row['id'];
                $_SESSION['admin_username'] = $row['username'];
                $_SESSION['admin_logged_in'] = true;
                $_SESSION['admin_role'] = 'admin';
                header("Location: admin/dashboard.php");
            } else {
                header("Location: index.php");
            }
            exit();
        } else {
            header("Location: login.php?error=Invalid username or password");
            exit();
        }
    } else {
        header("Location: login.php?error=Invalid username or password");
        exit();
    }
    
    mysqli_stmt_close($stmt);
} else {
    // If not a POST request, redirect to login form
    header("Location: login.php");
    exit();
}
?>