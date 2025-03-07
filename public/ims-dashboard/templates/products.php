<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch Shipments Data
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

$shipments = fetchData("http://localhost/ims/public/api/shipments");

// Generate CSRF token
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
    <meta name="csrf-token" content="<?= $csrfToken ?>">
</head>
<body>
    <?php include "../includes/sidebar.php"; ?>

    <div class="main-content">
        <h1>Product Management</h1>
        <button onclick="document.getElementById('addProductForm').style.display='block'">Add Product</button>

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
                <tr><td colspan="10">Loading...</td></tr>
            </tbody>
        </table>

        <!-- ✅ Add Product Form (Hidden) -->
        <div id="addProductForm" style="display: none;">
            <h2>Add Product</h2>
            <form id="product-form">
                <input type="text" id="product_name" placeholder="Product Name" required>
                <input type="text" id="product_code" placeholder="Product Code" required>
                <input type="number" id="original_quantity" placeholder="Original Quantity" required>
                <input type="number" id="price" placeholder="Price" required>
                <input type="number" id="cost" placeholder="Cost" required>
                <input type="text" id="category" placeholder="Category" required>

                <!-- Shipment Dropdown -->
                <label for="shipment_id">Shipment:</label>
                <select id="shipment_id" required>
                    <option value="">Select Shipment</option>
                    <?php foreach ($shipments as $shipment): ?>
                        <option value="<?= htmlspecialchars($shipment['id']) ?>">Shipment #<?= htmlspecialchars($shipment['id']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="submit">Save</button>
                <button type="button" onclick="document.getElementById('addProductForm').style.display='none'">Cancel</button>
            </form>
        </div>

        <!-- ✅ Edit Product Form (Hidden) -->
        <div id="editProductForm" style="display: none;">
            <h2>Edit Product</h2>
            <form id="edit-product-form">
                <input type="hidden" id="edit_product_id">
                
                <input type="text" id="edit_product_name" required>
                <input type="text" id="edit_product_code" required>
                <input type="number" id="edit_original_quantity" required>
                <input type="number" id="edit_price" required>
                <input type="number" id="edit_cost" required>
                <input type="text" id="edit_category" required>

                <label for="edit_shipment_id">Shipment:</label>
                <select id="edit_shipment_id" required>
                    <option value="">Select Shipment</option>
                    <?php foreach ($shipments as $shipment): ?>
                        <option value="<?= htmlspecialchars($shipment['id']) ?>">Shipment #<?= htmlspecialchars($shipment['id']) ?></option>
                    <?php endforeach; ?>
                </select>

                <button type="button" onclick="updateProduct()">Update</button>
                <button type="button" onclick="document.getElementById('editProductForm').style.display='none'">Cancel</button>
            </form>
        </div>
    </div>

    <script src="../js/products.js"></script>
</body>
</html>
