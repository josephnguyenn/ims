<?php
require_once __DIR__.'/../../define.php';
$order_id = (int)($_GET['id'] ?? 0);
// Lấy đơn hàng chỉ khi source = 'pos'
$order = $mysqli->query("SELECT * FROM orders WHERE id = $order_id AND source = 'pos'")->fetch_assoc();
if (!$order) {
    http_response_code(404);
    echo json_encode(['error' => 'Không tìm thấy hóa đơn POS này!']);
    exit;
}
$items = [];
$res2 = $mysqli->query("
  SELECT op.*, p.name AS product_name 
    FROM order_products op
    LEFT JOIN products p ON op.product_id = p.id
   WHERE op.order_id = $order_id
");
while ($row = $res2->fetch_assoc()) $items[] = $row;
echo json_encode(['order' => $order, 'items' => $items]);
