<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Order statistics
$total_orders = $conn->query("SELECT COUNT(*) FROM orders")->fetch_row()[0];
$completed_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'Received'")->fetch_row()[0];
$new_orders = $conn->query("SELECT COUNT(*) FROM orders WHERE status = 'Pending'")->fetch_row()[0];

// Fetch all products
$products = $conn->query("SELECT * FROM products ORDER BY created_at DESC")->fetch_all(MYSQLI_ASSOC);

// Toggle availability
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_id'])) {
    $id = $_POST['toggle_id'];
    $current_status = $_POST['current_status'];
    $new_status = $current_status === 'Available' ? 'Not Available' : 'Available';

    $stmt = $conn->prepare("UPDATE products SET availability = ? WHERE id = ?");
    $stmt->bind_param("si", $new_status, $id);
    $stmt->execute();
    header("Location: admin_dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOVEAUX - Admin Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background-color: #fafafa;
            color: #2c2c2c;
            line-height: 1.6;
        }

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

        /* Header */
        .admin-header {
            background: linear-gradient(135deg, #2c2c2c 0%, #3a3a3a 100%);
            padding: 50px 40px;
            text-align: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            margin-bottom: 40px;
        }

        .admin-title {
            color: #ffffff;
            font-size: 42px;
            font-weight: 600;
            letter-spacing: 3px;
            text-transform: uppercase;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            margin: 0;
            line-height: 1.2;
        }

        .admin-subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            font-weight: 400;
            letter-spacing: 1px;
            margin-top: 10px;
        }

        .content-wrapper {
            padding: 0 40px 40px;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 30px;
            margin-bottom: 50px;
        }

        .stat-card {
            background: white;
            padding: 30px;
            border-radius: 12px;
            border: 1px solid #e8e8e8;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.12);
        }

        .stat-label {
            font-size: 14px;
            font-weight: 500;
            color: #777;
            margin-bottom: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-value {
            font-size: 42px;
            font-weight: 700;
            color: #A1BC98;
        }

        /* Section Header */
        .section-header {
            margin-bottom: 30px;
        }

        .section-title {
            font-size: 28px;
            font-weight: 600;
            color: #2c2c2c;
        }

        /* Table */
        .table-container {
            background: white;
            border-radius: 12px;
            border: 1px solid #e8e8e8;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.08);
        }

        .table {
            width: 100%;
            border-collapse: collapse;
        }

        .table th {
            background-color: #f8f8f8;
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            color: #2c2c2c;
            border-bottom: 2px solid #e8e8e8;
            font-size: 14px;
        }

        .table td {
            padding: 20px;
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .table tr:hover {
            background-color: #fafafa;
        }

        /* Product Image */
        .product-images {
            display: flex;
            gap: 6px;
        }

        .product-thumb {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            object-fit: cover;
            border: 1px solid #e8e8e8;
        }

        .no-image {
            width: 50px;
            height: 50px;
            background-color: #f8f8f8;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            color: #999;
            border: 1px solid #e8e8e8;
        }

        /* Status Badge */
        .status-badge {
            display: inline-flex;
            align-items: center;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-available {
            background-color: #e8f3e5;
            color: #2d5016;
        }

        .status-unavailable {
            background-color: #f8d7da;
            color: #842029;
        }

        /* Buttons */
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 18px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-family: 'Poppins', sans-serif;
        }

        .btn-primary {
            background-color: #2c2c2c;
            color: white;
        }

        .btn-primary:hover {
            background-color: #3a3a3a;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background-color: #c82333;
            transform: translateY(-2px);
        }

        .btn-toggle {
            background-color: #A1BC98;
            color: white;
            margin-bottom: 8px;
        }

        .btn-toggle:hover {
            background-color: #8fa983;
            transform: translateY(-2px);
        }

        .btn-toggle.unavailable {
            background-color: #6b7280;
        }

        .btn-toggle.unavailable:hover {
            background-color: #4b5563;
        }

        .actions {
            display: flex;
            gap: 10px;
        }

        .price {
            font-weight: 700;
            color: #A1BC98;
            font-size: 18px;
        }

        .toggle-form {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
        }

        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
            }
            
            .main-content {
                margin-left: 0;
            }

            .content-wrapper {
                padding: 0 20px 20px;
            }

            .admin-title {
                font-size: 28px;
                letter-spacing: 2px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
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
                <a href="admin_dashboard.php" class="nav-link active">Dashboard</a>
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
                <a href="admin_reviews.php" class="nav-link">Reviews</a>
            </div>
        </nav>
        
        <div class="logout-section">
            <a href="logout.php" class="logout-btn">Logout</a>
        </div>
    </aside>

    <main class="main-content">
        <div class="admin-header">
            <h1 class="admin-title">NOVEAUX Admin</h1>
            <p class="admin-subtitle">Manage Your Store</p>
        </div>

        <div class="content-wrapper">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Total Orders</div>
                    <div class="stat-value"><?php echo number_format($total_orders); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Completed Orders</div>
                    <div class="stat-value"><?php echo number_format($completed_orders); ?></div>
                </div>
                <div class="stat-card">
                    <div class="stat-label">Pending Orders</div>
                    <div class="stat-value"><?php echo number_format($new_orders); ?></div>
                </div>
            </div>

            <div class="section-header">
                <h2 class="section-title">All Products</h2>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Product</th>
                            <th>Name</th>
                            <th>Details</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($products as $product): ?>
                        <tr>
                            <td>
                                <div class="product-images">
                                    <?php
                                    $images_displayed = 0;
                                    for ($i = 1; $i <= 3; $i++) {
                                        $img = $product["image$i"] ?? '';
                                        $img_path = 'uploads/' . basename($img);
                                        
                                        if (!empty($img) && file_exists($img_path)) {
                                            echo '<img src="' . htmlspecialchars($img_path) . '" class="product-thumb" alt="Product">';
                                            $images_displayed++;
                                        }
                                    }
                                    
                                    if ($images_displayed === 0) {
                                        echo '<div class="no-image">No Image</div>';
                                    }
                                    ?>
                                </div>
                            </td>
                            <td>
                                <div style="font-weight: 600; color: #2c2c2c;"><?php echo htmlspecialchars($product['name']); ?></div>
                            </td>
                            <td>
                                <div style="font-size: 14px; color: #777;">
                                    Size: <?php echo htmlspecialchars($product['size']); ?><br>
                                    Color: <?php echo htmlspecialchars($product['color']); ?>
                                </div>
                            </td>
                            <td>
                                <span class="price">₱<?php echo number_format($product['price'], 2); ?></span>
                            </td>
                            <td>
                                <form method="POST" class="toggle-form">
                                    <input type="hidden" name="toggle_id" value="<?php echo $product['id']; ?>">
                                    <input type="hidden" name="current_status" value="<?php echo $product['availability']; ?>">
                                    <button type="submit" class="btn btn-toggle <?php echo $product['availability'] !== 'Available' ? 'unavailable' : ''; ?>">
                                        <?php echo $product['availability'] === 'Available' ? 'Mark Unavailable' : 'Mark Available'; ?>
                                    </button>
                                    <span class="status-badge <?php echo $product['availability'] === 'Available' ? 'status-available' : 'status-unavailable'; ?>">
                                        <?php echo $product['availability']; ?>
                                    </span>
                                </form>
                            </td>
                            <td>
                                <span style="font-size: 14px; color: #777;">
                                    <?php echo date('M d, Y', strtotime($product['created_at'])); ?>
                                </span>
                            </td>
                            <td>
                                <div class="actions">
                                    <a href="edit_product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">Edit</a>
                                    <a href="delete_product.php?id=<?php echo $product['id']; ?>" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this product?');">Delete</a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
</body>
</html>