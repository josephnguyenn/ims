// //document.addEventListener("DOMContentLoaded", function () {
//     loadProductData();

//     const productForm = document.getElementById("product-form");
//     if (productForm) {
//         productForm.addEventListener("submit", function (event) {
//             event.preventDefault();
//             addProduct();
//         });
//     }

document.addEventListener("DOMContentLoaded", function () {
    const editForm = document.getElementById("edit-product-form");
    if (editForm) {
        editForm.addEventListener("submit", function (event) {
            event.preventDefault();
            updateProduct();
        });
    }    
});

function getShipmentIdFromURL() {
    const params = new URLSearchParams(window.location.search);
    return params.get('shipment_id');
}

function loadProductData() {
    const shipmentId = getShipmentIdFromURL();
    const productTable = document.getElementById("product-table");

    if (!productTable) {
        console.error("❌ Product table element not found.");
        return;
    }

    const url = shipmentId
        ? `http://localhost/ims/public/api/products?shipment_id=${shipmentId}`
        : "http://localhost/ims/public/api/products";

    fetch(url, {
        headers: { "Authorization": "Bearer " + sessionStorage.getItem("token") }
    })
    .then(response => response.json())
    .then(products => {
        productTable.innerHTML = "";

        if (!products || products.length === 0) {
            productTable.innerHTML = "<tr><td colspan='10'>No products found.</td></tr>";
            return;
        }

        products.forEach(product => {
            let row = document.createElement("tr");
            row.innerHTML = `
                <td>${product.id}</td>
                <td>${product.name}</td>
                <td>${product.code}</td>
                <td>${product.original_quantity}</td>
                <td>${product.actual_quantity}</td>
                <td>${product.price}</td>
                <td>${product.cost}</td>
                <td>${product.total_cost}</td>
                <td>Shipment #${product.shipment_id}</td>
                <td>${product.expired_date || "N/A"}</td>
                <td>
                    <button onclick="openEditModal(${product.id})">Edit</button>
                    <button onclick="deleteProduct(${product.id})">Delete</button>
                </td>
            `;
            productTable.appendChild(row);
        });
    })
    .catch(error => console.error("❌ Error loading product data:", error));
}

function addProduct() {
    let name = document.getElementById("product_name").value;
    let code = document.getElementById("product_code").value;
    let originalQuantity = document.getElementById("original_quantity").value;
    let price = document.getElementById("price").value;
    let cost = document.getElementById("cost").value;
    let taxInput = document.getElementById("tax").value.trim();
    let tax = taxInput === "" ? null : parseFloat(taxInput);
    let category = document.getElementById("category").value;
    let shipmentId = document.getElementById("shipment_id").value;

    if (!name || !code || !originalQuantity || !price || !cost || !category || !shipmentId) {
        alert("Please fill in all required fields.");
        return;
    }

    fetch("http://localhost/ims/public/api/products", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ 
            name, 
            code, 
            original_quantity: originalQuantity, 
            price, 
            cost, 
            tax,
            category, 
            shipment_id: shipmentId 
        })  
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Product created successfully") {
            alert("Product added successfully!");
            window.location.reload();
        } else {
            alert("Error adding product: " + (data.message || "Unknown error"));
        }
    })
    .catch(error => console.error("Error:", error));
}

function openEditModal(productId) {
    console.log("edit_product_id", document.getElementById("edit_product_id")); // should not be null
    fetch(`http://localhost/ims/public/api/products/${productId}`, {
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(res => res.json())
    .then(product => {
        document.getElementById("edit_product_id").value = product.id;
        document.getElementById("edit_product_name").value = product.name;
        document.getElementById("edit_product_code").value = product.code;
        document.getElementById("edit_original_quantity").value = product.original_quantity;
        document.getElementById("edit_price").value = product.price;
        document.getElementById("edit_cost").value = product.cost;
        document.getElementById("edit_tax").value = product.tax || 0;
        document.getElementById("edit_category").value = product.category;
        document.getElementById("edit_shipment_id").value = product.shipment_id;

        openModal("editProductForm");
    })
    .catch(err => {
        console.error("❌ Failed to load product for edit", err);
        alert("Failed to load product for editing.");
    });
}


function updateProduct() {
    let id = document.getElementById("edit_product_id").value;
    console.log("Updating product with ID:", id); // ✅ Add here to debug
    
    let name = document.getElementById("edit_product_name").value;
    let code = document.getElementById("edit_product_code").value;
    let originalQuantity = document.getElementById("edit_original_quantity").value;
    let price = document.getElementById("edit_price").value;
    let cost = document.getElementById("edit_cost").value;
    let taxInput = document.getElementById("edit_tax").value.trim();
    let tax = taxInput === "" ? null : parseFloat(taxInput)
    let category = document.getElementById("edit_category").value;
    let shipmentId = document.getElementById("edit_shipment_id").value;

        // ✅ DEBUG: Check the data before sending
        console.log("Updating product with ID:", id);
        console.log({
            name,
            code,
            original_quantity: originalQuantity,
            price,
            cost,
            tax,
            category,
            shipment_id: shipmentId
        });

    fetch(`http://localhost/ims/public/api/products/${id}`, {
        method: "PUT",
        headers: {
            "Content-Type": "application/json",
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        },
        body: JSON.stringify({ 
            name, 
            code, 
            original_quantity: originalQuantity, 
            price, 
            cost, 
            tax,
            category, 
            shipment_id: shipmentId 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Product updated successfully") {
            alert("Product updated successfully!");
            window.location.reload();
        } else {
            alert("Error updating product: " + (data.message || "Unknown error"));
        }
    })
    .catch(error => console.error("Error updating product:", error));
}

function deleteProduct(id) {
    if (!confirm("Are you sure you want to delete this product?")) return;

    fetch(`http://localhost/ims/public/api/products/${id}`, {
        method: "DELETE",
        headers: { "Authorization": "Bearer " + sessionStorage.getItem("token") }
    })
    .then(response => response.json())
    .then(data => {
        alert("Product deleted successfully");
        window.location.reload();
    })
    .catch(error => console.error("Error deleting product:", error));
}

function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error(`❌ Modal with ID '${modalId}' not found.`);
        return;
    }
    modal.style.display = 'flex';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) {
        console.error(`❌ Modal with ID '${modalId}' not found.`);
        return;
    }
    modal.style.display = 'none';
}