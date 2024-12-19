<?php
// Database configuration
$DB_CONFIG = [
    'host' => 'localhost',
    'username' => 'root',     // Default XAMPP MySQL username
    'password' => '',          // Default XAMPP MySQL password (empty)
    'database' => 'inventory_management'
];

// Function to create database and tables if they don't exist
function initializeDatabase($config) {
    // Create connection without selecting a database
    try {
        $conn = new mysqli(
            $config['host'], 
            $config['username'], 
            $config['password']
        );

        // Check connection
        if ($conn->connect_error) {
            throw new Exception("Connection failed: " . $conn->connect_error);
        }

        // Create database if not exists
        $create_db_query = "CREATE DATABASE IF NOT EXISTS `{$config['database']}`";
        if (!$conn->query($create_db_query)) {
            throw new Exception("Error creating database: " . $conn->error);
        }

        // Select the database
        $conn->select_db($config['database']);

        // Create users table
        $create_users_table = "CREATE TABLE IF NOT EXISTS users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            fullname VARCHAR(100) NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        // Create categories table
        $create_categories_table = "CREATE TABLE IF NOT EXISTS categories (
            category_ID INT AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL UNIQUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        // Create suppliers table
        $create_suppliers_table = "CREATE TABLE IF NOT EXISTS suppliers (
            supplier_id INT AUTO_INCREMENT PRIMARY KEY,
            supplier_name VARCHAR(200) NOT NULL UNIQUE,
            contact_num VARCHAR(20),
            address VARCHAR(200),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";

        // Create products table
        $create_products_table = "CREATE TABLE IF NOT EXISTS products (
            product_id INT AUTO_INCREMENT PRIMARY KEY,
            category_id INT NOT NULL,
            supplier_id INT NOT NULL,
            product_name VARCHAR(200) NOT NULL,
            price DECIMAL(10,2) NOT NULL,
            date_stored DATE DEFAULT CURRENT_DATE,
            quantity_stock INT NOT NULL,
            description VARCHAR(500),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (category_id) REFERENCES categories(category_id),
            FOREIGN KEY (supplier_id) REFERENCES suppliers(supplier_id)
        )";

        // Execute table creation queries
        if (!$conn->query($create_users_table)) {
            throw new Exception("Error creating users table: " . $conn->error);
        }

        if (!$conn->query($create_categories_table)) {
            throw new Exception("Error creating categories table: " . $conn->error);
        }

        if (!$conn->query($create_suppliers_table)) {
            throw new Exception("Error creating suppliers table: " . $conn->error);
        }

        if (!$conn->query($create_products_table)) {
            throw new Exception("Error creating products table: " . $conn->error);
        }

        return $conn;
    } catch (Exception $e) {
        // Log error or handle it appropriately
        error_log("Database Initialization Error: " . $e->getMessage());
        die("Database setup failed. Please check the error log.");
    }
}

// Establish database connection and create tables if not exist
try {
    $conn = initializeDatabase($DB_CONFIG);
} catch (Exception $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    die("Could not connect to the database.");
}
?>