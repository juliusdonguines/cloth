<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$user = $_SESSION['first_name'] ?? 'Guest';

// Get all received orders by the user
$sql = "SELECT o.id AS order_id, p.id AS product_id, p.name, p.size, p.color, p.price, p.image1
        FROM orders o
        JOIN products p ON o.product_id = p.id
        WHERE o.user_id = ? AND o.status = 'Received'
        ORDER BY o.order_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$orders = $result->fetch_all(MYSQLI_ASSOC);

// Fetch user's existing reviews
$reviewed = [];
$check = $conn->prepare("SELECT product_id FROM reviews WHERE user_id = ?");
$check->bind_param("i", $user_id);
$check->execute();
$check_result = $check->get_result();
while ($row = $check_result->fetch_assoc()) {
    $reviewed[] = $row['product_id'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOVEAUX - My Reviews</title>
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
        
        .content { max-width: 1400px; margin: 0 auto; padding: 30px 40px; }
        
        .product-card { background: #fff; border-radius: 16px; margin-bottom: 20px; overflow: hidden; box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
        .product-header { padding: 20px 25px; background: #f8f8f8; border-bottom: 1px solid #e8e8e8; }
        .product-header h3 { font-size: 16px; font-weight: 600; color: #2c2c2c; }
        
        .product-body { padding: 25px; }
        .product-info-container { display: flex; gap: 20px; align-items: flex-start; margin-bottom: 25px; }
        .product-image { width: 90px; height: 90px; border-radius: 12px; object-fit: cover; border: 1px solid #e8e8e8; flex-shrink: 0; background: #f8f8f8; }
        .product-details { flex: 1; }
        .product-name { font-size: 16px; font-weight: 600; color: #2c2c2c; margin-bottom: 8px; }
        .product-meta { font-size: 14px; color: #777; margin-bottom: 6px; }
        .product-price { font-size: 18px; font-weight: 700; color: #A1BC98; margin-top: 8px; }
        
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #2c2c2c; font-size: 15px; }
        .star-rating { display: flex; gap: 8px; margin-top: 8px; }
        .star { font-size: 32px; color: #e0e0e0; cursor: pointer; transition: all 0.2s; }
        .star.selected, .star:hover { color: #ffc107; transform: scale(1.1); }
        textarea { width: 100%; padding: 12px 16px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px; font-family: 'Poppins', sans-serif; transition: border-color 0.3s; min-height: 100px; resize: vertical; }
        textarea:focus { outline: none; border-color: #A1BC98; }
        
        .submit-btn { padding: 12px 28px; background: #A1BC98; color: #fff; border: none; border-radius: 8px; cursor: pointer; font-size: 14px; font-weight: 600; transition: all 0.3s; font-family: 'Poppins', sans-serif; }
        .submit-btn:hover { background: #8fa983; transform: translateY(-1px); }
        
        .already-reviewed { background: #e8f3e5; color: #2d5016; padding: 16px 20px; border-radius: 10px; border: 1px solid #c3e6cb; display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 500; }
        .already-reviewed::before { content: '✓'; font-size: 20px; }
        
        .no-orders { text-align: center; padding: 80px 40px; color: #999; background: #fff; border-radius: 16px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
        .no-orders-icon { font-size: 80px; margin-bottom: 20px; opacity: 0.3; }
        .no-orders h3 { font-size: 24px; color: #2c2c2c; margin-bottom: 10px; }
        .no-orders p { font-size: 15px; margin-bottom: 10px; }
        
        @media (max-width: 768px) {
            .nav-container { flex-direction: column; gap: 20px; padding: 20px; }
            .nav-links { flex-direction: column; gap: 15px; width: 100%; }
            .page-header { padding: 30px 20px 15px; }
            .page-title { font-size: 28px; }
            .content { padding: 20px; }
            .product-info-container { flex-direction: column; }
            .product-image { width: 100%; height: 200px; }
        }
    </style>
</head>
<body>
    <div class="top-bar">
        Welcome, <?php echo htmlspecialchars($user); ?> | Share your shopping experience
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
                <a href="orders.php">Orders</a>
                <a href="my_reviews.php" class="active">Reviews</a>
            </div>

            <div class="user-section">
                <a href="logout.php" class="logout-btn">Sign Out</a>
            </div>
        </div>
    </nav>

    <div class="page-header">
        <h1 class="page-title">My Reviews</h1>
        <p class="page-subtitle">Share your experience with products you've received</p>
    </div>

    <div class="content">
        <?php if (count($orders) > 0): ?>
            <?php foreach ($orders as $order): ?>
                <?php
                    $imagePath = 'uploads/' . basename($order['image1'] ?? '');
                    if (empty($order['image1']) || !file_exists($imagePath)) {
                        $imagePath = 'placeholder.jpg';
                    }
                ?>
                <div class="product-card">
                    <div class="product-header">
                        <h3>NOVEAUX</h3>
                    </div>
                    <div class="product-body">
                        <div class="product-info-container">
                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Product" class="product-image">
                            <div class="product-details">
                                <div class="product-name"><?php echo htmlspecialchars($order['name']); ?></div>
                                <div class="product-meta">Size: <?php echo htmlspecialchars($order['size']); ?> • Color: <?php echo htmlspecialchars($order['color']); ?></div>
                                <div class="product-price">₱<?php echo number_format($order['price'], 2); ?></div>
                            </div>
                        </div>

                        <?php if (in_array($order['product_id'], $reviewed)): ?>
                            <div class="already-reviewed">
                                You have already reviewed this product
                            </div>
                        <?php else: ?>
                            <form action="submit_review.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo $order['product_id']; ?>">
                                <input type="hidden" name="order_id" value="<?php echo $order['order_id']; ?>">
                                
                                <div class="form-group">
                                    <label>Your Rating:</label>
                                    <div class="star-rating">
                                        <span class="star" data-rating="1" data-form="form-<?php echo $order['product_id']; ?>">★</span>
                                        <span class="star" data-rating="2" data-form="form-<?php echo $order['product_id']; ?>">★</span>
                                        <span class="star" data-rating="3" data-form="form-<?php echo $order['product_id']; ?>">★</span>
                                        <span class="star" data-rating="4" data-form="form-<?php echo $order['product_id']; ?>">★</span>
                                        <span class="star" data-rating="5" data-form="form-<?php echo $order['product_id']; ?>">★</span>
                                    </div>
                                    <input type="hidden" name="rating" id="rating-<?php echo $order['product_id']; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>Your Review:</label>
                                    <textarea name="comment" placeholder="Share your experience with this product..." required></textarea>
                                </div>
                                
                                <button type="submit" class="submit-btn">Submit Review</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-orders">
                <div class="no-orders-icon">📝</div>
                <h3>No products available for review</h3>
                <p>You can only review products from completed orders</p>
                <p>Complete an order and mark it as received to leave a review</p>
            </div>
        <?php endif; ?>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const starGroups = {};
            
            document.querySelectorAll('.star').forEach(star => {
                const formId = star.getAttribute('data-form');
                if (!starGroups[formId]) {
                    starGroups[formId] = [];
                }
                starGroups[formId].push(star);
            });

            Object.keys(starGroups).forEach(formId => {
                const stars = starGroups[formId];
                const productId = formId.replace('form-', '');
                const ratingInput = document.getElementById('rating-' + productId);

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

                const starRating = stars[0].parentElement;
                starRating.addEventListener('mouseleave', function() {
                    const currentRating = ratingInput.value;
                    stars.forEach((s, i) => s.style.color = i < currentRating ? '#ffc107' : '#e0e0e0');
                });
            });
        });
    </script>
</body>
</html>