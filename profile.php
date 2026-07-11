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

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['first_name']);
    $last_name = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Basic validation
    if (empty($first_name) || empty($last_name) || empty($email)) {
        $error_message = "Please fill in all required fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = "Invalid email format";
    } else {
        // Check if email is already taken by another user
        $check_email = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = mysqli_prepare($conn, $check_email);
        mysqli_stmt_bind_param($stmt, "si", $email, $user_id);
        mysqli_stmt_execute($stmt);
        mysqli_stmt_store_result($stmt);
        
        if (mysqli_stmt_num_rows($stmt) > 0) {
            $error_message = "Email already registered by another user";
            mysqli_stmt_close($stmt);
        } else {
            mysqli_stmt_close($stmt);

            // Update user profile
            $update_query = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone = ?, address = ? WHERE id = ?";
            $stmt = mysqli_prepare($conn, $update_query);
            mysqli_stmt_bind_param($stmt, "sssssi", $first_name, $last_name, $email, $phone, $address, $user_id);
            
            if (mysqli_stmt_execute($stmt)) {
                $success_message = "Profile updated successfully";
                
                // Update session variables
                $_SESSION['first_name'] = $first_name;
                $_SESSION['last_name'] = $last_name;
                $_SESSION['email'] = $email;
            } else {
                $error_message = "Failed to update profile. Please try again.";
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Get current user data
$query = "SELECT username, email, first_name, last_name, phone, address, created_at FROM users WHERE id = ?";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $user_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

if (!$user) {
    // User not found - should not happen
    session_destroy();
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - My Profile</title>
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
        
        .profile-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .profile-header h1 {
            color: #0F1B33;
        }
        
        .profile-info {
            background-color: #f9f9f9;
            padding: 2rem;
            border-radius: 8px;
            margin-bottom: 2rem;
        }
        
        .info-row {
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #eee;
        }
        
        .info-label {
            font-weight: bold;
            color: #0F1B33;
            display: inline-block;
            width: 150px;
        }
        
        .form-container {
            background-color: #f9f9f9;
            padding: 2rem;
            border-radius: 8px;
        }
        
        .form-title {
            margin-bottom: 1.5rem;
            color: #0F1B33;
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
        
        .footer {
            background-color: #0F1B33;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
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
            margin-left: 1rem;
        }
        
        .profile-actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
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
            <a href="profile.php">Profile</a>
            <a href="orders.php">Orders</a>
            <a href="logout.php">Logout</a>
            <span class="user-info">Hi, <?php echo htmlspecialchars($_SESSION['username']); ?></span>
        </nav>
    </header>

    <div class="container">
        <div class="profile-header">
            <h1>My Profile</h1>
            <p>Manage your account information</p>
        </div>
        
        <?php if($error_message): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if($success_message): ?>
            <div class="alert alert-success">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <div class="profile-info">
            <h2 class="form-title">Account Information</h2>
            
            <div class="info-row">
                <span class="info-label">Username:</span>
                <?php echo htmlspecialchars($user['username']); ?>
            </div>
            
            <div class="info-row">
                <span class="info-label">Member Since:</span>
                <?php echo date('F j, Y', strtotime($user['created_at'])); ?>
            </div>
            
            <div class="info-row">
                <span class="info-label">Account Type:</span>
                <?php echo ($_SESSION['role'] === 'admin') ? 'Administrator' : 'Customer'; ?>
            </div>
            
            <div class="profile-actions">
                <a href="orders.php" class="btn">View Orders</a>
                <a href="order_tracking.php" class="btn btn-outline">Track Orders</a>
                <a href="logout.php" class="btn btn-outline">Logout</a>
            </div>
        </div>
        
        <div class="form-container">
            <h2 class="form-title">Edit Profile Information</h2>
            
            <form action="profile.php" method="POST" id="profileForm">
                <div class="form-group">
                    <label for="first_name">First Name *</label>
                    <input type="text" id="first_name" name="first_name" 
                           value="<?php echo htmlspecialchars($user['first_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="last_name">Last Name *</label>
                    <input type="text" id="last_name" name="last_name" 
                           value="<?php echo htmlspecialchars($user['last_name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" 
                           value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" 
                           value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="address">Shipping Address</label>
                    <textarea id="address" name="address"><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                </div>
                
                <button type="submit" class="btn">Update Profile</button>
            </form>
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
            const form = document.getElementById('profileForm');
            
            form.addEventListener('submit', function(e) {
                const email = document.getElementById('email').value;
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                
                if (!emailRegex.test(email)) {
                    e.preventDefault();
                    alert('Please enter a valid email address');
                    return false;
                }
                
                return true;
            });
        });
    </script>
</body>
</html>