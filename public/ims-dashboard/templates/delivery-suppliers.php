<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch Delivery Suppliers Data
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

$deliverySuppliers = fetchData("http://localhost/ims/public/api/delivery-suppliers");

// Generate CSRF token
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Supplier Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
</head>
<body>
    <?php include "../includes/sidebar.php"; ?>

    <div class="main-content">
        <h1>Delivery Supplier Management</h1>
        <button onclick="document.getElementById('addDeliverySupplierForm').style.display='block'">Add Supplier</button>

        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="delivery-supplier-table">
                <tr><td colspan="3">Loading...</td></tr>
            </tbody>
        </table>

        <!-- ✅ Add Delivery Supplier Form (Hidden) -->
        <div id="addDeliverySupplierForm" style="display: none;">
            <h2>Add Delivery Supplier</h2>
            <form id="delivery-supplier-form">
                <input type="text" id="delivery_supplier_name" placeholder="Supplier Name" required>
                <button type="submit">Save</button>
                <button type="button" onclick="document.getElementById('addDeliverySupplierForm').style.display='none'">Cancel</button>
            </form>
        </div>

        <!-- ✅ Edit Delivery Supplier Form (Hidden) -->
        <div id="editDeliverySupplierForm" style="display: none;">
            <h2>Edit Delivery Supplier</h2>
            <form id="edit-delivery-supplier-form">
                <input type="hidden" id="edit_delivery_supplier_id">
                <input type="text" id="edit_delivery_supplier_name" required>
                <button type="button" onclick="updateDeliverySupplier()">Update</button>
                <button type="button" onclick="document.getElementById('editDeliverySupplierForm').style.display='none'">Cancel</button>
            </form>
        </div>
    </div>

    <script src="../js/delivery-suppliers.js"></script>
</body>
</html>
