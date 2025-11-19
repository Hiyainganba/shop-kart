<?php
session_start(); 
include 'config/db_connect.php';

// --- SECURITY CHECK ---
if (!isset($_SESSION['user_id']) || !isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = "";

// Create upload directory
$target_dir = "assets/images/";
if (!file_exists($target_dir)) {
    mkdir($target_dir, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = $_POST['title'];
    $price = floatval($_POST['price']); // Selling Price
    $mrp = floatval($_POST['mrp']);     // Original Price (MRP)
    $category = $_POST['category'];
    $delivery = $_POST['delivery'];
    $stock = intval($_POST['stock']);

    // Logic Check: Selling Price vs MRP
    if ($price > $mrp && $mrp > 0) {
        $message = "Error: Selling Price cannot be higher than MRP.";
    } else {
        // --- FILE UPLOAD LOGIC ---
        $uploadOk = 1;
        $image_path = "";
        
        if(isset($_FILES["image"]) && $_FILES["image"]["error"] == 0) {
            $file_extension = strtolower(pathinfo($_FILES["image"]["name"], PATHINFO_EXTENSION));
            $new_filename = time() . "_" . rand(1000, 9999) . "." . $file_extension;
            $target_file = $target_dir . $new_filename;

            $check = getimagesize($_FILES["image"]["tmp_name"]);
            if($check === false) {
                $message = "File is not an image.";
                $uploadOk = 0;
            } else {
                // Strict Dimension Check (800x533)
                $width = $check[0];
                $height = $check[1];
                if ($width != 800 || $height != 533) {
                    $message = "Error: Image must be exactly 800x533 pixels. Yours: {$width}x{$height}.";
                    $uploadOk = 0;
                }
            }

            if ($_FILES["image"]["size"] > 5000000) {
                $message = "Sorry, your file is too large (Max 5MB).";
                $uploadOk = 0;
            }

            if ($uploadOk == 1) {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_path = $target_file; 
                } else {
                    $message = "Error moving file.";
                    $uploadOk = 0;
                }
            }
        } else {
            $message = "Please select an image file.";
            $uploadOk = 0;
        }

        // Insert Database (Includes MRP now)
        if ($uploadOk == 1 && $image_path != "") {
            $stmt = $conn->prepare("INSERT INTO products (title, price, mrp, image, category, delivery_info, stock_quantity) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("sddsssi", $title, $price, $mrp, $image_path, $category, $delivery, $stock);

            if ($stmt->execute()) {
                $message = "Product added successfully!";
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
    <title>Admin - Add Product</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">
    <nav class="bg-[#131921] text-white p-4 mb-8 flex justify-between sticky top-0 z-50">
        <div class="font-bold text-xl">ShopKart Admin</div>
        <div class="flex gap-4 text-sm items-center">
            <a href="admin_orders.php" class="hover:text-[#f90]">View Orders</a>
            <a href="index.php" class="hover:text-[#f90]">Back to Website</a>
            <a href="logout.php" class="text-red-400 hover:text-red-200">Logout</a>
        </div>
    </nav>

    <div class="container mx-auto px-4 max-w-lg">
        <div class="bg-white p-8 rounded shadow">
            <h2 class="text-xl font-bold mb-4">Add New Product</h2>
            
            <?php if($message): ?>
                <div class="<?php echo strpos($message, 'Error') !== false ? 'bg-red-100 text-red-800' : 'bg-green-100 text-green-800'; ?> p-3 rounded mb-4 text-sm font-bold">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST" enctype="multipart/form-data" class="flex flex-col gap-4">
                <div>
                    <label class="block text-sm font-bold mb-1">Product Title</label>
                    <input type="text" name="title" required class="w-full border p-2 rounded focus:ring-2 focus:ring-[#f90] outline-none">
                </div>
                
                <!-- Price Row -->
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-bold mb-1">MRP (Original)</label>
                        <input type="number" name="mrp" required class="w-full border p-2 rounded focus:ring-2 focus:ring-[#f90] outline-none bg-gray-50" placeholder="Original Price">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-bold mb-1">Selling Price</label>
                        <input type="number" name="price" required class="w-full border p-2 rounded focus:ring-2 focus:ring-[#f90] outline-none font-bold text-green-700" placeholder="Discounted">
                    </div>
                </div>

                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-sm font-bold mb-1">Stock Qty</label>
                        <input type="number" name="stock" value="10" min="0" required class="w-full border p-2 rounded focus:ring-2 focus:ring-[#f90] outline-none">
                    </div>
                    <div class="flex-1">
                        <label class="block text-sm font-bold mb-1">Category</label>
                        <select name="category" class="w-full border p-2 rounded focus:ring-2 focus:ring-[#f90] outline-none">
                            <option>Electronics</option>
                            <option>Fashion</option>
                            <option>Home</option>
                            <option>Books</option>
                            <option>Beauty</option>
                            <option>Toys</option>
                        </select>
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-bold mb-1">Product Image</label>
                    <input type="file" name="image" accept="image/*" required class="w-full border p-2 rounded focus:ring-2 focus:ring-[#f90] outline-none bg-white">
                    <p class="text-xs text-red-600 font-bold mt-1">Requirement: Image MUST be exactly 800x533 pixels.</p>
                </div>

                <div>
                    <label class="block text-sm font-bold mb-1">Delivery Info</label>
                    <input type="text" name="delivery" value="Free Delivery" class="w-full border p-2 rounded focus:ring-2 focus:ring-[#f90] outline-none">
                </div>
                <button class="bg-[#f90] hover:bg-[#f3a847] p-2 rounded font-bold mt-2 shadow-sm text-black">Add Product</button>
            </form>
        </div>
    </div>
</body>
</html>