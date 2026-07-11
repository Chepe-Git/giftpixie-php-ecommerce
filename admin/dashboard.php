<?php
session_start();
require_once '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Get statistics
$stats = [];

// Total users
$query = "SELECT COUNT(*) as total FROM users WHERE role = 'customer'";
$result = mysqli_query($conn, $query);
$stats['total_users'] = mysqli_fetch_assoc($result)['total'];

// Total products
$query = "SELECT COUNT(*) as total FROM products";
$result = mysqli_query($conn, $query);
$stats['total_products'] = mysqli_fetch_assoc($result)['total'];

// Total orders
$query = "SELECT COUNT(*) as total FROM orders";
$result = mysqli_query($conn, $query);
$stats['total_orders'] = mysqli_fetch_assoc($result)['total'];

// Total revenue
$query = "SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'";
$result = mysqli_query($conn, $query);
$stats['total_revenue'] = mysqli_fetch_assoc($result)['total'] ?? 0;

// Recent orders
$query = "SELECT o.id, o.order_date, o.total_amount, o.status, u.first_name, u.last_name 
          FROM orders o 
          JOIN users u ON o.user_id = u.id 
          ORDER BY o.order_date DESC 
          LIMIT 5";
$recent_orders = mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - Admin Dashboard</title>
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
        
        .dashboard-title {
            margin-bottom: 2rem;
            color: #0F1B33;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin-bottom: 3rem;
        }
        
        .stat-card {
            background-color: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            border-left: 4px solid #E53935;
        }
        
        .stat-card h3 {
            color: #666;
            font-size: 0.9rem;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }
        
        .stat-card p {
            font-size: 2rem;
            font-weight: bold;
            color: #0F1B33;
        }
        
        .dashboard-section {
            background-color: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .section-title {
            margin-bottom: 1.5rem;
            color: #0F1B33;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 0.5rem;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
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
    </style>
</head>
<body>
    <header class="admin-header">
        <a href="dashboard.php" class="logo">Gift<span>Pixie</span> Admin</a>
        
        <nav class="admin-nav">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="products.php">Products</a>
            <a href="orders.php">Orders</a>
            <a href="users.php">Users</a>
            <a href="faqs.php">FAQs</a>
        </nav>
        
        <div class="admin-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="logout.php" class="btn btn-small logout-btn">Logout</a>
        </div>
    </header>

    <div class="container">
        <h1 class="dashboard-title">Dashboard Overview</h1>
        
        <div class="stats-grid">
            <div class="stat-card">
                <h3>Total Users</h3>
                <p><?php echo $stats['total_users']; ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Total Products</h3>
                <p><?php echo $stats['total_products']; ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Total Orders</h3>
                <p><?php echo $stats['total_orders']; ?></p>
            </div>
            
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <p>Rs. <?php echo number_format($stats['total_revenue'], 2); ?></p>
            </div>
        </div>
        
        <div class="dashboard-section">
            <h2 class="section-title">Recent Orders</h2>
            
            <table>
                <thead>
                    <tr>
                        <th>Order #</th>
                        <th>Customer</th>
                        <th>Date</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($order = mysqli_fetch_assoc($recent_orders)): ?>
                        <tr>
                            <td>#<?php echo str_pad($order['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></td>
                            <td><?php echo date('M j, Y', strtotime($order['order_date'])); ?></td>
                            <td>Rs. <?php echo number_format($order['total_amount'], 2); ?></td>
                            <td>
                                <span class="status-badge status-<?php echo $order['status']; ?>">
                                    <?php echo ucfirst($order['status']); ?>
                                </span>
                            </td>
                            <td>
                                <a href="orders.php?action=view&id=<?php echo $order['id']; ?>" class="btn btn-small">View</a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
            
            <div style="text-align: center; margin-top: 1.5rem;">
                <a href="orders.php" class="btn">View All Orders</a>
            </div>
        </div>
        
        <div class="dashboard-section">
            <h2 class="section-title">Quick Actions</h2>
            
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <a href="products.php?action=add" class="btn">Add New Product</a>
                <a href="faqs.php?action=add" class="btn">Add FAQ</a>
                <a href="users.php" class="btn">Manage Users</a>
                <a href="../index.php" class="btn" style="background-color: #0F1B33;">View Store</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Admin dashboard loaded');
        });
    </script>
</body>
</html>
<?php
mysqli_free_result($recent_orders);
?>