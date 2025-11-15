<?php
$host = "localhost";
$username = "root";        // default for XAMPP
$password = "";            // default for XAMPP
$database = "user_system";

$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
