<?php
// pos-reports.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../define.php'; // kết nối $mysqli

// Lấy params
$from  = $_GET['from'] ?? date('Y-m-d', strtotime('-60 days'));
$to    = $_GET['to']   ?? date('Y-m-d');
$shift = isset($_GET['shift_id']) ? (int)$_GET['shift_id'] : null;

// Build query
$sql = "
  SELECT
    o.id,
    o.created_at,
    s.name       AS shift_name,
    o.payment_method,
    o.rounded_total_czk,
    o.rounded_total_eur,
    o.tip_czk,
    o.tip_eur
  FROM orders o
  LEFT JOIN shifts s ON o.shift_id = s.id
  WHERE DATE(o.created_at) BETWEEN ? AND ?
";
$params = [$from, $to];
$types  = "ss";

if ($shift) {
  $sql .= " AND o.shift_id = ?";
  $types  .= "i";
  $params[] = $shift;
}

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();
$invoices = $res->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Tính tổng
$summary = [
  'sum_czk_cash' => 0,
  'sum_czk_card' => 0,
  'sum_eur_cash' => 0,
  'sum_eur_card' => 0,
  'sum_tip_czk'  => 0,
  'sum_tip_eur'  => 0,
];
foreach ($invoices as $inv) {
  if ($inv['payment_method'] === 'cash') {
    $summary['sum_czk_cash'] += $inv['rounded_total_czk'];
    $summary['sum_eur_cash'] += $inv['rounded_total_eur'];
  } else {
    $summary['sum_czk_card'] += $inv['rounded_total_czk'];
    $summary['sum_eur_card'] += $inv['rounded_total_eur'];
  }
  $summary['sum_tip_czk'] += $inv['tip_czk'];
  $summary['sum_tip_eur'] += $inv['tip_eur'];
}

echo json_encode([
  'invoices' => $invoices,
  'summary'  => $summary
]);
