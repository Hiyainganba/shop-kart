<?php
session_start();
include 'config/db_connect.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$sql = "SELECT * FROM products WHERE id = $id";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
    header("Location: index.php");
    exit();
}

$product = $result->fetch_assoc();

// Calculate Discount Logic
$has_discount = false;
$discount_percent = 0;
if ($product['mrp'] > $product['price']) {
    $has_discount = true;
    $discount_percent = round((($product['mrp'] - $product['price']) / $product['mrp']) * 100);
}

// Related products logic
$related_sql = "SELECT * FROM products WHERE id != $id ORDER BY RAND() LIMIT 4";
$related_result = $conn->query($related_sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo $product['title']; ?> - ShopKart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-white font-sans text-[#0F1111]">

    <!-- Navbar -->
    <nav class="bg-[#131921] text-white p-4 flex items-center justify-between sticky top-0 z-50">
        <a href="index.php" class="text-2xl font-bold tracking-tighter">ShopKart<span class="text-[#f90] text-3xl">.</span></a>
        <div class="flex items-center gap-4">
             <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                <a href="admin_orders.php" class="text-sm font-bold text-[#f90] border border-[#f90] px-2 py-1 rounded hover:bg-[#f90] hover:text-black">Admin Panel</a>
             <?php endif; ?>
             <a href="cart.php" class="flex items-end hover:border border-white p-1 rounded relative">
                <div class="relative"><i data-lucide="shopping-cart" class="w-7 h-7"></i><span class="absolute -top-1 -right-1 bg-[#f90] text-black text-xs font-bold w-4 h-4 rounded-full flex items-center justify-center"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span></div>
                <span class="font-bold text-sm hidden sm:block translate-y-1 ml-1">Cart</span>
            </a>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="flex justify-between items-center mb-6">
            <a href="index.php" class="text-sm text-gray-500 hover:underline inline-block">&larr; Back to results</a>
            <!-- Admin Edit Shortcut -->
            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="bg-[#131921] text-white px-4 py-2 rounded shadow hover:bg-gray-800 text-sm font-bold flex items-center gap-2 transition"><i data-lucide="pencil" class="w-4 h-4"></i> Edit This Product</a>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-12 mb-16">
            <!-- Left: Image -->
            <div class="flex justify-center bg-gray-50 p-8 rounded border sticky top-24 h-fit">
                <img src="<?php echo $product['image']; ?>" alt="<?php echo $product['title']; ?>" class="max-h-[400px] object-contain mix-blend-multiply">
            </div>

            <!-- Right: Details -->
            <div>
                <h1 class="text-3xl font-medium mb-2"><?php echo $product['title']; ?></h1>
                
                <div class="flex items-center mb-4 text-sm">
                    <div class="flex text-[#f90]">
                        <i data-lucide="star" class="w-4 h-4 fill-current"></i><i data-lucide="star" class="w-4 h-4 fill-current"></i><i data-lucide="star" class="w-4 h-4 fill-current"></i><i data-lucide="star" class="w-4 h-4 fill-current"></i><i data-lucide="star-half" class="w-4 h-4 fill-current"></i>
                    </div>
                    <span class="text-blue-600 ml-2 hover:underline cursor-pointer"><?php echo $product['reviews']; ?> ratings</span>
                </div>

                <div class="border-t border-b py-4 my-4">
                    <!-- DISCOUNT DISPLAY SECTION -->
                    <?php if($has_discount): ?>
                        <div class="text-xl text-[#CC0C39] font-light mb-1">
                            -<?php echo $discount_percent; ?>% 
                            <span class="text-3xl font-medium text-black align-top ml-1">₹<?php echo number_format($product['price']); ?></span>
                        </div>
                        <div class="text-sm text-gray-500">
                            M.R.P.: <span class="line-through">₹<?php echo number_format($product['mrp']); ?></span>
                        </div>
                    <?php else: ?>
                        <div class="text-sm text-gray-500 mb-1">Price:</div>
                        <div class="text-3xl font-medium text-red-700">
                            <span class="text-xs align-top text-black">₹</span><?php echo number_format($product['price']); ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="text-sm text-gray-600 mt-2">Inclusive of all taxes</div>
                </div>

                <div class="mb-6">
                    <!-- Stock Status -->
                    <?php if($product['stock_quantity'] > 10): ?>
                        <div class="text-xl text-green-700 font-medium mb-2">In Stock.</div>
                    <?php elseif($product['stock_quantity'] > 0): ?>
                        <div class="text-xl text-red-700 font-medium mb-2">Only <?php echo $product['stock_quantity']; ?> left in stock!</div>
                    <?php else: ?>
                        <div class="text-xl text-red-600 font-medium mb-2">Currently Unavailable.</div>
                    <?php endif; ?>

                    <div class="flex items-center gap-2 text-sm mb-2">
                        <i data-lucide="truck" class="w-4 h-4 text-gray-500"></i>
                        <span><?php echo $product['delivery_info']; ?></span>
                    </div>
                    <div class="flex items-center gap-2 text-sm text-green-700 font-bold">
                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                        <span>Deliver to India</span>
                    </div>
                </div>

                <!-- Buy Box -->
                <div class="border border-gray-300 rounded p-6 bg-white shadow-sm max-w-sm">
                    <div class="text-xl font-bold text-red-700 mb-4">₹<?php echo number_format($product['price']); ?></div>

                    <?php if($product['stock_quantity'] > 0): ?>
                        <form action="cart_action.php" method="POST" class="flex flex-col gap-3">
                            <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                            <button type="submit" name="action" value="add" class="w-full bg-[#ffd814] hover:bg-[#f7ca00] border border-[#fcd200] py-2 rounded-full shadow-sm text-sm font-medium cursor-pointer">Add to Cart</button>
                            <button type="submit" name="action" value="add" class="w-full bg-[#fa8900] hover:bg-[#e37a00] border border-[#ca6d00] py-2 rounded-full shadow-sm text-sm font-medium cursor-pointer">Buy Now</button>
                        </form>
                    <?php else: ?>
                        <button disabled class="w-full bg-gray-200 text-gray-500 border border-gray-300 py-2 rounded-full shadow-sm text-sm font-medium cursor-not-allowed">Out of Stock</button>
                        <p class="text-xs text-gray-500 mt-2">We don't know when or if this item will be back in stock.</p>
                    <?php endif; ?>

                    <div class="mt-4 text-xs text-gray-500 flex items-center gap-1"><i data-lucide="lock" class="w-3 h-3"></i> Secure transaction</div>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <div class="border-t pt-8">
            <h2 class="text-2xl font-bold mb-6">Products related to this item</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6">
                <?php while($related = $related_result->fetch_assoc()): 
                     // Calculate Discount for Related Items
                     $r_discount = 0;
                     if($related['mrp'] > $related['price']) {
                         $r_discount = round((($related['mrp'] - $related['price']) / $related['mrp']) * 100);
                     }
                ?>
                    <div class="bg-white p-4 border rounded hover:shadow-lg transition">
                        <a href="product.php?id=<?php echo $related['id']; ?>" class="block h-40 flex items-center justify-center mb-4 p-2">
                            <img src="<?php echo $related['image']; ?>" class="max-h-full max-w-full object-contain">
                        </a>
                        <a href="product.php?id=<?php echo $related['id']; ?>" class="block font-medium text-sm text-blue-700 hover:underline line-clamp-2 mb-1">
                            <?php echo $related['title']; ?>
                        </a>
                        <div class="flex items-center mb-1">
                            <i data-lucide="star" class="w-3 h-3 text-[#f90] fill-[#f90]"></i><i data-lucide="star" class="w-3 h-3 text-[#f90] fill-[#f90]"></i><i data-lucide="star" class="w-3 h-3 text-[#f90] fill-[#f90]"></i><i data-lucide="star" class="w-3 h-3 text-[#f90] fill-[#f90]"></i>
                            <span class="text-xs text-gray-500 ml-1"><?php echo $related['reviews']; ?></span>
                        </div>
                        <div class="text-lg font-bold text-red-700">
                            ₹<?php echo number_format($related['price']); ?>
                        </div>
                        <?php if($r_discount > 0): ?>
                            <div class="text-xs text-gray-500">
                                M.R.P: <span class="line-through">₹<?php echo number_format($related['mrp']); ?></span> 
                                <span class="text-[#CC0C39] font-bold">(<?php echo $r_discount; ?>% off)</span>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endwhile; ?>
            </div>
        </div>
    </div>
    <script>lucide.createIcons();</script>
</body>
</html>