<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Get total income from shipped and received orders
$totalQuery = "SELECT SUM(p.price * o.quantity) AS total_income
               FROM orders o
               JOIN products p ON o.product_id = p.id
               WHERE o.status IN ('Shipped', 'Received')";

$result = $conn->query($totalQuery);
$data = $result->fetch_assoc();
$total_income = $data['total_income'] ?? 0.00;

// Get today's income
$todayQuery = "SELECT SUM(p.price * o.quantity) AS today_income
               FROM orders o
               JOIN products p ON o.product_id = p.id
               WHERE o.status IN ('Shipped', 'Received')
               AND DATE(o.order_date) = CURDATE()";

$todayResult = $conn->query($todayQuery);
$todayData = $todayResult->fetch_assoc();
$today_income = $todayData['today_income'] ?? 0.00;

// Get this week's income
$thisWeekQuery = "SELECT SUM(p.price * o.quantity) AS this_week_income
                  FROM orders o
                  JOIN products p ON o.product_id = p.id
                  WHERE o.status IN ('Shipped', 'Received')
                  AND YEARWEEK(o.order_date, 1) = YEARWEEK(CURDATE(), 1)";

$thisWeekResult = $conn->query($thisWeekQuery);
$thisWeekData = $thisWeekResult->fetch_assoc();
$this_week_income = $thisWeekData['this_week_income'] ?? 0.00;

// Calculate this month's income
$thisMonthQuery = "SELECT SUM(p.price * o.quantity) AS this_month_income
                   FROM orders o
                   JOIN products p ON o.product_id = p.id
                   WHERE o.status IN ('Shipped', 'Received')
                   AND MONTH(o.order_date) = MONTH(CURRENT_DATE())
                   AND YEAR(o.order_date) = YEAR(CURRENT_DATE())";

$thisMonthResult = $conn->query($thisMonthQuery);
$thisMonthData = $thisMonthResult->fetch_assoc();
$this_month_income = $thisMonthData['this_month_income'] ?? 0.00;

// Get monthly income for chart
$monthlyQuery = "
    SELECT 
        DATE_FORMAT(o.order_date, '%b %Y') AS month,
        SUM(p.price * o.quantity) AS income
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.status IN ('Shipped', 'Received')
    GROUP BY DATE_FORMAT(o.order_date, '%Y-%m')
    ORDER BY o.order_date ASC
";

$monthlyResult = $conn->query($monthlyQuery);
$months = [];
$incomes = [];

while ($row = $monthlyResult->fetch_assoc()) {
    $months[] = $row['month'];
    $incomes[] = $row['income'];
}

// Get daily income for the last 30 days
$dailyQuery = "
    SELECT 
        DATE(o.order_date) AS day,
        SUM(p.price * o.quantity) AS income
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.status IN ('Shipped', 'Received')
    AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
    GROUP BY DATE(o.order_date)
    ORDER BY o.order_date ASC
";

$dailyResult = $conn->query($dailyQuery);
$days = [];
$dailyIncomes = [];

while ($row = $dailyResult->fetch_assoc()) {
    $days[] = date('M j', strtotime($row['day']));
    $dailyIncomes[] = $row['income'];
}

// Get weekly income for the last 12 weeks
$weeklyQuery = "
    SELECT 
        CONCAT('Week ', WEEK(o.order_date, 1)) AS week,
        SUM(p.price * o.quantity) AS income
    FROM orders o
    JOIN products p ON o.product_id = p.id
    WHERE o.status IN ('Shipped', 'Received')
    AND o.order_date >= DATE_SUB(CURDATE(), INTERVAL 12 WEEK)
    GROUP BY YEARWEEK(o.order_date, 1)
    ORDER BY o.order_date ASC
";

$weeklyResult = $conn->query($weeklyQuery);
$weeks = [];
$weeklyIncomes = [];

while ($row = $weeklyResult->fetch_assoc()) {
    $weeks[] = $row['week'];
    $weeklyIncomes[] = $row['income'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NOVEAUX - Statistics</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .metric-card {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            transition: all 0.3s;
            border: 1px solid #e8e8e8;
            position: relative;
            overflow: hidden;
        }

        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
        }

        .metric-card.total::before { background: linear-gradient(90deg, #10b981, #059669); }
        .metric-card.today::before { background: linear-gradient(90deg, #3b82f6, #2563eb); }
        .metric-card.week::before { background: linear-gradient(90deg, #8b5cf6, #7c3aed); }
        .metric-card.month::before { background: linear-gradient(90deg, #f59e0b, #d97706); }

        .metric-card:hover {
            box-shadow: 0 4px 20px rgba(0,0,0,0.12);
            transform: translateY(-3px);
        }

        .metric-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .metric-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
        }

        .metric-card.total .metric-icon { background: linear-gradient(135deg, #10b981, #059669); }
        .metric-card.today .metric-icon { background: linear-gradient(135deg, #3b82f6, #2563eb); }
        .metric-card.week .metric-icon { background: linear-gradient(135deg, #8b5cf6, #7c3aed); }
        .metric-card.month .metric-icon { background: linear-gradient(135deg, #f59e0b, #d97706); }

        .metric-label {
            font-size: 13px;
            color: #777;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 12px;
        }

        .metric-value {
            font-size: 32px;
            font-weight: 700;
            color: #2c2c2c;
            margin-bottom: 8px;
            line-height: 1;
        }

        .metric-note {
            font-size: 12px;
            color: #999;
            line-height: 1.4;
        }

        .chart-tabs {
            display: flex;
            background: white;
            border-radius: 16px;
            padding: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            border: 1px solid #e8e8e8;
            gap: 8px;
        }

        .chart-tab {
            flex: 1;
            padding: 14px 20px;
            text-align: center;
            border-radius: 10px;
            cursor: pointer;
            font-weight: 500;
            color: #777;
            transition: all 0.3s;
            font-size: 14px;
            border: none;
            background: transparent;
            font-family: 'Poppins', sans-serif;
        }

        .chart-tab:hover {
            background: #f8f8f8;
            color: #A1BC98;
        }

        .chart-tab.active {
            background: #A1BC98;
            color: white;
            box-shadow: 0 2px 8px rgba(161, 188, 152, 0.3);
        }

        .chart-container {
            background: white;
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.08);
            border: 1px solid #e8e8e8;
        }

        .chart-header {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .chart-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c2c2c;
            margin-bottom: 8px;
        }

        .chart-subtitle {
            font-size: 14px;
            color: #777;
        }

        .chart-wrapper {
            position: relative;
            height: 400px;
        }

        .chart-content {
            display: none;
        }

        .chart-content.active {
            display: block;
        }

        .loading-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 400px;
            color: #999;
        }

        .loading-icon {
            font-size: 60px;
            margin-bottom: 20px;
            opacity: 0.3;
        }

        .loading-state p {
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

            .metrics-grid {
                grid-template-columns: 1fr;
            }
            
            .metric-value {
                font-size: 28px;
            }
            
            .chart-wrapper {
                height: 300px;
            }
            
            .metric-card {
                padding: 20px;
            }

            .chart-tabs {
                flex-direction: column;
                gap: 6px;
            }

            .chart-container {
                padding: 20px;
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
                <a href="statistics.php" class="nav-link active">Statistics</a>
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
            <h1 class="page-title">Revenue Statistics</h1>
            <p class="page-subtitle">Track your business revenue and performance</p>
        </div>

        <div class="content">
            <div class="metrics-grid">
                <div class="metric-card total">
                    <div class="metric-header">
                        <div class="metric-icon">💰</div>
                    </div>
                    <div class="metric-label">Total Revenue</div>
                    <div class="metric-value">₱<?php echo number_format($total_income, 2); ?></div>
                    <div class="metric-note">All-time income from completed orders</div>
                </div>
                
                <div class="metric-card today">
                    <div class="metric-header">
                        <div class="metric-icon">📅</div>
                    </div>
                    <div class="metric-label">Today's Revenue</div>
                    <div class="metric-value">₱<?php echo number_format($today_income, 2); ?></div>
                    <div class="metric-note">Income from today's completed orders</div>
                </div>

                <div class="metric-card week">
                    <div class="metric-header">
                        <div class="metric-icon">📊</div>
                    </div>
                    <div class="metric-label">This Week's Revenue</div>
                    <div class="metric-value">₱<?php echo number_format($this_week_income, 2); ?></div>
                    <div class="metric-note">Income from this week's completed orders</div>
                </div>

                <div class="metric-card month">
                    <div class="metric-header">
                        <div class="metric-icon">📈</div>
                    </div>
                    <div class="metric-label">This Month's Revenue</div>
                    <div class="metric-value">₱<?php echo number_format($this_month_income, 2); ?></div>
                    <div class="metric-note">Income from this month's completed orders</div>
                </div>
            </div>

            <div class="chart-tabs">
                <button class="chart-tab active" onclick="showChart('daily')">Daily (30 days)</button>
                <button class="chart-tab" onclick="showChart('weekly')">Weekly (12 weeks)</button>
                <button class="chart-tab" onclick="showChart('monthly')">Monthly</button>
            </div>

            <div class="chart-container">
                <!-- Daily Chart -->
                <div class="chart-content active" id="daily-chart">
                    <div class="chart-header">
                        <div class="chart-title">Daily Revenue Trend</div>
                        <div class="chart-subtitle">Income from completed orders over the last 30 days</div>
                    </div>
                    
                    <?php if (count($days) > 0): ?>
                        <div class="chart-wrapper">
                            <canvas id="dailyChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="loading-state">
                            <div class="loading-icon">📊</div>
                            <p>No daily revenue data available yet</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Weekly Chart -->
                <div class="chart-content" id="weekly-chart">
                    <div class="chart-header">
                        <div class="chart-title">Weekly Revenue Trend</div>
                        <div class="chart-subtitle">Income from completed orders over the last 12 weeks</div>
                    </div>
                    
                    <?php if (count($weeks) > 0): ?>
                        <div class="chart-wrapper">
                            <canvas id="weeklyChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="loading-state">
                            <div class="loading-icon">📊</div>
                            <p>No weekly revenue data available yet</p>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Monthly Chart -->
                <div class="chart-content" id="monthly-chart">
                    <div class="chart-header">
                        <div class="chart-title">Monthly Revenue Trend</div>
                        <div class="chart-subtitle">Income from completed orders over time</div>
                    </div>
                    
                    <?php if (count($months) > 0): ?>
                        <div class="chart-wrapper">
                            <canvas id="monthlyChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="loading-state">
                            <div class="loading-icon">📊</div>
                            <p>No monthly revenue data available yet</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>
</div>

<script>
    // Chart switching functionality
    function showChart(period) {
        // Hide all chart contents
        document.querySelectorAll('.chart-content').forEach(content => {
            content.classList.remove('active');
        });
        
        // Remove active class from all tabs
        document.querySelectorAll('.chart-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        
        // Show selected chart content
        document.getElementById(period + '-chart').classList.add('active');
        
        // Add active class to clicked tab
        event.target.classList.add('active');
    }

    // Chart configurations
    const chartOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                backgroundColor: '#2c2c2c',
                padding: 12,
                titleColor: '#fff',
                bodyColor: '#fff',
                borderColor: '#A1BC98',
                borderWidth: 1,
                cornerRadius: 8,
                displayColors: false,
                callbacks: {
                    label: function(context) {
                        return '₱' + context.parsed.y.toLocaleString('en-PH', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    }
                }
            }
        },
        scales: {
            x: {
                grid: {
                    display: false
                },
                ticks: {
                    color: '#777',
                    font: {
                        size: 12,
                        family: 'Poppins'
                    }
                }
            },
            y: {
                beginAtZero: true,
                grid: {
                    color: '#f0f0f0',
                    borderColor: '#e8e8e8'
                },
                ticks: {
                    color: '#777',
                    font: {
                        size: 12,
                        family: 'Poppins'
                    },
                    callback: function(value) {
                        return '₱' + value.toLocaleString();
                    }
                }
            }
        },
        interaction: {
            intersect: false,
            mode: 'index'
        }
    };

    // Daily Chart
    <?php if (count($days) > 0): ?>
    const dailyCtx = document.getElementById('dailyChart').getContext('2d');
    const dailyGradient = dailyCtx.createLinearGradient(0, 0, 0, 400);
    dailyGradient.addColorStop(0, 'rgba(59, 130, 246, 0.6)');
    dailyGradient.addColorStop(1, 'rgba(59, 130, 246, 0.05)');

    new Chart(dailyCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($days); ?>,
            datasets: [{
                label: 'Daily Revenue',
                data: <?php echo json_encode($dailyIncomes); ?>,
                backgroundColor: dailyGradient,
                borderColor: '#3b82f6',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#3b82f6',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 5,
                pointHoverRadius: 7,
                pointHoverBackgroundColor: '#3b82f6',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 3
            }]
        },
        options: chartOptions
    });
    <?php endif; ?>

    // Weekly Chart
    <?php if (count($weeks) > 0): ?>
    const weeklyCtx = document.getElementById('weeklyChart').getContext('2d');
    
    new Chart(weeklyCtx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($weeks); ?>,
            datasets: [{
                label: 'Weekly Revenue',
                data: <?php echo json_encode($weeklyIncomes); ?>,
                backgroundColor: '#8b5cf6',
                borderColor: '#7c3aed',
                borderWidth: 0,
                borderRadius: 10,
                borderSkipped: false,
                hoverBackgroundColor: '#7c3aed'
            }]
        },
        options: chartOptions
    });
    <?php endif; ?>

    // Monthly Chart
    <?php if (count($months) > 0): ?>
    const monthlyCtx = document.getElementById('monthlyChart').getContext('2d');
    const monthlyGradient = monthlyCtx.createLinearGradient(0, 0, 0, 400);
    monthlyGradient.addColorStop(0, 'rgba(161, 188, 152, 0.6)');
    monthlyGradient.addColorStop(1, 'rgba(161, 188, 152, 0.05)');

    new Chart(monthlyCtx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Monthly Revenue',
                data: <?php echo json_encode($incomes); ?>,
                backgroundColor: monthlyGradient,
                borderColor: '#A1BC98',
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: '#A1BC98',
                pointBorderColor: '#ffffff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
                pointHoverBackgroundColor: '#A1BC98',
                pointHoverBorderColor: '#ffffff',
                pointHoverBorderWidth: 3
            }]
        },
        options: chartOptions
    });
    <?php endif; ?>
</script>
</body>
</html>