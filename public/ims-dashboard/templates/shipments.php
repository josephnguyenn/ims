<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch Storage & Shipment Suppliers Data
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

$storages = fetchData("http://localhost/ims/public/api/storages");
$shipmentSuppliers = fetchData("http://localhost/ims/public/api/shipment-suppliers");

// Generate CSRF token
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shipment Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
</head>
<body>
    <?php include "../includes/sidebar.php"; ?>

    <div class="main-content">
        <h1>Shipment Management</h1>
        <button onclick="document.getElementById('addShipmentForm').style.display='block'">Add Shipment</button>

        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Supplier</th>
                    <th>Storage</th>
                    <th>Order Date</th>
                    <th>Received Date</th>
                    <th>Expired Date</th>
                    <th>Cost</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="shipment-table">
                <tr><td colspan="8">Loading...</td></tr>
            </tbody>
        </table>

        <!-- Add Shipment Form (Hidden) -->
        <div id="addShipmentForm" style="display: none;">
            <h2>Add Shipment</h2>
            <form id="shipment-form">
                <!-- Shipment Supplier Dropdown -->
                <label for="shipment_supplier_id">Shipment Supplier:</label>
                <select id="shipment_supplier_id" required>
                    <option value="">Select Supplier</option>
                    <?php foreach ($shipmentSuppliers as $supplier): ?>
                        <option value="<?= htmlspecialchars($supplier['id']) ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <!-- Storage Dropdown -->
                <label for="storage_id">Storage Location:</label>
                <select id="storage_id" required>
                    <option value="">Select Storage</option>
                    <?php foreach ($storages as $storage): ?>
                        <option value="<?= htmlspecialchars($storage['id']) ?>"><?= htmlspecialchars($storage['name']) ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="date" id="order_date" required>
                <input type="date" id="received_date">
                <input type="date" id="expired_date">
                <button type="submit">Save</button>
                <button type="button" onclick="document.getElementById('addShipmentForm').style.display='none'">Cancel</button>
            </form>
        </div>
        <!-- Edit Shipment Form (Hidden) -->
        <div id="editShipmentForm" style="display: none;">
                <h2>Edit Shipment</h2>
                <form id="edit-shipment-form">
                    <input type="hidden" id="edit_shipment_id">
                    
                    <label for="edit_shipment_supplier_id">Shipment Supplier:</label>
                    <select id="edit_shipment_supplier_id" required>
                        <option value="">Select Supplier</option>
                        <?php foreach ($shipmentSuppliers as $supplier): ?>
                            <option value="<?= htmlspecialchars($supplier['id']) ?>"><?= htmlspecialchars($supplier['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <label for="edit_storage_id">Storage Location:</label>
                    <select id="edit_storage_id" required>
                        <option value="">Select Storage</option>
                        <?php foreach ($storages as $storage): ?>
                            <option value="<?= htmlspecialchars($storage['id']) ?>"><?= htmlspecialchars($storage['name']) ?></option>
                        <?php endforeach; ?>
                    </select>

                    <input type="date" id="edit_order_date" required>
                    <input type="date" id="edit_received_date">
                    <input type="date" id="edit_expired_date">
                    <button type="button" onclick="updateShipment()">Update</button>
                    <button type="button" onclick="document.getElementById('editShipmentForm').style.display='none'">Cancel</button>
                </form>
            </div>
    </div>

    <script src="../js/shipments.js"></script>
</body>
</html>
