<?php
session_start();
require_once 'db.php';

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : 0;
$message = '';
$error = '';

// Check for messages from cart operations
if (isset($_SESSION['cart_message'])) {
    $message = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']);
}

if (isset($_SESSION['cart_error'])) {
    $error = $_SESSION['cart_error'];
    unset($_SESSION['cart_error']);
}

// Get cart items if user is logged in
$cart_items = [];
$subtotal = 0;

if ($user_id) {
    $cart_query = "SELECT c.id as cart_id, c.quantity, p.id as product_id, p.name, p.description, p.price, p.image, p.stock_quantity 
                   FROM cart c 
                   JOIN products p ON c.product_id = p.id 
                   WHERE c.user_id = ?";
    $stmt = mysqli_prepare($conn, $cart_query);
    mysqli_stmt_bind_param($stmt, "i", $user_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    
    while ($item = mysqli_fetch_assoc($result)) {
        $cart_items[] = $item;
        $subtotal += $item['price'] * $item['quantity'];
    }
    mysqli_stmt_close($stmt);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - Shopping Cart</title>
    <style>
        /* Reusing styles from index.php - slightly adjusted for this page */
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
        
        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 2.5rem 1rem;
            flex: 1;
        }
        
        .cart-container {
            margin: 0;
        }
        
        .cart-title {
            margin-bottom: 2rem;
            padding-bottom: 0.75rem;
            color: #0F1B33;
            border-bottom: 2px solid #E53935;
            font-size: 2rem;
        }
        
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        
        .cart-table th {
            background-color: #f5f5f5;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }
        
        .cart-table td {
            padding: 1rem;
            border-bottom: 1px solid #ddd;
            vertical-align: middle;
        }
        
        .cart-item-image {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 4px;
        }
        
        .quantity-input {
            width: 60px;
            padding: 0.5rem;
            text-align: center;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .remove-btn {
            background: none;
            border: none;
            color: #E53935;
            cursor: pointer;
            font-size: 1.2rem;
        }
        
        .cart-summary {
            background-color: #f9f9f9;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 2rem;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 0.5rem 0;
        }
        
        .summary-total {
            font-weight: bold;
            font-size: 1.2rem;
            border-top: 2px solid #ddd;
            padding-top: 1rem;
            margin-top: 1rem;
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
        
        .user-info {
            color: white;
            white-space: nowrap;
        }
        
        .empty-cart {
            text-align: center;
            padding: 3rem;
        }
        
        .empty-cart h2 {
            margin-bottom: 1rem;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1rem;
        }
        
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .cart-actions {
            margin-top: 1.5rem;
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.75rem;
        }

        .cart-actions .checkout-btn {
            margin-left: auto;
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

            .cart-title {
                font-size: 1.75rem;
            }

            .cart-actions .checkout-btn {
                margin-left: 0;
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
                    <span class="user-info">Hi, <?php echo isset($_SESSION['username']) ? htmlspecialchars($_SESSION['username']) : 'User'; ?></span>
                <?php else: ?>
                    <a href="login.php">Login</a>
                    <a href="register.php">Register</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>

    <div class="container">
        <div class="cart-container">
            <h1 class="cart-title">Your Shopping Cart</h1>
            
            <?php if($message): ?>
                <div class="alert alert-success">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>
            
            <?php if($error): ?>
                <div class="alert alert-error">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <?php if(empty($cart_items)): ?>
                    <div class="empty-cart">
                        <h2>Your cart is empty</h2>
                        <p>Add some products to get started!</p>
                        <a href="products.php" class="btn">Browse Products</a>
                    </div>
                <?php else: ?>
                    <form action="update_cart.php" method="POST" id="cartForm">
                        <div class="table-wrap">
                        <table class="cart-table" id="cartTable">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th>Price</th>
                                    <th>Quantity</th>
                                    <th>Total</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="cartItems">
                                <?php foreach($cart_items as $item): ?>
                                    <?php $item_total = $item['price'] * $item['quantity']; ?>
                                    <tr>
                                        <td>
                                            <div style="display: flex; align-items: center;">
                                                <?php if(file_exists('images/products/' . $item['image'])): ?>
    <img src="images/products/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="cart-item-image">
<?php else: ?>
    <img src="images/placeholder.jpg" alt="Product Image" class="cart-item-image">
<?php endif; ?>
                                                <div style="margin-left: 1rem;">
                                                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                                    <p><?php echo htmlspecialchars(substr($item['description'], 0, 50)) . '...'; ?></p>
                                                </div>
                                            </div>
                                        </td>
                                        <td>Rs. <?php echo number_format($item['price'], 2); ?></td>
                                        <td>
                                            <input type="hidden" name="cart_ids[]" value="<?php echo $item['cart_id']; ?>">
                                            <input type="number" class="quantity-input" name="quantities[]" 
                                                   value="<?php echo $item['quantity']; ?>" min="1" max="<?php echo (int) $item['stock_quantity']; ?>">
                                        </td>
                                        <td class="item-total">Rs. <?php echo number_format($item_total, 2); ?></td>
                                        <td>
                                            <a href="remove_from_cart.php?id=<?php echo $item['cart_id']; ?>" 
                                               class="remove-btn" 
                                               onclick="return confirm('Remove this item from cart?')">×</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        </div>
                        
                        <div class="cart-summary">
                            <div class="summary-row">
                                <span>Subtotal:</span>
                                <span id="subtotal">Rs. <?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <div class="summary-row">
                                <span>Shipping:</span>
                                <span>Rs. 500.00</span>
                            </div>
                            <div class="summary-row summary-total">
                                <span>Total:</span>
                                <span id="total">Rs. <?php echo number_format($subtotal + 500.00, 2); ?></span>
                            </div>
                            
                            <div class="cart-actions">
                                <button type="submit" name="update_cart" class="btn btn-outline">Update Cart</button>
                                <a href="products.php" class="btn btn-outline">Continue Shopping</a>
                                <a href="checkout.php" class="btn checkout-btn">Proceed to Checkout</a>
                            </div>
                        </div>
                    </form>
                <?php endif; ?>
            <?php else: ?>
                <div class="empty-cart">
                    <h2>Your cart is empty</h2>
                    <p>Please login to add items to your cart.</p>
                    <a href="login.php" class="btn">Login</a>
                    <a href="products.php" class="btn btn-outline">Browse Products</a>
                </div>
            <?php endif; ?>
        </div>
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
        const quantityInputs = document.querySelectorAll('.quantity-input');
        
        function formatNumber(num) {
            // Format number with commas for thousands
            return 'Rs. ' + num.toFixed(2).replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        }
        
        function parseCurrency(text) {
            // Remove 'Rs. ', commas, and any whitespace, then convert to float
            return parseFloat(text.replace('Rs.', '').replace(/,/g, '').trim());
        }
        
        function updateTotals() {
            let subtotal = 0;
            
            quantityInputs.forEach(input => {
                const row = input.closest('tr');
                const priceText = row.querySelector('td:nth-child(2)').textContent;
                const price = parseCurrency(priceText);
                const quantity = parseInt(input.value);
                const total = price * quantity;
                
                // Find the item total cell in the same row
                const totalCell = row.querySelector('.item-total');
                totalCell.textContent = formatNumber(total);
                
                subtotal += total;
            });
            
            // Update subtotal and total
            document.getElementById('subtotal').textContent = formatNumber(subtotal);
            const shipping = 500.00;
            const total = subtotal + shipping;
            document.getElementById('total').textContent = formatNumber(total);
        }
        
        // Add event listeners to quantity inputs
        quantityInputs.forEach(input => {
            input.addEventListener('change', function() {
                if (this.value < 1) {
                    this.value = 1;
                }
                if (this.value > 10) {
                    this.value = 10;
                }
                updateTotals();
            });
            
            // Also update when user types
            input.addEventListener('input', function() {
                updateTotals();
            });
        });
        
        // Initialize totals
        updateTotals();
    });
</script>
</body>
</html>