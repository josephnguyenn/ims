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
    <?php include "../includes/header.php"; ?>
    <div class="main">
    <?php include "../includes/sidebar.php"; ?>
    <div class="main-content">
        <div class="main-content-header">
            <h1>Customer Management</h1>
            <button class="add-button" onclick="document.getElementById('addCustomerForm').style.display='flex'">Add Customer</button>
        </div>

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
        <!-- Add Customer Modal -->
        <!-- Add Customer Modal -->
        <div id="addCustomerForm" class="modal" style="display: none;">
        <span class="close" onclick="closeModal('addCustomerForm')">&times;</span>
            <div class="modal-content">
                <h2>Add Customer</h2>
                <form id="customer-form" onsubmit="return addCustomer();">
                    <div class="add-row">
                        <label for="customer_name">Name:</label>
                        <input type="text" id="customer_name" placeholder="Customer Name" required>
                    </div>
                    <div class="add-row">
                        <label for="customer_email">Email:</label>
                        <input type="email" id="customer_email" placeholder="Email" required>
                    </div>
                    <div class="add-row">
                        <label for="customer_phone">Phone:</label>
                        <input type="text" id="customer_phone" placeholder="Phone" required>
                    </div>
                    <div class="add-row">
                        <label for="customer_address">Address:</label>
                        <input type="text" id="customer_address" placeholder="Address" required>
                    </div>
                    <div class="add-row">
                        <label for="customer_vat_code">VAT Code:</label>
                        <input type="text" id="customer_vat_code" placeholder="VAT Code">
                    </div>
                    <div class="form-buttons">
                        <button type="submit">Save</button>
                        <button type="button" onclick="closeModal('addCustomerForm')">Cancel</button>
                    </div>
                </form>
            </div>
        </div>



        <!-- ✅ Edit Customer Form (Hidden) -->
        <div id="editCustomerForm" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal('editCustomerForm')">&times;</span>
            <h2>Edit Customer</h2>
            <form id="edit-customer-form">
                <input type="hidden" id="edit_customer_id">
                <label for="edit_customer_name">Name:</label>
                <input type="text" id="edit_customer_name" required>
                <label for="edit_customer_email">Email:</label>
                <input type="email" id="edit_customer_email" required>
                <label for="edit_customer_phone">Phone:</label>
                <input type="text" id="edit_customer_phone" required>
                <label for="edit_customer_address">Address:</label>
                <input type="text" id="edit_customer_address" required>
                <label for="edit_customer_vat_code">VAT Code:</label>
                <input type="text" id="edit_customer_vat_code">
                <div class="form-buttons">
                    <button type="button" onclick="updateCustomer()">Update</button>
                    <button type="button" onclick="closeModal('editCustomerForm')">Cancel</button>
                </div>
            </form>
        </div>
    </div>


    </div>
</div>
    <link rel="stylesheet" href="../css/add.css">
    <script src="../js/customers.js"></script>
</body>
</html>
