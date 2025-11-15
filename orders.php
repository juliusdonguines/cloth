<?php 
session_start(); 
include 'db.php';  

if (!isset($_SESSION['user_id'])) {     
    header("Location: login.php");     
    exit(); 
}  

$user_id = $_SESSION['user_id']; 
$user = $_SESSION['first_name'] ?? 'Guest';  

$sql = "SELECT o.id, o.product_id, o.quantity, o.status, o.order_date, p.name AS product_name, p.size, p.color, p.price, p.image1         
        FROM orders o JOIN products p ON o.product_id = p.id WHERE o.user_id = ? ORDER BY o.order_date DESC";  

$stmt = $conn->prepare($sql); 
$stmt->bind_param("i", $user_id); 
$stmt->execute(); 
$all_orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$orders = ['to-pay' => [], 'to-ship' => [], 'to-receive' => [], 'completed' => [], 'cancelled' => [], 'purchase-history' => []];

foreach ($all_orders as $order) {
    $status = strtolower(trim($order['status']));
    if ($status === 'pending' || $status === 'to pay') $orders['to-pay'][] = $order;
    elseif ($status === 'processing' || $status === 'to ship') $orders['to-ship'][] = $order;
    elseif ($status === 'shipped' || $status === 'to receive') $orders['to-receive'][] = $order;
    elseif ($status === 'received' || $status === 'completed') {
        $orders['completed'][] = $order;
        $orders['purchase-history'][] = $order;
    }
    elseif ($status === 'cancelled' || $status === 'canceled') {
        $orders['cancelled'][] = $order;
        $orders['purchase-history'][] = $order;
    }
}

function renderOrders($orderList, $tabType) {
    if (empty($orderList)) {
        $icons = ['to-pay' => '💳', 'to-ship' => '📦', 'to-receive' => '🚚', 'completed' => '✓', 'cancelled' => '✕', 'purchase-history' => '📜'];
        echo "<div class='no-orders'><div class='no-orders-icon'>{$icons[$tabType]}</div><h3>No orders found</h3><p>Your orders will appear here</p></div>";
        return;
    }
    
    foreach ($orderList as $order) {
        $status = strtolower(trim($order['status']));
        $statusDisplay = ucwords(str_replace(['_', '-'], ' ', $status));
        $statusClass = str_replace(' ', '-', $status);
        $total = $order['price'] * $order['quantity'];
        
        $imagePath = 'uploads/' . basename($order['image1'] ?? '');
        if (empty($order['image1']) || !file_exists($imagePath)) {
            $imagePath = 'placeholder.jpg';
        }
        ?>
        <div class="order-card">
            <div class="order-header">
                <div class="order-info">
                    <span class="shop-name">NOVEAUX</span>
                    <span class="order-id">Order #<?php echo $order['id']; ?></span>
                </div>
                <span class="status-badge <?php echo $statusClass; ?>"><?php echo $statusDisplay; ?></span>
            </div>
            <div class="order-body">
                <div class="product-info">
                    <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Product" class="product-image">
                    <div class="product-details">
                        <div class="product-name"><?php echo htmlspecialchars($order['product_name']); ?></div>
                        <div class="product-meta">Size: <?php echo htmlspecialchars($order['size']); ?> • Color: <?php echo htmlspecialchars($order['color']); ?></div>
                        <div class="product-quantity">Quantity: <?php echo $order['quantity']; ?></div>
                        <div class="order-date">📅 <?php echo date('M j, Y \a\t g:i A', strtotime($order['order_date'])); ?></div>
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
                <div class="order-actions">
                    <?php if ($tabType === 'purchase-history'): ?>
                        <a href="buy_now.php?product_id=<?php echo $order['product_id']; ?>" class="btn btn-primary">Buy Again</a>
                    <?php elseif ($status === 'pending' || $status === 'to pay'): ?>
                        <button type="button" class="btn btn-cancel" onclick="openCancelModal(<?php echo $order['id']; ?>)">Cancel Order</button>
                    <?php elseif ($status === 'processing' || $status === 'to ship'): ?>
                        <button type="button" class="btn btn-cancel" onclick="openCancelModal(<?php echo $order['id']; ?>)">Cancel Order</button>
                    <?php elseif ($status === 'shipped' || $status === 'to receive'): ?>
                        <form action="mark_received.php" method="POST" style="display:inline;">
                            <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                            <button type="submit" class="btn btn-primary">Mark as Received</button>
                        </form>
                    <?php elseif ($status === 'received' || $status === 'completed'): ?>
                        <button type="button" class="btn btn-review" onclick="openReviewModal(<?php echo $order['id']; ?>, '<?php echo htmlspecialchars($order['product_name'], ENT_QUOTES); ?>', <?php echo $order['product_id']; ?>)">Submit Review</button>
                        <a href="buy_now.php?product_id=<?php echo $order['product_id']; ?>" class="btn btn-secondary">Buy Again</a>
                    <?php elseif ($status === 'cancelled' || $status === 'canceled'): ?>
                        <a href="buy_now.php?product_id=<?php echo $order['product_id']; ?>" class="btn btn-primary">Buy Again</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php }
}
?>  

<!DOCTYPE html> 
<html lang="en"> 
<head>   
    <meta charset="UTF-8">   
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOVEAUX - My Orders</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>     
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background-color: #fafafa; color: #2c2c2c; }
        
        .top-bar { background: #2c2c2c; color: #fff; padding: 12px 0; text-align: center; font-size: 13px; letter-spacing: 0.5px; }
        
        .navbar { background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.08); position: sticky; top: 0; z-index: 100; }
        .nav-container { max-width: 1400px; margin: 0 auto; padding: 20px 40px; display: flex; justify-content: space-between; align-items: center; }
        .logo-section { display: flex; align-items: center; gap: 12px; }
        .logo-mark { width: 45px; height: 45px; background: #A1BC98; border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 20px; }
        .logo-text { font-size: 24px; font-weight: 700; color: #2c2c2c; letter-spacing: 1px; }
        .nav-links { display: flex; gap: 35px; align-items: center; }
        .nav-links a { text-decoration: none; color: #555; font-weight: 500; font-size: 15px; transition: color 0.3s; position: relative; }
        .nav-links a:hover { color: #A1BC98; }
        .nav-links a.active { color: #A1BC98; }
        .nav-links a.active::after { content: ''; position: absolute; bottom: -5px; left: 0; right: 0; height: 2px; background: #A1BC98; }
        .user-section { display: flex; align-items: center; gap: 20px; }
        .logout-btn { padding: 10px 24px; background: #2c2c2c; color: #fff; border: none; border-radius: 6px; font-weight: 500; cursor: pointer; font-size: 14px; transition: all 0.3s; text-decoration: none; display: inline-block; }
        .logout-btn:hover { background: #A1BC98; }
        
        .page-header { max-width: 1400px; margin: 0 auto; padding: 40px 40px 20px; }
        .page-title { font-size: 36px; font-weight: 600; color: #2c2c2c; margin-bottom: 8px; }
        .page-subtitle { color: #777; font-size: 15px; }
        
        .tabs-container { background: #fff; border-bottom: 2px solid #f0f0f0; position: sticky; top: 77px; z-index: 99; box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
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
        
        .order-card { background: #fff; border-radius: 16px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
        .order-header { padding: 20px 25px; background: #f8f8f8; border-bottom: 1px solid #e8e8e8; display: flex; justify-content: space-between; align-items: center; }
        .order-info { display: flex; align-items: center; gap: 15px; }
        .shop-name { font-weight: 600; font-size: 16px; color: #2c2c2c; }
        .order-id { font-size: 13px; color: #999; }
        
        .status-badge { padding: 8px 16px; border-radius: 20px; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }
        .status-badge.to-pay, .status-badge.pending { background: #fff3cd; color: #856404; }
        .status-badge.to-ship, .status-badge.processing { background: #cfe2ff; color: #084298; }
        .status-badge.to-receive, .status-badge.shipped { background: #d1e7dd; color: #0f5132; }
        .status-badge.completed, .status-badge.received { background: #e8f3e5; color: #2d5016; }
        .status-badge.cancelled, .status-badge.canceled { background: #f8d7da; color: #842029; }
        
        .order-body { padding: 25px; }
        .product-info { display: flex; gap: 20px; align-items: center; }
        .product-image { width: 90px; height: 90px; border-radius: 12px; object-fit: cover; border: 1px solid #e8e8e8; flex-shrink: 0; background: #f8f8f8; }
        .product-details { flex: 1; }
        .product-name { font-size: 16px; font-weight: 600; color: #2c2c2c; margin-bottom: 8px; }
        .product-meta { font-size: 14px; color: #777; margin-bottom: 6px; }
        .product-quantity { font-size: 14px; color: #777; margin-bottom: 6px; }
        .order-date { font-size: 13px; color: #999; margin-top: 6px; }
        .product-price { text-align: right; flex-shrink: 0; }
        .price-amount { font-size: 20px; font-weight: 700; color: #A1BC98; }
        
        .order-footer { padding: 20px 25px; background: #f8f8f8; border-top: 1px solid #e8e8e8; display: flex; justify-content: space-between; align-items: center; }
        .order-total { font-size: 15px; color: #777; }
        .total-amount { font-size: 20px; font-weight: 700; color: #2c2c2c; margin-left: 10px; }
        .order-actions { display: flex; gap: 10px; }
        
        .btn { padding: 10px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s; text-decoration: none; display: inline-block; font-family: 'Poppins', sans-serif; }
        .btn-primary { background: #A1BC98; color: #fff; }
        .btn-primary:hover { background: #8fa983; transform: translateY(-1px); }
        .btn-secondary { background: transparent; border: 2px solid #2c2c2c; color: #2c2c2c; }
        .btn-secondary:hover { background: #2c2c2c; color: #fff; }
        .btn-review { background: #2c2c2c; color: #fff; }
        .btn-review:hover { background: #3a3a3a; }
        .btn-cancel { background: #dc3545; color: #fff; }
        .btn-cancel:hover { background: #c82333; }
        
        .no-orders { text-align: center; padding: 80px 40px; color: #999; background: #fff; border-radius: 16px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
        .no-orders-icon { font-size: 80px; margin-bottom: 20px; opacity: 0.3; }
        .no-orders h3 { font-size: 24px; color: #2c2c2c; margin-bottom: 10px; }
        .no-orders p { font-size: 15px; }
        
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); }
        .modal-content { background: #fff; margin: 5% auto; padding: 40px; border-radius: 16px; width: 90%; max-width: 550px; box-shadow: 0 8px 30px rgba(0,0,0,0.2); }
        .close { color: #999; float: right; font-size: 32px; font-weight: 300; cursor: pointer; line-height: 1; transition: color 0.3s; }
        .close:hover { color: #2c2c2c; }
        .modal h2 { margin-bottom: 25px; color: #2c2c2c; font-size: 28px; font-weight: 600; }
        .modal p { color: #777; margin-bottom: 25px; font-size: 15px; }
        .form-group { margin-bottom: 25px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #2c2c2c; font-size: 15px; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; font-family: 'Poppins', sans-serif; transition: border-color 0.3s; }
        .form-group input:focus, .form-group textarea:focus, .form-group select:focus { outline: none; border-color: #A1BC98; }
        .form-group textarea { height: 100px; resize: vertical; }
        .star-rating { display: flex; gap: 8px; margin-top: 8px; }
        .star { font-size: 32px; color: #e0e0e0; cursor: pointer; transition: all 0.2s; }
        .star.selected, .star:hover { color: #ffc107; transform: scale(1.1); }
        .modal-buttons { display: flex; gap: 12px; justify-content: flex-end; margin-top: 30px; }
        
        @media (max-width: 768px) {
            .nav-container { flex-direction: column; gap: 20px; padding: 20px; }
            .nav-links { flex-direction: column; gap: 15px; width: 100%; }
            .page-header { padding: 30px 20px 15px; }
            .page-title { font-size: 28px; }
            .content { padding: 20px; }
            .tabs { padding: 0 20px; }
            .tab { padding: 15px 12px; font-size: 14px; min-width: 120px; }
            .order-body { padding: 20px; }
            .product-info { flex-direction: column; align-items: flex-start; }
            .product-image { width: 80px; height: 80px; }
            .product-price { text-align: left; }
            .order-footer { flex-direction: column; gap: 15px; align-items: stretch; }
            .order-actions { width: 100%; flex-direction: column; }
            .btn { width: 100%; text-align: center; }
        }
    </style> 
</head> 
<body>
    <div class="top-bar">
        Welcome, <?php echo htmlspecialchars($user); ?> | Track and manage your orders
    </div>

    <nav class="navbar">
        <div class="nav-container">
            <div class="logo-section">
                <div class="logo-mark">N</div>
                <div class="logo-text">NOVEAUX</div>
            </div>

            <div class="nav-links">
                <a href="home.php">Shop</a>
                <a href="my_cart.php">Cart</a>
                <a href="orders.php" class="active">Orders</a>
                <a href="my_reviews.php">Reviews</a>
            </div>

            <div class="user-section">
                <a href="logout.php" class="logout-btn">Sign Out</a>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <h1 class="page-title">My Orders</h1>
        <p class="page-subtitle">Track and manage your orders</p>
    </div>

    <div class="tabs-container">
        <div class="tabs">
            <button class="tab active" onclick="showTab('to-pay')">To Pay <span class="tab-count"><?php echo count($orders['to-pay']); ?></span></button>
            <button class="tab" onclick="showTab('to-ship')">To Ship <span class="tab-count"><?php echo count($orders['to-ship']); ?></span></button>
            <button class="tab" onclick="showTab('to-receive')">To Receive <span class="tab-count"><?php echo count($orders['to-receive']); ?></span></button>
            <button class="tab" onclick="showTab('completed')">Completed <span class="tab-count"><?php echo count($orders['completed']); ?></span></button>
            <button class="tab" onclick="showTab('cancelled')">Cancelled <span class="tab-count"><?php echo count($orders['cancelled']); ?></span></button>
            <button class="tab" onclick="showTab('purchase-history')">History <span class="tab-count"><?php echo count($orders['purchase-history']); ?></span></button>
        </div>
    </div>

    <div class="content">
        <?php foreach ($orders as $key => $orderList): ?>
            <div id="<?php echo $key; ?>-tab" class="tab-content <?php echo $key === 'to-pay' ? 'active' : ''; ?>">
                <?php renderOrders($orderList, $key); ?>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="reviewModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeReviewModal()">&times;</span>
            <h2>Submit Review</h2>
            <form action="submit_review.php" method="POST">
                <input type="hidden" name="order_id" id="modal_order_id">
                <input type="hidden" name="product_id" id="modal_product_id">
                <div class="form-group">
                    <label>Product:</label>
                    <input type="text" id="modal_product_name" readonly style="background: #f8f8f8;">
                </div>
                <div class="form-group">
                    <label>Rating:</label>
                    <div class="star-rating">
                        <span class="star" data-rating="1">★</span>
                        <span class="star" data-rating="2">★</span>
                        <span class="star" data-rating="3">★</span>
                        <span class="star" data-rating="4">★</span>
                        <span class="star" data-rating="5">★</span>
                    </div>
                    <input type="hidden" name="rating" id="rating" required>
                </div>
                <div class="form-group">
                    <label>Review Comment:</label>
                    <textarea name="comment" id="comment" placeholder="Share your experience with this product..." required></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeReviewModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Review</button>
                </div>
            </form>
        </div>
    </div>

    <div id="cancelModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeCancelModal()">&times;</span>
            <h2>Cancel Order</h2>
            <p>Please tell us why you're canceling this order:</p>
            <form action="cancel_order.php" method="POST">
                <input type="hidden" name="order_id" id="cancel_order_id">
                <div class="form-group">
                    <label>Reason for Cancellation: <span style="color: #dc3545;">*</span></label>
                    <select name="cancel_reason" required>
                        <option value="">Select a reason...</option>
                        <option value="Wrong address">Wrong address</option>
                        <option value="Changed my mind">Changed my mind</option>
                        <option value="Found better price">Found better price</option>
                        <option value="Ordered by mistake">Ordered by mistake</option>
                        <option value="Delivery too slow">Delivery too slow</option>
                        <option value="Want to change item">Want to change item</option>
                        <option value="Other">Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Additional Comments (Optional):</label>
                    <textarea name="cancel_comment" placeholder="Please provide more details if needed..."></textarea>
                </div>
                <div class="modal-buttons">
                    <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">Go Back</button>
                    <button type="submit" class="btn btn-cancel">Confirm Cancellation</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function showTab(tabName) {
            document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
            document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
            document.getElementById(tabName + '-tab').classList.add('active');
            event.target.classList.add('active');
        }

        function openReviewModal(orderId, productName, productId) {
            document.getElementById('modal_order_id').value = orderId;
            document.getElementById('modal_product_id').value = productId;
            document.getElementById('modal_product_name').value = productName;
            document.getElementById('reviewModal').style.display = 'block';
            document.getElementById('rating').value = '';
            document.getElementById('comment').value = '';
            document.querySelectorAll('.star').forEach(s => s.classList.remove('selected'));
        }

        function closeReviewModal() {
            document.getElementById('reviewModal').style.display = 'none';
        }

        function openCancelModal(orderId) {
            document.getElementById('cancel_order_id').value = orderId;
            document.getElementById('cancelModal').style.display = 'block';
        }

        function closeCancelModal() {
            document.getElementById('cancelModal').style.display = 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const stars = document.querySelectorAll('.star');
            const ratingInput = document.getElementById('rating');

            stars.forEach((star, index) => {
                star.addEventListener('click', function() {
                    const rating = this.getAttribute('data-rating');
                    ratingInput.value = rating;
                    stars.forEach((s, i) => {
                        s.classList.toggle('selected', i < rating);
                    });
                });

                star.addEventListener('mouseover', function() {
                    const rating = this.getAttribute('data-rating');
                    stars.forEach((s, i) => s.style.color = i < rating ? '#ffc107' : '#e0e0e0');
                });
            });

            document.querySelector('.star-rating').addEventListener('mouseleave', function() {
                const currentRating = ratingInput.value;
                stars.forEach((s, i) => s.style.color = i < currentRating ? '#ffc107' : '#e0e0e0');
            });
        });

        window.addEventListener('click', function(event) {
            if (event.target === document.getElementById('reviewModal')) closeReviewModal();
            if (event.target === document.getElementById('cancelModal')) closeCancelModal();
        });
    </script>
</body> 
</html>