<?php
session_start();
include 'config/db_connect.php';

// Check login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'] ?? 'customer';

// 1. Fetch Order & Customer Details
// Logic: If Admin, fetch any order. If Customer, only fetch OWN order.
$sql = "SELECT o.*, u.full_name, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.user_id 
        WHERE o.order_id = ?";

if ($user_role !== 'admin') {
    $sql .= " AND o.user_id = ?"; // Add restriction for non-admins
}

$stmt = $conn->prepare($sql);

if ($user_role === 'admin') {
    $stmt->bind_param("i", $order_id);
} else {
    $stmt->bind_param("ii", $order_id, $user_id);
}

$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) {
    die("Invoice not found or access denied.");
}

// 2. Fetch Order Items
$sql_items = "SELECT * FROM order_items WHERE order_id = ?";
$stmt_items = $conn->prepare($sql_items);
$stmt_items->bind_param("i", $order_id);
$stmt_items->execute();
$items = $stmt_items->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #<?php echo $order_id; ?> - ShopKart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .shadow-lg { box-shadow: none; }
        }
    </style>
</head>
<body class="bg-gray-100 font-sans text-gray-800 p-8">

    <div class="max-w-3xl mx-auto bg-white p-10 rounded shadow-lg">
        
        <!-- Header -->
        <div class="flex justify-between items-start border-b pb-8 mb-8">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 mb-1">INVOICE</h1>
                <p class="text-sm text-gray-500">#INV-<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold tracking-tighter mb-1">
                    ShopKart<span class="text-[#f90] text-3xl">.</span>
                </div>
                <p class="text-sm text-gray-500">123 E-Commerce St, Tech City</p>
                <p class="text-sm text-gray-500">support@shopkart.com</p>
            </div>
        </div>

        <!-- Bill To / Ship To -->
        <div class="flex justify-between mb-10">
            <div>
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Billed To</h3>
                <p class="font-bold text-lg"><?php echo $order['full_name']; ?></p>
                <p class="text-gray-500 text-sm"><?php echo $order['email']; ?></p>
            </div>
            <div class="text-right max-w-xs">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Shipped To</h3>
                <p class="text-gray-700 text-sm leading-relaxed">
                    <?php echo nl2br(htmlspecialchars($order['address'])); ?>
                </p>
            </div>
            <div class="text-right">
                <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">Date</h3>
                <p class="font-bold"><?php echo date("d M, Y", strtotime($order['created_at'])); ?></p>
                <div class="mt-2">
                    <span class="bg-green-100 text-green-800 text-xs px-2 py-1 rounded uppercase font-bold">Paid</span>
                </div>
            </div>
        </div>

        <!-- Items Table -->
        <table class="w-full mb-10">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left py-3 px-4 text-xs font-bold text-gray-500 uppercase">Item Description</th>
                    <th class="text-center py-3 px-4 text-xs font-bold text-gray-500 uppercase">Qty</th>
                    <th class="text-right py-3 px-4 text-xs font-bold text-gray-500 uppercase">Price</th>
                    <th class="text-right py-3 px-4 text-xs font-bold text-gray-500 uppercase">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php while($item = $items->fetch_assoc()): ?>
                <tr class="border-b border-gray-100">
                    <td class="py-4 px-4 font-medium text-gray-900">
                        <?php echo $item['product_name']; ?>
                    </td>
                    <td class="py-4 px-4 text-center text-gray-500">
                        <?php echo $item['quantity']; ?>
                    </td>
                    <td class="py-4 px-4 text-right text-gray-500">
                        ₹<?php echo number_format($item['price']); ?>
                    </td>
                    <td class="py-4 px-4 text-right font-bold text-gray-900">
                        ₹<?php echo number_format($item['price'] * $item['quantity']); ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <!-- Totals -->
        <div class="flex justify-end border-t pt-6">
            <div class="w-64">
                <div class="flex justify-between mb-2 text-gray-600">
                    <span>Subtotal</span>
                    <span>₹<?php echo number_format($order['total_amount']); ?></span>
                </div>
                <div class="flex justify-between mb-2 text-gray-600">
                    <span>Shipping</span>
                    <span>Free</span>
                </div>
                <div class="flex justify-between border-t pt-4 mt-4">
                    <span class="font-bold text-xl">Total</span>
                    <span class="font-bold text-xl text-[#b12704]">₹<?php echo number_format($order['total_amount']); ?></span>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="mt-16 border-t pt-8 text-center text-sm text-gray-400 no-print">
            <p>Thank you for your business!</p>
            <button onclick="window.print()" class="mt-4 bg-blue-600 text-white px-6 py-2 rounded font-bold hover:bg-blue-700">
                Print Invoice
            </button>
            <a href="index.php" class="ml-4 text-blue-600 hover:underline">Back to Store</a>
        </div>

    </div>
</body>
</html>