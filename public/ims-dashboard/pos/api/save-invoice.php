<?php
require_once '../../define.php';
header('Content-Type: application/json');
session_start();

// Debugging - raw input
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// Fallback debug response
if (json_last_error() !== JSON_ERROR_NONE) {
  http_response_code(400);
  echo json_encode([
    'error' => 'Invalid JSON payload',
    'raw' => $raw,
    'json_error' => json_last_error_msg()
  ]);
  exit;
}

file_put_contents('debug_invoice.log', print_r($data, true));
$orderId = (int)($data['order_id'] ?? 0);
$html = trim($data['html'] ?? '');

// Check validity
if (!$orderId || !$html) {
  http_response_code(400);
  echo json_encode([
    'error' => 'Missing data',
    'received_order_id' => $orderId,
    'html_length' => strlen($html)
  ]);
  exit;
}

// Proceed to save
$stmt = $mysqli->prepare("INSERT INTO invoices (order_id, html) VALUES (?, ?) ON DUPLICATE KEY UPDATE html = VALUES(html)");
$stmt->bind_param('is', $orderId, $html);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
