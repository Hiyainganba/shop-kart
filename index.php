<?php
session_start();
include 'config/db_connect.php';

$search = isset($_GET['search']) ? $_GET['search'] : '';
$category = isset($_GET['category']) ? $_GET['category'] : '';
$sql = "SELECT * FROM products WHERE 1=1";

if ($search) {
    $safe_search = $conn->real_escape_string($search);
    $sql .= " AND title LIKE '%$safe_search%'";
}
if ($category && $category != 'All') {
    $safe_category = $conn->real_escape_string($category);
    $sql .= " AND category = '$safe_category'";
}

$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ShopKart - Online Shopping</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>.clip-path-tag { clip-path: polygon(0 0, 100% 0, 100% 100%, 0 100%, 10px 50%); padding-left: 15px; }</style>
</head>
<body class="bg-gray-100 font-sans text-[#0F1111]">

    <nav class="bg-[#131921] text-white sticky top-0 z-50">
        <div class="container mx-auto px-4 h-16 flex items-center gap-4">
            <a href="index.php" class="text-2xl font-bold tracking-tighter hover:border border-white p-1 rounded">ShopKart<span class="text-[#f90] text-3xl">.</span></a>
            
            <div onclick="getUserLocation()" class="hidden md:flex flex-col text-xs hover:border border-white p-1 rounded cursor-pointer relative group">
                <span class="text-gray-300 pl-4">Deliver to</span>
                <div class="flex items-center font-bold"><i data-lucide="map-pin" class="w-4 h-4 mr-1"></i><span id="location-text">India</span></div>
            </div>

            <form action="index.php" method="GET" class="flex-1 flex h-10 rounded overflow-hidden focus-within:ring-2 focus-within:ring-[#f90]">
                <select name="category" class="bg-gray-100 text-black text-xs px-2 border-r border-gray-300 outline-none hidden sm:block">
                    <option value="All">All</option>
                    <option value="Electronics" <?php if($category == 'Electronics') echo 'selected'; ?>>Electronics</option>
                    <option value="Fashion" <?php if($category == 'Fashion') echo 'selected'; ?>>Fashion</option>
                    <option value="Home" <?php if($category == 'Home') echo 'selected'; ?>>Home</option>
                </select>
                <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" class="flex-1 px-4 text-black outline-none" placeholder="Search ShopKart...">
                <button type="submit" class="bg-[#f90] hover:bg-[#f3a847] px-4 text-black"><i data-lucide="search" class="w-5 h-5"></i></button>
            </form>

            <div class="flex items-center gap-4">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <div class="hidden md:flex flex-col text-xs hover:border border-white p-1 rounded cursor-pointer group relative">
                        <span class="text-gray-300">Hello, <?php echo htmlspecialchars($_SESSION['user_name']); ?></span>
                        <div class="font-bold">Account & Lists</div>
                        <div class="absolute top-full right-0 w-40 bg-white rounded shadow-lg hidden group-hover:block text-black z-50 border border-gray-200">
                            <div class="p-2 text-sm font-bold border-b bg-gray-50">My Account</div>
                            <a href="my_orders.php" class="block px-4 py-2 hover:bg-gray-100">My Orders</a>
                            <a href="logout.php" class="block px-4 py-2 hover:bg-gray-100 text-red-600">Logout</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="login.php" class="hidden md:flex flex-col text-xs hover:border border-white p-1 rounded cursor-pointer">
                        <span class="text-gray-300">Hello, Sign in</span>
                        <div class="font-bold">Account & Lists</div>
                    </a>
                <?php endif; ?>

                <a href="cart.php" class="flex items-end hover:border border-white p-1 rounded relative">
                    <div class="relative"><i data-lucide="shopping-cart" class="w-7 h-7"></i><span class="absolute -top-1 -right-1 bg-[#f90] text-black text-xs font-bold w-4 h-4 rounded-full flex items-center justify-center"><?php echo isset($_SESSION['cart']) ? count($_SESSION['cart']) : 0; ?></span></div>
                    <span class="font-bold text-sm hidden sm:block translate-y-1 ml-1">Cart</span>
                </a>
            </div>
        </div>
        
        <div class="bg-[#232f3e] text-white text-sm py-2 px-4 flex gap-6 overflow-x-auto items-center">
            <a href="index.php" class="flex items-center gap-1 font-bold"><i data-lucide="menu" class="w-4 h-4"></i> All</a>
            <a href="index.php?category=Electronics">Electronics</a>
            <a href="index.php?category=Fashion">Fashion</a>
            <a href="index.php?category=Home">Home</a>
            
            <!-- ADMIN LINKS: Now includes Inventory -->
            <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] == 'admin'): ?>
                <a href="admin_products.php" class="text-[#f90] font-bold border border-[#f90] px-2 rounded hover:bg-[#f90] hover:text-black transition">Inventory</a>
                <a href="admin.php" class="text-[#f90] font-bold border border-[#f90] px-2 rounded hover:bg-[#f90] hover:text-black transition">Add Product</a>
                <a href="admin_orders.php" class="text-[#f90] font-bold border border-[#f90] px-2 rounded hover:bg-[#f90] hover:text-black transition">View Orders</a>
            <?php endif; ?>
        </div>
    </nav>

    <?php if(!$search && !$category): ?>
    <div class="relative bg-gray-200 h-[300px] md:h-[400px] overflow-hidden">
        <div class="absolute inset-0 flex items-center justify-center bg-gradient-to-r from-gray-900 to-gray-800">
            <div class="text-center text-white px-4">
                <h1 class="text-3xl md:text-5xl font-bold mb-4">Big Sale is Live!</h1>
                <p class="text-xl mb-6">Up to 70% off on Electronics & Fashion</p>
                <button class="bg-[#f90] text-black font-bold py-2 px-6 rounded shadow-lg hover:bg-[#f3a847]">Shop Now</button>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 w-full h-24 bg-gradient-to-t from-gray-100 to-transparent"></div>
    </div>
    <?php endif; ?>

    <div class="container mx-auto px-4 <?php echo (!$search && !$category) ? '-mt-16' : 'mt-8'; ?> relative z-10 mb-12">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php
            if ($result->num_rows > 0) {
                while($row = $result->fetch_assoc()) {
                    // CALCULATE DISCOUNT
                    $has_discount = false;
                    $discount_percent = 0;
                    if ($row['mrp'] > $row['price']) {
                        $has_discount = true;
                        $discount_percent = round((($row['mrp'] - $row['price']) / $row['mrp']) * 100);
                    }
            ?>
                <div class="bg-white p-4 flex flex-col h-full z-10 relative hover:shadow-xl transition-shadow border border-gray-200 rounded-sm">
                    <?php if($row['is_bestseller']): ?>
                        <div class="absolute top-0 left-0 bg-[#f90] text-white text-xs font-bold px-2 py-1 clip-path-tag">Best Seller</div>
                    <?php endif; ?>
                    
                    <a href="product.php?id=<?php echo $row['id']; ?>" class="h-48 flex items-center justify-center mb-4 bg-gray-50 p-2 cursor-pointer">
                        <img src="<?php echo $row['image']; ?>" alt="<?php echo $row['title']; ?>" class="max-h-full max-w-full object-contain hover:scale-105 transition-transform">
                    </a>
                    
                    <div class="flex-1">
                        <a href="product.php?id=<?php echo $row['id']; ?>" class="font-medium text-sm text-gray-900 line-clamp-2 hover:text-[#c7511f] cursor-pointer mb-1">
                            <?php echo $row['title']; ?>
                        </a>
                        <div class="flex items-center mb-1">
                            <i data-lucide="star" class="w-3 h-3 text-[#f90] fill-[#f90]"></i>
                            <i data-lucide="star" class="w-3 h-3 text-[#f90] fill-[#f90]"></i>
                            <i data-lucide="star" class="w-3 h-3 text-[#f90] fill-[#f90]"></i>
                            <i data-lucide="star" class="w-3 h-3 text-[#f90] fill-[#f90]"></i>
                            <span class="text-xs text-blue-600 ml-1 hover:underline cursor-pointer"><?php echo $row['reviews']; ?></span>
                        </div>
                        
                        <!-- DISCOUNT PRICE DISPLAY -->
                        <div class="mb-2">
                            <div class="text-2xl font-medium">
                                <span class="text-xs align-top">₹</span><?php echo number_format($row['price']); ?>
                            </div>
                            <?php if($has_discount): ?>
                                <div class="text-sm">
                                    <span class="text-gray-500 line-through">M.R.P.: ₹<?php echo number_format($row['mrp']); ?></span>
                                    <span class="text-sm text-[#CC0C39] font-bold ml-1">(<?php echo $discount_percent; ?>% off)</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="text-xs text-gray-500 mb-2"><?php echo $row['delivery_info']; ?></div>
                    </div>
                    
                    <form action="cart_action.php" method="POST">
                        <input type="hidden" name="product_id" value="<?php echo $row['id']; ?>">
                        <?php if($row['stock_quantity'] > 0): ?>
                            <button type="submit" class="w-full bg-[#ffd814] hover:bg-[#f7ca00] border border-[#fcd200] text-black text-sm py-1.5 rounded-full active:scale-95 transition-transform">Add to Cart</button>
                        <?php else: ?>
                            <button disabled class="w-full bg-gray-200 text-gray-500 border border-gray-300 text-sm py-1.5 rounded-full cursor-not-allowed">Out of Stock</button>
                        <?php endif; ?>
                    </form>
                </div>
            <?php
                }
            } else {
                echo "<div class='col-span-full text-center py-12 bg-white rounded shadow'><h2 class='text-xl font-bold mb-2'>No products found</h2><a href='index.php' class='text-blue-600 hover:underline'>Clear Filters</a></div>";
            }
            ?>
        </div>
    </div>

    <footer class="bg-[#232f3e] text-white mt-12 pb-8">
        <div class="bg-[#37475a] py-4 text-center text-sm hover:bg-[#485769] cursor-pointer" onclick="window.scrollTo({top:0, behavior:'smooth'})">Back to top</div>
        <div class="text-center pt-8 text-xs text-gray-400">© 1996-2024, ShopKart.com, Inc. or its affiliates</div>
    </footer>

    <script>
        lucide.createIcons();
        async function getUserLocation() {
            const locText = document.getElementById('location-text');

            if (!navigator.geolocation) {
                alert("Geolocation is not supported by your browser");
                return;
            }

            navigator.geolocation.getCurrentPosition(async (position) => {
                const lat = position.coords.latitude;
                const lon = position.coords.longitude;

                try {
                    const response = await fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${lat}&lon=${lon}&accept-language=en`);
                    if (!response.ok) throw new Error("API Error");

                    const data = await response.json();
                    const addr = data.address;

                    if (addr) {
                        const placeName = addr.city || addr.town || addr.village || addr.municipality || addr.suburb || addr.state_district || addr.state || "India"; 
                        const postcode = addr.postcode ? ` ${addr.postcode}` : '';
                        const fullLocation = `${placeName}${postcode}`;

                        locText.textContent = fullLocation;
                        localStorage.setItem('user_location', fullLocation);
                    }
                } catch (error) {
                    console.error("Error fetching address:", error);
                    locText.textContent = "India";
                }
            });
        }
        
        // Load saved location
        window.onload = function() {
            const savedLoc = localStorage.getItem('user_location');
            if (savedLoc) {
                document.getElementById('location-text').textContent = savedLoc;
            }
        };
    </script>
</body>
</html>