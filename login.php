<?php
session_start();
// If user is already logged in, redirect to home
if(isset($_SESSION['user_id'])) {
    header("Location: index.php");
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
    <title>GiftPixie - Login</title>
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
            max-width: 400px;
            margin: 3rem auto;
            padding: 0 1rem;
        }
        
        .form-container {
            background-color: #f9f9f9;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .form-title {
            text-align: center;
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
        
        .register-link {
            text-align: center;
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
            <a href="login.php">Login</a>
            <a href="register.php">Register</a>
        </nav>
    </header>

    <div class="container">
        <div class="form-container">
            <h1 class="form-title">Login to GiftPixie</h1>
            
            <?php if($error_message): ?>
                <div class="alert alert-error">
                    <?php echo $error_message; ?>
                </div>
            <?php endif; ?>
            
            <form action="login_process.php" method="POST" id="loginForm">
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <input type="text" id="username" name="username" required>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn">Login</button>
            </form>
            
            <div class="register-link">
                <p>Don't have an account? <a href="register.php">Register here</a></p>
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
            const form = document.getElementById('loginForm');
            
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