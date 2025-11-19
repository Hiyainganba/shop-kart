<?php
session_start();
include 'config/db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    // Hash the password for security
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

    // Check if email already exists
    $check = $conn->prepare("SELECT user_id FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    if ($check->get_result()->num_rows > 0) {
        $error = "Email already exists!";
    } else {
        // Insert new user
        $stmt = $conn->prepare("INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password);
        
        if ($stmt->execute()) {
            $_SESSION['user_id'] = $stmt->insert_id;
            $_SESSION['user_name'] = $name;
            header("Location: index.php");
            exit();
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register - ShopKart</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white font-sans flex items-center justify-center min-h-screen flex-col">
    
    <a href="index.php" class="text-4xl font-bold tracking-tighter mb-6">
        ShopKart<span class="text-[#f90] text-5xl">.</span>
    </a>

    <div class="w-full max-w-sm border border-gray-300 rounded p-8 shadow-sm">
        <h1 class="text-3xl font-normal mb-4">Create Account</h1>
        
        <?php if(isset($error)) echo "<p class='text-red-600 text-sm mb-4'>$error</p>"; ?>

        <form method="POST" class="flex flex-col gap-4">
            <div>
                <label class="font-bold text-sm block mb-1">Your name</label>
                <input type="text" name="name" required class="w-full border border-gray-400 rounded p-2 focus:ring-2 focus:ring-[#e77600] outline-none shadow-inner">
            </div>
            <div>
                <label class="font-bold text-sm block mb-1">Email</label>
                <input type="email" name="email" required class="w-full border border-gray-400 rounded p-2 focus:ring-2 focus:ring-[#e77600] outline-none shadow-inner">
            </div>
            <div>
                <label class="font-bold text-sm block mb-1">Password</label>
                <input type="password" name="password" required placeholder="At least 6 characters" class="w-full border border-gray-400 rounded p-2 focus:ring-2 focus:ring-[#e77600] outline-none shadow-inner">
            </div>
            <button class="bg-[#ffd814] hover:bg-[#f7ca00] border border-[#fcd200] py-2 rounded shadow-sm mt-2 font-medium">
                Create your ShopKart account
            </button>
        </form>

        <div class="mt-6 text-sm">
            Already have an account? <a href="login.php" class="text-blue-700 hover:underline hover:text-[#c45500]">Sign in</a>
        </div>
    </div>
</body>
</html>