🛒 ShopKart - PHP/MySQL E-Commerce Platform

ShopKart is a fully functional, dynamic E-Commerce website built from scratch using Core PHP and MySQL. It features a dual-interface system for both Customers (shopping experience) and Administrators (inventory & order management).

🚀 Features

👤 Customer Interface

Browse & Search: Filter products by category, search by name.

Smart Cart: Add/remove items, auto-stock validation.

Checkout System: Delivery address capture and simulated payment gateway (Card/UPI/COD).

User Accounts: Registration, Login, and Order History.

Invoicing: Auto-generated printable PDF-style invoices.

Live Location: Auto-detect city/pincode using OpenStreetMap API.

🛡️ Admin Dashboard

Secure Login: Role-based authentication (Admin vs Customer).

Inventory Management: Add, Edit, and Delete products.

Stock Control: Real-time stock updates; set Low Stock warnings.

Order Fulfillment: View orders, update status (Pending → Shipped → Delivered).

Revenue Tracking: View total sales and customer details.

🛠️ Tech Stack

Frontend: HTML5, Tailwind CSS (via CDN), JavaScript (Lucide Icons).

Backend: PHP (Session management, PDO/MySQLi).

Database: MySQL (Relational schema).

Server: Apache (XAMPP/WAMP/LAMP).

⚙️ Installation & Setup

This project is a Dynamic Website and requires a server environment to run. It cannot be run directly by opening HTML files.

Prerequisites

Download and Install XAMPP (Windows/Linux/Mac).

Step 1: Clone the Repository

Navigate to your XAMPP installation folder (usually C:\xampp\htdocs).

(Or simply download the ZIP and extract it into a folder named shopkart inside htdocs).

Step 2: Database Setup

Start Apache and MySQL in the XAMPP Control Panel.

Open your browser and go to http://localhost/phpmyadmin.

Create a new database named ecommerce_db.

Click on the Import tab.

Choose the database.sql file provided in this repository (or run the SQL commands manually).

Click Go to create the tables.

Step 3: Configure Connection

Open config/db_connect.php in a code editor.

Ensure the credentials match your local setup (Default XAMPP credentials below):

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "ecommerce_db";


Step 4: Run the Project

Open your browser and visit:

http://localhost/shopkart/index.php


🔐 Admin Credentials 

To access the Admin Panel, we need admin credentials

Email: admin@example.com

Password: admin1234567890 

Role: Admin

📂 Folder Structure

shopkart/
├── assets/             # Product images and static files
├── config/             # Database connection files
├── admin.php           # Product upload page
├── admin_orders.php    # Order management dashboard
├── admin_products.php  # Inventory management
├── cart.php            # Shopping cart logic
├── checkout.php        # Checkout & Payment simulation
├── invoice.php         # Invoice generation logic
├── index.php           # Home page
├── login.php           # Authentication
└── README.md           # Project documentation


🤝 Contributing

Feel free to fork this repository and submit pull requests. For major changes, please open an issue first to discuss what you would like to change.

