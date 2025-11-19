<?php
session_start();
include 'config/db_connect.php';

$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$products = [];
$total = 0;

// Fetch product details
if (!empty($cartItems)) {
    $ids = implode(',', array_keys($cartItems));
    // Only run query if we have IDs
    if (!empty($ids)) {
        $sql = "SELECT * FROM products WHERE id IN ($ids)";
        $result = $conn->query($sql);
        while ($row = $result->fetch_assoc()) {
            $row['qty'] = $cartItems[$row['id']];
            $row['line_total'] = $row['price'] * $row['qty'];
            $products[] = $row;
            $total += $row['line_total'];
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Shopping Cart - ShopKart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100 font-sans text-[#0F1111]">

    <nav class="bg-[#131921] text-white p-4 flex items-center justify-between sticky top-0 z-50">
        <a href="index.php" class="text-2xl font-bold tracking-tighter">
            ShopKart<span class="text-[#f90] text-3xl">.</span>
        </a>
        <div class="flex items-center gap-4">
             <span class="text-sm">Hello, <?php echo isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Sign in'; ?></span>
             <a href="index.php" class="hover:text-[#f90]">Continue Shopping</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 flex flex-col lg:flex-row gap-6 items-start">
        
        <!-- LEFT: Cart Items -->
        <div class="bg-white p-6 shadow-sm flex-1 w-full">
            <h2 class="text-2xl font-medium border-b pb-4 mb-4 flex justify-between items-center">
                Shopping Cart
                <span class="text-sm font-normal text-gray-500">Price</span>
            </h2>

            <?php if (empty($products)): ?>
                <div class="text-center py-10">
                    <h3 class="text-xl font-bold mb-4">Your ShopKart Basket is empty</h3>
                    <a href="index.php" class="text-blue-600 hover:underline">Shop today's deals</a>
                </div>
            <?php else: ?>
                
                <?php foreach ($products as $product): ?>
                <div class="flex gap-4 py-6 border-b last:border-0">
                    <div class="w-32 h-32 flex-shrink-0 flex items-center justify-center bg-gray-50">
                        <img src="<?php echo $product['image']; ?>" class="max-w-full max-h-full object-contain">
                    </div>

                    <div class="flex-1">
                        <h3 class="font-medium text-lg text-blue-700 line-clamp-2"><?php echo $product['title']; ?></h3>
                        
                        <!-- STOCK INFO DISPLAY -->
                        <div class="mt-1 text-sm">
                            <?php if($product['stock_quantity'] > 10): ?>
                                <span class="text-green-600 font-medium">In Stock</span>
                                <span class="text-gray-500 text-xs">(<?php echo $product['stock_quantity']; ?> available)</span>
                            <?php elseif($product['stock_quantity'] > 0): ?>
                                <span class="text-red-600 font-bold">Only <?php echo $product['stock_quantity']; ?> left in stock - order soon.</span>
                            <?php else: ?>
                                <span class="text-red-600 font-bold">Out of Stock</span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="text-xs text-gray-500 mt-1"><?php echo $product['delivery_info']; ?></div>
                        
                        <!-- Unit Price Display -->
                        <div class="text-xs text-gray-400 mt-1">Unit Price: ₹<?php echo number_format($product['price']); ?></div>

                        <div class="mt-4 flex items-center gap-4">
                            <form action="cart_action.php" method="POST" class="flex items-center border rounded bg-gray-50 shadow-sm text-sm">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="update">
                                <!-- Max quantity limited by stock -->
                                <input type="number" name="qty" value="<?php echo $product['qty']; ?>" min="1" max="<?php echo $product['stock_quantity']; ?>" class="w-12 text-center bg-transparent outline-none" onchange="this.form.submit()">
                            </form>
                            <div class="h-4 w-px bg-gray-300"></div>
                            <form action="cart_action.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                                <input type="hidden" name="action" value="remove">
                                <button type="submit" class="text-sm text-blue-600 hover:underline">Delete</button>
                            </form>
                        </div>
                    </div>

                    <div class="font-bold text-lg">
                        ₹<?php echo number_format($product['line_total']); ?>
                    </div>
                </div>
                <?php endforeach; ?>

                <div class="text-right pt-4 text-xl">
                    Subtotal (<?php echo array_sum($cartItems); ?> items): <span class="font-bold">₹<?php echo number_format($total); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- RIGHT: Checkout Sidebar -->
        <?php if (!empty($products)): ?>
        <div class="bg-white p-6 shadow-sm w-full lg:w-80 flex-shrink-0">
            <div class="flex items-center gap-2 mb-4 text-green-700 text-sm">
                <i data-lucide="shield-check" class="w-5 h-5"></i>
                <span>Your order is eligible for FREE Delivery.</span>
            </div>
            <div class="text-lg mb-6">
                Subtotal: <span class="font-bold">₹<?php echo number_format($total); ?></span>
            </div>
            
            <?php if(isset($_SESSION['user_id'])): ?>
                <form action="checkout.php" method="POST">
                    <button type="submit" class="w-full bg-[#ffd814] hover:bg-[#f7ca00] border border-[#fcd200] py-2 rounded-md shadow-sm text-sm font-medium">
                        Proceed to Buy
                    </button>
                </form>
            <?php else: ?>
                <a href="login.php" class="block text-center w-full bg-[#ffd814] hover:bg-[#f7ca00] border border-[#fcd200] py-2 rounded-md shadow-sm text-sm font-medium">
                    Sign in to Checkout
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>

    </div>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>