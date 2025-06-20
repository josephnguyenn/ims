<?php
require_once '../../define.php';
header('Content-Type: application/json');
session_start();

$data = json_decode(file_get_contents('php://input'), true);
$orderId = (int)($data['order_id'] ?? 0);
$html = $data['html'] ?? '';

if (!$orderId || !$html) {
  http_response_code(400);
  echo json_encode(['error' => 'Missing data']);
  exit;
}

// If already exists, update
$stmt = $mysqli->prepare("INSERT INTO invoices (order_id, html) VALUES (?, ?) ON DUPLICATE KEY UPDATE html = VALUES(html)");
$stmt->bind_param('is', $orderId, $html);
$stmt->execute();
$stmt->close();

echo json_encode(['success' => true]);
