<?php
// public/ims-dashboard/pos/api/pos-reports.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../define.php';  // kết nối $mysqli

// 1) Đọc tham số
$from  = $_GET['from']   ?? date('Y-m-d', strtotime('-60 days'));
$to    = $_GET['to']     ?? date('Y-m-d');
$shift = isset($_GET['shift_id']) ? (int)$_GET['shift_id'] : null;

// 2) Lấy tỷ giá CZK→EUR từ settings
$rate = 1.0;
if ($r = $mysqli->query("SELECT value FROM settings WHERE name='exchange_rate' LIMIT 1")) {
    $row  = $r->fetch_assoc();
    $rate = (float)$row['value'] ?: 1.0;
    $r->free();
}

// 3) Build và chạy query
// 3) Build và chạy query
$sql = "
  SELECT
    o.id,
    o.created_at,
    s.name AS shift_name,
    u.name AS cashier_name,
    o.cashier_id,
    o.payment_method,
    o.payment_currency,
    o.rounded_total_czk,
    o.tip_czk,
    o.tip_eur,
    o.amount_tendered_czk,
    o.amount_tendered_eur
  FROM orders o
  LEFT JOIN shifts s ON o.shift_id = s.id
  LEFT JOIN users u ON o.cashier_id = u.id
  WHERE o.created_at BETWEEN ? AND ?
";

// 🛠️ Apply shift filter BEFORE ORDER BY
$params = ["$from 00:00:00", "$to 23:59:59"];
$types  = "ss";

if ($shift) {
  $sql      .= " AND o.shift_id = ?";
  $types    .= "i";
  $params[]  = $shift;
}

$sql .= " ORDER BY o.created_at DESC"; // ✅ Move this to the end

// Run query
$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

// 4) Fetch và tính EUR
$invoices = [];
while ($inv = $res->fetch_assoc()) {
  $inv['rounded_total_eur'] = round($inv['rounded_total_czk'] / $rate, 2);
  $inv['tip_czk']           = (float)$inv['tip_czk'];
  $inv['tip_eur']           = (float)$inv['tip_eur'];
  $inv['amount_tendered_czk'] = (float)($inv['amount_tendered_czk'] ?? 0);
  $inv['amount_tendered_eur'] = (float)($inv['amount_tendered_eur'] ?? 0);
  $invoices[] = $inv;
}
$stmt->close();

// 5) Tổng kết summary
$summary = [
  'sum_czk_cash' => 0,
  'sum_czk_card' => 0,
  'sum_czk_transfer' => 0,
  'sum_eur_cash' => 0,
  'sum_eur_card' => 0,
  'sum_eur_transfer' => 0,
  'sum_tip_czk'  => 0,
  'sum_tip_eur'  => 0,
];

foreach ($invoices as $inv) {
  if ($inv['payment_method'] === 'cash') {
    $summary['sum_czk_cash'] += $inv['amount_tendered_czk'];
    $summary['sum_eur_cash'] += $inv['amount_tendered_eur'];
  } elseif ($inv['payment_method'] === 'card') {
    $summary['sum_czk_card'] += $inv['amount_tendered_czk'];
    $summary['sum_eur_card'] += $inv['amount_tendered_eur'];
  } elseif ($inv['payment_method'] === 'transfer') {
    $summary['sum_czk_transfer'] += $inv['amount_tendered_czk'];
    $summary['sum_eur_transfer'] += $inv['amount_tendered_eur'];
  }

  $summary['sum_tip_czk'] += $inv['tip_czk'];
  $summary['sum_tip_eur'] += $inv['tip_eur'];
}

// 6) Trả về JSON
echo json_encode([
  'invoices' => $invoices,
  'summary'  => $summary
]);
