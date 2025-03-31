<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch Orders, Customers, and Delivery Suppliers
function fetchData($apiUrl) {
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $_SESSION['token']
    ]);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true);
}

$orders = fetchData("http://localhost/ims/public/api/orders");
$customers = fetchData("http://localhost/ims/public/api/customers");
$deliverySuppliers = fetchData("http://localhost/ims/public/api/delivery-suppliers");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include "../includes/header.php"; ?>
    <div class="main">
    <?php include "../includes/sidebar.php"; ?>

    <div class="main-content">  
        <div class="main-content-header">
            <h1>Orders Management</h1>
            <button class="add-button" onclick="openModal('addOrderModal')">Add Order</button>
        </div>

        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Customer</th>
                    <th>Delivery Supplier</th>
                    <th>Total Price</th>
                    <th>Paid Amount</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="order-table">
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']) ?></td>
                        <td><?= htmlspecialchars($order['customer']['name']) ?></td>
                        <td><?= htmlspecialchars($order['delivery_supplier']['name']) ?></td>
                        <td>$<?= htmlspecialchars($order['total_price']) ?></td>
                        <td>$<?= htmlspecialchars($order['paid_amount']) ?></td>
                        <td>
                            <button onclick="window.location.href='order-products.php?order_id=<?= $order['id'] ?>'">Manage Products</button>
                            <button onclick="openEditOrderForm(<?= $order['id'] ?>, <?= $order['delivery_supplier']['id'] ?>, <?= $order['paid_amount'] ?>)">Edit</button>
                            <button onclick="deleteOrder(<?= $order['id'] ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <!-- ✅ Add Order Modal -->
        <div id="addOrderModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('addOrderModal')">&times;</span>
                <h2>Add Order</h2>
                <form id="order-form">

                <div class="add-row">
                    <label for="customer_id">Customer:</label>
                    <select id="customer_id" required>
                        <option value="">Select Customer</option>
                        <?php foreach ($customers as $customer): ?>
                            <option value="<?= htmlspecialchars($customer['id']) ?>"><?= htmlspecialchars($customer['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="add-row">
                    <label for="delivery_supplier_id">Delivery Supplier:</label>
                    <select id="delivery_supplier_id" required>
                        <option value="">Select Delivery Supplier</option>
                        <?php foreach ($deliverySuppliers as $supplier): ?>
                            <option value="<?= htmlspecialchars($supplier['id']) ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                    <button type="submit">Save</button>
                </form>
            </div>
        </div>

        <!-- ✅ Edit Order Modal -->
        <div id="editOrderModal" class="modal">
            <div class="modal-content">
                <span class="close" onclick="closeModal('editOrderModal')">&times;</span>
                <h2>Edit Order</h2>
                <form id="edit-order-form">
                    <div class="add-row">
                        <input type="hidden" id="edit_order_id">
                        <label for="edit_delivery_supplier_id">Delivery Supplier:</label>
                        <select id="edit_delivery_supplier_id" required>
                            <option value="">Select Delivery Supplier</option>
                            <?php foreach ($deliverySuppliers as $supplier): ?>
                                <option value="<?= htmlspecialchars($supplier['id']) ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                     <div class="add-row">
                        <label for="edit_paid_amount">Paid Amount:</label>
                        <input type="number" id="edit_paid_amount" placeholder="Enter Paid Amount" required>
                     </div>               


                    <button type="submit">Update</button>
                </form>
            </div>
        </div>
    </div>
</div>
    <link rel="stylesheet" href="../css/add.css">
    <script src="../js/orders.js"></script>
</body>
</html>
