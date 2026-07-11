<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';
$shipping_cost = 500.00;

// Get cart items for the user
$cart_query = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.price, p.image, p.stock_quantity 
               FROM cart c 
               JOIN products p ON c.product_id = p.id 
               WHERE c.user_id = ?";
$stmt = mysqli_prepare($conn, $cart_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$cart_result = mysqli_stmt_get_result($stmt);

// Check if cart is empty
if (mysqli_num_rows($cart_result) === 0) {
    $_SESSION['cart_error'] = "Your cart is empty";
    header("Location: cart.php");
    exit();
}

// Handle checkout form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = trim($_POST['shipping_address']);
    
    if (empty($shipping_address)) {
        $error_message = "Please enter a shipping address";
    } else {
        // Start transaction
        mysqli_begin_transaction($conn);
        
        try {
            // Calculate total
            $total_amount = 0;
            mysqli_data_seek($cart_result, 0); // Reset result pointer
            
            while ($item = mysqli_fetch_assoc($cart_result)) {
                // Check stock again
                if ($item['quantity'] > $item['stock_quantity']) {
                    throw new Exception("Product '" . $item['name'] . "' only has " . $item['stock_quantity'] . " items in stock.");
                }
                
                $total_amount += $item['price'] * $item['quantity'];
            }
            
            // Add shipping cost
            $total_amount += $shipping_cost;
            
            // Create order
            $order_query = "INSERT INTO orders (user_id, total_amount, shipping_address) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $order_query);
            mysqli_stmt_bind_param($stmt, "ids", $user_id, $total_amount, $shipping_address);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to create order");
            }
            
            $order_id = mysqli_insert_id($conn);
            mysqli_stmt_close($stmt);
            
            // Reset result pointer again
            mysqli_data_seek($cart_result, 0);
            
            // Create order items and update product stock
            while ($item = mysqli_fetch_assoc($cart_result)) {
                // Add order item
                $order_item_query = "INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)";
                $stmt = mysqli_prepare($conn, $order_item_query);
                mysqli_stmt_bind_param($stmt, "iiid", $order_id, $item['product_id'], $item['quantity'], $item['price']);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to add order item");
                }
                mysqli_stmt_close($stmt);
                
                // Update product stock
                $update_stock_query = "UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?";
                $stmt = mysqli_prepare($conn, $update_stock_query);
                mysqli_stmt_bind_param($stmt, "ii", $item['quantity'], $item['product_id']);
                
                if (!mysqli_stmt_execute($stmt)) {
                    throw new Exception("Failed to update product stock");
                }
                mysqli_stmt_close($stmt);
            }
            
            // Clear cart
            $clear_cart_query = "DELETE FROM cart WHERE user_id = ?";
            $stmt = mysqli_prepare($conn, $clear_cart_query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            
            if (!mysqli_stmt_execute($stmt)) {
                throw new Exception("Failed to clear cart");
            }
            mysqli_stmt_close($stmt);
            
            // Commit transaction
            mysqli_commit($conn);
            
            $success_message = "Order placed successfully! Your order ID is: " . $order_id;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            mysqli_rollback($conn);
            $error_message = $e->getMessage();
        }
    }
}

// Reset result pointer for display
mysqli_data_seek($cart_result, 0);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - Checkout</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        html,
        body {
            min-height: 100%;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            font-family: Arial, sans-serif;
            background-color: white;
            color: #0F1B33;
        }
        
        .header {
            background-color: #0F1B33;
            color: white;
        }

        .header-inner {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
        }
        
        .logo {
            font-size: 1.5rem;
            font-weight: bold;
            color: white;
            text-decoration: none;
        }
        
        .logo span {
            color: #E53935;
        }
        
        .nav {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .nav a {
            color: white;
            text-decoration: none;
        }
        
        .nav a:hover {
            color: #E53935;
        }
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.5rem 1rem;
            flex: 1;
        }
        
        .checkout-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
        }
        
        .page-title {
            margin-bottom: 2rem;
            padding-bottom: 0.75rem;
            color: #0F1B33;
            border-bottom: 2px solid #E53935;
            font-size: 2rem;
        }

        .section-title {
            margin-bottom: 1.5rem;
            color: #0F1B33;
            border-bottom: 2px solid #E53935;
            padding-bottom: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            font-family: Arial, sans-serif;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .btn {
            display: inline-block;
            background-color: #E53935;
            color: white;
            padding: 0.75rem 1.5rem;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 1rem;
            margin-top: 1rem;
        }
        
        .btn:hover {
            background-color: #c62828;
        }
        
        .btn-outline {
            background-color: white;
            color: #0F1B33;
            border: 2px solid #0F1B33;
        }
        
        .btn-outline:hover {
            background-color: #0F1B33;
            color: white;
        }
        
        .order-summary {
            background-color: #f9f9f9;
            padding: 1.5rem;
            border-radius: 8px;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .order-total {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 2px solid #ddd;
            font-weight: bold;
            font-size: 1.2rem;
        }
        
        .footer {
            margin-top: auto;
            background-color: #0F1B33;
            color: white;
            text-align: center;
            padding: 2rem 1rem;
        }

        .footer-inner {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
        }

        .footer p + p {
            margin-top: 0.35rem;
        }

        .footer a {
            color: white;
            margin: 0 0.75rem;
        }

        .footer a:hover {
            color: #E53935;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .alert-error {
            background-color: #ffebee;
            color: #c62828;
            border: 1px solid #ffcdd2;
        }
        
        .alert-success {
            background-color: #e8f5e9;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .user-info {
            color: white;
            white-space: nowrap;
        }

        .success-actions {
            margin-top: 1rem;
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .success-actions .btn {
            margin-top: 0;
        }

        @media (max-width: 768px) {
            .header-inner {
                flex-direction: column;
                align-items: center;
            }

            .nav {
                justify-content: center;
                gap: 0.75rem 1rem;
            }

            .container {
                padding: 2rem 1rem;
            }

            .page-title {
                font-size: 1.75rem;
            }

            .checkout-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-inner">
            <a href="index.php" class="logo">Gift<span>Pixie</span></a>
            <nav class="nav">
                <a href="index.php">Home</a>
                <a href="products.php">Products</a>
                <a href="cart.php">Cart</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="profile.php">Profile</a>
                    <a href="orders.php">Orders</a>
                    <a href="logout.php">Logout</a>
                    <span class="user-info">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">Checkout</h1>
        
        <?php if($error_message): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if($success_message): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
                <div class="success-actions">
                    <a href="orders.php" class="btn">View My Orders</a>
                    <a href="products.php" class="btn btn-outline">Continue Shopping</a>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if(!$success_message): ?>
            <div class="checkout-container">
                <div>
                    <h2 class="section-title">Shipping Information</h2>
                    <form action="checkout.php" method="POST">
                        <div class="form-group">
                            <label for="full_name">Full Name</label>
                            <input type="text" id="full_name" 
                                   value="<?php echo htmlspecialchars($_SESSION['first_name'] . ' ' . $_SESSION['last_name']); ?>" 
                                   readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" id="email" value="<?php echo htmlspecialchars($_SESSION['email']); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="shipping_address">Shipping Address *</label>
                            <textarea id="shipping_address" name="shipping_address" required 
                                      placeholder="Enter your complete shipping address including street, city, state, and zip code"></textarea>
                        </div>
                        
                        <button type="submit" class="btn">Place Order</button>
                        <a href="cart.php" class="btn btn-outline">Return to Cart</a>
                    </form>
                </div>
                
                <div>
                    <h2 class="section-title">Order Summary</h2>
                    <div class="order-summary">
                        <?php
                        $subtotal = 0;
                        mysqli_data_seek($cart_result, 0);
                        
                        while ($item = mysqli_fetch_assoc($cart_result)):
                            $item_total = $item['price'] * $item['quantity'];
                            $subtotal += $item_total;
                        ?>
                            <div class="order-item">
                                <div>
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p>Quantity: <?php echo $item['quantity']; ?> × Rs. <?php echo number_format($item['price'], 2); ?></p>
                                </div>
                                <div>
                                    <p>Rs. <?php echo number_format($item_total, 2); ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        
                        <div class="order-item">
                            <div>Subtotal</div>
                            <div>Rs. <?php echo number_format($subtotal, 2); ?></div>
                        </div>
                        
                        <div class="order-item">
                            <div>Shipping</div>
                            <div>Rs. <?php echo number_format($shipping_cost, 2); ?></div>
                        </div>
                        
                        <div class="order-total">
                            <div>Total</div>
                            <div>Rs. <?php echo number_format($subtotal + $shipping_cost, 2); ?></div>
                        </div>
                    </div>
                    
                    <div style="margin-top: 2rem; background-color: #f0f0f0; padding: 1rem; border-radius: 8px;">
                        <h3>Important Notes</h3>
                        <ul style="margin-top: 0.5rem; padding-left: 1.5rem;">
                            <li>All orders are processed within 1-2 business days</li>
                            <li>Shipping typically takes 3-5 business days</li>
                            <li>You can view order status and tracking details from My Orders</li>
                            <li>For any questions, contact support@giftpixie.com</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <footer class="footer">
        <div class="footer-inner">
            <p>&copy; <?php echo date('Y'); ?> GiftPixie. All rights reserved.</p>
            <p>
                <a href="contact_about.php">Contact & About</a>
                <a href="privacy_terms.php">Privacy & Terms</a>
            </p>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            
            if (form) {
                form.addEventListener('submit', function(e) {
                    const address = document.getElementById('shipping_address').value.trim();
                    
                    if (!address) {
                        e.preventDefault();
                        alert('Please enter a shipping address');
                        return false;
                    }
                    
                    if (address.length < 10) {
                        e.preventDefault();
                        alert('Please enter a complete shipping address');
                        return false;
                    }
                    
                    return true;
                });
            }
        });
    </script>
</body>
</html>
<?php
mysqli_free_result($cart_result);
?>