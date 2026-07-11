<?php
session_start();
// If admin is already logged in, redirect to dashboard
if(isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: dashboard.php");
    exit();
}

$error_message = '';
if(isset($_GET['error'])) {
    $error_message = htmlspecialchars($_GET['error']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - Admin Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: Arial, sans-serif;
            background-color: #0F1B33;
            color: white;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        
        .login-container {
            background-color: white;
            color: #0F1B33;
            padding: 3rem;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo h1 {
            font-size: 2rem;
            color: #0F1B33;
        }
        
        .logo span {
            color: #E53935;
        }
        
        .form-title {
            text-align: center;
            margin-bottom: 1.5rem;
            color: #0F1B33;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: bold;
            color: #0F1B33;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
        }
        
        .btn {
            display: block;
            width: 100%;
            background-color: #E53935;
            color: white;
            padding: 0.75rem;
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
        
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        
        .back-link a {
            color: #0F1B33;
            text-decoration: none;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>Gift<span>Pixie</span></h1>
            <p>Admin Panel</p>
        </div>
        
        <h2 class="form-title">Admin Login</h2>
        
        <?php if($error_message): ?>
            <div class="alert alert-error">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>
        
        <form action="login_process.php" method="POST" id="adminLoginForm">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn">Login</button>
        </form>
        
        <div class="back-link">
            <a href="../index.php">← Back to Store</a>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('adminLoginForm');
            
            form.addEventListener('submit', function(e) {
                const username = document.getElementById('username').value.trim();
                const password = document.getElementById('password').value;
                
                // Basic client-side validation
                if (!username || !password) {
                    e.preventDefault();
                    alert('Please fill in all fields');
                    return false;
                }
                
                return true;
            });
        });
    </script>
</body>
</html>