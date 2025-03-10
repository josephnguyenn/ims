<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

// ✅ Get Order ID from URL
if (!isset($_GET['order_id'])) {
    die("Order ID is required.");
}
$order_id = $_GET['order_id'];

// ✅ Fetch API Data
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

$order = fetchData("http://localhost/ims/public/api/orders/$order_id");
$products = fetchData("http://localhost/ims/public/api/products");

// ✅ Ensure Order Exists
if (!isset($order['id'])) {
    die("Order not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Order Products</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include "../includes/sidebar.php"; ?>

    <div class="main-content">
        <h1>Manage Products for Order #<?= htmlspecialchars($order['id']) ?></h1>
        <h3>Customer: <?= htmlspecialchars($order['customer']['name'] ?? 'Unknown') ?></h3>
        <h3>Total Price: $<?= htmlspecialchars($order['total_price'] ?? '0.00') ?></h3>

        <button id="openAddProductForm">Add Product</button>

        <table border="1">
            <thead>
                <tr>
                    <th>Product Name</th>
                    <th>Quantity</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="order-product-table">
                <!-- Products will be loaded here by JavaScript -->
            </tbody>
        </table>

        <!-- ✅ Add Product Form -->
        <div id="addProductForm" style="display: none;">
            <h2>Add Product to Order</h2>
            <form id="order-product-form">
                <input type="hidden" id="order_id" value="<?= htmlspecialchars($order['id']) ?>">
                
                <label for="product_id">Select Product:</label>
                <select id="product_id" required>
                    <option value="">Select Product</option>
                    <?php foreach ($products as $product): ?>
                        <option value="<?= htmlspecialchars($product['id']) ?>"><?= htmlspecialchars($product['name']) ?> (Stock: <?= htmlspecialchars($product['actual_quantity']) ?>)</option>
                    <?php endforeach; ?>
                </select>

                <input type="number" id="quantity" placeholder="Quantity" required>
                <button type="submit">Add</button>
                <button type="button" onclick="document.getElementById('addProductForm').style.display='none'">Cancel</button>
            </form>
        </div>

        <!-- ✅ Edit Product Form -->
        <div id="editProductForm" style="display: none;">
            <h2>Edit Product in Order</h2>
            <form id="edit-order-product-form">
                <input type="hidden" id="edit_order_product_id">
                <input type="hidden" id="edit_order_id" value="<?= htmlspecialchars($order['id']) ?>">

                <label for="edit_quantity">Quantity:</label>
                <input type="number" id="edit_quantity" placeholder="Quantity" required>

                <button type="submit">Update</button>
                <button type="button" onclick="document.getElementById('editProductForm').style.display='none'">Cancel</button>
            </form>
        </div>
    </div>

    <script src="../js/order-products.js"></script>
</body>
</html>