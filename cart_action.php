<?php
session_start();

// 1. Initialize Cart if it doesn't exist
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// 2. Handle "Add to Cart"
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['product_id'])) {
    $product_id = $_POST['product_id'];
    $action = isset($_POST['action']) ? $_POST['action'] : 'add';

    if ($action == 'add') {
        // If product is already in cart, increase quantity
        if (isset($_SESSION['cart'][$product_id])) {
            $_SESSION['cart'][$product_id]++;
        } else {
            // Add new product with quantity 1
            $_SESSION['cart'][$product_id] = 1;
        }
    }
    
    // Handle "Remove" (will be used in cart.php)
    if ($action == 'remove') {
        unset($_SESSION['cart'][$product_id]);
    }

    // Handle "Update Quantity"
    if ($action == 'update') {
        $qty = intval($_POST['qty']);
        if ($qty > 0) {
            $_SESSION['cart'][$product_id] = $qty;
        } else {
            unset($_SESSION['cart'][$product_id]);
        }
    }
}

// 3. Go back to the Cart Page
header("Location: cart.php");
exit();
?>