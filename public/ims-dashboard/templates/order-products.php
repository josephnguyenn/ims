<?php
session_start();
if (!isset($_SESSION['token'])) {
    header("Location: ../login.php");
    exit();
}

if (!isset($_GET['order_id'])) {
    die("Order ID is required.");
}
$order_id = $_GET['order_id'];

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
$shipments = fetchData("http://localhost/ims/public/api/shipments");
$orderProducts = fetchData("http://localhost/ims/public/api/order-products/$order_id");

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
    <style>
        .autocomplete-suggestions {
            border: 1px solid #ccc;
            max-height: 150px;
            overflow-y: auto;
            background: #fff;
            position: absolute;
            z-index: 999;
        }
        .autocomplete-suggestion {
            padding: 8px;
            cursor: pointer;
        }
        .autocomplete-suggestion:hover {
            background-color: #eee;
        }
    </style>
</head>
<body>
<?php include "../includes/header.php"; ?>
<div class="main">
<?php include "../includes/sidebar.php"; ?>

<div class="main-content">
    <div class="main-content-header">
        <h1>Manage Products for Order #<?= htmlspecialchars($order['id']) ?></h1>
        <button class="add-button" id="openAddProductForm">Add Product</button>
    </div>
    <h3>Customer: <?= htmlspecialchars($order['customer']['name'] ?? 'Unknown') ?></h3>
    <h3>Total Price: $<?= htmlspecialchars($order['total_price'] ?? '0.00') ?></h3>
    <button onclick="window.open('generate-invoice.php?order_id=<?= $order['id'] ?>', '_blank')">🧾 Generate Invoice</button>
    <div id="addProductForm" style="display: none; position: relative;">
        <h2>Add Product to Order</h2>
        <form id="order-product-form" class="form-container">
    <input type="hidden" id="order_id" value="<?= htmlspecialchars($order['id']) ?>">
    <div class="form-group">
        <label for="shipment_id">Choose Shipment</label>
        <select id="shipment_id" required>
            <option value="">Select Shipment</option>
            <?php foreach ($shipments as $shipment): ?>
                <option value="<?= $shipment['id'] ?>">Shipment #<?= $shipment['id'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div class="form-group">
        <label for="product_search">Search Product (by code)</label>
        <input type="text" id="product_search" placeholder="Enter product code...">
        <div id="product_suggestions" class="autocomplete-suggestions"></div>
    </div>

    <div class="form-group">
        <label for="product_id">Select Product</label>
        <select id="product_id" required disabled>
            <option value="">Select Shipment First</option>
        </select>
    </div>

    <div class="form-group-inline">
        <div class="form-field">
            <label for="product_code">Product Code</label>
            <input type="text" id="product_code" readonly>
        </div>
        <div class="form-field">
            <label for="product_name">Product Name</label>
            <input type="text" id="product_name" readonly>
        </div>
    </div>

    <div class="form-group-inline">
        <div class="form-field">
            <label for="stock">Stock</label>
            <input type="text" id="stock" readonly>
        </div>
        <div class="form-field">
            <label for="price">Price</label>
            <input type="text" id="price" readonly>
        </div>
    </div>

    <div class="form-group">
        <label for="quantity">Quantity</label>
        <input type="number" id="quantity" placeholder="Enter quantity" required>
        <p>Total: <span id="calculated_total">$0.00</span></p>
    </div>

    <div class="form-actions">
        <button type="submit" class="btn btn-primary">Add Product</button>
        <button type="button" class="btn btn-secondary" onclick="document.getElementById('addProductForm').style.display='none'">Cancel</button>
    </div>
</form>

<style>
    .form-container {
        max-width: 600px;
        margin: auto;
        padding: 20px;
    }
    .form-group {
        margin-bottom: 15px;
    }
    .form-group-inline {
        display: flex;
        gap: 15px;
        margin-bottom: 15px;
    }
    .form-field {
        flex: 1;
    }
    .form-group label,
    .form-field label {
        font-weight: bold;
        display: block;
        margin-bottom: 5px;
    }
    input[type="text"],
    input[type="number"],
    select {
        width: 100%;
        padding: 8px;
        border-radius: 4px;
        border: 1px solid #ccc;
    }
    .form-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    .btn {
        padding: 8px 16px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
    }
    .btn-primary {
        background-color: #1a4ba8;
        color: white;
    }
    .btn-secondary {
        background-color: #aaa;
        color: white;
    }
</style>
    </div>

    <table border="1">
        <thead>
            <tr>
                <th>Product Name</th>
                <th>Quantity</th>
                <th>Price</th>
                <th>Total</th>
                <th>Shipment</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($orderProducts)): ?>
                <?php foreach ($orderProducts as $item): ?>
                <?php
                $product = is_array($item['product']) ? $item['product'] : null;
                ?>
                <tr>
                    <td><?= $product ? htmlspecialchars($product['name']) : 'N/A' ?></td>
                    <td><?= htmlspecialchars($item['quantity']) ?></td>
                    <td>$<?= $product ? htmlspecialchars($product['price']) : '0.00' ?></td>
                    <td>$<?= $product ? number_format($product['price'] * $item['quantity'], 2) : '0.00' ?></td>
                    <td><?= $product ? 'Shipment #' . htmlspecialchars($product['shipment_id']) : 'N/A' ?></td>
                    <td>
                        <button onclick="openEditOrderProductForm(<?= $item['id'] ?>, <?= $item['quantity'] ?>)">Edit</button>
                        <button onclick="deleteOrderProduct(<?= $item['id'] ?>, <?= $order['id'] ?>)">Delete</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="6">No products in this order.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

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
</div>
<script src="../js/order-products.js"></script>
<script>
    let allProducts = [];

    document.addEventListener("DOMContentLoaded", function () {
        fetch("http://localhost/ims/public/api/products", {
            headers: { "Authorization": "Bearer " + sessionStorage.getItem("token") }
        })
        .then(res => res.json())
        .then(data => {
            allProducts = data;
        });

        const productSearch = document.getElementById("product_search");
        const suggestionsBox = document.getElementById("product_suggestions");
        const quantityInput = document.getElementById("quantity");
        const priceInput = document.getElementById("price");
        const totalDisplay = document.getElementById("calculated_total");
        const stockInput = document.getElementById("stock");

        productSearch.addEventListener("input", function () {
            const value = this.value.toLowerCase();
            suggestionsBox.innerHTML = "";

            if (!value) return;

            const filtered = allProducts.filter(p => p.code.toLowerCase().includes(value));

            filtered.forEach(p => {
                const div = document.createElement("div");
                div.classList.add("autocomplete-suggestion");
                div.textContent = `${p.code} - ${p.name} | Stock: ${p.actual_quantity} | Shipment: ${p.shipment_id} | Price: $${p.price}`;
                div.addEventListener("click", () => {
                    document.getElementById("product_id").innerHTML = `<option value="${p.id}" selected>${p.name} (${p.code})</option>`;
                    document.getElementById("product_id").disabled = false;
                    document.getElementById("product_code").value = p.code;
                    document.getElementById("product_name").value = p.name;
                    document.getElementById("stock").value = p.actual_quantity;
                    priceInput.value = p.price;
                    suggestionsBox.innerHTML = "";
                    productSearch.value = p.code;
                });
                suggestionsBox.appendChild(div);
            });
        });

        quantityInput.addEventListener("input", function () {
            const quantity = parseInt(this.value);
            const price = parseFloat(priceInput.value);
            const stock = parseInt(stockInput.value);

            if (quantity > stock) {
                alert("Quantity cannot exceed stock available.");
                this.value = stock;
            }

            if (!isNaN(quantity) && !isNaN(price)) {
                totalDisplay.textContent = "$" + (quantity * price).toFixed(2);
            } else {
                totalDisplay.textContent = "$0.00";
            }
        });

        // ✅ Populate product dropdown based on selected shipment
        document.getElementById("shipment_id").addEventListener("change", function () {
            const shipmentId = parseInt(this.value);
            const productSelect = document.getElementById("product_id");
            productSelect.innerHTML = `<option value="">Select Product</option>`;

            if (!shipmentId) {
                productSelect.disabled = true;
                return;
            }

            const filteredProducts = allProducts.filter(p => p.shipment_id == shipmentId);

            filteredProducts.forEach(p => {
                const option = document.createElement("option");
                option.value = p.id;
                option.textContent = `${p.name} (${p.code}) - Stock: ${p.actual_quantity}`;
                productSelect.appendChild(option);
            });

            productSelect.disabled = false;
        });

        // ✅ Auto-fill fields when selecting from dropdown
        document.getElementById("product_id").addEventListener("change", function () {
            const selectedId = parseInt(this.value);
            const product = allProducts.find(p => p.id == selectedId);

            if (product) {
                document.getElementById("product_code").value = product.code;
                document.getElementById("product_name").value = product.name;
                document.getElementById("stock").value = product.actual_quantity;
                priceInput.value = product.price;
            }
        });
    });

</script>
</body>
</html>
