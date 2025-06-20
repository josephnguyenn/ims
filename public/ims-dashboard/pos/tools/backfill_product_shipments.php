<?php
// backfill_product_shipments.php
require_once __DIR__ . '/../../define.php'; // includes $mysqli connection

echo "<h3>Backfilling product_shipments...</h3>";

$products = $mysqli->query("SELECT * FROM products WHERE shipment_id IS NOT NULL");

$count = 0;

while ($p = $products->fetch_assoc()) {
    $stmt = $mysqli->prepare("
        INSERT INTO product_shipments (product_id, shipment_id, actual_quantity, cost, expiry_date)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param(
        'iiids',
        $p['id'],
        $p['shipment_id'],
        $p['actual_quantity'],
        $p['cost'],
        $p['expired_date']
    );
    $stmt->execute();
    $stmt->close();
    $count++;
}

echo "✅ Inserted $count records into product_shipments.<br>";
