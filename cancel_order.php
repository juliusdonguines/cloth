<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['order_id']) && isset($_POST['cancel_reason'])) {
    $order_id = intval($_POST['order_id']);
    $user_id = $_SESSION['user_id'];
    $cancel_reason = trim($_POST['cancel_reason']);
    $cancel_comment = trim($_POST['cancel_comment'] ?? '');
    
    // Verify the order belongs to the user and can be cancelled
    $check_stmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
    $check_stmt->bind_param("ii", $order_id, $user_id);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    
    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        $status = strtolower(trim($order['status']));
        
        // Only allow cancellation for pending, to pay, processing, or to ship orders
        if (in_array($status, ['pending', 'to pay', 'processing', 'to ship'])) {
            // Combine reason and comment
            $full_reason = $cancel_reason;
            if (!empty($cancel_comment)) {
                $full_reason .= " - " . $cancel_comment;
            }
            
            $update_stmt = $conn->prepare("UPDATE orders SET status = 'cancelled', cancel_reason = ? WHERE id = ? AND user_id = ?");
            $update_stmt->bind_param("sii", $full_reason, $order_id, $user_id);
            
            if ($update_stmt->execute()) {
                $_SESSION['message'] = "Order has been cancelled successfully.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Failed to cancel order. Please try again.";
                $_SESSION['message_type'] = "error";
            }
            $update_stmt->close();
        } else {
            $_SESSION['message'] = "This order cannot be cancelled at this stage.";
            $_SESSION['message_type'] = "error";
        }
    } else {
        $_SESSION['message'] = "Order not found.";
        $_SESSION['message_type'] = "error";
    }
    
    $check_stmt->close();
} else {
    $_SESSION['message'] = "Invalid request.";
    $_SESSION['message_type'] = "error";
}

header("Location: orders.php");
exit();
?>