<?php
session_start();
require_once 'db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user's orders
$orders_query = "SELECT o.id, o.order_date, o.total_amount, o.status, o.tracking_number, 
                        COUNT(oi.id) as item_count 
                 FROM orders o 
                 LEFT JOIN order_items oi ON o.id = oi.order_id 
                 WHERE o.user_id = ? 
                 GROUP BY o.id 
                 ORDER BY o.order_date DESC";
$stmt = mysqli_prepare($conn, $orders_query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$orders_result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - My Orders</title>
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
        
        .page-title {
            margin-bottom: 2rem;
            padding-bottom: 0.75rem;
            color: #0F1B33;
            border-bottom: 2px solid #E53935;
            font-size: 2rem;
        }
        
        .orders-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }
        
        .orders-table th {
            background-color: #f5f5f5;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid #ddd;
        }
        
        .orders-table td {
            padding: 1rem;
            border-bottom: 1px solid #ddd;
        }
        
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: bold;
        }
        
        .status-pending { background-color: #fff3cd; color: #856404; }
        .status-processing { background-color: #cce5ff; color: #004085; }
        .status-shipped { background-color: #d1ecf1; color: #0c5460; }
        .status-delivered { background-color: #d4edda; color: #155724; }
        .status-cancelled { background-color: #f8d7da; color: #721c24; }
        
        .btn {
            display: inline-block;
            background-color: #E53935;
            color: white;
            padding: 0.5rem 1rem;
            text-decoration: none;
            border-radius: 4px;
            border: none;
            cursor: pointer;
            font-size: 0.875rem;
        }
        
        .btn:hover {
            background-color: #c62828;
        }
        
        .btn-outline {
            background-color: white;
            color: #0F1B33;
            border: 1px solid #0F1B33;
        }
        
        .btn-outline:hover {
            background-color: #0F1B33;
            color: white;
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
        
        .empty-state {
            text-align: center;
            padding: 3rem;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        
        .empty-state h3 {
            margin-bottom: 1rem;
        }
        
        .user-info {
            color: white;
            white-space: nowrap;
        }

        .table-wrap {
            width: 100%;
            overflow-x: auto;
        }

        .action-buttons {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }
        
        .order-details {
            margin-top: 3rem;
        }
        
        .order-details h2 {
            margin-bottom: 1rem;
            color: #0F1B33;
        }
        
        .order-items {
            background-color: #f9f9f9;
            padding: 1.5rem;
            border-radius: 8px;
            margin-top: 1rem;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .order-item:last-child {
            border-bottom: none;
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
        <h1 class="page-title">My Orders</h1>
        
        <?php if(mysqli_num_rows($orders_result) === 0): ?>
            <div class="empty-state">
                <h3>No orders yet</h3>
                <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
                <a href="products.php" class="btn" style="margin-top: 1rem;">Browse Products</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
            <table class="orders-table">
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Total</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                        <tr>
                            <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                            <td><?php echo $order['item_count']; ?> item(s)</td>
                            <td>Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <a href="order_tracking.php?id=<?php echo $order['id']; ?>" class="btn">Track</a>
                                    <a href="#" onclick="viewOrderDetails(<?php echo $order['id']; ?>)" class="btn btn-outline">Details</a>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
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
        function viewOrderDetails(orderId) {
            // In a real application, this would be an AJAX call to get order details
            // For this demo, we'll redirect to a details page or show a modal
            alert('Order details for #' + orderId + ' would be shown here.\n\nIn a full application, this would display detailed order information.');
            return false;
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Add any JavaScript functionality here
        });
    </script>
</body>
</html>
<?php
mysqli_free_result($orders_result);
?>