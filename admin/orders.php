<?php
session_start();
require_once '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$order_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$message = '';
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = intval($_POST['order_id']);
    $status = trim($_POST['status']);
    $tracking_number = trim($_POST['tracking_number']);
    
    $query = "UPDATE orders SET status = ?, tracking_number = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "ssi", $status, $tracking_number, $order_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = "Order status updated successfully!";
    } else {
        $error = "Failed to update order status: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
}

// Get order details for view
if ($action === 'view' && $order_id > 0) {
    $order_query = "SELECT o.*, u.first_name, u.last_name, u.email, u.phone, u.address 
                    FROM orders o 
                    JOIN users u ON o.user_id = u.id 
                    WHERE o.id = ?";
    $stmt = mysqli_prepare($conn, $order_query);
    mysqli_stmt_bind_param($stmt, "i", $order_id);
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
}

// Get all orders for listing
if ($action === 'list') {
    $status_filter = isset($_GET['status']) ? $_GET['status'] : '';
    
    $orders_query = "SELECT o.*, u.first_name, u.last_name, COUNT(oi.id) as item_count 
                     FROM orders o 
                     JOIN users u ON o.user_id = u.id 
                     LEFT JOIN order_items oi ON o.id = oi.order_id 
                     WHERE 1=1";
    
    if ($status_filter) {
        $orders_query .= " AND o.status = '" . mysqli_real_escape_string($conn, $status_filter) . "'";
    }
    
    $orders_query .= " GROUP BY o.id ORDER BY o.order_date DESC";
    $orders_result = mysqli_query($conn, $orders_query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - Manage Orders</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f5f5;
            color: #0F1B33;
        }
        
        .admin-header {
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
        
        .admin-nav {
            display: flex;
            gap: 1.5rem;
        }
        
        .admin-nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 4px;
        }
        
        .admin-nav a:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        .admin-nav a.active {
            background-color: #E53935;
        }
        
        .admin-info {
            color: white;
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }
        
        .page-title {
            margin-bottom: 2rem;
            color: #0F1B33;
        }
        
        .alert {
            padding: 1rem;
            border-radius: 4px;
            margin-bottom: 1.5rem;
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
        
        .btn-small {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }
        
        .logout-btn {
            background-color: transparent;
            border: 1px solid white;
            color: white;
        }
        
        .logout-btn:hover {
            background-color: rgba(255, 255, 255, 0.1);
        }
        
        /* Table styles */
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        
        table th {
            background-color: #f9f9f9;
            padding: 1rem;
            text-align: left;
            border-bottom: 2px solid #ddd;
            font-weight: bold;
        }
        
        table td {
            padding: 1rem;
            border-bottom: 1px solid #eee;
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
        
        .filter-bar {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            align-items: center;
            flex-wrap: wrap;
        }
        
        .filter-select {
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: white;
        }
        
        /* Order details styles */
        .order-details {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 1.5rem 0;
        }
        
        .info-card {
            background-color: #f9f9f9;
            padding: 1rem;
            border-radius: 4px;
            border-left: 4px solid #E53935;
        }
        
        .info-card h3 {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .info-card p {
            font-size: 1rem;
            color: #0F1B33;
        }
        
        .order-items {
            margin: 2rem 0;
        }
        
        .order-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .item-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            background-color: #f5f5f5;
        }
        
        /* Form styles */
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #0F1B33;
        }
        
        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <a href="dashboard.php" class="logo">Gift<span>Pixie</span> Admin</a>
        
        <nav class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="products.php">Products</a>
            <a href="orders.php" class="active">Orders</a>
            <a href="users.php">Users</a>
            <a href="faqs.php">FAQs</a>
        </nav>
        
        <div class="admin-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="logout.php" class="btn btn-small logout-btn">Logout</a>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">
            <?php echo $action === 'view' ? 'Order Details' : 'Manage Orders'; ?>
        </h1>
        
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
        
        <?php if($action === 'view' && $order): ?>
            <div class="order-details">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                    <h2>Order #<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></h2>
                    <a href="orders.php" class="btn">Back to Orders</a>
                </div>
                
                <div class="info-grid">
                    <div class="info-card">
                        <h3>Customer</h3>
                        <p><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></p>
                        <p><?php echo htmlspecialchars($order['email']); ?></p>
                        <p><?php echo htmlspecialchars($order['phone']); ?></p>
                    </div>
                    
                    <div class="info-card">
                        <h3>Order Date</h3>
                        <p><?php echo date('F j, Y, g:i a', strtotime($order['order_date'])); ?></p>
                    </div>
                    
                    <div class="info-card">
                        <h3>Shipping Address</h3>
                        <p><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                    </div>
                    
                    <div class="info-card">
                        <h3>Order Total</h3>
                        <p style="font-size: 1.5rem; font-weight: bold; color: #E53935;">
                            Rs. <?php echo number_format($order['total_amount'], 2); ?>
                        </p>
                    </div>
                </div>
                
                <div class="order-items">
                    <h3>Order Items</h3>
                    <?php while($item = mysqli_fetch_assoc($items_result)): ?>
                        <div class="order-item">
                            <div class="item-info">
                              <?php if(file_exists('../images/products/' . $item['image'])): ?>
                              <img src="../images/products/<?php echo $item['image']; ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" class="item-image">
                               <?php else: ?>
                               <img src="../images/placeholder.jpg" alt="Product Image" class="item-image">
                            <?php endif; ?>
                                <div>
                                    <h4><?php echo htmlspecialchars($item['name']); ?></h4>
                                    <p>Quantity: <?php echo $item['quantity']; ?> × $<?php echo number_format($item['price'], 2); ?></p>
                                </div>
                            </div>
                            <div>
                                <p style="font-weight: bold;">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div style="margin-top: 2rem; padding-top: 1.5rem; border-top: 2px solid #eee;">
                    <h3>Update Order Status</h3>
                    <form action="orders.php" method="POST" style="max-width: 500px; margin-top: 1rem;">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        
                        <div class="form-group">
                            <label for="status">Status</label>
                            <select id="status" name="status" required>
                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>Pending</option>
                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>Processing</option>
                                <option value="shipped" <?php echo $order['status'] == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                                <option value="delivered" <?php echo $order['status'] == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="tracking_number">Tracking Number</label>
                            <input type="text" id="tracking_number" name="tracking_number" 
                                   value="<?php echo htmlspecialchars($order['tracking_number']); ?>"
                                   placeholder="Enter tracking number">
                        </div>
                        
                        <button type="submit" name="update_status" class="btn">Update Status</button>
                    </form>
                </div>
            </div>
            
        <?php else: // List orders ?>
            <div class="filter-bar">
                <form method="GET" action="orders.php" style="display: flex; gap: 1rem; align-items: center;">
                    <select name="status" class="filter-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="pending" <?php echo $status_filter == 'pending' ? 'selected' : ''; ?>>Pending</option>
                        <option value="processing" <?php echo $status_filter == 'processing' ? 'selected' : ''; ?>>Processing</option>
                        <option value="shipped" <?php echo $status_filter == 'shipped' ? 'selected' : ''; ?>>Shipped</option>
                        <option value="delivered" <?php echo $status_filter == 'delivered' ? 'selected' : ''; ?>>Delivered</option>
                        <option value="cancelled" <?php echo $status_filter == 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <?php if($status_filter): ?>
                        <a href="orders.php" class="btn btn-outline">Clear Filter</a>
                    <?php endif; ?>
                </form>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Items</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($orders_result) === 0): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No orders found.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while($order = mysqli_fetch_assoc($orders_result)): ?>
                            <tr>
                                <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                                <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                                <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                                <td><?php echo $order['item_count']; ?></td>
                                <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                                <td>
                                    <span class="status-badge status-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" 
                                           class="btn btn-small">View</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Orders management loaded');
        });
    </script>
</body>
</html>
<?php
if (isset($orders_result)) mysqli_free_result($orders_result);
if (isset($items_result)) mysqli_free_result($items_result);
?>