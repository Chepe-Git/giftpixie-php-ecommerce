<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    // Validate input
    if ($product_id <= 0 || $quantity <= 0) {
        $_SESSION['cart_error'] = "Invalid product or quantity";
        header("Location: products.php");
        exit();
    }
    
    // Check if product exists and has enough stock
    $check_product = "SELECT id, name, price, stock_quantity FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $check_product);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$product) {
        $_SESSION['cart_error'] = "Product not found";
        header("Location: products.php");
        exit();
    }
    
    if ($product['stock_quantity'] < $quantity) {
        $_SESSION['cart_error'] = "Not enough stock available. Only " . $product['stock_quantity'] . " items left.";
        header("Location: product.php?id=" . $product_id);
        exit();
    }
    
    // Check if item already exists in cart
    $check_cart = "SELECT id, quantity FROM cart WHERE user_id = ? AND product_id = ?";
    $stmt = mysqli_prepare($conn, $check_cart);
    mysqli_stmt_bind_param($stmt, "ii", $user_id, $product_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_store_result($stmt);
    mysqli_stmt_bind_result($stmt, $cart_id, $existing_quantity);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    
    if ($cart_id) {
        // Update existing cart item
        $new_quantity = $existing_quantity + $quantity;
        if ($new_quantity > $product['stock_quantity']) {
            $_SESSION['cart_error'] = "Cannot add more than available stock";
            header("Location: product.php?id=" . $product_id);
            exit();
        }
        
        $update_cart = "UPDATE cart SET quantity = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $update_cart);
        mysqli_stmt_bind_param($stmt, "ii", $new_quantity, $cart_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['cart_message'] = "Cart updated successfully!";
        } else {
            $_SESSION['cart_error'] = "Failed to update cart";
        }
        mysqli_stmt_close($stmt);
    } else {
        // Add new item to cart
        $add_to_cart = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)";
        $stmt = mysqli_prepare($conn, $add_to_cart);
        mysqli_stmt_bind_param($stmt, "iii", $user_id, $product_id, $quantity);
        
        if (mysqli_stmt_execute($stmt)) {
            $_SESSION['cart_message'] = "Product added to cart successfully!";
        } else {
            $_SESSION['cart_error'] = "Failed to add product to cart";
        }
        mysqli_stmt_close($stmt);
    }
    
    // Redirect back to product page or cart
    if (isset($_POST['redirect_to_cart']) && $_POST['redirect_to_cart'] == '1') {
        header("Location: cart.php");
    } else {
        header("Location: product.php?id=" . $product_id);
    }
    exit();
} else {
    // If not a POST request, redirect to products
    header("Location: products.php");
    exit();
}
?>