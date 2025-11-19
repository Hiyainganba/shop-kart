
<?php
session_start();
include 'config/db_connect.php';

// --- SECURITY CHECK ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$message = "";
$target_dir = "assets/images/";

// Fetch current data
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$product = $stmt->get_result()->fetch_assoc();

if (!$product) {
    die("Product not found.");
}

// --- UPDATE LOGIC ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $price = floatval($_POST['price']);
    $mrp = floatval($_POST['mrp']); // New MRP field
    $category = $_POST['category'];
    $delivery = $_POST['delivery'];
    $stock = intval($_POST['stock']);
    
    // Default to existing image
    $image_path = $product['image']; 
    
    // Logic Check: Selling Price vs MRP
    if ($price > $mrp && $mrp > 0) {
        $message = "Error: Selling Price cannot be higher than MRP.";
    } else {
        // Check if NEW image is uploaded
        if (!empty($_FILES["image"]["name"])) {
            $uploadOk = 1;
            
            if ($_FILES["image"]["error"] == 0) {
                $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
                $new_filename = time() . "_" . rand(1000, 9999) . "." . $file_extension;
                $target_file = $target_dir . $new_filename;

                // Dimension Check (Strict 800x533)
                $check = getimagesize($_FILES["image"]["tmp_name"]);
                if ($check !== false) {
                    $width = $check[0];
                    $height = $check[1];
                    if ($width != 800 || $height != 533) {
                        $message = "Error: New image must be exactly 800x533 pixels. Yours: {$width}x{$height}.";
                        $uploadOk = 0;
                    }
                } else {
                    $message = "File is not an image.";
                    $uploadOk = 0;
                }

                if ($uploadOk == 1) {
                    if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                        $image_path = $target_file; // Update path to new image
                    } else {
                        $message = "Error uploading file.";
                        $uploadOk = 0;
                    }
                }
            }
        }

        // Update Database if no error
        if (empty($message) || strpos($message, 'Error') === false) {
            // Added 'mrp' to the update query
            $update_stmt = $conn->prepare("UPDATE products SET title=?, price=?, mrp=?, image=?, category=?, delivery_info=?, stock_quantity=? WHERE id=?");
            $update_stmt->bind_param("sddsssii", $title, $price, $mrp, $image_path, $category, $delivery, $stock, $id);
            
            if ($update_stmt->execute()) {
                $message = "Product updated successfully!";
                // Refresh data so the form shows new values
                $stmt->execute();
                $product = $stmt->get_result()->fetch_assoc();
            } else {
                $message = "Database Error: " . $conn->error;
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - ShopKart Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-50 font-sans text-[#0F1111]">

    <nav class="bg-[#131921] text-white p-4 sticky top-0 z-50 flex justify-between items-center shadow-md">
        <div class="font-bold text-xl flex items-center gap-2">
            <span class="text-[#f90]">ShopKart</span> Admin
        </div>
        <a href="admin_products.php" class="text-white hover:text-[#f90] flex items-center gap-1 text-sm">
            <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to Inventory
        </a>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-2xl">
        <div class="bg-white p-8 rounded shadow-lg">
            <div class="flex justify-between items-center mb-6 border-b pb-4">
                <h1 class="text-2xl font-bold">Edit Product</h1>
                <span class="text-gray-400 text-sm">ID: #<?php echo $product['id']; ?></span>
            </div>
            
            <?php if($message): ?>
                <div class="<?php echo strpos($message, 'Error') !== false ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> p-3 rounded mb-4 text-sm font-bold">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-5">
                
                <!-- Title -->
                <div>
                    <label class="block text-sm font-bold mb-1 text-gray-700">Product Title</label>
                    <input type="text" name="title" value="<?php echo htmlspecialchars($product['title']); ?>" required class="w-full border p-2 rounded focus:ring-2 focus:ring-[#f90] outline-none">
                </div>

                <!-- Price & Stock Row -->
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-bold mb-1 text-gray-700">MRP (Original)</label>
                        <input type="number" name="mrp" value="<?php echo $product['mrp']; ?>" required class="w-full border p-2 rounded bg-gray-50 focus:ring-2 focus:ring-[#f90] outline-none">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-bold mb-1 text-gray-700">Selling Price</label>
                        <input type="number" name="price" value="<?php echo $product['price']; ?>" required class="w-full border p-2 rounded focus:ring-2 focus:ring-[#f90] outline-none font-bold text-green-700 border-green-200">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-bold mb-1 text-gray-700">Stock Qty</label>
                        <input type="number" name="stock" value="<?php echo $product['stock_quantity']; ?>" required class="w-full border p-2 rounded focus:ring-2 focus:ring-[#f90] outline-none">
                    </div>
                </div>

                <!-- Category & Delivery Row -->
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-bold mb-1 text-gray-700">Category</label>
                        <select name="category" class="w-full border p-2 rounded focus:ring-2 focus:ring-[#f90] outline-none bg-white">
                            <?php 
                                $cats = ['Electronics', 'Fashion', 'Home', 'Books', 'Beauty', 'Toys'];
                                foreach($cats as $c) {
                                    $selected = ($product['category'] == $c) ? 'selected' : '';
                                    echo "<option value='$c' $selected>$c</option>";
                                }
                            ?>
                        </select>
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-bold mb-1 text-gray-700">Delivery Info</label>
                        <input type="text" name="delivery" value="<?php echo htmlspecialchars($product['delivery_info']); ?>" class="w-full border p-2 rounded focus:ring-2 focus:ring-[#f90] outline-none">
                    </div>
                </div>

                <!-- Image Update -->
                <div>
                    <label class="block text-sm font-bold mb-2 text-gray-700">Product Image</label>
                    <div class="flex items-start gap-4">
                        <img src="<?php echo $product['image']; ?>" class="w-24 h-24 object-contain border rounded bg-gray-50">
                        <div class="flex-1">
                            <input type="file" name="image" accept="image/*" class="w-full border p-2 rounded focus:ring-2 focus:ring-[#f90] outline-none bg-white text-sm">
                            <p class="text-xs text-gray-500 mt-1">Leave empty to keep current image.</p>
                            <p class="text-xs text-red-600 mt-1 font-bold">New images MUST be 800x533 pixels.</p>
                        </div>
                    </div>
                </div>

                <button type="submit" class="bg-[#f90] hover:bg-[#f3a847] text-black font-bold py-3 rounded shadow-md mt-2 transition transform active:scale-95">
                    Save Changes
                </button>
            </form>
        </div>
    </div>

    <script>lucide.createIcons();</script>
</body>
</html>