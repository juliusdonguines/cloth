<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$product_id = $_POST['product_id'];
$quantity = $_POST['quantity'];

// Check if the product is already in the cart
$check = $conn->prepare("SELECT id FROM cart WHERE user_id = ? AND product_id = ?");
$check->bind_param("ii", $user_id, $product_id);
$check->execute();
$result = $check->get_result();

if ($result->num_rows > 0) {
    // Update quantity if exists
    $conn->query("UPDATE cart SET quantity = quantity + $quantity WHERE user_id = $user_id AND product_id = $product_id");
} else {
    // Insert new item
    $stmt = $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $product_id, $quantity);
    $stmt->execute();
}

header("Location: my_cart.php");
exit();
?>
