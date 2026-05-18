<?php
require_once(__DIR__ . '/security.php');

// Database Configuration
$host = 'localhost';
$dbname = 'stay_vibe';
$username = 'root';
$password = '';

// Create connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to utf8mb4
$conn->set_charset("utf8mb4");

// Define constant for base site URL
define('BASE_URL', 'http://localhost/Stat_vibe/Frontend/');
?>
