<?php
session_start();
require_once '../db.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

$action = isset($_GET['action']) ? $_GET['action'] : 'list';
$faq_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        
        if ($action === 'add' || $action === 'edit') {
            $question = trim($_POST['question']);
            $answer = trim($_POST['answer']);
            $display_order = intval($_POST['display_order']);
            
            // Validation
            if (empty($question) || empty($answer)) {
                $error = "Please fill in both question and answer";
            } else {
                if ($action === 'add') {
                    // Add new FAQ
                    $query = "INSERT INTO faqs (question, answer, display_order) VALUES (?, ?, ?)";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ssi", $question, $answer, $display_order);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "FAQ added successfully!";
                        $action = 'list';
                    } else {
                        $error = "Failed to add FAQ: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($stmt);
                } else if ($action === 'edit' && $faq_id > 0) {
                    // Update FAQ
                    $query = "UPDATE faqs SET question = ?, answer = ?, display_order = ? WHERE id = ?";
                    $stmt = mysqli_prepare($conn, $query);
                    mysqli_stmt_bind_param($stmt, "ssii", $question, $answer, $display_order, $faq_id);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $message = "FAQ updated successfully!";
                        $action = 'list';
                    } else {
                        $error = "Failed to update FAQ: " . mysqli_error($conn);
                    }
                    mysqli_stmt_close($stmt);
                }
            }
        }
    }
}

// Delete FAQ
if ($action === 'delete' && $faq_id > 0) {
    $delete_query = "DELETE FROM faqs WHERE id = ?";
    $stmt = mysqli_prepare($conn, $delete_query);
    mysqli_stmt_bind_param($stmt, "i", $faq_id);
    
    if (mysqli_stmt_execute($stmt)) {
        $message = "FAQ deleted successfully!";
    } else {
        $error = "Failed to delete FAQ: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
    $action = 'list';
}

// Get FAQ data for editing
$faq = null;
if ($action === 'edit' && $faq_id > 0) {
    $query = "SELECT * FROM faqs WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $faq_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $faq = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
    
    if (!$faq) {
        $error = "FAQ not found";
        $action = 'list';
    }
}

// Get all FAQs for listing
if ($action === 'list') {
    $faqs_query = "SELECT * FROM faqs ORDER BY display_order, id";
    $faqs_result = mysqli_query($conn, $faqs_query);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GiftPixie - Manage FAQs</title>
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
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 1rem;
            font-family: Arial, sans-serif;
        }
        
        .form-group textarea {
            min-height: 150px;
            resize: vertical;
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
        
        .faq-question {
            font-weight: bold;
            color: #0F1B33;
        }
        
        .faq-answer {
            color: #666;
            margin-top: 0.5rem;
            font-size: 0.9rem;
        }
        
        .order-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background-color: #f0f0f0;
            border-radius: 4px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <a href="dashboard.php" class="logo">Gift<span>Pixie</span> Admin</a>
        
        <nav class="admin-nav">
            <a href="dashboard.php">Dashboard</a>
            <a href="products.php">Products</a>
            <a href="orders.php">Orders</a>
            <a href="users.php">Users</a>
            <a href="faqs.php" class="active">FAQs</a>
        </nav>
        
        <div class="admin-info">
            <span>Welcome, <?php echo htmlspecialchars($_SESSION['admin_username']); ?></span>
            <a href="logout.php" class="btn btn-small logout-btn">Logout</a>
        </div>
    </header>

    <div class="container">
        <h1 class="page-title">
            <?php 
            if ($action === 'add') echo 'Add New FAQ';
            elseif ($action === 'edit') echo 'Edit FAQ';
            else echo 'Manage FAQs';
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
                <form action="faqs.php<?php echo $action === 'edit' ? '?id=' . $faq_id : ''; ?>" method="POST">
                    <input type="hidden" name="action" value="<?php echo $action; ?>">
                    
                    <div class="form-group">
                        <label for="question">Question</label>
                        <textarea id="question" name="question" required><?php echo $faq ? htmlspecialchars($faq['question']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="answer">Answer</label>
                        <textarea id="answer" name="answer" required><?php echo $faq ? htmlspecialchars($faq['answer']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label for="display_order">Display Order</label>
                        <input type="number" id="display_order" name="display_order" min="0"
                               value="<?php echo $faq ? $faq['display_order'] : '0'; ?>">
                        <small>Lower numbers display first. Use 0 for default ordering.</small>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn">
                            <?php echo $action === 'add' ? 'Add FAQ' : 'Update FAQ'; ?>
                        </button>
                        <a href="faqs.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
            
        <?php else: // List FAQs ?>
            <div style="text-align: right; margin-bottom: 1.5rem;">
                <a href="faqs.php?action=add" class="btn">Add New FAQ</a>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Order</th>
                        <th>Question & Answer</th>
                        <th>Created</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(mysqli_num_rows($faqs_result) === 0): ?>
                        <tr>
                            <td colspan="4" style="text-align: center; padding: 2rem;">
                                No FAQs found. <a href="faqs.php?action=add">Add your first FAQ</a>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php while($faq = mysqli_fetch_assoc($faqs_result)): ?>
                            <tr>
                                <td>
                                    <span class="order-badge"><?php echo $faq['display_order']; ?></span>
                                </td>
                                <td>
                                    <div class="faq-question"><?php echo htmlspecialchars($faq['question']); ?></div>
                                    <div class="faq-answer"><?php echo substr(htmlspecialchars($faq['answer']), 0, 100) . '...'; ?></div>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($faq['created_at'])); ?></td>
                                <td>
                                    <div style="display: flex; gap: 0.5rem;">
                                        <a href="faqs.php?action=edit&id=<?php echo $faq['id']; ?>" 
                                           class="btn btn-small">Edit</a>
                                        <a href="faqs.php?action=delete&id=<?php echo $faq['id']; ?>" 
                                           class="btn btn-small btn-outline"
                                           onclick="return confirm('Are you sure you want to delete this FAQ?')">Delete</a>
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
            console.log('FAQ management loaded');
        });
    </script>
</body>
</html>
<?php
if (isset($faqs_result)) mysqli_free_result($faqs_result);
?>