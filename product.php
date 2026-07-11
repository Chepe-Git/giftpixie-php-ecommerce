<?php
session_start();
require_once 'db.php';

// Get product ID from URL
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 1;

// Fetch product details
$product_query = "SELECT p.*, c.name as category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.id 
                  WHERE p.id = ?";
$stmt = mysqli_prepare($conn, $product_query);
mysqli_stmt_bind_param($stmt, "i", $product_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result); // CORRECTED: Use $result, not $stmt
mysqli_stmt_close($stmt);

if (!$product) {
    // Product not found, redirect to products page
    header("Location: products.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - <?php echo htmlspecialchars($product['name']); ?></title>
    <style>
        /* Reusing styles from index.php */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: white;
            color: #0F1B33;
        }
        
        .header {
            background-color: #0F1B33;
            color: white;
            padding: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        
        .nav a {
            color: white;
            text-decoration: none;
            margin-left: 1.5rem;
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
            margin: 0.5rem 0.5rem 0.5rem 0;
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            margin: 3rem 0;
        }
        
        .product-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 8px;
            background-color: #f5f5f5;
        }
        
        .product-title {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #0F1B33;
        }
        
        .product-price {
            color: #E53935;
            font-size: 1.8rem;
            font-weight: bold;
            margin: 1rem 0;
        }
        
        .product-description {
            line-height: 1.6;
            margin: 1.5rem 0;
        }
        
        .quantity-selector {
            display: flex;
            align-items: center;
            margin: 1.5rem 0;
        }
        
        .quantity-btn {
            background-color: #f5f5f5;
            border: 1px solid #ddd;
            width: 40px;
            height: 40px;
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        .quantity-input {
            width: 60px;
            height: 40px;
            text-align: center;
            border: 1px solid #ddd;
            margin: 0 0.5rem;
        }
        
        .footer {
            background-color: #0F1B33;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
        }
        
        .user-info {
            color: white;
            margin-left: 1rem;
        }
        
        .product-category {
            background-color: #f0f0f0;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            display: inline-block;
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
    </style>
</head>
<body>
    <header class="header">
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
    </header>

    <div class="container">
        <?php 
        // Display messages from cart operations
        if (isset($_SESSION['cart_message'])) {
            echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['cart_message']) . '</div>';
            unset($_SESSION['cart_message']);
        }
        if (isset($_SESSION['cart_error'])) {
            echo '<div class="alert alert-error">' . htmlspecialchars($_SESSION['cart_error']) . '</div>';
            unset($_SESSION['cart_error']);
        }
        ?>
        
        <div class="product-detail">
            <div>
                <?php if(file_exists('images/products/' . $product['image'])): ?>
    <img src="images/products/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
<?php else: ?>
    <img src="images/placeholder.jpg" alt="Product Image" class="product-image">
<?php endif; ?>
            </div>
            
            <div>
                <span class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></span>
                <h1 class="product-title"><?php echo htmlspecialchars($product['name']); ?></h1>
                <p class="product-price">Rs.<?php echo number_format($product['price'], 2); ?></p>
                
                <p class="product-description">
                    <?php echo htmlspecialchars($product['description']); ?>
                </p>
                
                <form action="add_to_cart.php" method="POST" id="addToCartForm">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    
                    <div class="quantity-selector">
                        <button type="button" class="quantity-btn" id="decreaseQty">-</button>
                        <input type="number" class="quantity-input" id="quantity" name="quantity" value="1" min="1" max="<?php echo min($product['stock_quantity'], 10); ?>">
                        <button type="button" class="quantity-btn" id="increaseQty">+</button>
                        <span style="margin-left: 1rem;">Stock: <span id="stockQuantity"><?php echo $product['stock_quantity']; ?></span> available</span>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn" id="addToCartBtn">Add to Cart</button>
                        <input type="hidden" name="redirect_to_cart" value="0" id="redirectToCart">
                        <button type="submit" class="btn btn-outline" onclick="document.getElementById('redirectToCart').value='1';">Add & View Cart</button>
                        <a href="products.php" class="btn btn-outline">Continue Shopping</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <footer class="footer">
        <p>&copy; <?php echo date('Y'); ?> GiftPixie. All rights reserved.</p>
        <p>
            <a href="contact_about.php" style="color: white; margin: 0 1rem;">Contact & About</a>
            <a href="privacy_terms.php" style="color: white; margin: 0 1rem;">Privacy & Terms</a>
        </p>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const quantityInput = document.getElementById('quantity');
            const decreaseBtn = document.getElementById('decreaseQty');
            const increaseBtn = document.getElementById('increaseQty');
            const stockQuantity = parseInt(document.getElementById('stockQuantity').textContent);
            
            decreaseBtn.addEventListener('click', function() {
                let current = parseInt(quantityInput.value);
                if (current > 1) {
                    quantityInput.value = current - 1;
                }
            });
            
            increaseBtn.addEventListener('click', function() {
                let current = parseInt(quantityInput.value);
                if (current < stockQuantity && current < 10) {
                    quantityInput.value = current + 1;
                } else if (current >= stockQuantity) {
                    alert('Cannot exceed available stock');
                } else {
                    alert('Maximum order quantity is 10');
                }
            });
            
            quantityInput.addEventListener('change', function() {
                let value = parseInt(this.value);
                
                if (isNaN(value) || value < 1) {
                    this.value = 1;
                } else if (value > stockQuantity) {
                    this.value = stockQuantity;
                    alert('Cannot exceed available stock');
                } else if (value > 10) {
                    this.value = 10;
                    alert('Maximum order quantity is 10');
                }
            });
            
            const form = document.getElementById('addToCartForm');
            form.addEventListener('submit', function(e) {
                const quantity = parseInt(quantityInput.value);
                
                if (quantity < 1) {
                    e.preventDefault();
                    alert('Please select a valid quantity');
                    return false;
                }
                
                <?php if(!isset($_SESSION['user_id'])): ?>
                    e.preventDefault();
                    alert('Please login to add items to cart');
                    window.location.href = 'login.php?redirect=' + encodeURIComponent(window.location.href);
                    return false;
                <?php endif; ?>
                
                return true;
            });
        });
    </script>
</body>
</html>