<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if cart_id is provided
if (isset($_GET['id'])) {
    $cart_id = intval($_GET['id']);
    
    // Validate input
    if ($cart_id <= 0) {
        $_SESSION['cart_error'] = "Invalid cart item";
        header("Location: cart.php");
        exit();
    }
    
    // Check if cart item belongs to user
    $check_cart = "SELECT id FROM cart WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $check_cart);
    mysqli_stmt_bind_param($stmt, "ii", $cart_id, $user_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    
    if (mysqli_stmt_num_rows($stmt) === 0) {
        $_SESSION['cart_error'] = "Cart item not found";
        header("Location: cart.php");
        exit();
    }
    mysqli_stmt_close($stmt);
    
    // Remove cart item
    $delete_cart = "DELETE FROM cart WHERE id = ? AND user_id = ?";
    $stmt = mysqli_prepare($conn, $delete_cart);
    mysqli_stmt_bind_param($stmt, "ii", $cart_id, $user_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['cart_message'] = "Item removed from cart successfully!";
    } else {
        $_SESSION['cart_error'] = "Failed to remove item from cart";
    }
    mysqli_stmt_close($stmt);
    
    header("Location: cart.php");
    exit();
} else {
    // If no ID provided, redirect to cart
    header("Location: cart.php");
    exit();
}
?>