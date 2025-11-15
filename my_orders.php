<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all orders with product images
$sql = "SELECT o.id, o.quantity, o.status, o.order_date, o.cancel_reason, u.first_name, u.last_name, 
        p.name AS product_name, p.size, p.color, p.price, p.image1
        FROM orders o
        JOIN users u ON o.user_id = u.id
        JOIN products p ON o.product_id = p.id
        ORDER BY o.order_date DESC";

$result = $conn->query($sql);
$all_orders = $result->fetch_all(MYSQLI_ASSOC);

// Separate orders by status
$orders = [
    'all' => $all_orders,
    'pending' => [],
    'processing' => [],
    'shipped' => [],
    'received' => [],
    'cancelled' => []
];

foreach ($all_orders as $order) {
    $status = strtolower(trim($order['status']));
    if ($status === 'pending' || $status === 'to pay') {
        $orders['pending'][] = $order;
    } elseif ($status === 'processing' || $status === 'to ship') {
        $orders['processing'][] = $order;
    } elseif ($status === 'shipped' || $status === 'to receive') {
        $orders['shipped'][] = $order;
    } elseif ($status === 'received' || $status === 'completed') {
        $orders['received'][] = $order;
    } elseif ($status === 'cancelled' || $status === 'canceled') {
        $orders['cancelled'][] = $order;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOVEAUX - Orders Management</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: #fafafa; color: #2c2c2c; line-height: 1.6; }
        
        .layout {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 260px;
            background: #fff;
            border-right: 1px solid #e8e8e8;
            padding: 30px 0;
            position: fixed;
            height: 100vh;
            overflow-y: auto;
            box-shadow: 2px 0 10px rgba(0,0,0,0.05);
        }

        .logo {
            padding: 0 30px 30px;
            border-bottom: 2px solid #e8e8e8;
            margin-bottom: 30px;
        }

        .logo-section {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 15px;
        }

        .logo-mark {
            width: 45px;
            height: 45px;
            background: #A1BC98;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 20px;
        }

        .logo-text {
            font-size: 24px;
            font-weight: 700;
            color: #2c2c2c;
            letter-spacing: 1px;
        }

        .logo h2 {
            font-size: 16px;
            font-weight: 500;
            color: #777;
            margin-top: 5px;
        }

        .nav-menu {
            padding: 0 15px;
        }

        .nav-item {
            margin-bottom: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 14px 20px;
            text-decoration: none;
            color: #555;
            border-radius: 10px;
            font-weight: 500;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            background-color: #f8f8f8;
            color: #A1BC98;
        }

        .nav-link.active {
            background-color: #e8f3e5;
            color: #A1BC98;
        }

        .logout-section {
            margin-top: auto;
            padding: 30px 15px 0;
            border-top: 2px solid #e8e8e8;
        }

        .logout-btn {
            display: block;
            width: 100%;
            padding: 14px 20px;
            background-color: #2c2c2c;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            text-align: center;
            font-weight: 600;
            font-size: 15px;
            transition: all 0.3s ease;
        }

        .logout-btn:hover {
            background-color: #A1BC98;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            margin-left: 260px;
        }

        .page-header { 
            background: linear-gradient(135deg, #2c2c2c 0%, #3a3a3a 100%);
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            margin-bottom: 0;
        }
        .page-title { 
            color: #ffffff;
            font-size: 42px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin: 0;
            line-height: 1.2;
        }
        .page-subtitle { 
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            font-weight: 400;
            letter-spacing: 1px;
            margin-top: 10px;
        }
        
        .tabs-container { background: #fff; border-bottom: 2px solid #f0f0f0; position: sticky; top: 0; z-index: 99; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .tabs { display: flex; max-width: 1400px; margin: 0 auto; padding: 0 40px; overflow-x: auto; }
        .tabs::-webkit-scrollbar { height: 0; }
        .tab { flex: 1; min-width: 140px; padding: 18px 20px; background: none; border: none; cursor: pointer; font-size: 15px; font-weight: 500; color: #777; border-bottom: 3px solid transparent; transition: all 0.3s; white-space: nowrap; font-family: 'Poppins', sans-serif; }
        .tab.active { color: #A1BC98; border-bottom-color: #A1BC98; }
        .tab:hover { color: #A1BC98; }
        .tab-count { display: inline-block; margin-left: 8px; padding: 4px 10px; background: #f0f0f0; border-radius: 12px; font-size: 12px; font-weight: 600; }
        .tab.active .tab-count { background: #e8f3e5; color: #A1BC98; }
        
        .content { max-width: 1400px; margin: 0 auto; padding: 30px 40px; }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        
        .order-card { background: #fff; border-radius: 16px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 2px 15px rgba(0,0,0,0.08); transition: all 0.3s; }
        .order-card:hover { box-shadow: 0 4px 20px rgba(0,0,0,0.12); }
        
        .order-header { padding: 20px 25px; background: #f8f8f8; border-bottom: 1px solid #e8e8e8; display: flex; justify-content: space-between; align-items: center; }
        .customer-info { display: flex; align-items: center; gap: 15px; }
        .customer-avatar { width: 50px; height: 50px; border-radius: 50%; background: linear-gradient(135deg, #A1BC98 0%, #8fa983 100%); display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 20px; }
        .customer-details { flex: 1; }
        .customer-name { font-weight: 600; font-size: 16px; color: #2c2c2c; margin-bottom: 4px; }
        .order-id { font-size: 13px; color: #999; }
        
        .status-badge { padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-badge.pending, .status-badge.to-pay { background: #fff3cd; color: #856404; }
        .status-badge.processing, .status-badge.to-ship { background: #cfe2ff; color: #084298; }
        .status-badge.shipped, .status-badge.to-receive { background: #d1e7dd; color: #0f5132; }
        .status-badge.received, .status-badge.completed { background: #e8f3e5; color: #2d5016; }
        .status-badge.cancelled, .status-badge.canceled { background: #f8d7da; color: #842029; }
        
        .order-body { padding: 25px; }
        .product-info { display: flex; gap: 20px; align-items: center; }
        .product-image { width: 100px; height: 100px; border-radius: 12px; object-fit: cover; border: 1px solid #e8e8e8; flex-shrink: 0; background: #f8f8f8; }
        .product-details { flex: 1; }
        .product-name { font-size: 18px; font-weight: 600; color: #2c2c2c; margin-bottom: 10px; }
        .product-variation { font-size: 14px; color: #777; margin-bottom: 6px; }
        .product-quantity { font-size: 14px; color: #777; margin-bottom: 6px; }
        .order-date { font-size: 13px; color: #999; margin-top: 8px; }
        
        .cancel-reason { margin-top: 15px; padding: 12px 16px; background: #fff5f5; border-left: 4px solid #ef4444; border-radius: 8px; font-size: 14px; color: #991b1b; }
        .cancel-label { font-weight: 600; margin-bottom: 6px; display: flex; align-items: center; gap: 6px; }
        
        .product-price { text-align: right; flex-shrink: 0; }
        .price-amount { font-size: 24px; font-weight: 700; color: #A1BC98; }
        
        .order-footer { padding: 20px 25px; background: #f8f8f8; border-top: 1px solid #e8e8e8; display: flex; justify-content: space-between; align-items: center; }
        .order-total { font-size: 15px; color: #777; }
        .total-amount { font-size: 20px; font-weight: 700; color: #2c2c2c; margin-left: 10px; }
        
        .update-form { display: flex; gap: 10px; align-items: center; }
        .status-select { padding: 10px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; background: #fff; color: #2c2c2c; min-width: 140px; font-family: 'Poppins', sans-serif; transition: all 0.3s; }
        .status-select:focus { outline: none; border-color: #A1BC98; }
        .update-btn { padding: 10px 20px; background: #A1BC98; color: #fff; border: none; border-radius: 8px; font-size: 14px; font-weight: 600; cursor: pointer; transition: all 0.3s; font-family: 'Poppins', sans-serif; }
        .update-btn:hover { background: #8fa983; transform: translateY(-1px); }
        
        .no-orders { text-align: center; padding: 80px 40px; color: #999; background: #fff; border-radius: 16px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
        .no-orders-icon { font-size: 80px; margin-bottom: 20px; opacity: 0.3; }
        .no-orders h3 { font-size: 24px; color: #2c2c2c; margin-bottom: 10px; }
        .no-orders p { font-size: 15px; }
        
        .completed-label { color: #A1BC98; font-size: 15px; font-weight: 600; display: flex; align-items: center; gap: 6px; }
        .cancelled-label { color: #999; font-size: 15px; display: flex; align-items: center; gap: 6px; }
        
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }

            .page-header { padding: 30px 20px; }
            .page-title { font-size: 28px; letter-spacing: 2px; }
            .content { padding: 20px; }
            .tabs { padding: 0 20px; }
            .tab { padding: 15px 12px; font-size: 14px; min-width: 120px; }
            .order-body { padding: 20px; }
            .product-info { flex-direction: column; align-items: flex-start; }
            .product-image { width: 80px; height: 80px; }
            .product-price { text-align: left; }
            .order-footer { flex-direction: column; gap: 15px; align-items: stretch; }
            .update-form { width: 100%; }
            .status-select { flex: 1; }
        }
    </style>
</head>
<body>
<div class="layout">
    <aside class="sidebar">
        <div class="logo">
            <div class="logo-section">
                <div class="logo-mark">N</div>
                <div class="logo-text">NOVEAUX</div>
            </div>
            <h2>Admin Panel</h2>
        </div>

        <nav class="nav-menu">
            <div class="nav-item">
                <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
            </div>
            <div class="nav-item">
                <a href="product.php" class="nav-link">Products</a>
            </div>
            <div class="nav-item">
                <a href="my_orders.php" class="nav-link active">Orders</a>
            </div>
            <div class="nav-item">
                <a href="customers.php" class="nav-link">Customers</a>
            </div>
            <div class="nav-item">
                <a href="statistics.php" class="nav-link">Statistics</a>
            </div>
            <div class="nav-item">
                <a href="admin_reviews.php" class="nav-link">Reviews</a>
            </div>
        </nav>
        
        <div class="logout-section">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Orders Management</h1>
            <p class="page-subtitle">Manage and track all customer orders</p>
        </div>

        <div class="tabs-container">
            <div class="tabs">
                <button class="tab active" onclick="showTab('all')">All Orders <span class="tab-count"><?php echo count($orders['all']); ?></span></button>
                <button class="tab" onclick="showTab('pending')">Pending <span class="tab-count"><?php echo count($orders['pending']); ?></span></button>
                <button class="tab" onclick="showTab('processing')">Processing <span class="tab-count"><?php echo count($orders['processing']); ?></span></button>
                <button class="tab" onclick="showTab('shipped')">Shipped <span class="tab-count"><?php echo count($orders['shipped']); ?></span></button>
                <button class="tab" onclick="showTab('received')">Received <span class="tab-count"><?php echo count($orders['received']); ?></span></button>
                <button class="tab" onclick="showTab('cancelled')">Cancelled <span class="tab-count"><?php echo count($orders['cancelled']); ?></span></button>
            </div>
        </div>

        <div class="content">
            <?php foreach ($orders as $key => $orderList): ?>
                <div id="<?php echo $key; ?>-tab" class="tab-content <?php echo $key === 'all' ? 'active' : ''; ?>">
                    <?php if (empty($orderList)): ?>
                        <div class="no-orders">
                            <div class="no-orders-icon">📦</div>
                            <h3>No orders found</h3>
                            <p>Orders will appear here once customers place them.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($orderList as $order): ?>
                            <?php
                                $status = strtolower(trim($order['status']));
                                $statusDisplay = ucwords(str_replace(['_', '-'], ' ', $status));
                                $statusClass = str_replace(' ', '-', $status);
                                $total = $order['price'] * $order['quantity'];
                                
                                $imagePath = 'uploads/' . basename($order['image1'] ?? '');
                                if (empty($order['image1']) || !file_exists($imagePath)) {
                                    $imagePath = 'uploads/placeholder.jpg';
                                }
                                
                                $initial = strtoupper(substr($order['first_name'], 0, 1));
                            ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div class="customer-info">
                                        <div class="customer-avatar"><?php echo $initial; ?></div>
                                        <div class="customer-details">
                                            <div class="customer-name"><?php echo htmlspecialchars($order['first_name'] . ' ' . $order['last_name']); ?></div>
                                            <div class="order-id">Order #<?php echo $order['id']; ?></div>
                                        </div>
                                    </div>
                                    <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusDisplay; ?></span>
                                </div>
                                <div class="order-body">
                                    <div class="product-info">
                                        <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Product" class="product-image">
                                        <div class="product-details">
                                            <div class="product-name"><?php echo htmlspecialchars($order['product_name']); ?></div>
                                            <div class="product-variation">Size: <?php echo htmlspecialchars($order['size']); ?> • Color: <?php echo htmlspecialchars($order['color']); ?></div>
                                            <div class="product-quantity">Quantity: <?php echo $order['quantity']; ?></div>
                                            <div class="order-date">📅 Ordered on <?php echo date('M j, Y \a\t g:i A', strtotime($order['order_date'])); ?></div>
                                            <?php if (!empty($order['cancel_reason']) && ($status === 'cancelled' || $status === 'canceled')): ?>
                                                <div class="cancel-reason">
                                                    <div class="cancel-label">⚠️ Cancellation Reason:</div>
                                                    <?php echo htmlspecialchars($order['cancel_reason']); ?>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="product-price">
                                            <div class="price-amount">₱<?php echo number_format($total, 2); ?></div>
                                        </div>
                                    </div>
                                </div>
                                <div class="order-footer">
                                    <div>
                                        <span class="order-total">Order Total:</span>
                                        <span class="total-amount">₱<?php echo number_format($total, 2); ?></span>
                                    </div>
                                    <?php if ($status !== 'cancelled' && $status !== 'canceled' && $status !== 'received' && $status !== 'completed'): ?>
                                        <form action="update_order_status.php" method="POST" class="update-form">
                                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                            <select name="status" class="status-select">
                                                <option value="Pending" <?php if ($status === 'pending' || $status === 'to pay') echo 'selected'; ?>>Pending</option>
                                                <option value="Processing" <?php if ($status === 'processing' || $status === 'to ship') echo 'selected'; ?>>Processing</option>
                                                <option value="Shipped" <?php if ($status === 'shipped' || $status === 'to receive') echo 'selected'; ?>>Shipped</option>
                                            </select>
                                            <button type="submit" class="update-btn">Update</button>
                                        </form>
                                    <?php elseif ($status === 'received' || $status === 'completed'): ?>
                                        <span class="completed-label">✓ Order Completed</span>
                                    <?php else: ?>
                                        <span class="cancelled-label">✗ Order Cancelled</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</div>

<script>
    function showTab(tabName) {
        document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.getElementById(tabName + '-tab').classList.add('active');
        event.target.classList.add('active');
    }
</script>
</body>
</html>