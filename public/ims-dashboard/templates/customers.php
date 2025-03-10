<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

// Fetch Customers Data
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

$customers = fetchData("http://localhost/ims/public/api/customers");

// Generate CSRF token
$csrfToken = bin2hex(random_bytes(32));
$_SESSION['csrf_token'] = $csrfToken;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Management</title>
    <link rel="stylesheet" href="../css/style.css">
    <meta name="csrf-token" content="<?= $csrfToken ?>">
</head>
<body>
    <?php include "../includes/sidebar.php"; ?>

    <div class="main-content">
        <h1>Customer Management</h1>
        <button onclick="document.getElementById('addCustomerForm').style.display='block'">Add Customer</button>

        <table border="1">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Address</th>
                    <th>VAT Code</th>
                    <th>Total Orders</th>
                    <th>Total Debt</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody id="customer-table">
                <tr><td colspan="9">Loading...</td></tr>
            </tbody>
        </table>

        <!-- ✅ Add Customer Form (Hidden) -->
        <div id="addCustomerForm" style="display: none;">
            <h2>Add Customer</h2>
            <form id="customer-form">
                <input type="text" id="customer_name" placeholder="Customer Name" required>
                <input type="email" id="customer_email" placeholder="Email" required>
                <input type="text" id="customer_phone" placeholder="Phone" required>
                <input type="text" id="customer_address" placeholder="Address" required>
                <input type="text" id="customer_vat_code" placeholder="VAT Code">
                <button type="submit">Save</button>
                <button type="button" onclick="document.getElementById('addCustomerForm').style.display='none'">Cancel</button>
            </form>
        </div>

        <!-- ✅ Edit Customer Form (Hidden) -->
        <div id="editCustomerForm" style="display: none;">
            <h2>Edit Customer</h2>
            <form id="edit-customer-form">
                <input type="hidden" id="edit_customer_id">
                <input type="text" id="edit_customer_name" required>
                <input type="email" id="edit_customer_email" required>
                <input type="text" id="edit_customer_phone" required>
                <input type="text" id="edit_customer_address" required>
                <input type="text" id="edit_customer_vat_code">
                <button type="button" onclick="updateCustomer()">Update</button>
                <button type="button" onclick="document.getElementById('editCustomerForm').style.display='none'">Cancel</button>
            </form>
        </div>

    </div>

    <script src="../js/customers.js"></script>
</body>
</html>
