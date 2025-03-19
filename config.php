<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'expense_tracker');

// Create database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if ($conn->query($sql) === TRUE) {
    $conn->select_db(DB_NAME);
    
    // Create expenses table
    $sql = "CREATE TABLE IF NOT EXISTS expenses (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        category VARCHAR(100) NOT NULL,
        description TEXT,
        date_added DATETIME DEFAULT CURRENT_TIMESTAMP,
        user_id INT(11),
        is_frozen BOOLEAN DEFAULT 0
    )";
    $conn->query($sql);

    // Create income table
    $sql = "CREATE TABLE IF NOT EXISTS income (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        source VARCHAR(100) NOT NULL,
        description TEXT,
        date_added DATETIME DEFAULT CURRENT_TIMESTAMP,
        user_id INT(11)
    )";
    $conn->query($sql);

    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql);
}

session_start();
?>