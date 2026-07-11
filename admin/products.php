<?php
session_start();
require_once '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$product_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Handle actions
$message = '';
$error = '';

// Get categories for dropdown
$categories_query = "SELECT * FROM categories ORDER BY name";
$categories_result = mysqli_query($conn, $categories_query);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add' || $action === 'edit') {
            $name = trim($_POST['name']);
            $description = trim($_POST['description']);
            $price = floatval($_POST['price']);
            $category_id = intval($_POST['category_id']);
            $stock_quantity = intval($_POST['stock_quantity']);
            $image = trim($_POST['image']);
            
            // Validation
            if (empty($name) || $price <= 0 || $category_id <= 0) {
                $error = "Please fill in all required fields correctly";
            } else {
                if ($action === 'add') {
                    // Add new product
                    $query = "INSERT INTO products (name, description, price, category_id, stock_quantity, image) 
                              VALUES (?, ?, ?, ?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ssdiis", $name, $description, $price, $category_id, $stock_quantity, $image);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "Product added successfully!";
                        $action = 'list';
                    } else {
                        $error = "Failed to add product: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($stmt);
                } else if ($action === 'edit' && $product_id > 0) {
                    // Update product
                    $query = "UPDATE products SET name = ?, description = ?, price = ?, 
                              category_id = ?, stock_quantity = ?, image = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ssdiisi", $name, $description, $price, 
                                           $category_id, $stock_quantity, $image, $product_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "Product updated successfully!";
                        $action = 'list';
                    } else {
                        $error = "Failed to update product: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

// Delete product
if ($action === 'delete' && $product_id > 0) {
    // Check if product exists in any orders
    $check_query = "SELECT COUNT(*) as count FROM order_items WHERE product_id = ?";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $count);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);
    
    if ($count > 0) {
        $error = "Cannot delete product that exists in orders. You can hide it by setting stock to 0.";
        $action = 'list';
    } else {
        $delete_query = "DELETE FROM products WHERE id = ?";
        $stmt = mysqli_prepare($conn, $delete_query);
        mysqli_stmt_bind_param($stmt, "i", $product_id);
        
        if (mysqli_stmt_execute($stmt)) {
            $message = "Product deleted successfully!";
        } else {
            $error = "Failed to delete product: " . mysqli_error($conn);
        }
        mysqli_stmt_close($stmt);
        $action = 'list';
    }
}

// Get product data for editing
$product = null;
if ($action === 'edit' && $product_id > 0) {
    $query = "SELECT * FROM products WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $product_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$product) {
        $error = "Product not found";
        $action = 'list';
    }
}

// Get all products for listing
if ($action === 'list') {
    $products_query = "SELECT p.*, c.name as category_name 
                       FROM products p 
                       LEFT JOIN categories c ON p.category_id = c.id 
                       ORDER BY p.created_at DESC";
    $products_result = mysqli_query($conn, $products_query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - Manage Products</title>
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
        
        /* Form styles */
        .form-container {
            background-color: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            max-width: 600px;
            margin: 0 auto;
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
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            font-family: Arial, sans-serif;
        }
        
        .form-group textarea {
            min-height: 100px;
            resize: vertical;
        }
        
        .required::after {
            content: " *";
            color: #E53935;
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
        
        .actions {
            display: flex;
            gap: 0.5rem;
        }
        
        .stock-low {
            color: #E53935;
            font-weight: bold;
        }
        
        .product-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <a href="dashboard.php" class="logo">Gift<span>Pixie</span> Admin</a>
        
        <nav class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="products.php" class="active">Products</a>
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
        <h1 class="page-title">
            <?php 
            if ($action === 'add') echo 'Add New Product';
            elseif ($action === 'edit') echo 'Edit Product';
            else echo 'Manage Products';
            ?>
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
        
        <?php if($action === 'add' || $action === 'edit'): ?>
            <div class="form-container">
                <form action="products.php<?php echo $action === 'edit' ? '?id=' . $product_id : ''; ?>" method="POST">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    
                    <div class="form-group">
                        <label for="name" class="required">Product Name</label>
                        <input type="text" id="name" name="name" 
                               value="<?php echo $product ? htmlspecialchars($product['name']) : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea id="description" name="description"><?php echo $product ? htmlspecialchars($product['description']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="price" class="required">Price (Rs.)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0.01"
                               value="<?php echo $product ? $product['price'] : ''; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="category_id" class="required">Category</label>
                        <select id="category_id" name="category_id" required>
                            <option value="">Select Category</option>
                            <?php while($category = mysqli_fetch_assoc($categories_result)): ?>
                                <option value="<?php echo $category['id']; ?>"
                                    <?php if($product && $product['category_id'] == $category['id']) echo 'selected'; ?>>
                                    <?php echo htmlspecialchars($category['name']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="stock_quantity" class="required">Stock Quantity</label>
                        <input type="number" id="stock_quantity" name="stock_quantity" min="0"
                               value="<?php echo $product ? $product['stock_quantity'] : '10'; ?>" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="image">Image Filename</label>
                        <input type="text" id="image" name="image" 
                               value="<?php echo $product ? htmlspecialchars($product['image']) : 'placeholder.jpg'; ?>"
                               placeholder="e.g., product1.jpg">
                        <small>Note: Admin uploads images separately. Enter the filename here.</small>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn">
                            <?php echo $action === 'add' ? 'Add Product' : 'Update Product'; ?>
                        </button>
                        <a href="products.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
            
            <?php mysqli_data_seek($categories_result, 0); // Reset categories pointer ?>
            
        <?php else: // List products ?>
            <div style="text-align: right; margin-bottom: 1.5rem;">
                <a href="products.php?action=add" class="btn">Add New Product</a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Image</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Price</th>
                        <th>Stock</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($products_result) === 0): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                No products found. <a href="products.php?action=add">Add your first product</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while($product = mysqli_fetch_assoc($products_result)): ?>
                            <tr>
                                <td>
    <?php if(file_exists('../images/products/' . $product['image'])): ?>
        <img src="../images/products/<?php echo $product['image']; ?>" 
             alt="<?php echo htmlspecialchars($product['name']); ?>" 
             class="product-image">
    <?php else: ?>
        <img src="../images/placeholder.jpg" alt="Product Image" class="product-image">
    <?php endif; ?>
</td>
                                <td>
                                    <strong><?php echo htmlspecialchars($product['name']); ?></strong><br>
                                    <small><?php echo substr($product['description'], 0, 50) . '...'; ?></small>
                                </td>
                                <td><?php echo htmlspecialchars($product['category_name']); ?></td>
                                <td>Rs. <?php echo number_format($product['price'], 2); ?></td>
                                <td class="<?php echo $product['stock_quantity'] < 5 ? 'stock-low' : ''; ?>">
                                    <?php echo $product['stock_quantity']; ?>
                                </td>
                                <td>
                                    <div class="actions">
                                        <a href="products.php?action=edit&id=<?php echo $product['id']; ?>" 
                                           class="btn btn-small">Edit</a>
                                        <a href="products.php?action=delete&id=<?php echo $product['id']; ?>" 
                                           class="btn btn-small btn-outline"
                                           onclick="return confirm('Are you sure you want to delete this product?')">Delete</a>
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
            // Form validation
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const price = form.querySelector('#price');
                    const stock = form.querySelector('#stock_quantity');
                    
                    if (price && parseFloat(price.value) <= 0) {
                        e.preventDefault();
                        alert('Price must be greater than 0');
                        return false;
                    }
                    
                    if (stock && parseInt(stock.value) < 0) {
                        e.preventDefault();
                        alert('Stock quantity cannot be negative');
                        return false;
                    }
                    
                    return true;
                });
            });
        });
    </script>
</body>
</html>
<?php
if (isset($categories_result)) mysqli_free_result($categories_result);
if (isset($products_result)) mysqli_free_result($products_result);
?>