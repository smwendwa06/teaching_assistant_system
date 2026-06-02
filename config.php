<?php
// Database configuration
// $host = "localhost";
// $user = "root";
// $password = "";
// $database = "users_db";

// Create connection
// $conn = new mysqli($host, $user, $password, $database);

// Check connection
// if ($conn->connect_error) {
//     error_log("Database connection failed: " . $conn->connect_error);
//     exit("Sorry, we are experiencing technical issues. Please try again later.");
// }

// <?php
$host = "127.0.0.1"; // Using the IP instead of 'localhost' can sometimes bypass DNS lag
$user = "root";
$password = ""; // Check if your database actually has a password! (XAMPP default is empty)
$database = "users_db";
$port = 3307; // Double-check this against the XAMPP Control Panel

$conn = new mysqli($host, $user, $password, $database, $port);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully";
?>