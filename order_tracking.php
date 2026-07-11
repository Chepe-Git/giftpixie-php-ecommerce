<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Get order details
$order_query = "SELECT o.*, u.first_name, u.last_name, u.email 
                FROM orders o 
                JOIN users u ON o.user_id = u.id 
                WHERE o.id = ? AND o.user_id = ?";
$stmt = mysqli_prepare($conn, $order_query);
mysqli_stmt_bind_param($stmt, "ii", $order_id, $user_id);
mysqli_stmt_execute($stmt);
$order_result = mysqli_stmt_get_result($stmt);
$order = mysqli_fetch_assoc($order_result); // CORRECTED: Use $order_result, not $stmt
mysqli_stmt_close($stmt);

// Get order items
$items_query = "SELECT oi.*, p.name, p.image 
                FROM order_items oi 
                JOIN products p ON oi.product_id = p.id 
                WHERE oi.order_id = ?";
$stmt = mysqli_prepare($conn, $items_query);
mysqli_stmt_bind_param($stmt, "i", $order_id);
mysqli_stmt_execute($stmt);
$items_result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - Order Tracking</title>
    <style>
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
        
        .container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .page-title {
            margin-bottom: 2rem;
            color: #0F1B33;
        }
        
        .tracking-container {
            background-color: #f9f9f9;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .order-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .info-card {
            background-color: white;
            padding: 1rem;
            border-radius: 4px;
            border-left: 4px solid #E53935;
        }
        
        .info-card h3 {
            color: #0F1B33;
            margin-bottom: 0.5rem;
            font-size: 1rem;
        }
        
        .tracking-steps {
            margin: 2rem 0;
        }
        
        .step {
            display: flex;
            align-items: center;
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #eee;
        }
        
        .step:last-child {
            border-bottom: none;
        }
        
        .step-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 1rem;
            font-weight: bold;
            color: white;
        }
        
        .step.active .step-icon {
            background-color: #E53935;
        }
        
        .step.completed .step-icon {
            background-color: #4CAF50;
        }
        
        .step-content h4 {
            margin-bottom: 0.25rem;
        }
        
        .step-content p {
            color: #666;
            font-size: 0.9rem;
        }
        
        .order-items {
            margin-top: 2rem;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding: 1rem;
            background-color: white;
            border-radius: 4px;
        }
        
        .item-info {
            display: flex;
            align-items: center;
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            margin-right: 1rem;
            background-color: #f5f5f5;
        }
        
        .footer {
            background-color: #0F1B33;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
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
        
        .user-info {
            color: white;
            margin-left: 1rem;
        }
        
        .not-found {
            text-align: center;
            padding: 3rem;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        
        .not-found h3 {
            margin-bottom: 1rem;
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
                <span class="user-info">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
            <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php">Register</a>
            <?php endif; ?>
        </nav>
    </header>

    <div class="container">
        <h1 class="page-title">Order Tracking</h1>
        
        <?php if(!$order): ?>
            <div class="not-found">
                <h3>Order Not Found</h3>
                <p>The order you're looking for doesn't exist or you don't have permission to view it.</p>
                <a href="orders.php" class="btn" style="margin-top: 1rem;">View All Orders</a>
            </div>
        <?php else: ?>
            <div class="tracking-container">
                <div class="order-info">
                    <div class="info-card">
                        <h3>Order Number</h3>
                        <p>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></p>
                    </div>
                    
                    <div class="info-card">
                        <h3>Order Date</h3>
                        <p><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
                    </div>
                    
                    <div class="info-card">
                        <h3>Total Amount</h3>
                        <p>Rs.<?php echo number_format($order['total_amount'], 2); ?></p>
                    </div>
                    
                    <div class="info-card">
                        <h3>Tracking Number</h3>
                        <p><?php echo $order['tracking_number'] ? $order['tracking_number'] : 'Not assigned yet'; ?></p>
                    </div>
                </div>
                
                <div class="tracking-steps">
                    <h3>Order Status: <span style="color: #E53935;"><?php echo ucfirst($order['status']); ?></span></h3>
                    
                    <div class="step <?php echo in_array($order['status'], ['pending', 'processing', 'shipped', 'delivered', 'cancelled']) ? 'completed' : ''; ?>">
                        <div class="step-icon">1</div>
                        <div class="step-content">
                            <h4>Order Placed</h4>
                            <p>Order received and confirmed</p>
                            <p><small><?php echo date('M j, Y', strtotime($order['order_date'])); ?></small></p>
                        </div>
                    </div>
                    
                    <div class="step <?php echo in_array($order['status'], ['processing', 'shipped', 'delivered']) ? 'completed' : ''; ?> 
                                   <?php echo $order['status'] == 'processing' ? 'active' : ''; ?>">
                        <div class="step-icon">2</div>
                        <div class="step-content">
                            <h4>Processing</h4>
                            <p>Order is being prepared for shipping</p>
                        </div>
                    </div>
                    
                    <div class="step <?php echo in_array($order['status'], ['shipped', 'delivered']) ? 'completed' : ''; ?> 
                                   <?php echo $order['status'] == 'shipped' ? 'active' : ''; ?>">
                        <div class="step-icon">3</div>
                        <div class="step-content">
                            <h4>Shipped</h4>
                            <p>Order has been shipped</p>
                            <?php if($order['tracking_number']): ?>
                                <p><small>Tracking: <?php echo $order['tracking_number']; ?></small></p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="step <?php echo $order['status'] == 'delivered' ? 'completed' : ''; ?> 
                                   <?php echo $order['status'] == 'delivered' ? 'active' : ''; ?>">
                        <div class="step-icon">4</div>
                        <div class="step-content">
                            <h4>Delivered</h4>
                            <p>Order delivered to customer</p>
                        </div>
                    </div>
                </div>
                
                <div class="order-items">
                    <h3>Order Items</h3>
                    <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                        <div class="order-item">
                            <div class="item-info">
                                <?php if(file_exists('images/products/' . $item['image'])): ?>
    <img src="images/products/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
<?php else: ?>
    <img src="images/placeholder.jpg" alt="Product Image" class="item-image">
<?php endif; ?>
                                <div>
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p>Quantity: <?php echo $item['quantity']; ?></p>
                                </div>
                            </div>
                            <div>
                                <p>Rs.<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div style="margin-top: 2rem; text-align: center;">
                    <a href="orders.php" class="btn btn-outline">Back to Orders</a>
                    <?php if($order['status'] == 'shipped' && $order['tracking_number']): ?>
                        <button onclick="alert('Tracking details would open in a new window in a real application. Tracking number: <?php echo $order['tracking_number']; ?>')" class="btn">
                            Track Package
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
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
            // Add any JavaScript functionality here
            console.log('Order tracking page loaded');
        });
    </script>
</body>
</html>
<?php
mysqli_free_result($items_result);
?>