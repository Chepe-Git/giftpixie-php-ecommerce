<?php
session_start();
require_once '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$message = '';
$error = '';

// Handle user deletion
if ($action === 'delete' && $user_id > 0) {
    // Check if trying to delete self
    if ($user_id == $_SESSION['admin_id']) {
        $error = "You cannot delete your own account!";
        $action = 'list';
    } else {
        // Check if user has orders
        $check_query = "SELECT COUNT(*) as count FROM orders WHERE user_id = ?";
        $stmt = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt, "i", $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_bind_result($stmt, $count);
        mysqli_stmt_fetch($stmt);
        mysqli_stmt_close($stmt);
        
        if ($count > 0) {
            $error = "Cannot delete user with existing orders. You can deactivate by changing their role.";
            $action = 'list';
        } else {
            // Delete user
            $delete_query = "DELETE FROM users WHERE id = ? AND role = 'customer'";
            $stmt = mysqli_prepare($conn, $delete_query);
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $message = "User deleted successfully!";
            } else {
                $error = "Failed to delete user: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt);
            $action = 'list';
        }
    }
}

// Get all users
if ($action === 'list') {
    $role_filter = isset($_GET['role']) ? $_GET['role'] : '';
    
    $users_query = "SELECT id, username, email, first_name, last_name, role, created_at 
                    FROM users WHERE 1=1";
    
    if ($role_filter) {
        $users_query .= " AND role = '" . mysqli_real_escape_string($conn, $role_filter) . "'";
    } else {
        // Default show all except current admin if no filter
        $users_query .= " AND id != " . $_SESSION['admin_id'];
    }
    
    $users_query .= " ORDER BY created_at DESC";
    $users_result = mysqli_query($conn, $users_query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - Manage Users</title>
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
        
        .role-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: bold;
        }
        
        .role-admin { background-color: #E53935; color: white; }
        .role-customer { background-color: #0F1B33; color: white; }
    </style>
</head>
<body>
    <header class="admin-header">
        <a href="dashboard.php" class="logo">Gift<span>Pixie</span> Admin</a>
        
        <nav class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="products.php">Products</a>
            <a href="orders.php">Orders</a>
            <a href="users.php" class="active">Users</a>
            <a href="faqs.php">FAQs</a>
        </nav>
        
        <div class="admin-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="logout.php" class="btn btn-small logout-btn">Logout</a>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">Manage Users</h1>
        
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
        
        <div class="filter-bar">
            <form method="GET" action="users.php" style="display: flex; gap: 1rem; align-items: center;">
                <select name="role" class="filter-select" onchange="this.form.submit()">
                    <option value="">All Users</option>
                    <option value="admin" <?php echo $role_filter == 'admin' ? 'selected' : ''; ?>>Admins</option>
                    <option value="customer" <?php echo $role_filter == 'customer' ? 'selected' : ''; ?>>Customers</option>
                </select>
                <?php if($role_filter): ?>
                    <a href="users.php" class="btn btn-outline">Clear Filter</a>
                <?php endif; ?>
            </form>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if(mysqli_num_rows($users_result) === 0): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2rem;">
                            No users found.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php while($user = mysqli_fetch_assoc($users_result)): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo htmlspecialchars($user['username']); ?></td>
                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="role-badge role-<?php echo $user['role']; ?>">
                                    <?php echo ucfirst($user['role']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div style="display: flex; gap: 0.5rem;">
                                    <?php if($user['role'] == 'customer'): ?>
                                        <a href="users.php?action=delete&id=<?php echo $user['id']; ?>" 
                                           class="btn btn-small btn-outline"
                                           onclick="return confirm('Are you sure you want to delete this user? This action cannot be undone.')">Delete</a>
                                    <?php else: ?>
                                        <span style="color: #666; font-size: 0.875rem;">Admin</span>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php endif; ?>
            </tbody>
        </table>
        
        <div style="margin-top: 1.5rem; padding: 1rem; background-color: #f9f9f9; border-radius: 4px;">
            <h3>Notes:</h3>
            <ul style="margin-top: 0.5rem; padding-left: 1.5rem;">
                <li>Admins cannot be deleted from this interface for security reasons</li>
                <li>Users with existing orders cannot be deleted</li>
                <li>Total users: <?php echo mysqli_num_rows($users_result); ?></li>
            </ul>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            console.log('User management loaded');
        });
    </script>
</body>
</html>
<?php
if (isset($users_result)) mysqli_free_result($users_result);
?>