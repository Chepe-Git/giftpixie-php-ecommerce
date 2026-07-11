<?php
session_start();
require_once 'db.php'; // Add database connection

// Fetch featured products from database (limit to 4)
$featured_query = "SELECT p.*, c.name as category_name 
                   FROM products p 
                   LEFT JOIN categories c ON p.category_id = c.id 
                   ORDER BY p.id ASC 
                   LIMIT 4";
$featured_result = mysqli_query($conn, $featured_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - Home</title>
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
        
        .hero {
            background: linear-gradient(rgba(24, 33, 45, 0.8), rgba(10, 12, 20, 0.8)), url('./images/hero-bg.jpg');
            background-size: cover;
            color: white;
            text-align: center;
            padding: 4rem 2rem;
            margin-bottom: 2rem;
        }
        
        .hero h1 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
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
        
        .section-title {
            text-align: center;
            margin: 2rem 0;
            color: #0F1B33;
        }
        
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }
        
        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            transition: transform 0.3s;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .product-img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background-color: #f5f5f5;
        }
        
        .product-info {
            padding: 1rem;
        }
        
        .product-price {
            color: #E53935;
            font-weight: bold;
            font-size: 1.2rem;
            margin: 0.5rem 0;
        }
        
        .footer {
            background-color: #0F1B33;
            color: white;
            text-align: center;
            padding: 2rem;
            margin-top: 3rem;
        }
        
        .categories {
            display: flex;
            justify-content: center;
            gap: 1rem;
            margin: 2rem 0;
            flex-wrap: wrap;
        }
        
        .category-btn {
            background-color: #f5f5f5;
            color: #0F1B33;
            padding: 0.5rem 1.5rem;
            border-radius: 20px;
            text-decoration: none;
            border: 2px solid #0F1B33;
        }
        
        .category-btn:hover {
            background-color: #0F1B33;
            color: white;
        }
        
        .user-info {
            color: white;
            margin-left: 1rem;
        }
        
        .product-category {
            font-size: 0.875rem;
            color: #666;
            margin-bottom: 0.5rem;
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

    <section class="hero">
        <h1>Thoughtful Gifts for Every Moment</h1>
        <p style="margin-bottom: 30px;">Discover unique and thoughtful gifts that will bring smiles to your loved ones' faces.<br></p>
        <a href="products.php" class="btn">Shop Now</a>
    </section>

    <div class="container">
        <h2 class="section-title">Popular Categories</h2>
        <div class="categories">
            <a href="products.php?category=Birthday" class="category-btn">Birthday</a>
            <a href="products.php?category=Anniversary" class="category-btn">Anniversary</a>
            <a href="products.php?category=Other" class="category-btn">Other Occasions</a>
        </div>

        <h2 class="section-title">Featured Products</h2>
        <div class="products-grid">
            <?php if(mysqli_num_rows($featured_result) === 0): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 2rem;">
                    <p>No featured products available yet.</p>
                    <a href="products.php" class="btn">Browse All Products</a>
                </div>
            <?php else: ?>
                <?php while($product = mysqli_fetch_assoc($featured_result)): ?>
                    <div class="product-card">
                        <?php if(file_exists('images/products/' . $product['image'])): ?>
                            <img src="images/products/<?php echo $product['image']; ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 class="product-img">
                        <?php else: ?>
                            <img src="images/placeholder.jpg" 
                                 alt="Product Image" 
                                 class="product-img">
                        <?php endif; ?>
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p class="product-price">Rs.<?php echo number_format($product['price'], 2); ?></p>
                            <a href="product.php?id=<?php echo $product['id']; ?>" class="btn">View Details</a>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
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
        // Simple JavaScript for any frontend interactions
        document.addEventListener('DOMContentLoaded', function() {
            console.log('GiftPixie loaded successfully');
        });
    </script>
</body>
</html>
<?php
mysqli_free_result($featured_result);
?>