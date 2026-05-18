<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
require_once 'Frontend/includes/connect.php';

$sql = "CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    address VARCHAR(255),
    company_account_name VARCHAR(255),
    company_bank_name VARCHAR(255),
    company_account_number VARCHAR(100),
    company_ifsc VARCHAR(50),
    company_upi VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql)) {
    echo "Table 'admins' created successfully.<br>";
    
    // Check if empty
    $res = $conn->query("SELECT * FROM admins");
    if ($res->num_rows == 0) {
        $password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = $conn->query("INSERT INTO admins (name, email, password, phone, company_account_name, company_bank_name, company_account_number, company_ifsc) 
                                VALUES ('Super Admin', 'admin@stayvibes.com', '$password', '1234567890', 'STAY VIBES RESORT PVT LTD', 'HDFC BANK', '50200088997766', 'HDFC0001234')");
        if ($insert) {
            echo "Default admin created: admin@stayvibes.com / admin123<br>";
        }
    } else {
        echo "Admin already exists.<br>";
    }
} else {
    echo "Error creating table: " . $conn->error;
}
?>
