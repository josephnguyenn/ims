<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['order_id'])) {
    die("ID objednávky je vyžadováno.");
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
    die("Objednávka nebyla nalezena.");
}

$customer = $order['customer'] ?? [
    'id' => 'N/A',
    'name' => 'N/A',
    'address' => 'N/A',
    'email' => '',
    'phone' => '',
];

$date = date('d.m.Y', strtotime($order['created_at'] ?? date('Y-m-d')));
?>

<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <title>Faktura - Objednávka #<?= htmlspecialchars($order['id']) ?></title>
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
        .download-pdf-btn {
        background-color: #007bff; /* Blue background */
        color: white; /* White text */
        border: none; /* Remove border */
        padding: 10px 20px; /* Add padding */
        font-size: 16px; /* Increase font size */
        border-radius: 5px; /* Rounded corners */
        cursor: pointer; /* Pointer cursor on hover */
        transition: background-color 0.3s ease; /* Smooth hover effect */
    }

    .download-pdf-btn:hover {
        background-color: #0056b3; /* Darker blue on hover */
    }

    .download-pdf-btn:active {
        background-color: #003f7f; /* Even darker blue when clicked */
    }
    </style>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/0.4.1/html2canvas.min.js"></script>
</head>
<body>

<button class="download-pdf-btn" onclick="downloadPDF()">📄 Stáhnout PDF</button><script>
function downloadPDF() {
    const element = document.querySelector(".invoice-wrapper");
    const opt = {
        margin:       0,
        filename:     'faktura-<?= $order_id ?>.pdf',
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
        <h1>FAKTURA - DAŇOVÝ DOKLAD</h1>
        <p>Číslo faktury: FA-<?= date('dmY') ?>-<?= htmlspecialchars($order['id']) ?></p>
    </div>
</div>

<div class="info-box">
    <div class="info-section">
        <h3>Dodavatel</h3>
        <p><strong>Název:</strong> Tappo Market</p>
        <p><strong>Adresa:</strong> 123 Tappo Market Street</p>
        <p><strong>Město:</strong> Ho Či Minovo Město</p>
        <p><strong>PSČ:</strong> 700000</p>
        <p><strong>IČO:</strong> SUP-001</p>
        <p><strong>DIČ:</strong> 123456789</p>
        <p><strong>Banka:</strong> TCB Banka</p>
        <p><strong>Číslo účtu:</strong> 123456789</p>
        <p><strong>Datum vydání:</strong> <?= $date ?></p>
        <p><strong>Datum daňového dokladu:</strong> <?= $date ?></p>
    </div>
    <div class="info-section">
        <h3>Zákazník</h3>
        <p><strong>Jméno:</strong> <?= htmlspecialchars($customer['name']) ?></p>
        <p><strong>Adresa:</strong> <?= htmlspecialchars($customer['address']) ?></p>
        <p><strong>Město:</strong> <?= htmlspecialchars($customer['city']) ?></p>
        <p><strong>PSČ:</strong> <?= htmlspecialchars($customer['postal_code']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($customer['email']) ?></p>
        <p><strong>Telefon:</strong> <?= htmlspecialchars($customer['phone']) ?></p>
        <p><strong>ID zákazníka:</strong> <?= htmlspecialchars($customer['id']) ?></p>
        <p><strong>DIČ:</strong> <?= htmlspecialchars($customer['tax_code']) ?></p>
        <p><strong>IČO:</strong> <?= htmlspecialchars($customer['vat_code']) ?></p>
        <p><strong>Splatnost:</strong> <?= date('d.m.Y', strtotime('+5 days')) ?></p>
    </div>
</div>



    <div class="section">
        <div class="section-title">Podrobnosti o platbě</div>
        <table>
            <thead>
                <tr>
                    <th>Položka</th>
                    <th>Množství</th>
                    <th>Cena</th>
                    <th>Celkem bez DPH</th>
                    <th>DPH</th>
                    <th>Sazba DPH (%)</th>
                    <th>Celkem s DPH</th>
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
                            <td><?= number_format($price, 2) ?> CZK</td>
                            <td><?= number_format($preVAT, 2) ?> CZK</td>
                            <td><?= number_format($vatAmount, 2) ?> CZK</td>
                            <td><?= $vatRate ?>%</td>
                            <td><?= number_format($lineTotal, 2) ?> CZK</td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr><td colspan="7">Žádné produkty v objednávce.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="totals-box">
        <p><strong>Celkem bez DPH:</strong> <?= number_format($subtotal, 2) ?> CZK</p>
        <p><strong>DPH:</strong> <?= number_format($totalVAT, 2) ?> CZK</p>
        <p class="highlight">Celkem k úhradě: <strong><?= number_format($totalAmount, 2) ?> CZK</strong></p>
    </div>
    </div>

</body>
</html>