<?php
session_start();
include 'config/db_connect.php';

// --- SECURITY CHECK ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// --- QUICK UPDATE STOCK/PRICE LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update_stock'])) {
    $id = intval($_POST['product_id']);
    $new_stock = intval($_POST['stock']);
    $new_price = floatval($_POST['price']);

    $stmt = $conn->prepare("UPDATE products SET stock_quantity = ?, price = ? WHERE id = ?");
    $stmt->bind_param("idi", $new_stock, $new_price, $id);
    
    if ($stmt->execute()) {
        $msg = "Product updated successfully!";
    } else {
        $error = "Error updating: " . $conn->error;
    }
}

// Fetch all products sorted by newest
$sql = "SELECT * FROM products ORDER BY id DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Inventory - ShopKart Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 font-sans text-[#0F1111]">

    <!-- Admin Navigation -->
    <nav class="bg-[#131921] text-white p-4 sticky top-0 z-50 flex justify-between items-center shadow-md">
        <div class="font-bold text-xl flex items-center gap-2">
            <span class="text-[#f90]">ShopKart</span> Inventory
        </div>
        <div class="flex gap-4 text-sm items-center">
            <a href="admin.php" class="hover:text-[#f90] flex items-center gap-1">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Add Product
            </a>
            <a href="admin_orders.php" class="hover:text-[#f90] flex items-center gap-1">
                <i data-lucide="shopping-bag" class="w-4 h-4"></i> Orders
            </a>
            <a href="index.php" class="hover:text-[#f90] flex items-center gap-1">
                <i data-lucide="external-link" class="w-4 h-4"></i> Website
            </a>
            <a href="logout.php" class="text-red-400 hover:text-red-200 border border-red-400 rounded px-3 py-1">Logout</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Product Inventory</h1>
            <a href="admin.php" class="bg-[#f90] text-black px-4 py-2 rounded font-bold hover:bg-[#f3a847] flex items-center gap-2 shadow-sm">
                <i data-lucide="plus" class="w-4 h-4"></i> Add New Product
            </a>
        </div>

        <!-- Feedback Message -->
        <?php if(isset($msg)): ?>
            <div class="bg-green-100 text-green-800 p-4 rounded mb-6 flex items-center gap-2 border-l-4 border-green-500 shadow-sm">
                <i data-lucide="check-circle" class="w-5 h-5"></i> <?php echo $msg; ?>
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow overflow-hidden border border-gray-200">
            <table class="w-full text-left border-collapse">
                <thead class="bg-gray-100 text-gray-600 text-xs uppercase tracking-wider border-b">
                    <tr>
                        <th class="p-4">Image</th>
                        <th class="p-4">Product Name</th>
                        <th class="p-4 w-32">Price (₹)</th>
                        <th class="p-4 w-32">Stock Qty</th>
                        <th class="p-4 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php while($row = $result->fetch_assoc()): ?>
                    <tr class="hover:bg-gray-50 transition duration-150">
                        <!-- Inline Update Form -->
                        <form method="POST">
                            <td class="p-4">
                                <img src="<?php echo $row['image']; ?>" class="w-12 h-12 object-contain border rounded bg-white p-1">
                            </td>
                            <td class="p-4">
                                <div class="font-medium text-gray-900"><?php echo $row['title']; ?></div>
                                <div class="text-xs text-gray-500"><?php echo $row['category']; ?></div>
                            </td>
                            <td class="p-4">
                                <input type="number" name="price" value="<?php echo $row['price']; ?>" class="w-full border p-1.5 rounded focus:ring-2 focus:ring-[#f90] outline-none bg-gray-50 focus:bg-white transition">
                            </td>
                            <td class="p-4">
                                <input type="number" name="stock" value="<?php echo $row['stock_quantity']; ?>" class="w-full border p-1.5 rounded focus:ring-2 focus:ring-[#f90] outline-none bg-gray-50 focus:bg-white transition <?php echo $row['stock_quantity'] < 5 ? 'text-red-600 font-bold border-red-300' : ''; ?>">
                            </td>
                            <td class="p-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                                    
                                    <!-- Quick Save Button -->
                                    <button type="submit" name="update_stock" class="bg-blue-100 text-blue-600 p-2 rounded hover:bg-blue-200 transition" title="Quick Save Price & Stock">
                                        <i data-lucide="save" class="w-4 h-4"></i>
                                    </button>
                                    
                                    <!-- Full Edit Button (Links to edit_product.php) -->
                                    <a href="edit_product.php?id=<?php echo $row['id']; ?>" class="bg-gray-100 text-gray-600 p-2 rounded hover:bg-gray-200 transition" title="Edit Full Details">
                                        <i data-lucide="pencil" class="w-4 h-4"></i>
                                    </a>
                                </div>
                            </td>
                        </form>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>