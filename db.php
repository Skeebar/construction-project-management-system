<?php
$host = 'localhost';       // Database host (usually localhost)
$user = 'root';            // Database username
$pass = '';                // Database password
$dbname = 'construction_db'; // Your database name

// Create connection
$conn = new mysqli($host, $user, $pass, $dbname);

// Check connection
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

// Set character set to UTF-8
$conn->set_charset("utf8");
?>
