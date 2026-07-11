<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - Privacy & Terms</title>
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
            line-height: 1.6;
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
        }
        
        .container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 0 1rem;
        }
        
        .page-title {
            text-align: center;
            margin: 2rem 0;
            color: #0F1B33;
        }
        
        .content-section {
            margin: 2rem 0;
        }
        
        .content-section h2 {
            color: #0F1B33;
            margin: 1.5rem 0 1rem 0;
            border-bottom: 2px solid #E53935;
            padding-bottom: 0.5rem;
        }
        
        .content-section h3 {
            color: #0F1B33;
            margin: 1rem 0 0.5rem 0;
        }
        
        .content-section p {
            margin-bottom: 1rem;
        }
        
        .content-section ul {
            margin-left: 1.5rem;
            margin-bottom: 1rem;
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
        
        .last-updated {
            text-align: center;
            color: #666;
            font-style: italic;
            margin-bottom: 2rem;
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
        <h1 class="page-title">Privacy Policy & Terms of Service</h1>
        <p class="last-updated">Last Updated: July 2026</p>
        
        <div class="content-section">
            <h2>Privacy Policy</h2>
            <p>At GiftPixie, we take your privacy seriously. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website.</p>
            
            <h3>Information We Collect</h3>
            <p>We collect information that you provide directly to us when you:</p>
            <ul>
                <li>Create an account</li>
                <li>Place an order</li>
                <li>Subscribe to our newsletter</li>
                <li>Contact our customer service</li>
                <li>Participate in surveys or promotions</li>
            </ul>
            
            <h3>How We Use Your Information</h3>
            <p>We use the information we collect to:</p>
            <ul>
                <li>Process and fulfill your orders</li>
                <li>Communicate with you about orders, products, and promotions</li>
                <li>Improve our website and customer service</li>
                <li>Prevent fraudulent transactions</li>
                <li>Comply with legal obligations</li>
            </ul>
            
            <h3>Data Security</h3>
            <p>We implement reasonable security measures to protect your personal information. However, no method of transmission over the Internet is 100% secure, and we cannot guarantee absolute security.</p>
            
            <h3>Cookies</h3>
            <p>We use cookies to enhance your browsing experience, analyze site traffic, and personalize content. You can control cookies through your browser settings.</p>
        </div>
        
        <div class="content-section">
            <h2>Terms of Service</h2>
            <p>By accessing and using GiftPixie, you accept and agree to be bound by these Terms of Service.</p>
            
            <h3>Account Registration</h3>
            <p>You must provide accurate and complete information when creating an account. You are responsible for maintaining the confidentiality of your account credentials.</p>
            
            <h3>Product Information</h3>
            <p>We strive to display accurate product information, including descriptions, images, and prices. However, we cannot guarantee that all information is completely accurate, current, or error-free.</p>
            
            <h3>Orders and Payment</h3>
            <p>All orders are subject to product availability. We reserve the right to cancel any order for any reason. Prices are subject to change without notice.</p>
            
            <h3>Shipping and Delivery</h3>
            <p>Shipping times are estimates and not guaranteed. Risk of loss and title for products pass to you upon delivery to the carrier.</p>
            
            <h3>Returns and Refunds</h3>
            <p>We accept returns within 30 days of delivery for unused items in original packaging. Refunds will be issued to the original payment method within 7-10 business days.</p>
            
            <h3>Intellectual Property</h3>
            <p>All content on this website, including text, graphics, logos, and images, is the property of GiftPixie and protected by copyright laws.</p>
            
            <h3>Limitation of Liability</h3>
            <p>GiftPixie shall not be liable for any indirect, incidental, special, consequential, or punitive damages resulting from your use of the website.</p>
            
            <h3>Changes to Terms</h3>
            <p>We reserve the right to modify these terms at any time. Changes will be effective immediately upon posting to the website.</p>
        </div>
        
        <div class="content-section">
            <h2>Contact Us</h2>
            <p>If you have any questions about our Privacy Policy or Terms of Service, please contact us:</p>
            <ul>
                <li>Email: legal@giftpixie.com</li>
                <li>Phone: (555) 123-4567</li>
                <li>Mail: 123 Gift Street, Pallekele, Kandy</li>
            </ul>
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
        // Simple script to scroll to section if URL has hash
        document.addEventListener('DOMContentLoaded', function() {
            if (window.location.hash) {
                const element = document.querySelector(window.location.hash);
                if (element) {
                    setTimeout(() => {
                        element.scrollIntoView({ behavior: 'smooth' });
                    }, 100);
                }
            }
        });
    </script>
</body>
</html>