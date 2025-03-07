<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - IMS</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include "../includes/sidebar.php"; ?>

    <div class="main-content">
        <h1>Dashboard</h1>
        <div class="stats">
            <div class="stat-card">
                <h3>Total Sales</h3>
                <p id="total-sales">$0</p>
            </div>
            <div class="stat-card">
                <h3>Total Revenue</h3>
                <p id="total-revenue">$0</p>
            </div>
            <div class="stat-card">
                <h3>Total Debt</h3>
                <p id="total-debt">$0</p>
            </div>
        </div>
    </div>

    <script src="../js/dashboard.js"></script>
</body>
</html>
