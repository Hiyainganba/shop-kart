<?php
session_start();
include 'config/db_connect.php';

// Ensure user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$cartItems = $_SESSION['cart'];
$total_amount = 0;

// 1. Fetch Products and Perform Stock Check
$ids = implode(',', array_keys($cartItems));
$sql = "SELECT id, title, price, stock_quantity FROM products WHERE id IN ($ids)";
$result = $conn->query($sql);
$products = [];
$stock_error = "";

while ($row = $result->fetch_assoc()) {
    $qty = $cartItems[$row['id']];
    $row['qty'] = $qty;
    
    // STOCK CHECK LOGIC
    if ($row['stock_quantity'] < $qty) {
        $stock_error .= "Sorry, only " . $row['stock_quantity'] . " units of '" . $row['title'] . "' are available.<br>";
    }
    
    $total_amount += $row['price'] * $qty;
    $products[] = $row;
}

// --- HANDLE FORM SUBMISSION ---
$order_success = false;
$order_id = 0;

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['address'])) {
    
    // Only proceed if there are no stock errors
    if (empty($stock_error)) { 
        $address = $conn->real_escape_string($_POST['address']);
        $payment_method = $_POST['payment_method'];
        
        // Payment Logic
        $payment_status = ($payment_method == 'COD') ? 'pending' : 'paid';
        
        // 1. Insert Order
        $stmt = $conn->prepare("INSERT INTO orders (user_id, total_amount, status, address, payment_method, payment_status) VALUES (?, ?, 'pending', ?, ?, ?)");
        $stmt->bind_param("idsss", $user_id, $total_amount, $address, $payment_method, $payment_status);

        if ($stmt->execute()) {
            $order_id = $stmt->insert_id;
            
            // Prepare statements for Items and Stock Update
            $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price) VALUES (?, ?, ?, ?, ?)");
            $stmt_stock = $conn->prepare("UPDATE products SET stock_quantity = stock_quantity - ? WHERE id = ?");
            
            foreach ($products as $p) {
                // 2. Insert Order Item
                $stmt_item->bind_param("iisid", $order_id, $p['id'], $p['title'], $p['qty'], $p['price']);
                $stmt_item->execute();
                
                // 3. DECREASE STOCK
                $stmt_stock->bind_param("ii", $p['qty'], $p['id']);
                $stmt_stock->execute();
            }

            unset($_SESSION['cart']); // Clear cart
            $order_success = true;
        } else {
            $error = "Error: " . $conn->error;
        }
    } else {
        $error = $stock_error; // Block submission if stock changed during checkout
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - ShopKart</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/lucide@latest"></script>
</head>
<body class="bg-gray-100 font-sans text-[#0F1111]">
    
    <nav class="bg-[#131921] text-white p-4 flex items-center justify-between sticky top-0 z-50">
        <a href="index.php" class="text-2xl font-bold tracking-tighter">
            ShopKart<span class="text-[#f90] text-3xl">.</span>
        </a>
        <div class="text-sm text-gray-300 flex items-center gap-1">
            <i data-lucide="lock" class="w-3 h-3"></i> Secure Checkout
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8 max-w-4xl">
        
        <?php if ($order_success): ?>
            <!-- SUCCESS SCREEN -->
            <div class="bg-white p-12 rounded shadow text-center border border-gray-200">
                <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i data-lucide="check-circle" class="w-10 h-10 text-green-600"></i>
                </div>
                <h2 class="text-3xl font-bold text-green-700 mb-2">Order Placed!</h2>
                <p class="text-gray-600 mb-4">Your order #<?php echo $order_id; ?> has been confirmed.</p>
                
                <div class="flex flex-col sm:flex-row justify-center gap-4 mt-6">
                    <a href="invoice.php?order_id=<?php echo $order_id; ?>" target="_blank" class="bg-gray-100 text-gray-800 px-6 py-2 rounded border hover:bg-gray-200 font-medium flex items-center justify-center gap-2">
                        <i data-lucide="printer" class="w-4 h-4"></i> View Invoice
                    </a>
                    <a href="index.php" class="bg-[#ffd814] text-black px-6 py-2 rounded border border-[#fcd200] font-medium hover:bg-[#f7ca00] flex items-center justify-center">
                        Continue Shopping
                    </a>
                </div>
            </div>
        <?php else: ?>
            <!-- CHECKOUT FORM -->
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold">Checkout</h1>
                <div class="text-gray-500 text-sm"><?php echo count($products); ?> Items</div>
            </div>
            
            <form id="checkout-form" method="POST" class="flex flex-col md:flex-row gap-8">
                
                <!-- LEFT: Forms -->
                <div class="flex-1 flex flex-col gap-6">
                    
                    <!-- Stock Error Alert -->
                    <?php if(!empty($stock_error)): ?>
                        <div class="bg-red-100 text-red-800 p-4 rounded border border-red-200 font-bold text-sm">
                            <?php echo $stock_error; ?>
                            <a href="cart.php" class="underline ml-2">Go to Cart to adjust quantity</a>
                        </div>
                    <?php endif; ?>

                    <!-- 1. Address -->
                    <div class="bg-white p-6 rounded shadow-sm border border-gray-200">
                        <h2 class="text-lg font-bold mb-4 border-b pb-2">1. Delivery Address</h2>
                        <?php if(isset($error) && empty($stock_error)): ?>
                            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm"><?php echo $error; ?></div>
                        <?php endif; ?>
                        <div class="flex flex-col gap-4">
                            <div>
                                <label class="block text-sm font-bold mb-1">Full Name</label>
                                <input type="text" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" class="w-full border p-2 rounded bg-gray-100 cursor-not-allowed text-gray-600" readonly>
                            </div>
                            <div>
                                <label class="block text-sm font-bold mb-1">Address</label>
                                <textarea name="address" rows="3" required placeholder="Street, City, State, Pincode" class="w-full border p-2 rounded focus:ring-2 focus:ring-[#e77600] outline-none"></textarea>
                                <button type="button" onclick="fillLocation()" class="text-xs text-blue-600 hover:underline mt-2 flex items-center gap-1">
                                    <i data-lucide="map-pin" class="w-3 h-3"></i> Autofill from my current location
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- 2. Payment Method -->
                    <div class="bg-white p-6 rounded shadow-sm border border-gray-200">
                        <h2 class="text-lg font-bold mb-4 border-b pb-2">2. Payment Method</h2>
                        <div class="flex flex-col gap-3">
                            <label class="flex items-center gap-3 p-3 border rounded cursor-pointer hover:bg-gray-50 has-[:checked]:border-[#e77600] has-[:checked]:bg-orange-50">
                                <input type="radio" name="payment_method" value="Card" class="accent-[#e77600]" required onchange="togglePayment('online')">
                                <div class="flex-1">
                                    <span class="font-bold block">Credit / Debit Card</span>
                                    <span class="text-xs text-gray-500">Visa, Mastercard, RuPay</span>
                                </div>
                                <i data-lucide="credit-card" class="w-5 h-5 text-gray-400"></i>
                            </label>
                            
                            <label class="flex items-center gap-3 p-3 border rounded cursor-pointer hover:bg-gray-50 has-[:checked]:border-[#e77600] has-[:checked]:bg-orange-50">
                                <input type="radio" name="payment_method" value="UPI" class="accent-[#e77600]" required onchange="togglePayment('online')">
                                <div class="flex-1">
                                    <span class="font-bold block">UPI</span>
                                    <span class="text-xs text-gray-500">Google Pay, PhonePe, Paytm</span>
                                </div>
                                <i data-lucide="smartphone" class="w-5 h-5 text-gray-400"></i>
                            </label>

                            <label class="flex items-center gap-3 p-3 border rounded cursor-pointer hover:bg-gray-50 has-[:checked]:border-[#e77600] has-[:checked]:bg-orange-50">
                                <input type="radio" name="payment_method" value="COD" class="accent-[#e77600]" required checked onchange="togglePayment('cod')">
                                <div class="flex-1">
                                    <span class="font-bold block">Cash on Delivery</span>
                                    <span class="text-xs text-gray-500">Pay when you receive</span>
                                </div>
                                <i data-lucide="banknote" class="w-5 h-5 text-gray-400"></i>
                            </label>
                        </div>
                    </div>

                    <button type="button" onclick="handlePlaceOrder()" class="w-full bg-[#ffd814] hover:bg-[#f7ca00] border border-[#fcd200] py-3 rounded shadow-sm font-medium text-lg disabled:opacity-50 disabled:cursor-not-allowed" <?php if(!empty($stock_error)) echo 'disabled'; ?>>
                        Place Order
                    </button>
                </div>

                <!-- RIGHT: Order Summary -->
                <div class="w-full md:w-80 bg-white p-6 rounded shadow-sm border border-gray-200 h-fit">
                    <h2 class="text-lg font-bold mb-4 border-b pb-2">Order Summary</h2>
                    <div class="flex flex-col gap-4 max-h-96 overflow-y-auto pr-2 custom-scrollbar">
                        <?php foreach ($products as $p): ?>
                        <div class="flex gap-3 text-sm border-b pb-3 last:border-0">
                            <div class="flex-1">
                                <div class="font-bold line-clamp-2 mb-1"><?php echo $p['title']; ?></div>
                                <div class="flex justify-between items-center">
                                    <div class="text-red-700 font-bold">₹<?php echo number_format($p['price']); ?></div>
                                    <div class="text-gray-500 text-xs">Qty: <?php echo $p['qty']; ?></div>
                                </div>
                                <!-- Stock Warning in Cart -->
                                <?php if($p['stock_quantity'] < 5 && $p['stock_quantity'] > 0): ?>
                                    <div class="text-xs text-red-600 mt-1 font-bold">Only <?php echo $p['stock_quantity']; ?> left!</div>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="flex justify-between border-t pt-4 mt-4 font-bold text-lg text-[#b12704]">
                        <span>Order Total:</span>
                        <span>₹<?php echo number_format($total_amount); ?></span>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <!-- FAKE PAYMENT MODAL -->
    <div id="payment-modal" class="fixed inset-0 bg-black bg-opacity-50 hidden z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-lg w-full max-w-md p-6 relative shadow-2xl">
            <h3 class="text-xl font-bold mb-4 flex items-center gap-2">
                <i data-lucide="lock" class="w-5 h-5 text-green-600"></i> Secure Payment
            </h3>
            
            <div class="space-y-4" id="card-form">
                <div class="p-3 bg-blue-50 border border-blue-100 rounded text-sm text-blue-800 mb-4">
                    <i data-lucide="info" class="w-4 h-4 inline mr-1"></i> Demo Mode: Enter any dummy details.
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Card Number</label>
                    <input type="text" placeholder="0000 0000 0000 0000" class="w-full border p-2 rounded font-mono">
                </div>
                <div class="flex gap-4">
                    <div class="flex-1">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Expiry</label>
                        <input type="text" placeholder="MM/YY" class="w-full border p-2 rounded font-mono">
                    </div>
                    <div class="w-24">
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-1">CVV</label>
                        <input type="password" placeholder="123" class="w-full border p-2 rounded font-mono">
                    </div>
                </div>
                <button onclick="processPayment()" class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded font-bold shadow mt-4 flex justify-center items-center gap-2">
                    <span id="pay-btn-text">Pay ₹<?php echo number_format($total_amount); ?></span>
                    <div id="pay-spinner" class="hidden w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>
                </button>
            </div>
            
            <button onclick="closeModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
                <i data-lucide="x" class="w-5 h-5"></i>
            </button>
        </div>
    </div>

    <script>
        lucide.createIcons();
        let currentMethod = 'COD';

        function togglePayment(type) {
            currentMethod = type;
        }

        function handlePlaceOrder() {
            const address = document.querySelector('textarea[name="address"]').value;
            if(!address) {
                alert("Please enter a delivery address.");
                return;
            }

            if(currentMethod === 'online') {
                document.getElementById('payment-modal').classList.remove('hidden');
            } else {
                // Submit COD directly
                document.getElementById('checkout-form').submit();
            }
        }

        function processPayment() {
            const btnText = document.getElementById('pay-btn-text');
            const spinner = document.getElementById('pay-spinner');
            
            // Simulate Processing
            btnText.textContent = "Processing...";
            spinner.classList.remove('hidden');
            
            setTimeout(() => {
                document.getElementById('checkout-form').submit();
            }, 2000);
        }

        function closeModal() {
            document.getElementById('payment-modal').classList.add('hidden');
        }

        function fillLocation() {
            const savedLoc = localStorage.getItem('user_location');
            if (savedLoc && savedLoc !== "India") {
                document.querySelector('textarea[name="address"]').value = savedLoc;
            } else {
                alert("No precise location saved yet. Please go to the Home Page and click 'Deliver to India' to detect your location.");
            }
        }
    </script>
</body>
</html>