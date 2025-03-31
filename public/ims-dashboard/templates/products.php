<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

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

$allProducts = fetchData("http://localhost/ims/public/api/products");
$shipments = fetchData("http://localhost/ims/public/api/shipments");
$storages = fetchData("http://localhost/ims/public/api/storages");
$shipmentSuppliers = fetchData("http://localhost/ims/public/api/shipment-suppliers");

$shipment_id = $_GET['shipment_id'] ?? null;
$selectedShipment = [];
$storageName = 'Unknown';
$supplierName = 'Unknown';

if ($shipment_id) {
    $products = array_filter($allProducts, function ($product) use ($shipment_id) {
        return $product['shipment_id'] == $shipment_id;
    });

    foreach ($shipments as $shipment) {
        if ($shipment['id'] == $shipment_id) {
            $selectedShipment = $shipment;
            // Find storage name
            foreach ($storages as $storage) {
                if ($storage['id'] == $shipment['storage_id']) {
                    $storageName = $storage['name'];
                    break;
                }
            }
            // Find supplier name
            foreach ($shipmentSuppliers as $supplier) {
                if ($supplier['id'] == $shipment['shipment_supplier_id']) {
                    $supplierName = $supplier['name'];
                    break;
                }
            }
            break;
        }
    }
} else {
    $products = $allProducts;
}

$product_code_filter = $_GET['product_code_filter'] ?? null;

if ($shipment_id) {
    $products = array_filter($allProducts, function ($product) use ($shipment_id) {
        return $product['shipment_id'] == $shipment_id;
    });
} else {
    $products = $allProducts;
}

if (!empty($product_code_filter)) {
    $products = array_filter($products, function ($product) use ($product_code_filter) {
        return strpos($product['code'], $product_code_filter) !== false;
    });
}

$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/shipment-product.css">

    <meta name="csrf-token" content="<?= $csrfToken ?>">

</head>
<body>
    <?php include "../includes/header.php"; ?>

    <div class="main">
        <?php include "../includes/sidebar.php"; ?>

        <div class="main-content">
            <div class="main-content-header">
                <h1>Product Management</h1>
            <button class="add-button" onclick="openModal('addProductForm')">Add Product</button>
        </div>
        <form method="get" style="margin-bottom: 20px; width: 30%;">
            <!-- Hidden field to keep shipment_id in the URL -->
            <?php if (!empty($shipment_id)): ?>
                <input type="hidden" name="shipment_id" value="<?= htmlspecialchars($shipment_id) ?>">
            <?php endif; ?>

            <!-- Input field for product code filter -->
            <input type="text" name="product_code_filter" placeholder="Filter by Product Code" value="<?= htmlspecialchars($_GET['product_code_filter'] ?? '') ?>">
            <button type="submit">Filter</button>
            <a href="?<?= !empty($shipment_id) ? "shipment_id=$shipment_id" : "" ?>" class="reset-button">Reset Filters</a>
        </form>
            

        <div class="shipment-meta">
            <?php if ($shipment_id && !empty($selectedShipment)): ?>
                <h3>Products in Shipment <?= htmlspecialchars($shipment_id) ?></h3>
                <p>Storage: <?= htmlspecialchars($storageName) ?></p>
                <p>Supplier: <?= htmlspecialchars($supplierName) ?></p>
                <p>Received Date: <?= htmlspecialchars($selectedShipment['received_date']) ?></p>
                <p>Expired Date: <?= htmlspecialchars($selectedShipment['expired_date']) ?></p>
                <p>Total Cost: $<?= htmlspecialchars($selectedShipment['cost']) ?></p>
            <?php endif; ?>
        </div>




        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Code</th>
                    <th>Original Qty</th>
                    <th>Actual Qty</th>
                    <th>Price</th>
                    <th>Cost</th>
                    <th>Total Cost</th>
                    <th>Shipment</th>
                    <th>Expired Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="product-table">
                <?php if (!empty($products)): ?>
                    <?php foreach ($products as $product): ?>
                        <tr>
                            <td><?= htmlspecialchars($product['id']) ?></td>
                            <td><?= htmlspecialchars($product['name']) ?></td>
                            <td><?= htmlspecialchars($product['code']) ?></td>
                            <td><?= htmlspecialchars($product['original_quantity']) ?></td>
                            <td><?= htmlspecialchars($product['actual_quantity']) ?></td>
                            <td>$<?= htmlspecialchars($product['price']) ?></td>
                            <td>$<?= htmlspecialchars($product['cost']) ?></td>
                            <td>$<?= htmlspecialchars($product['total_cost']) ?></td>
                            <td>Shipment <?= htmlspecialchars($product['shipment_id']) ?></td>
                            <td><?= htmlspecialchars($product['expired_date'] ?? 'N/A') ?></td>
                            <td>
                            <button onclick="openEditModal(<?= $product['id'] ?>)">Edit</button>
                            <button onclick="deleteProduct(<?= $product['id'] ?>)">Delete</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="11">No products in this shipment.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
        
    <div id="addProductForm" class="modal" style="display: none;">
        <div class="modal-content">
            <h2>Add Product</h2>
                <form id="product-form" class="form-container">
                    <div class="add-row">
                        <label for="product_name">Product Name:</label>
                        <input type="text" id="product_name" placeholder="Enter product name" required>
                    </div>

                    <div class="add-row">
                        <label for="product_code">Product Code:</label>
                        <input type="text" id="product_code" placeholder="Enter product code" required>
                    </div>

                    <div class="add-row">
                        <label for="original_quantity">Original Quantity:</label>
                        <input type="number" id="original_quantity" placeholder="Enter original quantity" required>
                    </div>

                    <div class="add-row">
                        <label for="price">Price:</label>
                        <input type="number" id="price" placeholder="Enter price" required>
                    </div>

                    <div class="add-row">
                        <label for="cost">Cost:</label>
                        <input type="number" id="cost" placeholder="Enter cost" required>
                    </div>

                    <div class="add-row">
                            <label for="tax">Tax (%):</label>
                            <input type="number" id="tax" placeholder="Enter tax" step="0.01" value="0" required>
                    </div>


                    <div class="add-row">
                        <label for="category">Category:</label>
                        <input type="text" id="category" placeholder="Enter category" required>
                    </div>

                    <div class="add-row">
                        <label for="shipment_id">Shipment:</label>
                        <select id="shipment_id" required>
                            <option value="">Select Shipment</option>
                            <?php foreach ($shipments as $shipment): ?>
                                <option value="<?= htmlspecialchars($shipment['id']) ?>">Shipment #<?= htmlspecialchars($shipment['id']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="button-save">Save</button>
                        <button type="button" onclick="closeModal('addProductForm')" class="button-cancel">Cancel</button>
                    </div>
                </form>
        </div>
    </div>
        <div id="editProductForm" class="modal" style="display: none;">
    <div class="modal-content">
        <h2>Edit Product</h2>
        <form id="edit-product-form" class="form-container">
        <input type="hidden" id="edit_product_id">

        <div class="add-row">
            <label for="edit_product_name">Name:</label>
            <input type="text" id="edit_product_name" required>
        </div>

        <div class="add-row">
            <label for="edit_product_code">Code:</label>
            <input type="text" id="edit_product_code" required>
        </div>

        <div class="add-row">
            <label for="edit_original_quantity">Original Qty:</label>
            <input type="number" id="edit_original_quantity" required>
        </div>

        <div class="add-row">
            <label for="edit_price">Price:</label>
            <input type="number" id="edit_price" required>
        </div>

        <div class="add-row">
            <label for="edit_cost">Cost:</label>
            <input type="number" id="edit_cost" required>
        </div>

        <div class="add-row">
            <label for="edit_tax">Tax (%):</label>
            <input type="number" id="edit_tax" step="0.01">
        </div>

        <div class="add-row">
            <label for="edit_category">Category:</label>
            <input type="text" id="edit_category" required>
        </div>

        <div class="add-row">
            <label for="edit_shipment_id">Shipment:</label>
            <select id="edit_shipment_id" required>
            <option value="">Select Shipment</option>
            <?php foreach ($shipments as $shipment): ?>
                <option value="<?= $shipment['id'] ?>">Shipment #<?= $shipment['id'] ?></option>
            <?php endforeach; ?>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="button-save">Update</button>
            <button type="button" onclick="closeModal('editProductForm')" class="button-cancel">Cancel</button>
        </div>
        </form>
    </div>
    </div>

    </div>
</div>
    <script src="../js/products.js"></script>
    <link rel="stylesheet" href="../css/add.css">
</body>
</html>
