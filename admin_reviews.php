<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch all reviews with user and product details
$sql = "SELECT r.id, r.rating, r.comment, r.created_at, 
               u.first_name, u.last_name, 
               p.name AS product_name, p.image1
        FROM reviews r
        JOIN users u ON r.user_id = u.id
        JOIN products p ON r.product_id = p.id
        ORDER BY r.created_at DESC";

$result = $conn->query($sql);
$reviews = $result->fetch_all(MYSQLI_ASSOC);

// Calculate review statistics
$total_reviews = count($reviews);
$average_rating = 0;
$rating_counts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

if ($total_reviews > 0) {
    $total_rating = 0;
    foreach ($reviews as $review) {
        $total_rating += $review['rating'];
        $rating_counts[$review['rating']]++;
    }
    $average_rating = round($total_rating / $total_reviews, 1);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOVEAUX - Customer Reviews</title>
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
            margin-bottom: 40px;
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
        
        .content { 
            max-width: 1400px; 
            margin: 0 auto; 
            padding: 0 40px 40px; 
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border: 1px solid #e8e8e8;
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, #f59e0b, #d97706);
        }

        .stat-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            transform: translateY(-3px);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .stat-label {
            font-size: 13px;
            color: #777;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .stat-value {
            font-size: 36px;
            font-weight: 700;
            color: #2c2c2c;
            margin-bottom: 8px;
            line-height: 1;
        }

        .stat-note {
            font-size: 12px;
            color: #999;
            line-height: 1.4;
        }

        .reviews-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            overflow: hidden;
            border: 1px solid #e8e8e8;
        }

        .reviews-header {
            padding: 25px 30px;
            background: #f8f8f8;
            border-bottom: 2px solid #e8e8e8;
        }

        .reviews-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c2c2c;
        }

        .reviews-list {
            max-height: 600px;
            overflow-y: auto;
        }

        .reviews-list::-webkit-scrollbar {
            width: 8px;
        }

        .reviews-list::-webkit-scrollbar-track {
            background: #f0f0f0;
        }

        .reviews-list::-webkit-scrollbar-thumb {
            background: #A1BC98;
            border-radius: 4px;
        }

        .review-item {
            padding: 25px 30px;
            border-bottom: 1px solid #f0f0f0;
            transition: all 0.2s;
        }

        .review-item:hover {
            background-color: #f8f8f8;
        }

        .review-item:last-child {
            border-bottom: none;
        }

        .review-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 15px;
        }

        .review-meta {
            flex: 1;
        }

        .review-top {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 8px;
        }

        .product-image {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            object-fit: cover;
            border: 2px solid #e8e8e8;
            flex-shrink: 0;
            background: #f8f8f8;
        }

        .customer-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #A1BC98 0%, #8fa983 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 16px;
            flex-shrink: 0;
        }

        .review-info {
            flex: 1;
        }

        .customer-name {
            font-weight: 600;
            color: #2c2c2c;
            font-size: 15px;
            margin-bottom: 3px;
        }

        .product-name {
            color: #777;
            font-size: 13px;
        }

        .rating-section {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-shrink: 0;
        }

        .stars {
            color: #f59e0b;
            font-size: 18px;
            letter-spacing: 2px;
        }

        .rating-badge {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .review-comment {
            color: #555;
            margin-bottom: 12px;
            line-height: 1.6;
            padding: 15px;
            background: #f8f8f8;
            border-radius: 10px;
            border-left: 3px solid #A1BC98;
        }

        .review-footer {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #999;
            font-size: 13px;
        }

        .review-date {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .empty-state {
            text-align: center;
            padding: 80px 40px;
            color: #999;
        }

        .empty-icon {
            font-size: 80px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .empty-state h3 {
            font-size: 24px;
            color: #2c2c2c;
            margin-bottom: 10px;
        }

        .empty-state p {
            font-size: 15px;
            color: #777;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }

            .page-header { 
                padding: 30px 20px; 
            }
            
            .page-title { 
                font-size: 28px; 
                letter-spacing: 2px; 
            }
            
            .content { 
                padding: 0 20px 20px; 
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .stat-card {
                padding: 20px;
            }

            .stat-value {
                font-size: 28px;
            }
            
            .review-item {
                padding: 20px;
            }
            
            .review-header {
                flex-direction: column;
                gap: 15px;
            }

            .rating-section {
                align-self: flex-start;
            }
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
                <a href="my_orders.php" class="nav-link">Orders</a>
            </div>
            <div class="nav-item">
                <a href="customers.php" class="nav-link">Customers</a>
            </div>
            <div class="nav-item">
                <a href="statistics.php" class="nav-link">Statistics</a>
            </div>
            <div class="nav-item">
                <a href="admin_reviews.php" class="nav-link active">Reviews</a>
            </div>
        </nav>
        
        <div class="logout-section">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1 class="page-title">Customer Reviews</h1>
            <p class="page-subtitle">Monitor customer feedback and product ratings</p>
        </div>

        <div class="content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">💬</div>
                    </div>
                    <div class="stat-label">Total Reviews</div>
                    <div class="stat-value"><?php echo $total_reviews; ?></div>
                    <div class="stat-note">All customer reviews received</div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">⭐</div>
                    </div>
                    <div class="stat-label">Average Rating</div>
                    <div class="stat-value"><?php echo $average_rating; ?></div>
                    <div class="stat-note">Overall customer satisfaction</div>
                </div>

                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">🌟</div>
                    </div>
                    <div class="stat-label">5-Star Reviews</div>
                    <div class="stat-value"><?php echo $rating_counts[5]; ?></div>
                    <div class="stat-note">Excellent ratings received</div>
                </div>
            </div>

            <div class="reviews-container">
                <div class="reviews-header">
                    <div class="reviews-title">All Customer Reviews</div>
                </div>
                
                <?php if (count($reviews) > 0): ?>
                    <div class="reviews-list">
                        <?php foreach ($reviews as $review): ?>
                            <?php
                                $initial = strtoupper(substr($review['first_name'], 0, 1));
                                
                                // Get product image path
                                $imagePath = 'uploads/' . basename($review['image1'] ?? '');
                                if (empty($review['image1']) || !file_exists($imagePath)) {
                                    $imagePath = 'uploads/placeholder.jpg';
                                }
                            ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <div class="review-meta">
                                        <div class="review-top">
                                            <img src="<?php echo htmlspecialchars($imagePath); ?>" alt="Product" class="product-image">
                                            <div class="customer-avatar"><?php echo $initial; ?></div>
                                            <div class="review-info">
                                                <div class="customer-name"><?php echo htmlspecialchars($review['first_name'] . ' ' . $review['last_name']); ?></div>
                                                <div class="product-name">Reviewed: <?php echo htmlspecialchars($review['product_name']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="rating-section">
                                        <div class="stars"><?php echo str_repeat('★', $review['rating']) . str_repeat('☆', 5 - $review['rating']); ?></div>
                                        <div class="rating-badge"><?php echo $review['rating']; ?>.0</div>
                                    </div>
                                </div>
                                
                                <?php if (!empty($review['comment'])): ?>
                                    <div class="review-comment"><?php echo nl2br(htmlspecialchars($review['comment'])); ?></div>
                                <?php endif; ?>
                                
                                <div class="review-footer">
                                    <div class="review-date">
                                        📅 <?php echo date('M j, Y \a\t g:i A', strtotime($review['created_at'])); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">⭐</div>
                        <h3>No reviews yet</h3>
                        <p>Customer reviews will appear here once they start rating products.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>