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
$host = "localhost";
$user = "root";
$password = "";
$database = "users_db";

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>