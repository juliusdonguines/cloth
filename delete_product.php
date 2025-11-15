<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'] ?? null;
if (!$id) {
    echo "Invalid product ID.";
    exit();
}

// Force delete product and all related orders
$stmt = $conn->prepare("DELETE FROM products WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$stmt->close();

echo "<script>alert('Product and related orders deleted successfully.'); window.location='admin_dashboard.php';</script>";
?>
