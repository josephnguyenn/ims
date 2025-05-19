<?php
// Start session
session_start();
// Check if user is logged in
if (!isset($_SESSION['token']) || !isset($_SESSION['role'])) {
    header("Location: login.php");
    exit;
}
// Include database connection
include "../define.php";


// Connect to DB
$mysqli = new mysqli("localhost", "root", "", "tappo_market");
$mysqli->set_charset("utf8");

// Fetch categories
$categories = [];
$result = $mysqli->query("SELECT DISTINCT category FROM products WHERE category IS NOT NULL ORDER BY category ASC");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row['category'];
}

// Fetch products (default to first category)
$defaultCategory = $categories[0] ?? '';
$products = [];
if ($defaultCategory) {
    $stmt = $mysqli->prepare("SELECT * FROM products WHERE category = ?");
    $stmt->bind_param("s", $defaultCategory);
    $stmt->execute();
    $products = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>POS System</title>
    <link rel="stylesheet" href="css/pos-style.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<div class="pos-wrapper">
    <!-- LEFT: Product Selection -->
    <div class="pos-left">
        <div class="category-tabs">
            <?php foreach ($categories as $cat): ?>
                <button class="category-tab" data-category="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></button>
            <?php endforeach; ?>
        </div>
        <div class="product-list" id="product-list">
            <?php foreach ($products as $p): ?>
                <div class="product-card" data-id="<?= $p['id'] ?>" data-name="<?= htmlspecialchars($p['name']) ?>" data-price="<?= $p['price'] ?>">
                    <div class="product-name"><?= htmlspecialchars($p['name']) ?></div>
                    <div class="product-price"><?= number_format($p['price'], 2) ?> CZK</div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- RIGHT: Cart & Payment -->
    <div class="pos-right">
        <div class="barcode-scan">
            <input type="text" id="barcode-input" placeholder="Scan barcode or type...">
        </div>

        <div class="controls">
            <button id="page-up">▲ Qty</button>
            <button id="page-down">▼ Qty</button>
            <button id="weigh">Weigh</button>
            <button id="toggle-print">Auto Print: <span id="print-status">OFF</span></button>
            <button id="print-invoice">Print (F11)</button>
        </div>

        <div class="numpad">
            <?php foreach ([1,2,3,4,5,6,7,8,9,0] as $num): ?>
                <button class="num-button"><?= $num ?></button>
            <?php endforeach; ?>
        </div>


                <table class="cart-table" id="cart-table">
            <thead>
                <tr><th>Product</th><th>Qty</th><th>Price</th><th>Total</th></tr>
            </thead>
            <tbody></tbody>
        </table>

        <div class="total-area">
            <div>Total: <span id="total-czk">0 CZK</span> | <span id="total-eur">0 EUR</span></div>
        </div>
    </div>
</div>

<script src="js/pos-script.js"></script>
</body>
</html>
