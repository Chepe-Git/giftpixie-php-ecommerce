<?php
// db.php - Simple MySQL connection for GiftPixie
// Update these values with your database credentials

$host = 'localhost';
$username = 'root';  // Change this to your MySQL username
$password = '';      // Change this to your MySQL password
$database = 'giftpixie_portfolio';

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set charset to utf8mb4
mysqli_set_charset($conn, "utf8mb4");

// Note: In a real application, this file would be in a protected directory
// and would use environment variables for credentials
?>