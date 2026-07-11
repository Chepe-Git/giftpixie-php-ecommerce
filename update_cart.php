<?php
session_start();
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: cart.php");
    exit();
}

$user_id = (int) $_SESSION['user_id'];
$cart_ids = $_POST['cart_ids'] ?? [];
$quantities = $_POST['quantities'] ?? [];

if (!is_array($cart_ids) || !is_array($quantities) || count($cart_ids) !== count($quantities) || empty($cart_ids)) {
    $_SESSION['cart_error'] = "Invalid cart update request.";
    header("Location: cart.php");
    exit();
}

mysqli_begin_transaction($conn);

try {
    $check_query = "SELECT c.id, p.stock_quantity
                    FROM cart c
                    JOIN products p ON c.product_id = p.id
                    WHERE c.id = ? AND c.user_id = ?";
    $check_stmt = mysqli_prepare($conn, $check_query);

    $update_query = "UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?";
    $update_stmt = mysqli_prepare($conn, $update_query);

    foreach ($cart_ids as $index => $cart_id_value) {
        $cart_id = (int) $cart_id_value;
        $quantity = (int) $quantities[$index];

        if ($cart_id <= 0 || $quantity <= 0) {
            throw new Exception("Cart quantities must be at least 1.");
        }

        mysqli_stmt_bind_param($check_stmt, "ii", $cart_id, $user_id);
        mysqli_stmt_execute($check_stmt);
        $result = mysqli_stmt_get_result($check_stmt);
        $cart_item = mysqli_fetch_assoc($result);

        if (!$cart_item) {
            throw new Exception("A cart item could not be found.");
        }

        if ($quantity > (int) $cart_item['stock_quantity']) {
            throw new Exception("One of the requested quantities is greater than the available stock.");
        }

        mysqli_stmt_bind_param($update_stmt, "iii", $quantity, $cart_id, $user_id);
        if (!mysqli_stmt_execute($update_stmt)) {
            throw new Exception("The cart could not be updated.");
        }
    }

    mysqli_stmt_close($check_stmt);
    mysqli_stmt_close($update_stmt);
    mysqli_commit($conn);

    $_SESSION['cart_message'] = "Cart updated successfully!";
} catch (Exception $e) {
    mysqli_rollback($conn);
    $_SESSION['cart_error'] = $e->getMessage();
}

header("Location: cart.php");
exit();
?>
