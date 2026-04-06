<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$_SESSION = [];
session_destroy(); // Destroy the session
header("Location: login.php"); // Redirect to login page
exit();
?>