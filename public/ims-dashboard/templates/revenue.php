<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch revenue data from API
$apiUrl = "http://localhost/ims/public/api/reports/sales";
$ch = curl_init($apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $_SESSION['token']
]);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
$totalSales = $data['total_sales'] ?? 0;
$totalRevenue = $data['total_revenue'] ?? 0;
$totalDebt = $data['total_debt'] ?? 0;
$actualRevenue = $totalSales - $totalDebt;

// Fetch order count
$orderCountUrl = "http://localhost/ims/public/api/orders";
$ch2 = curl_init($orderCountUrl);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Bearer ' . $_SESSION['token']
]);
$orderResponse = curl_exec($ch2);
curl_close($ch2);

$orders = json_decode($orderResponse, true);
$totalOrders = is_array($orders) ? count($orders) : 0;

$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Revenue Report</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
    <style>
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-top: 30px;
        }
        .dashboard-card {
            background-color: #fff;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .dashboard-card h2 {
            font-size: 18px;
            margin-bottom: 10px;
            color: #666;
        }
        .dashboard-card p {
            font-size: 24px;
            font-weight: bold;
            color: #1a4ba8;
        }
    </style>
</head>
<body>
    <?php include "../includes/header.php"; ?>
    <div class="main">
        <?php include "../includes/sidebar.php"; ?>

        <div class="main-content">
            <div class="main-content-header">
                <h1>Revenue Report</h1>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h2>Estimated Revenue</h2>
                    <p>$<?= number_format($totalSales, 2) ?></p>
                </div>
                <div class="dashboard-card">
                    <h2>Total Orders</h2>
                    <p><?= $totalOrders ?></p>
                </div>
                <div class="dashboard-card">
                    <h2>Debt to Be Collected</h2>
                    <p>$<?= number_format($totalDebt, 2) ?></p>
                </div>
                <div class="dashboard-card">
                    <h2>Actual Revenue</h2>
                    <p>$<?= number_format($actualRevenue, 2) ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>