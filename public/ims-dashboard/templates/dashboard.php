<?php
session_start();
if (! isset($_SESSION['token'])) {
    header('Location: ../login.php');
    exit();
}
include '../define.php';
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <title>Trang Chủ - Tappo Market</title>
    
    <!-- PWA Support -->
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#1a4ba8">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Tappo IMS">
    <link rel="apple-touch-icon" href="/ims-dashboard/images/icon-192x192.png">
    
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/enhancements.css">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <style>
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .date-filter {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .date-filter input {
            padding: 6px 10px;
            border-radius: 6px;
            border: 1px solid #ccc;
            font-size: 14px;
        }
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 40px;
        }
        .dashboard-card {
            background-color: var(--bg-card);
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        .dashboard-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }
        .dashboard-card h2 {
            font-size: 14px;
            margin-bottom: 8px;
            color: var(--text-secondary);
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .dashboard-card p {
            font-size: 28px;
            font-weight: bold;
            color: var(--accent-primary);
            margin: 0;
        }
        .section-title {
            margin: 40px 0 20px;
            font-size: 20px;
            font-weight: 700;
            color: var(--text-primary);
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .section-title::before {
            content: '';
            width: 4px;
            height: 24px;
            background: linear-gradient(135deg, var(--accent-primary), var(--accent-secondary));
            border-radius: 2px;
        }
        .charts-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 30px;
        }
        .chart-card {
            background-color: var(--bg-card);
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow);
            border: 1px solid var(--border-color);
            transition: all 0.3s ease;
        }
        .chart-card:hover {
            box-shadow: var(--shadow-hover);
            transform: translateY(-2px);
        }
        .chart-card h3 {
            font-size: 16px;
            margin-bottom: 15px;
            color: var(--text-primary);
            font-weight: 600;
        }
        .chart-card canvas {
            max-height: 300px;
        }
        [data-theme="dark"] .chart-card {
            background-color: var(--bg-card);
            border-color: var(--border-color);
        }
        [data-theme="dark"] .chart-card h3 {
            color: var(--text-primary);
        }
        [data-theme="dark"] .dashboard-card {
            background-color: var(--bg-card);
            border-color: var(--border-color);
        }
        [data-theme="dark"] .dashboard-card h2 {
            color: var(--text-secondary);
        }
        [data-theme="dark"] .dashboard-card p {
            color: var(--accent-primary);
        }
        [data-theme="dark"] th {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
        }
        [data-theme="dark"] td {
            color: var(--text-secondary);
        }
        @media (max-width: 768px) {
            .charts-grid {
                grid-template-columns: 1fr;
            }
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: var(--bg-card);
            border-radius: 8px;
            overflow: hidden;
            box-shadow: var(--shadow);
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }
        th {
            background-color: var(--bg-secondary);
            color: var(--text-primary);
            font-weight: 600;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: 0.5px;
        }
        td {
            color: var(--text-secondary);
        }
        tr:hover {
            background-color: var(--bg-hover);
        }
        tr:last-child td {
            border-bottom: none;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>
<div class="main">
    <?php include '../includes/sidebar.php'; ?>

    <div class="main-content">
        <div class="dashboard-header">
            <h1>Trang Chủ - Tappo Market</h1>
            <div class="date-filter">
                <label for="from_date">Từ:</label>
                <input type="date" id="from_date">
                <label for="to_date">Đến:</label>
                <input type="date" id="to_date">
                <button onclick="filterDashboard()">Xác nhận</button>
            </div>
        </div>

        <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['role'] === 'manager')) { ?>
        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Tổng doanh thu</h2>
                <p id="dashboard-revenue">0Kč</p>
            </div>
            <div class="dashboard-card">
                <h2>Tổng đơn đặt hàng</h2>
                <p id="dashboard-orders">0</p>
            </div>
            <div class="dashboard-card">
                <h2>Tổng nợ</h2>
                <p id="dashboard-debt">0Kč</p>
            </div>
            <div class="dashboard-card">
                <h2>Doanh thu thực tế</h2>
                <p id="dashboard-actual">0Kč</p>
            </div>
        </div>

        <!-- Chart.js Visualizations -->
        <h2 class="section-title">📊 Biểu đồ và Phân tích</h2>
        <div class="charts-grid">
            <div class="chart-card">
                <h3>Xu hướng doanh thu 30 ngày</h3>
                <canvas id="salesTrendChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Top 10 sản phẩm bán chạy</h3>
                <canvas id="topProductsChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Doanh thu vs Nợ</h3>
                <canvas id="revenueDebtChart"></canvas>
            </div>
            <div class="chart-card">
                <h3>Tình trạng kho hàng</h3>
                <canvas id="inventoryPieChart"></canvas>
            </div>
        </div>
    <?php } elseif (isset($_SESSION['role']) && $_SESSION['role'] === 'staff') { ?>
        <p>Chào mừng nhân viên!</p>
        <?php } else { ?>
        <p>Bạn không có quyền truy cập vào nội dung này.</p>
    <?php } ?>


        <h2 class="section-title">🧯 Sản phẩm sắp hết hạn (Trong 30 ngày)</h2>
        <table id="expired-products">
            <thead>
                <tr>
                    <th>Tên sản phẩm</th>
                    <th>Kho</th>
                    <th>Lô Hàng</th>
                    <th>Ngày hết hạn</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="4">Loading...</td></tr>
            </tbody>
        </table>

        <h2 class="section-title">📦 Các sản phẩm bán chạy</h2>
        <table id="top-products">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Tổng số bán</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="2">Loading...</td></tr>
            </tbody>
        </table>

        <h2 class="section-title">📥 Các sản phẩm nhập nhiều</h2>
        <table id="most-imported">
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Tổng só nhập</th>
                </tr>
            </thead>
            <tbody>
                <tr><td colspan="2">Loading...</td></tr>
            </tbody>
        </table>
    </div>
</div>
<script>
    const BASE_URL = "<?= BASE_URL ?>";
</script>   
<script src="../js/dashboard.js"></script>
<script src="../js/dashboard-charts.js"></script>
<script src="../js/notification-manager.js"></script>
<script src="../js/theme-toggle.js"></script>
<script src="../js/keyboard-shortcuts.js"></script>
<script src="../js/autocomplete.js"></script>
<script src="../js/pwa-installer.js"></script>
</body>
</html>
