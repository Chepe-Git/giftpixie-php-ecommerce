<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - Contact & About</title>
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
        }
        
        .btn:hover {
            background-color: #c62828;
        }
        
        .container {
            max-width: 1200px;
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
            padding: 2rem;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        
        .content-section h2 {
            color: #0F1B33;
            margin-bottom: 1rem;
            border-bottom: 2px solid #E53935;
            padding-bottom: 0.5rem;
        }
        
        .contact-form {
            display: grid;
            gap: 1rem;
            max-width: 600px;
            margin: 0 auto;
        }
        
        .form-group {
            display: flex;
            flex-direction: column;
        }
        
        .form-group label {
            margin-bottom: 0.5rem;
            font-weight: bold;
        }
        
        .form-group input,
        .form-group textarea {
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-family: Arial, sans-serif;
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .contact-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }
        
        .info-card {
            background-color: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .info-card h3 {
            color: #E53935;
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
        <h1 class="page-title">Contact Us & About GiftPixie</h1>
        
        <div class="content-section">
            <h2>About GiftPixie</h2>
            <p>Welcome to GiftPixie, your one-stop shop for thoughtful and meaningful gifts for every occasion. Founded in 2023, we believe that the perfect gift can make any moment more special.</p>
            <p>Our mission is to help you find the ideal present for birthdays, anniversaries, graduations, and all life's celebrations. We carefully curate our collection to include only the highest quality gifts that bring joy to both the giver and receiver.</p>
            <p>At GiftPixie, we understand that finding the right gift can be challenging. That's why we've organized our products by occasion and created helpful gift guides to make your shopping experience easy and enjoyable.</p>
        </div>
        
        <div class="content-section">
            <h2>Our Values</h2>
            <ul style="list-style-type: none; line-height: 1.8;">
                <li>✓ <strong>Quality:</strong> We source only high-quality products</li>
                <li>✓ <strong>Thoughtfulness:</strong> Every gift is chosen with care</li>
                <li>✓ <strong>Customer Service:</strong> We're here to help you find the perfect gift</li>
                <li>✓ <strong>Sustainability:</strong> We use eco-friendly packaging when possible</li>
                <li>✓ <strong>Value:</strong> Great gifts at reasonable prices</li>
            </ul>
        </div>
        
        <div class="content-section">
            <h2>Contact Information</h2>
            <div class="contact-info">
                <div class="info-card">
                    <h3>Address</h3>
                    <p>123 Gift Street<br>Pallekele, Kandy<br>Sri Lanka</p>
                </div>
                
                <div class="info-card">
                    <h3>Contact Details</h3>
                    <p>Phone: (555) 123-4567<br>Email: info@giftpixie.com<br>Hours: Mon-Fri 9AM-6PM PST</p>
                </div>
                
                <div class="info-card">
                    <h3>Customer Support</h3>
                    <p>For order inquiries: orders@giftpixie.com<br>For general questions: support@giftpixie.com<br>Response time: Within 24 hours</p>
                </div>
            </div>
        </div>
        
        <div class="content-section">
            <h2>Send Us a Message</h2>
            <form class="contact-form" id="contactForm">
                <div class="form-group">
                    <label for="name">Your Name</label>
                    <input type="text" id="name" name="name" required>
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required>
                </div>
                
                <div class="form-group">
                    <label for="subject">Subject</label>
                    <input type="text" id="subject" name="subject" required>
                </div>
                
                <div class="form-group">
                    <label for="message">Your Message</label>
                    <textarea id="message" name="message" required></textarea>
                </div>
                
                <button type="submit" class="btn">Send Message</button>
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
            const contactForm = document.getElementById('contactForm');
            
            contactForm.addEventListener('submit', function(e) {
                e.preventDefault();
                
                // Simple form validation
                const name = document.getElementById('name').value.trim();
                const email = document.getElementById('email').value.trim();
                const subject = document.getElementById('subject').value.trim();
                const message = document.getElementById('message').value.trim();
                
                if (!name || !email || !subject || !message) {
                    alert('Please fill in all fields');
                    return;
                }
                
                // Simple email validation
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    alert('Please enter a valid email address');
                    return;
                }
                
                // In a real application, this would send the form data to the server
                // For this demo, we'll just show a success message
                alert('Thank you for your message! We will get back to you soon.');
                contactForm.reset();
            });
        });
    </script>
</body>
</html>