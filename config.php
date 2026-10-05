<?php
session_start();

$host = "localhost";
$user = "root";
$pass = "";
$db   = "lost_found_db";

$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->query("CREATE DATABASE IF NOT EXISTS `$db`");
$conn->select_db($db);

$conn->set_charset("utf8mb4");

/* USERS TABLE */
$conn->query("
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    roll_no VARCHAR(30) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    course VARCHAR(100),
    semester VARCHAR(20),
    password VARCHAR(255) NOT NULL,
    role ENUM('student','admin') DEFAULT 'student',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

/* ITEMS TABLE */
$conn->query("
CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('Lost','Found') NOT NULL,
    title VARCHAR(150) NOT NULL,
    category VARCHAR(100),
    description TEXT,
    location VARCHAR(150),
    item_date DATE,
    image VARCHAR(255),
    status ENUM('Open','Claimed','Returned') DEFAULT 'Open',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)");

/* CLAIMS TABLE */
$conn->query("
CREATE TABLE IF NOT EXISTS claims (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    claimant_id INT NOT NULL,
    color VARCHAR(100),
    identifying_mark VARCHAR(255),
    ownership_details TEXT,
    status ENUM('Pending','Verified','Rejected') DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (item_id) REFERENCES items(id) ON DELETE CASCADE,
    FOREIGN KEY (claimant_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE(item_id, claimant_id)
)");

function e($value) {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function logged() {
    return isset($_SESSION['user_id']);
}

function require_login() {
    if (!logged()) {
        header("Location: login.php");
        exit;
    }
}

function admin_logged() {
    return isset($_SESSION['admin_id']);
}

function require_admin() {
    if (!admin_logged()) {
        header("Location: admin_login.php");
        exit;
    }
}
?>