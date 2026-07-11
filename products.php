<?php
session_start();
require_once 'db.php';

// Get category filter
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';

// Build query based on filter
if ($category_filter) {
    $products_query = "SELECT p.*, c.name as category_name 
                       FROM products p 
                       JOIN categories c ON p.category_id = c.id 
                       WHERE c.name = ? 
                       ORDER BY p.name";
    $stmt = mysqli_prepare($conn, $products_query);
    mysqli_stmt_bind_param($stmt, "s", $category_filter);
    mysqli_stmt_execute($stmt);
    $products_result = mysqli_stmt_get_result($stmt);
} else {
    $products_query = "SELECT p.*, c.name as category_name 
                       FROM products p 
                       JOIN categories c ON p.category_id = c.id 
                       ORDER BY p.name";
    $products_result = mysqli_query($conn, $products_query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - Products<?php echo $category_filter ? ': ' . htmlspecialchars($category_filter) : ''; ?></title>
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
        
        .category-btn.active {
            background-color: #0F1B33;
            color: white;
        }
        
        .user-info {
            color: white;
            margin-left: 1rem;
        }
        
        .search-filter {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 2rem 0;
            padding: 1rem;
            background-color: #f9f9f9;
            border-radius: 8px;
        }
        
        .search-box {
            flex: 1;
            padding: 0.5rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            margin-right: 1rem;
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

    <div class="container">
        <h1 class="section-title">Our Gift Collection<?php echo $category_filter ? ': ' . htmlspecialchars($category_filter) : ''; ?></h1>
        
        <div class="categories">
            <a href="products.php" class="category-btn <?php echo !$category_filter ? 'active' : ''; ?>">All Products</a>
            <a href="products.php?category=Birthday" class="category-btn <?php echo $category_filter == 'Birthday' ? 'active' : ''; ?>">Birthday</a>
            <a href="products.php?category=Anniversary" class="category-btn <?php echo $category_filter == 'Anniversary' ? 'active' : ''; ?>">Anniversary</a>
            <a href="products.php?category=Other" class="category-btn <?php echo $category_filter == 'Other' ? 'active' : ''; ?>">Other Occasions</a>
        </div>

        <div class="search-filter">
            <input type="text" class="search-box" placeholder="Search products..." id="searchInput">
            <select id="sortSelect" class="search-box">
                <option value="">Sort by</option>
                <option value="price_low">Price: Low to High</option>
                <option value="price_high">Price: High to Low</option>
                <option value="name">Name A-Z</option>
            </select>
        </div>

        <div class="products-grid" id="productsGrid">
            <?php if(mysqli_num_rows($products_result) === 0): ?>
                <div style="grid-column: 1 / -1; text-align: center; padding: 3rem;">
                    <h3>No products found</h3>
                    <p>Try selecting a different category or check back later.</p>
                    <a href="products.php" class="btn">View All Products</a>
                </div>
            <?php else: ?>
                <?php while($product = mysqli_fetch_assoc($products_result)): ?>
                    <div class="product-card">
                        <?php if(file_exists('images/products/' . $product['image'])): ?>
    <img src="images/products/<?php echo $product['image']; ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-img">
<?php else: ?>
    <img src="images/placeholder.jpg" alt="Product Image" class="product-img">
<?php endif; ?>
                        <div class="product-info">
                            <div class="product-category"><?php echo htmlspecialchars($product['category_name']); ?></div>
                            <h3><?php echo htmlspecialchars($product['name']); ?></h3>
                            <p><?php echo htmlspecialchars(substr($product['description'], 0, 60)) . '...'; ?></p>
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
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('searchInput');
            const sortSelect = document.getElementById('sortSelect');
            const productCards = document.querySelectorAll('.product-card');
            
            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                productCards.forEach(card => {
                    const title = card.querySelector('h3').textContent.toLowerCase();
                    const description = card.querySelector('p:not(.product-price)').textContent.toLowerCase();
                    const category = card.querySelector('.product-category').textContent.toLowerCase();
                    
                    if (title.includes(searchTerm) || description.includes(searchTerm) || category.includes(searchTerm)) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
            
            sortSelect.addEventListener('change', function() {
                // Simple client-side sorting
                const container = document.getElementById('productsGrid');
                const cards = Array.from(productCards);
                
                cards.sort((a, b) => {
                    const priceA = parseFloat(a.querySelector('.product-price').textContent.replace('$', ''));
                    const priceB = parseFloat(b.querySelector('.product-price').textContent.replace('$', ''));
                    const nameA = a.querySelector('h3').textContent.toLowerCase();
                    const nameB = b.querySelector('h3').textContent.toLowerCase();
                    
                    switch(this.value) {
                        case 'price_low':
                            return priceA - priceB;
                        case 'price_high':
                            return priceB - priceA;
                        case 'name':
                            return nameA.localeCompare(nameB);
                        default:
                            return 0;
                    }
                });
                
                // Reorder cards in container
                cards.forEach(card => container.appendChild(card));
            });
        });
    </script>
</body>
</html>
<?php
mysqli_free_result($products_result);
if (isset($stmt)) mysqli_stmt_close($stmt);
?>