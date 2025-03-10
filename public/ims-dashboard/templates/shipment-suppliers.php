<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch Shipment Suppliers Data
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
    <title>Shipment Supplier Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
</head>
<body>
    <?php include "../includes/sidebar.php"; ?>

    <div class="main-content">
        <h1>Shipment Supplier Management</h1>
        <button onclick="document.getElementById('addShipmentSupplierForm').style.display='block'">Add Shipment Supplier</button>

        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="shipment-supplier-table">
                <tr><td colspan="3">Loading...</td></tr>
            </tbody>
        </table>

        <!-- ✅ Add Shipment Supplier Form (Hidden) -->
        <div id="addShipmentSupplierForm" style="display: none;">
            <h2>Add Shipment Supplier</h2>
            <form id="shipment-supplier-form">
                <input type="text" id="supplier_name" placeholder="Supplier Name" required>
                <button type="submit">Save</button>
                <button type="button" onclick="document.getElementById('addShipmentSupplierForm').style.display='none'">Cancel</button>
            </form>
        </div>

        <!-- ✅ Edit Shipment Supplier Form (Hidden) -->
        <div id="editShipmentSupplierForm" style="display: none;">
            <h2>Edit Shipment Supplier</h2>
            <form id="edit-shipment-supplier-form">
                <input type="hidden" id="edit_supplier_id">
                <input type="text" id="edit_supplier_name" required>
                <button type="button" onclick="updateShipmentSupplier()">Update</button>
                <button type="button" onclick="document.getElementById('editShipmentSupplierForm').style.display='none'">Cancel</button>
            </form>
        </div>
    </div>

    <script src="../js/shipment-suppliers.js"></script>
</body>
</html>
