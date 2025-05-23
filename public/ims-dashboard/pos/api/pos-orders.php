<?php
// api/pos-orders.php
header('Content-Type: application/json');
session_start();

// 1) Authentication
if (!isset($_SESSION['token'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

include '../define.php';  // defines $mysqli, sets charset, etc.

// 2) Parse JSON payload
$payload = json_decode(file_get_contents('php://input'), true);
if (!$payload) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid JSON']);
    exit;
}

$mysqli->begin_transaction();
try {
    // 3) (Optional) Create or select customer
    $customerId = null;
    if (!empty($payload['customer'])) {
        $c = $payload['customer'];
        if (!empty($c['id'])) {
            $customerId = (int)$c['id'];
        } elseif (!empty($c['name'])) {
            $stmt = $mysqli->prepare("
                INSERT INTO customers
                  (name, phone, created_at, updated_at)
                VALUES (?, ?, NOW(), NOW())
            ");
            $stmt->bind_param('ss', $c['name'], $c['phone']);
            $stmt->execute();
            $customerId = $mysqli->insert_id;
            $stmt->close();
        }
    }

    // 4) Insert order master
    $pay = $payload['payment'];
    $stmt = $mysqli->prepare("
        INSERT INTO orders
          (cashier_id, customer_id,
           subtotal_czk, tip_czk, grand_total_czk, rounded_total_czk,
           payment_currency, amount_tendered_czk, amount_tendered_eur,
           change_due_czk, change_due_eur, payment_method,
           created_at, updated_at)
        VALUES
          (?,?,?,?,?,?,?,?,?,?,?,?, NOW(), NOW())
    ");
    $stmt->bind_param(
        'iiddiddidds',
        $_SESSION['user_id'],
        $customerId,
        $payload['subtotal_czk'],
        $payload['tip_czk'],
        $payload['grand_total_czk'],
        $payload['rounded_total_czk'],
        $pay['currency'],
        $payload['amount_tendered_czk'],
        $payload['amount_tendered_eur'],
        $payload['change_due_czk'],
        $payload['change_due_eur'],
        $pay['method']
    );
    $stmt->execute();
    $orderId = $mysqli->insert_id;
    $stmt->close();

    // 5) Insert each line item
    $stmt = $mysqli->prepare("
        INSERT INTO order_products
          (order_id, product_id, quantity, price, created_at, updated_at)
        VALUES
          (?,?,?,? , NOW(), NOW())
    ");
    foreach ($payload['items'] as $item) {
        $stmt->bind_param(
            'iiid',
            $orderId,
            $item['product_id'],
            $item['quantity'],
            $item['unit_price']
        );
        $stmt->execute();
    }
    $stmt->close();

    // 6) Commit transaction & respond
    $mysqli->commit();
    echo json_encode(['id' => $orderId]);
}
catch (Exception $e) {
    $mysqli->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
