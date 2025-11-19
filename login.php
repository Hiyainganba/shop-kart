<?php
session_start();
include 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // 1. UPDATE: Select 'role' column from database
    $stmt = $conn->prepare("SELECT user_id, full_name, password_hash, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        if (password_verify($password, $row['password_hash'])) {
            // Login Success
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_name'] = $row['full_name'];
            
            // 2. UPDATE: Save the role in the session
            // This is critical for checking permissions on other pages
            $_SESSION['user_role'] = $row['role']; 
            
            // 3. UPDATE: Redirect based on role
            if ($row['role'] == 'admin') {
                header("Location: admin_orders.php"); // Send Admins to Dashboard
            } else {
                header("Location: index.php"); // Send Customers to Home
            }
            exit();
        } else {
            $error = "Invalid password.";
        }
    } else {
        $error = "No account found with this email.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - ShopKart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white font-sans flex items-center justify-center min-h-screen flex-col">
    
    <a href="index.php" class="text-4xl font-bold tracking-tighter mb-6">
        ShopKart<span class="text-[#f90] text-5xl">.</span>
    </a>

    <div class="w-full max-w-sm border border-gray-300 rounded p-8 shadow-sm">
        <h1 class="text-3xl font-normal mb-4">Sign in</h1>
        
        <?php if(isset($error)) echo "<p class='text-red-600 text-sm mb-4'>$error</p>"; ?>

        <form method="POST" class="flex flex-col gap-4">
            <div>
                <label class="font-bold text-sm block mb-1">Email</label>
                <input type="email" name="email" required class="w-full border border-gray-400 rounded p-2 focus:ring-2 focus:ring-[#e77600] outline-none">
            </div>
            <div>
                <label class="font-bold text-sm block mb-1">Password</label>
                <input type="password" name="password" required class="w-full border border-gray-400 rounded p-2 focus:ring-2 focus:ring-[#e77600] outline-none">
            </div>
            <button class="bg-[#ffd814] hover:bg-[#f7ca00] border border-[#fcd200] py-2 rounded shadow-sm mt-2 font-medium">
                Continue
            </button>
        </form>

        <div class="mt-4 text-xs text-gray-600">
            By continuing, you agree to ShopKart's Conditions of Use and Privacy Notice.
        </div>
    </div>

    <div class="w-full max-w-sm mt-4 text-center">
        <div class="relative mb-4">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-300"></div></div>
            <div class="relative flex justify-center text-xs bg-white px-2 text-gray-500">New to ShopKart?</div>
        </div>
        <a href="register.php" class="block w-full bg-gray-100 hover:bg-gray-200 border border-gray-300 rounded py-1.5 text-sm shadow-sm">
            Create your ShopKart account
        </a>
    </div>
</body>
</html>