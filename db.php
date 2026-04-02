<?php
// Simple database connection using procedural MySQLi

$host = "localhost";
$username = "root"; // Default XAMPP username
$password = ""; // Default XAMPP password is empty
$database = "expense_tracker";

// Create connection
$conn = mysqli_connect($host, $username, $password, $database);

// Check connection
if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}

// Set character set to utf8mb4 for good practice
mysqli_set_charset($conn, "utf8mb4");

?>
