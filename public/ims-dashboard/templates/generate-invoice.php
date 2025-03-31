<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['order_id'])) {
    die("Order ID is required.");
}
$order_id = $_GET['order_id'];

function fetchData($url) {
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $_SESSION['token']
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$order = fetchData("http://localhost/ims/public/api/orders/$order_id");
$orderProducts = fetchData("http://localhost/ims/public/api/order-products/$order_id");

if (!$order || !isset($order['id'])) {
    die("Order not found.");
}

$customer = $order['customer'] ?? [];
$date = date('d/m/Y', strtotime($order['created_at'] ?? 'now'));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa Đơn - Order #<?= htmlspecialchars($order['id']) ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { text-align: center; font-size: 24px; font-weight: bold; margin-bottom: 20px; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; margin-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        .totals { text-align: right; margin-top: 20px; }
        .highlight { font-weight: bold; font-size: 18px; }
    </style>
</head>
<body>
    <div class="header">HÓA ĐƠN - CHỨNG TỪ THUẾ</div>

    <div class="section">
        <div class="section-title">Nhà cung cấp</div>
        <p><strong>Tên:</strong> Chợ Tappo</p>
        <p><strong>Địa chỉ:</strong> 123 Chợ Tappo Street, HCM</p>
    </div>

    <div class="section">
        <div class="section-title">Khách hàng</div>
        <p><strong>Tên:</strong> <?= htmlspecialchars($customer['name'] ?? 'N/A') ?></p>
        <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($customer['address'] ?? 'N/A') ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($customer['email'] ?? '') ?></p>
        <p><strong>SĐT:</strong> <?= htmlspecialchars($customer['phone'] ?? '') ?></p>
        <p><strong>Ngày lập hóa đơn:</strong> <?= $date ?></p>
    </div>

    <div class="section">
        <div class="section-title">Chi tiết thanh toán</div>
        <table>
            <thead>
                <tr>
                    <th>Sản phẩm</th>
                    <th>Số lượng</th>
                    <th>Đơn giá</th>
                    <th>Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <?php $total = 0; ?>
                <?php foreach ($orderProducts as $item): ?>
                    <?php 
                        $lineTotal = $item['price'] * $item['quantity']; 
                        $total += $lineTotal;
                    ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product']['name']) ?></td>
                        <td><?= $item['quantity'] ?></td>
                        <td>$<?= number_format($item['price'], 2) ?></td>
                        <td>$<?= number_format($lineTotal, 2) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="totals">
        <p class="highlight">Tổng cộng: $<?= number_format($total, 2) ?></p>
        <p>Đã thanh toán: $<?= number_format($order['paid_amount'], 2) ?></p>
        <p>Còn lại: $<?= number_format($total - $order['paid_amount'], 2) ?></p>
    </div>
</body>
</html>
