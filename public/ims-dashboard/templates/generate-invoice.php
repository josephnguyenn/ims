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

$customer = $order['customer'] ?? [
    'id' => 'N/A',
    'name' => 'N/A',
    'address' => 'N/A',
    'email' => '',
    'phone' => '',
];

$date = date('d/m/Y', strtotime($order['created_at'] ?? date('Y-m-d')));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Hóa Đơn - Order #<?= htmlspecialchars($order['id']) ?></title>
    <link rel="stylesheet" href="../css/invoice.css">
    <style>
        body { font-family: Arial, sans-serif; margin: 40px; }
        .header { display: flex; justify-content: space-between; font-weight: bold; margin-bottom: 20px; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; margin-bottom: 10px; font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #000; padding: 8px; text-align: left; }
        .totals { text-align: right; margin-top: 20px; }
        .highlight { font-weight: bold; font-size: 18px; }
        .info-table td { border: none; padding: 4px 8px; }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
</head>
<body>

<button onclick="downloadPDF()">📄 Download PDF</button>
<script>
function downloadPDF() {
    const element = document.querySelector(".invoice-wrapper");
    const opt = {
        margin:       0,
        filename:     'invoice-<?= $order_id ?>.pdf',
        image:        { type: 'jpeg', quality: 0.98 },
        html2canvas:  { scale: 2 },
        jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
    };

    html2pdf().set(opt).from(element).save();
}
</script>
<div class="invoice-wrapper">

<div class="invoice-header">
    <img src="../uploads/images/logo.png" alt="Tappo Market" class="header-logo">
    <div class="invoice-title">
        <h1>HÓA ĐƠN - CHỨNG TỪ THUẾ</h1>
        <p>Số hóa đơn: HD-<?= date('dmY') ?>-<?= htmlspecialchars($order['id']) ?></p>
    </div>
</div>

<div class="info-box">
    <div class="info-section">
        <h3>Nhà cung cấp</h3>
        <p><strong>Tên:</strong> Chợ Tappo</p>
        <p><strong>Địa chỉ:</strong> 123 Chợ Tappo Street</p>
        <p><strong>Thành phố:</strong> TP Hồ Chí Minh</p>
        <p><strong>Mã bưu chính:</strong> 700000</p>
        <p><strong>Nhận dạng:</strong> SUP-001</p>
        <p><strong>Ngân hàng:</strong> Ngân hàng TCB</p>
        <p><strong>Số tài khoản:</strong> 123456789</p>
        <p><strong>Ngày phát hành:</strong> <?= $date ?></p>
        <p><strong>Ngày phát hành thuế:</strong> <?= $date ?></p>
    </div>
    <div class="info-section">
        <h3>Khách hàng</h3>
        <p><strong>Tên:</strong> <?= htmlspecialchars($customer['name']) ?></p>
        <p><strong>Địa chỉ:</strong> <?= htmlspecialchars($customer['address']) ?></p>
        <p><strong>Thành phố:</strong> <?= htmlspecialchars($customer['city']) ?></p>
        <p><strong>Mã bưu chính:</strong> <?= htmlspecialchars($customer['postal_code']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($customer['email']) ?></p>
        <p><strong>SĐT:</strong> <?= htmlspecialchars($customer['phone']) ?></p>
        <p><strong>Mã KH:</strong> <?= htmlspecialchars($customer['id']) ?></p>
        <p><strong>MST:</strong> <?= htmlspecialchars($customer['vat_code']) ?></p>
        <p><strong>Hạn thanh toán:</strong> <?= date('d/m/Y', strtotime('+5 days')) ?></p>
    </div>
</div>



    <div class="section">
        <div class="section-title">Chi tiết thanh toán</div>
        <table>
            <thead>
                <tr>
                    <th>Mục</th>
                    <th>Số lượng</th>
                    <th>Giá</th>
                    <th>Tổng chưa VAT</th>
                    <th>VAT</th>
                    <th>Tỷ lệ thuế (%)</th>
                    <th>Tổng cộng bao gồm VAT</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    $subtotal = 0;
                    $totalVAT = 0;
                    $totalAmount = 0;
                ?>
                <?php if (!empty($orderProducts)): ?>
                    <?php foreach ($orderProducts as $item): ?>
                        <?php 
                            $product = $item['product'] ?? ['name' => 'N/A', 'price' => 0, 'vat' => 10];
                            $price = $item['price'] ?? $product['price'];
                            $qty = $item['quantity'];
                            $vatRate = $product['vat'] ?? 10;
                            $preVAT = $price * $qty;
                            $vatAmount = $preVAT * ($vatRate / 100);
                            $lineTotal = $preVAT + $vatAmount;

                            $subtotal += $preVAT;
                            $totalVAT += $vatAmount;
                            $totalAmount += $lineTotal;
                        ?>
                        <tr>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= $qty ?></td>
                            <td>$<?= number_format($price, 2) ?></td>
                            <td>$<?= number_format($preVAT, 2) ?></td>
                            <td>$<?= number_format($vatAmount, 2) ?></td>
                            <td><?= $vatRate ?>%</td>
                            <td>$<?= number_format($lineTotal, 2) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">Không có sản phẩm trong đơn hàng.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="totals-box">
        <p><strong>Tổng chưa VAT:</strong> $<?= number_format($subtotal, 2) ?></p>
        <p><strong>VAT:</strong> $<?= number_format($totalVAT, 2) ?></p>
        <p class="highlight">Tổng phải trả: <strong>$<?= number_format($totalAmount, 2) ?></strong></p>
    </div>
    </div>

</body>
</html>
