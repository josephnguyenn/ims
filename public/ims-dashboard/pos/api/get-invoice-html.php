<?php
require_once '../../define.php';
header('Content-Type: text/html; charset=utf-8');
session_start();

$orderId = (int)($_GET['order_id'] ?? 0);
if (!$orderId) {
  http_response_code(400);
  echo 'Missing order ID';
  exit;
}

$res = $mysqli->query("SELECT html FROM invoices WHERE order_id = $orderId LIMIT 1");
if ($res && $row = $res->fetch_assoc()) {
  echo $row['html'];
} else {
  echo 'Invoice not found.';
}
