<?php
// Database connection
$mysqli = new mysqli("localhost", "root", "", "tappo_market");
$mysqli->set_charset("utf8");

if (isset($_GET['category'])) {
    $category = $_GET['category'];
    $stmt = $mysqli->prepare("SELECT * FROM products WHERE category = ?");
    $stmt->bind_param("s", $category);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($p = $result->fetch_assoc()) {
        echo '<div class="product-card" data-id="' . $p['id'] . '" data-name="' . htmlspecialchars($p['name']) . '" data-price="' . $p['price'] . '">
                <div class="product-name">' . htmlspecialchars($p['name']) . '</div>
                <div class="product-price">' . number_format($p['price']) . ' CZK</div>
              </div>';
    }
}

if (isset($_GET['barcode'])) {
    $barcode = $_GET['barcode'];
    // JOIN với shipments để lấy received_date, chọn lô hàng cũ nhất còn tồn
    $stmt = $mysqli->prepare("
        SELECT p.*, s.received_date
        FROM products p
        JOIN shipments s ON p.shipment_id = s.id
        WHERE p.code = ?
          AND p.actual_quantity > 0
        ORDER BY s.received_date ASC
        LIMIT 1
    ");
    $stmt->bind_param("s", $barcode);
    $stmt->execute();
    $product = $stmt->get_result()->fetch_assoc();
    echo json_encode($product);
}
