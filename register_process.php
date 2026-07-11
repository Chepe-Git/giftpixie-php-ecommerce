<?php
session_start();
require_once 'db.php';

// Initialize variables
$errors = [];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $phone = trim($_POST['phone']);
    
    // Basic validation
    if (empty($username)) {
        $errors[] = "Username is required";
    }
    
    if (empty($email)) {
        $errors[] = "Email is required";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Invalid email format";
    }
    
    if (empty($password)) {
        $errors[] = "Password is required";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters";
    }
    
    if ($password !== $confirm_password) {
        $errors[] = "Passwords do not match";
    }
    
    if (empty($first_name)) {
        $errors[] = "First name is required";
    }
    
    if (empty($last_name)) {
        $errors[] = "Last name is required";
    }
    
    // Check if username already exists
    if (empty($errors)) {
        $check_username = "SELECT id FROM users WHERE username = ?";
        $stmt = mysqli_prepare($conn, $check_username);
        mysqli_stmt_bind_param($stmt, "s", $username);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "Username already exists";
        }
        mysqli_stmt_close($stmt);
    }
    
    // Check if email already exists
    if (empty($errors)) {
        $check_email = "SELECT id FROM users WHERE email = ?";
        $stmt = mysqli_prepare($conn, $check_email);
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $errors[] = "Email already registered";
        }
        mysqli_stmt_close($stmt);
    }
    
    // If no errors, insert user into database
    if (empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $insert_user = "INSERT INTO users (username, email, password, first_name, last_name, phone, role) 
                       VALUES (?, ?, ?, ?, ?, ?, 'customer')";
        
        $stmt = mysqli_prepare($conn, $insert_user);
        mysqli_stmt_bind_param($stmt, "ssssss", $username, $email, $hashed_password, $first_name, $last_name, $phone);
        
        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            // Registration successful - redirect to login
            header("Location: register.php?success=1");
            exit();
        } else {
            $errors[] = "Registration failed. Please try again.";
        }
    }
    
    // If there were errors, redirect back to registration form with error messages
    if (!empty($errors)) {
        $error_string = urlencode(implode(", ", $errors));
        header("Location: register.php?error=" . $error_string);
        exit();
    }
} else {
    // If not a POST request, redirect to registration form
    header("Location: register.php");
    exit();
}
?>