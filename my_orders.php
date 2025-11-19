<?php
session_start();
include 'config/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Orders - ShopKart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100 font-sans text-[#0F1111]">
    
    <nav class="bg-[#131921] text-white p-4 sticky top-0 z-50">
        <div class="container mx-auto flex justify-between items-center">
            <a href="index.php" class="text-2xl font-bold tracking-tighter">
                ShopKart<span class="text-[#f90] text-3xl">.</span>
            </a>
            <div class="flex gap-4 text-sm items-center">
                <a href="index.php" class="hover:text-[#f90] flex items-center gap-1"><i data-lucide="home" class="w-4 h-4"></i> Home</a>
                <a href="cart.php" class="hover:text-[#f90] flex items-center gap-1"><i data-lucide="shopping-cart" class="w-4 h-4"></i> Cart</a>
                <a href="logout.php" class="text-red-400 hover:text-red-200 border border-red-400 rounded px-3 py-1">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="flex items-center justify-between mb-6">
            <h1 class="text-2xl font-bold">Your Orders</h1>
            <span class="text-sm text-gray-500"><?php echo $result->num_rows; ?> orders placed</span>
        </div>

        <?php if ($result->num_rows > 0): ?>
            <div class="bg-white rounded shadow-sm overflow-hidden border border-gray-200">
                <table class="w-full text-left border-collapse">
                    <thead class="bg-gray-100 border-b">
                        <tr>
                            <th class="p-4 text-sm font-bold text-gray-600">Order ID</th>
                            <th class="p-4 text-sm font-bold text-gray-600">Date</th>
                            <th class="p-4 text-sm font-bold text-gray-600 hidden md:table-cell">Shipping To</th>
                            <th class="p-4 text-sm font-bold text-gray-600">Total</th>
                            <th class="p-4 text-sm font-bold text-gray-600">Status</th>
                            <th class="p-4 text-sm font-bold text-gray-600">Invoice</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr class="border-b last:border-0 hover:bg-gray-50 transition">
                            <td class="p-4 font-medium text-blue-600">#<?php echo $row['order_id']; ?></td>
                            <td class="p-4 text-gray-700">
                                <?php echo date("d M Y", strtotime($row['created_at'])); ?>
                            </td>
                            <td class="p-4 text-gray-600 text-sm max-w-xs truncate hidden md:table-cell">
                                <?php echo htmlspecialchars($row['address'] ?? 'N/A'); ?>
                            </td>
                            <td class="p-4 font-bold text-gray-900">₹<?php echo number_format($row['total_amount']); ?></td>
                            <td class="p-4">
                                <?php 
                                    $statusColor = 'bg-yellow-100 text-yellow-800'; 
                                    if($row['status'] == 'shipped') $statusColor = 'bg-blue-100 text-blue-800';
                                    if($row['status'] == 'delivered') $statusColor = 'bg-green-100 text-green-800';
                                ?>
                                <span class="<?php echo $statusColor; ?> text-xs px-2 py-1 rounded-full font-bold uppercase">
                                    <?php echo $row['status']; ?>
                                </span>
                            </td>
                            <td class="p-4">
                                <a href="invoice.php?order_id=<?php echo $row['order_id']; ?>" target="_blank" class="text-blue-600 hover:underline flex items-center gap-1 text-sm">
                                    <i data-lucide="file-text" class="w-4 h-4"></i> View
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="bg-white p-12 text-center rounded shadow-sm border border-gray-200">
                <h3 class="text-lg font-bold text-gray-900 mb-2">No orders yet</h3>
                <a href="index.php" class="text-blue-600 hover:underline">Start Shopping</a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>