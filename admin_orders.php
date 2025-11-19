<?php
session_start();
include 'config/db_connect.php';

// --- SECURITY CHECK ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all orders with User details
$sql = "SELECT orders.*, users.full_name, users.email 
        FROM orders 
        JOIN users ON orders.user_id = users.user_id 
        ORDER BY orders.created_at DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin - All Orders</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 font-sans text-[#0F1111]">

    <!-- ADMIN NAVIGATION BAR -->
    <nav class="bg-[#131921] text-white p-4 sticky top-0 z-50 flex justify-between items-center shadow-md">
        <div class="font-bold text-xl flex items-center gap-2">
            <span class="text-[#f90]">ShopKart</span> Admin
        </div>
        <div class="flex gap-4 text-sm items-center">
            <a href="admin.php" class="hover:text-[#f90] font-medium flex items-center gap-1">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Product
            </a>
            <a href="index.php" class="hover:text-[#f90] font-medium flex items-center gap-1">
                <i data-lucide="external-link" class="w-4 h-4"></i> Website
            </a>
            <a href="logout.php" class="text-red-400 hover:text-red-200 border border-red-400 rounded px-3 py-1 transition hover:bg-red-900">Logout</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 min-h-screen">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Order Management</h1>
                <p class="text-sm text-gray-500 mt-1">Total Orders: <?php echo $result->num_rows; ?></p>
            </div>
        </div>

        <!-- Success Message Notification -->
        <?php if(isset($_GET['msg']) && $_GET['msg'] == 'updated'): ?>
            <div class="bg-green-100 text-green-800 p-4 rounded mb-6 border-l-4 border-green-500 shadow-sm flex items-center gap-2 animate-pulse">
                <i data-lucide="check-circle" class="w-5 h-5"></i> Order status updated successfully!
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider border-b">
                        <tr>
                            <th class="p-4 font-bold">Order</th>
                            <th class="p-4 font-bold">Customer</th>
                            <th class="p-4 font-bold">Shipping To</th>
                            <th class="p-4 font-bold">Payment</th>
                            <th class="p-4 font-bold">Total</th>
                            <th class="p-4 font-bold">Status</th>
                            <th class="p-4 font-bold">Invoice</th>
                            <th class="p-4 font-bold">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm divide-y divide-gray-100">
                        <?php if ($result->num_rows > 0): ?>
                            <?php while($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition duration-150">
                                <!-- Order ID -->
                                <td class="p-4 font-bold text-blue-600">
                                    #<?php echo $row['order_id']; ?>
                                </td>

                                <!-- Customer Info -->
                                <td class="p-4">
                                    <div class="font-medium text-gray-900"><?php echo $row['full_name']; ?></div>
                                    <div class="text-xs text-gray-500"><?php echo $row['email']; ?></div>
                                    <div class="text-xs text-gray-400 mt-1 flex items-center gap-1">
                                        <i data-lucide="calendar" class="w-3 h-3"></i>
                                        <?php echo date("M d, Y", strtotime($row['created_at'])); ?>
                                    </div>
                                </td>
                                
                                <!-- Shipping Address -->
                                <td class="p-4 max-w-xs">
                                    <div class="truncate text-gray-600" title="<?php echo htmlspecialchars($row['address']); ?>">
                                        <?php echo htmlspecialchars($row['address']); ?>
                                    </div>
                                </td>

                                <!-- Payment Info -->
                                <td class="p-4">
                                    <div class="text-xs font-bold text-gray-700 mb-1"><?php echo $row['payment_method']; ?></div>
                                    <?php if($row['payment_status'] == 'paid'): ?>
                                        <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded text-[10px] uppercase font-bold inline-flex items-center gap-1">
                                            <i data-lucide="check" class="w-3 h-3"></i> Paid
                                        </span>
                                    <?php else: ?>
                                        <span class="bg-orange-100 text-orange-800 px-2 py-0.5 rounded text-[10px] uppercase font-bold inline-flex items-center gap-1">
                                            <i data-lucide="clock" class="w-3 h-3"></i> Due
                                        </span>
                                    <?php endif; ?>
                                </td>

                                <!-- Total Amount -->
                                <td class="p-4 font-bold text-gray-900">
                                    ₹<?php echo number_format($row['total_amount']); ?>
                                </td>
                                
                                <!-- Current Status -->
                                <td class="p-4">
                                    <?php 
                                        $statusColor = 'bg-yellow-100 text-yellow-800';
                                        if($row['status'] == 'shipped') $statusColor = 'bg-blue-100 text-blue-800';
                                        if($row['status'] == 'delivered') $statusColor = 'bg-gray-100 text-gray-800';
                                    ?>
                                    <span class="<?php echo $statusColor; ?> px-2 py-1 rounded text-xs font-bold uppercase tracking-wide">
                                        <?php echo $row['status']; ?>
                                    </span>
                                </td>

                                <!-- Invoice Button -->
                                <td class="p-4">
                                    <a href="invoice.php?order_id=<?php echo $row['order_id']; ?>" target="_blank" class="text-gray-500 hover:text-blue-600 transition" title="Print Invoice">
                                        <i data-lucide="printer" class="w-5 h-5"></i>
                                    </a>
                                </td>

                                <!-- Action Buttons -->
                                <td class="p-4">
                                    <form action="order_action.php" method="POST">
                                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                        
                                        <?php if($row['status'] == 'pending'): ?>
                                            <button type="submit" name="status" value="shipped" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1.5 rounded shadow-sm transition flex items-center gap-1">
                                                <i data-lucide="truck" class="w-3 h-3"></i> Ship
                                            </button>
                                        <?php elseif($row['status'] == 'shipped'): ?>
                                            <button type="submit" name="status" value="delivered" class="bg-green-600 hover:bg-green-700 text-white text-xs px-3 py-1.5 rounded shadow-sm transition flex items-center gap-1">
                                                <i data-lucide="check-circle" class="w-3 h-3"></i> Deliver
                                            </button>
                                        <?php else: ?>
                                            <span class="text-gray-400 text-xs italic flex items-center gap-1">
                                                <i data-lucide="lock" class="w-3 h-3"></i> Closed
                                            </span>
                                        <?php endif; ?>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="p-12 text-center text-gray-500 bg-white">
                                    <div class="flex flex-col items-center justify-center">
                                        <i data-lucide="inbox" class="w-12 h-12 text-gray-300 mb-2"></i>
                                        <p>No orders found yet.</p>
                                    </div>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>