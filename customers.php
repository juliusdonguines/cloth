<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Fetch users who are not admins
$sql = "SELECT id, first_name, last_name, email, address, contact_number, birthday, created_at 
        FROM users 
        WHERE role != 'admin'
        ORDER BY created_at DESC";

$result = $conn->query($sql);
$users = $result->fetch_all(MYSQLI_ASSOC);

$totalCustomers = count($users);
$newThisMonth = count(array_filter($users, function($user) { 
    return strtotime($user['created_at']) > strtotime('-30 days'); 
}));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOVEAUX - Customer Management</title>
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
        }

        .stat-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            transform: translateY(-2px);
        }

        .stat-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            background: linear-gradient(135deg, #A1BC98 0%, #8fa983 100%);
        }

        .stat-number {
            font-size: 36px;
            font-weight: 700;
            color: #2c2c2c;
            margin-bottom: 8px;
            line-height: 1;
        }

        .stat-label {
            color: #777;
            font-size: 14px;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .customers-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .customers-header {
            padding: 25px 30px;
            background: #f8f8f8;
            border-bottom: 2px solid #e8e8e8;
        }

        .customers-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c2c2c;
        }

        .table-wrapper {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead {
            background: #f8f8f8;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        th {
            padding: 18px 20px;
            text-align: left;
            font-weight: 600;
            color: #555;
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            border-bottom: 2px solid #e8e8e8;
        }

        td {
            padding: 20px;
            border-bottom: 1px solid #f0f0f0;
            font-size: 14px;
            color: #2c2c2c;
        }

        tbody tr {
            transition: all 0.2s;
        }

        tbody tr:hover {
            background-color: #f8f8f8;
        }

        tbody tr:last-child td {
            border-bottom: none;
        }

        .customer-cell {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .customer-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, #A1BC98 0%, #8fa983 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-weight: 700;
            font-size: 18px;
            flex-shrink: 0;
        }

        .customer-info {
            flex: 1;
        }

        .customer-name {
            font-weight: 600;
            color: #2c2c2c;
            margin-bottom: 4px;
            font-size: 15px;
        }

        .customer-email {
            color: #777;
            font-size: 13px;
        }

        .contact-info {
            color: #555;
        }

        .location-cell {
            color: #555;
            max-width: 200px;
        }

        .date-cell {
            color: #999;
            font-size: 13px;
        }

        .date-badge {
            display: inline-block;
            padding: 6px 12px;
            background: #e8f3e5;
            color: #A1BC98;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }

        .new-badge {
            background: #fff3cd;
            color: #856404;
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
                grid-template-columns: 1fr;
            }

            .table-wrapper {
                overflow-x: scroll;
            }

            table {
                min-width: 800px;
            }

            th, td {
                padding: 12px 15px;
                font-size: 13px;
            }

            .customer-avatar {
                width: 35px;
                height: 35px;
                font-size: 16px;
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
                <a href="customers.php" class="nav-link active">Customers</a>
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
            <h1 class="page-title">Customer Management</h1>
            <p class="page-subtitle">Manage and view all registered customers</p>
        </div>

        <div class="content">
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">👥</div>
                    </div>
                    <div class="stat-number"><?php echo $totalCustomers; ?></div>
                    <div class="stat-label">Total Customers</div>
                </div>
                <div class="stat-card">
                    <div class="stat-header">
                        <div class="stat-icon">✨</div>
                    </div>
                    <div class="stat-number"><?php echo $newThisMonth; ?></div>
                    <div class="stat-label">New This Month</div>
                </div>
            </div>

            <div class="customers-container">
                <div class="customers-header">
                    <div class="customers-title">Customer Directory</div>
                </div>
                
                <?php if ($totalCustomers > 0): ?>
                    <div class="table-wrapper">
                        <table>
                            <thead>
                                <tr>
                                    <th>Customer</th>
                                    <th>Contact Number</th>
                                    <th>Location</th>
                                    <th>Birthday</th>
                                    <th>Joined</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                    <?php
                                        $initial = strtoupper(substr($user['first_name'], 0, 1));
                                        $isNew = strtotime($user['created_at']) > strtotime('-30 days');
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="customer-cell">
                                                <div class="customer-avatar"><?php echo $initial; ?></div>
                                                <div class="customer-info">
                                                    <div class="customer-name"><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                                    <div class="customer-email"><?php echo htmlspecialchars($user['email']); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="contact-info"><?php echo htmlspecialchars($user['contact_number']); ?></td>
                                        <td class="location-cell"><?php echo htmlspecialchars($user['address']); ?></td>
                                        <td><?php echo htmlspecialchars(date('M j, Y', strtotime($user['birthday']))); ?></td>
                                        <td class="date-cell">
                                            <span class="date-badge <?php echo $isNew ? 'new-badge' : ''; ?>">
                                                <?php echo htmlspecialchars(date('M j, Y', strtotime($user['created_at']))); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-icon">👥</div>
                        <h3>No customers yet</h3>
                        <p>Customer registrations will appear here once users sign up.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>
</body>
</html>