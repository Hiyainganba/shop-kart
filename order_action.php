<?php
session_start();
include 'config/db_connect.php';

// --- SECURITY CHECK ---
// Only Admins can access this file
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $order_id = intval($_POST['order_id']);
    $new_status = $_POST['status'];

    // Allowed statuses to prevent hacking
    $allowed_statuses = ['pending', 'shipped', 'delivered'];

    if (in_array($new_status, $allowed_statuses)) {
        $stmt = $conn->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
        $stmt->bind_param("si", $new_status, $order_id);

        if ($stmt->execute()) {
            // Success
            header("Location: admin_orders.php?msg=updated");
        } else {
            echo "Error updating record: " . $conn->error;
        }
    } else {
        echo "Invalid status.";
    }
} else {
    header("Location: admin_orders.php");
}
?>