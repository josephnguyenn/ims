<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}
include "../define.php";

// Ensure shipment ID is provided
if (!isset($_GET['shipment_id'])) {
    die("Shipment ID is required.");
}

$shipment_id = htmlspecialchars($_GET['shipment_id']);

// Fetch Shipment Data
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

// Fetch Shipment Details and Products
$shipment = fetchData(BASE_URL . '/api/shipments/$shipment_id');
$products = fetchData(BASE_URL . '/api/products'); // Full product list for adding products
$shipmentProducts = fetchData(BASE_URL . '/api/shipment-products/$shipment_id');
echo "<pre>";
print_r($shipmentProducts);
echo "</pre>";
exit();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipment Products</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <?php include "../includes/sidebar.php"; ?>

    <div class="main-content">
        <h1>Shipment #<?= htmlspecialchars($shipment['id']) ?> Details</h1>
        <p>Supplier: <?= htmlspecialchars($shipment['supplier']['name'] ?? 'Unknown') ?></p>
        <p>Storage Location: <?= htmlspecialchars($shipment['storage']['name'] ?? 'Unknown') ?></p>
        <p>Order Date: <?= htmlspecialchars($shipment['order_date']) ?></p>
        <p>Received Date: <?= htmlspecialchars($shipment['received_date'] ?? 'N/A') ?></p>
        <p>Expired Date: <?= htmlspecialchars($shipment['expired_date'] ?? 'N/A') ?></p>
        <p>Cost: $<?= htmlspecialchars($shipment['cost']) ?></p>

        <h2>Products in Shipment</h2>
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
            <tbody id="shipment-product-table">
                <?php if (!empty($shipmentProducts)) : ?>
                    <?php foreach ($shipmentProducts as $product) : ?>
                        <tr>
                            <td><?= htmlspecialchars($product['product']['name'] ?? 'Unknown') ?></td>
                            <td><?= htmlspecialchars($product['quantity']) ?></td>
                            <td>$<?= htmlspecialchars($product['price']) ?></td>
                            <td>$<?= htmlspecialchars($product['quantity'] * $product['price']) ?></td>
                            <td>
                                <button onclick="deleteShipmentProduct(<?= htmlspecialchars($product['id']) ?>, <?= $shipment_id ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr><td colspan="5">No products in this shipment.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>

        <!-- Add Product Form -->
        <h2>Add Product to Shipment</h2>
        <form id="add-shipment-product-form">
            <input type="hidden" id="shipment_id" value="<?= $shipment_id ?>">

            <label for="product_id">Select Product:</label>
            <select id="product_id" required>
                <option value="">Select Product</option>
                <?php foreach ($products as $product): ?>
                    <option value="<?= htmlspecialchars($product['id']) ?>">
                        <?= htmlspecialchars($product['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="quantity">Quantity:</label>
            <input type="number" id="quantity" required>

            <label for="price">Price:</label>
            <input type="number" id="price" required>

            <button type="button" onclick="addShipmentProduct()">Add Product</button>
        </form>
    </div>

    <script src="../js/shipment-products.js"></script>
</body>
</html>
