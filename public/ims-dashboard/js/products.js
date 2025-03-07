document.addEventListener("DOMContentLoaded", function () {
    loadProductData();

    const productForm = document.getElementById("product-form");
    if (productForm) {
        productForm.addEventListener("submit", function (event) {
            event.preventDefault();
            addProduct();
        });
    }
});

// ✅ Load Products into Table
function loadProductData() {
    fetch("http://localhost/ims/public/api/products", {
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(response => response.json())
    .then(products => {
        let productTable = document.getElementById("product-table");
        productTable.innerHTML = "";

        if (products.length === 0) {
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
                    <button onclick="openEditProductModal(${product.id}, '${product.name}', '${product.code}', ${product.original_quantity}, ${product.price}, ${product.cost}, '${product.category}', ${product.shipment_id})">Edit</button>
                    <button onclick="deleteProduct(${product.id})">Delete</button>
                </td>
            `;
            productTable.appendChild(row);
        });
    })
    .catch(error => console.error("Error loading product data:", error));
}


// ✅ Function to Add Product
function addProduct() {
    let name = document.getElementById("product_name")?.value;
    let code = document.getElementById("product_code")?.value;
    let originalQuantity = document.getElementById("original_quantity")?.value;
    let price = document.getElementById("price")?.value;
    let cost = document.getElementById("cost")?.value;
    let category = document.getElementById("category")?.value;
    let shipmentId = document.getElementById("shipment_id")?.value;

    // ✅ Check if any field is missing
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
            category, 
            shipment_id: shipmentId 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Product created successfully") {
            alert("Product added!");
            document.getElementById("addProductForm").style.display = "none";
            loadProductData();
        } else {
            alert("Error adding product: " + (data.message || "Unknown error"));
        }
    })
    .catch(error => console.error("Error:", error));
}

function updateProduct() {
    let id = document.getElementById("edit_product_id").value;
    let name = document.getElementById("edit_product_name").value;
    let code = document.getElementById("edit_product_code").value;
    let originalQuantity = document.getElementById("edit_original_quantity").value;
    let price = document.getElementById("edit_price").value;
    let cost = document.getElementById("edit_cost").value;
    let category = document.getElementById("edit_category").value;
    let shipmentId = document.getElementById("edit_shipment_id").value;

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
            category, 
            shipment_id: shipmentId 
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.message === "Product updated successfully") {
            alert("Product updated!");
            document.getElementById("editProductForm").style.display = "none";
            loadProductData(); // ✅ Reload Product List
        } else {
            alert("Error updating product: " + (data.message || "Unknown error"));
        }
    })
    .catch(error => console.error("Error updating product:", error));
}


// ✅ Function to Open Edit Modal with Data
function openEditProductModal(id, name, code, originalQuantity, price, cost, category, shipmentId) {
    document.getElementById("edit_product_id").value = id;
    document.getElementById("edit_product_name").value = name;
    document.getElementById("edit_product_code").value = code;
    document.getElementById("edit_original_quantity").value = originalQuantity;
    document.getElementById("edit_price").value = price;
    document.getElementById("edit_cost").value = cost;
    document.getElementById("edit_category").value = category;
    document.getElementById("edit_shipment_id").value = shipmentId;
    
    document.getElementById("editProductForm").style.display = "block";
}

// ✅ Function to Delete a Product
function deleteProduct(id) {
    if (!confirm("Are you sure you want to delete this product?")) return;

    fetch(`http://localhost/ims/public/api/products/${id}`, {
        method: "DELETE",
        headers: {
            "Authorization": "Bearer " + sessionStorage.getItem("token")
        }
    })
    .then(response => response.json())
    .then(data => {
        alert("Product deleted successfully");
        loadProductData();
    })
    .catch(error => console.error("Error deleting product:", error));
}
