<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trang Chủ - Tappo Market</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
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
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .dashboard-card h2 {
            font-size: 16px;
            margin-bottom: 8px;
            color: #444;
        }
        .dashboard-card p {
            font-size: 22px;
            font-weight: bold;
            color: #1a4ba8;
        }
        .section-title {
            margin: 40px 0 10px;
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ccc;
            text-align: left;
            color: #10408b;
        }
        th {
            background-color: #f5f5f5;
        }
    </style>
</head>
<body>
<?php include "../includes/header.php"; ?>
<div class="main">
    <?php include "../includes/sidebar.php"; ?>

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

        <h2 class="section-title">🧯 Lô hàng sắp hết hạng (Trong 30 ngày)</h2>
        <table id="expired-shipments">
            <thead>
                <tr>
                    <th>Mã lô hàng</th>
                    <th>Kho</th>
                    <th>Nhà cung cấp</th>
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
<script src="../js/dashboard.js"></script>
<script>
    sessionStorage.setItem("token", "<?= $_SESSION['token'] ?>");
    
</script>
</body>
</html>
