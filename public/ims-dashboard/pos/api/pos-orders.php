<?php
// public/ims-dashboard/pos/api/pos-orders.php
header('Content-Type: application/json');
session_start();
require_once __DIR__ . '/../../define.php';

// parse JSON
$payload = json_decode(file_get_contents('php://input'), true);
if (!$payload) {
    http_response_code(400);
    echo json_encode(['error'=>'Invalid JSON']);
    exit;
}

// simple token check
if (!preg_match('/^Bearer\s+(.+)$/', $_SERVER['HTTP_AUTHORIZATION'] ?? '', $m)) {
    http_response_code(401);
    echo json_encode(['error'=>'Unauthorized']);
    exit;
}

// extract fields
$cashierId            = (int)$payload['cashier_id'];
$customerId           = isset($payload['customer_id'])          ? (int)$payload['customer_id']          : null;
$deliverySupplierId   = isset($payload['delivery_supplier_id']) ? (int)$payload['delivery_supplier_id'] : null;
$paid_amount          = (float)$payload['paid_amount'];
$subtotal_czk         = (float)$payload['subtotal_czk'];
$tip_czk              = (float)$payload['tip_czk'];
$grand_total_czk      = (float)$payload['grand_total_czk'];
$rounded_total_czk    = (float)$payload['rounded_total_czk'];
$payment_currency     = $mysqli->real_escape_string($payload['payment_currency']);
$amount_tendered_czk  = isset($payload['amount_tendered_czk']) ? (float)$payload['amount_tendered_czk'] : null;
$amount_tendered_eur  = isset($payload['amount_tendered_eur']) ? (float)$payload['amount_tendered_eur'] : null;
$change_due_czk       = isset($payload['change_due_czk'])      ? (float)$payload['change_due_czk']      : null;
$change_due_eur       = isset($payload['change_due_eur'])      ? (float)$payload['change_due_eur']      : null;
$payment_method       = $mysqli->real_escape_string($payload['payment_method']);

$mysqli->begin_transaction();
try {
    // 1) Insert order (now including paid_amount)
    $stmt = $mysqli->prepare("
        INSERT INTO orders
          (cashier_id, customer_id, delivery_supplier_id, paid_amount,
           subtotal_czk, tip_czk, grand_total_czk, rounded_total_czk,
           payment_currency, amount_tendered_czk, amount_tendered_eur,
           change_due_czk, change_due_eur, payment_method,
           created_at, updated_at)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,? ,NOW(),NOW())
    ");
    // 13 types: ii d   d  d  d  d  s  d  d  d  d  s
    $stmt->bind_param(
        'iiidddddsdddds',
        $cashierId,
        $customerId,
        $deliverySupplierId,
        $paid_amount,
        $subtotal_czk,
        $tip_czk,
        $grand_total_czk,
        $rounded_total_czk,
        $payment_currency,
        $amount_tendered_czk,
        $amount_tendered_eur,
        $change_due_czk,
        $change_due_eur,
        $payment_method
    );
    $stmt->execute();
    $orderId = $mysqli->insert_id;
    $stmt->close();

    // 2) Insert each line item
    $itemStmt = $mysqli->prepare("
        INSERT INTO order_products
          (order_id, product_id, quantity, price, created_at, updated_at)
        VALUES (?,?,?,? ,NOW(),NOW())
    ");
    foreach ($payload['items'] as $item) {
        $itemStmt->bind_param(
            'iiid',
            $orderId,
            $item['product_id'],
            $item['quantity'],
            $item['unit_price']
        );
        $itemStmt->execute();
    }
    $itemStmt->close();

    // 3) Decrement stock
    $decStmt = $mysqli->prepare("
        UPDATE products
           SET actual_quantity = actual_quantity - ?
         WHERE id = ?
    ");
    foreach ($payload['items'] as $item) {
        $decStmt->bind_param(
            'ii',
            $item['quantity'],
            $item['product_id']
        );
        $decStmt->execute();
    }
    $decStmt->close();

    // 4) Commit
    $mysqli->commit();
    echo json_encode(['id'=>$orderId]);
}
catch (Exception $e) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['error'=>$e->getMessage()]);
}
