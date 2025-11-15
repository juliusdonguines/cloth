<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if (isset($_POST['order_id'])) {
    $order_id = intval($_POST['order_id']);
    $user_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("UPDATE orders SET status = 'Received' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $order_id, $user_id);
    $stmt->execute();

    header("Location: orders.php");
    exit();
} else {
    echo "Invalid request.";
}
?>
